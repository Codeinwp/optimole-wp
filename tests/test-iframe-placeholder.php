<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */
class Test_Iframe_Placeholder extends WP_UnitTestCase {

	const IFRAME = '<iframe width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" allowfullscreen></iframe>';

	const PLACEHOLDER_SELECTOR = 'iframe[data-opt-src]:not([data-opt-lazy-loaded])';

	/**
	 * The fixed comment the plugin used to print and replace, which page content can hold.
	 */
	const STORED_MARKER = '/** optmliframelazyloadplaceholder */';

	/**
	 * Settings values replaced in setUp, restored in tearDown.
	 *
	 * @var array<string, mixed>
	 */
	private $previous_settings = [];

	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		foreach ( [ 'lazyload', 'video_lazyload' ] as $key ) {
			$this->previous_settings[ $key ] = $settings->get( $key );
		}
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],
		] );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'video_lazyload', 'enabled' );
		$this->reset_found_iframe();
		$this->init_replacers();
	}

	public function tearDown(): void {
		$settings = new Optml_Settings();
		foreach ( $this->previous_settings as $key => $value ) {
			$settings->update( $key, $value );
		}
		$this->reset_found_iframe();
		$this->init_replacers();
		parent::tearDown();
	}

	private function reset_found_iframe(): void {
		$found_iframe = new ReflectionProperty( Optml_Lazyload_Replacer::class, 'found_iframe' );
		$found_iframe->setAccessible( true );
		$found_iframe->setValue( null, false );
	}

	private function init_replacers(): void {
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();
	}

	/**
	 * The markup the plugin prints in the head.
	 */
	private function plugin_head(): string {
		ob_start();
		Optml_Main::instance()->admin->inline_bootstrap_script();

		return (string) ob_get_clean();
	}

	private function comment_link(): string {
		return '<a href="https://example.com/" title="' . self::STORED_MARKER . ' autofocus onfocus=alert(1) x=">hi</a>';
	}

	public function test_plugin_head_holds_the_placeholder_style(): void {
		$this->assertStringContainsString( self::PLACEHOLDER_SELECTOR, $this->plugin_head() );
	}

	public function test_plugin_head_has_no_placeholder_style_without_video_lazyload(): void {
		( new Optml_Settings() )->update( 'video_lazyload', 'disabled' );

		$this->assertStringNotContainsString( self::PLACEHOLDER_SELECTOR, $this->plugin_head() );
	}

	public function test_marker_text_in_the_page_is_kept(): void {
		$title = '<title>Search ' . self::STORED_MARKER . '</title>';
		$html  = '<html><head>' . $title . $this->plugin_head() . '</head><body>' . self::IFRAME . $this->comment_link() . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $title, $replaced );
		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertSame( 1, substr_count( $replaced, self::PLACEHOLDER_SELECTOR ) );
	}

	public function test_head_end_text_in_the_page_is_kept(): void {
		$script = '<script>var end = "</head>";</script>';
		$html   = '<html><head><!-- </head> --></head><body>' . self::IFRAME . $script . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( '<head><!-- </head> --></head>', $replaced );
		$this->assertStringContainsString( $script, $replaced );
		$this->assertStringNotContainsString( self::PLACEHOLDER_SELECTOR, $replaced );
	}

	public function test_marker_in_partial_content_is_not_expanded(): void {
		$replaced = Optml_Manager::instance()->replace_content( self::IFRAME . $this->comment_link(), true );

		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertStringNotContainsString( self::PLACEHOLDER_SELECTOR, $replaced );
	}

	public function test_placeholder_css_cannot_close_an_attribute(): void {
		$this->assertDoesNotMatchRegularExpression( '/["\'<>&]/', Optml_Lazyload_Replacer::IFRAME_PLACEHOLDER_CLASS );
	}
}
