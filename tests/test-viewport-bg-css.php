<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

use OptimoleWP\BgOptimizer\Lazyload;
use OptimoleWP\PageProfiler\Profile;
use OptimoleWP\Preload\Links;

/**
 * Page placeholders are replaced only where the plugin printed them in this request.
 */
class Test_Viewport_Bg_Css extends WP_UnitTestCase {

	const PROFILE_ID = 'bgcss1946';

	const WATCHER = '[class*="wp-block-cover"][style*="background-image"]';

	/**
	 * The marker text page content can know: it was fixed before markers carried a token.
	 */
	const STORED_MARKER = '/* OPTML_VIEWPORT_BG_SELECTORS */';

	const STYLE_HANDLE = 'optm_lazyload_noscript_style';

	/**
	 * Settings values replaced in setUp, restored in tearDown.
	 *
	 * @var array<string, mixed>
	 */
	private $previous_settings = [];

	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		foreach ( [ 'lazyload', 'lazyload_type' ] as $key ) {
			$this->previous_settings[ $key ] = $settings->get( $key );
		}
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],
		] );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'lazyload_type', 'viewport' );
		$this->init_replacers();
		add_filter( 'optml_page_profile_id', [ self::class, 'profile_id' ] );

		foreach ( [ Profile::DEVICE_TYPE_MOBILE, Profile::DEVICE_TYPE_DESKTOP ] as $device ) {
			Optml_Manager::instance()->page_profiler->store( self::PROFILE_ID, $device, [], [ self::WATCHER => [] ] );
		}
	}

	public function tearDown(): void {
		remove_filter( 'optml_page_profile_id', [ self::class, 'profile_id' ] );
		wp_deregister_style( self::STYLE_HANDLE );
		$links = new ReflectionProperty( Links::class, 'links' );
		$links->setAccessible( true );
		$links->setValue( null, [] );
		Profile::reset_current_profile();
		$settings = new Optml_Settings();
		foreach ( $this->previous_settings as $key => $value ) {
			$settings->update( $key, $value );
		}
		$this->init_replacers();
		parent::tearDown();
	}

	public static function profile_id(): string {
		return self::PROFILE_ID;
	}

	private function init_replacers(): void {
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();
	}

	/**
	 * The inline style the plugin prints in the head, with the general background CSS between its markers.
	 */
	private function plugin_style(): string {
		Optml_Main::instance()->admin->frontend_scripts();
		ob_start();
		wp_styles()->do_item( self::STYLE_HANDLE );

		return (string) ob_get_clean();
	}

	/**
	 * Page content, such as a comment, carrying a marker pair inside an attribute.
	 */
	private function comment(): string {
		return '<abbr title="' . self::STORED_MARKER . ' ' . self::STORED_MARKER . ' data-note=1 ">x</abbr>';
	}

	private function personalized_rule(): string {
		return 'html ' . self::WATCHER . ':not(.optml-bg-lazyloaded) { background-image: none !important; }';
	}

	public function test_marker_carries_a_random_token(): void {
		$this->assertMatchesRegularExpression( '/^\/\* OPTML_VIEWPORT_BG_SELECTORS [0-9a-f]{16} \*\/$/', Lazyload::get_marker() );
		$this->assertSame( Lazyload::get_marker(), Lazyload::get_marker(), 'The printed and the replaced marker are the same.' );
	}

	public function test_comment_markers_are_kept_when_the_plugin_prints_none(): void {
		// The AMP case: the plugin prints no style, so the comment holds the only marker pair.
		$html = '<html><head><title>Post</title></head><body><p>' . $this->comment() . '</p></body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $this->comment(), $replaced );
		$this->assertStringNotContainsString( $this->personalized_rule(), $replaced );
	}

	public function test_preload_placeholder_text_in_content_is_kept(): void {
		Links::add_link( [ 'url' => 'https://example.com/hero.jpg', 'priority' => 'high' ] );
		$content = '<textarea><script id=optmlpreload></script></textarea>';
		$html    = '<html><head>' . Optml_Admin::get_preload_links_placeholder() . '</head><body>' . $content . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $content, $replaced );
		$head = substr( $replaced, 0, strpos( $replaced, '</head>' ) );
		$this->assertMatchesRegularExpression( '/<link rel="preload" media="screen" href="[^"]*hero\.jpg"/', $head );
	}

	public function test_html_tag_copy_in_content_gets_no_class(): void {
		$add_class = static function (): array {
			return [ 'optml-test' ];
		};
		add_filter( 'optml_additional_html_classes', $add_class );
		$content = '<textarea><html lang="en"></textarea>';
		$html    = '<html lang="en"><head></head><body>' . $content . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );
		remove_filter( 'optml_additional_html_classes', $add_class );

		$this->assertMatchesRegularExpression( '/^<html class="[^"]*optml-test[^"]*" lang="en">/', $replaced );
		$this->assertStringContainsString( $content, $replaced );
	}

	public function test_debug_comment_is_added_only_after_the_document(): void {
		$_GET['optml_debug'] = 'true';
		$content = '<textarea></html></textarea>';
		$html    = '<html><head></head><body>' . $content . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );
		unset( $_GET['optml_debug'] );

		$this->assertStringContainsString( $content, $replaced );
		$this->assertSame( 1, substr_count( $replaced, 'plugin=optimole-wp' ) );
		$this->assertStringContainsString( '</body></html><!-- plugin=optimole-wp', $replaced );
	}

	public function test_optimizer_placeholder_text_in_content_is_kept(): void {
		// A page without a stored profile, so the plugin swaps its placeholder for the optimizer script.
		$profile_without_data = static function (): string {
			return 'bgcss1946-new';
		};
		add_filter( 'optml_page_profile_id', $profile_without_data, 20 );
		$content = '<textarea><script id=optmloptimizer></script></textarea>';
		$html    = '<html><head></head><body>' . $content . Optml_Admin::get_optimizer_script( true ) . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );
		remove_filter( 'optml_page_profile_id', $profile_without_data, 20 );

		$this->assertStringContainsString( $content, $replaced );
		$this->assertSame( 1, substr_count( $replaced, 'optimoleDataOptimizer' ) );
	}

	public function test_marker_printed_before_the_plugin_style_is_not_paired_with_it(): void {
		// Text another plugin prints in the head, such as a title built from user input.
		$title = '<title>Sale ' . self::STORED_MARKER . '</title>';
		$html  = '<html><head>' . $title . $this->plugin_style() . '</head><body><p>Body.</p></body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $title, $replaced );
		$this->assertStringContainsString( $this->personalized_rule(), $replaced );
	}
}
