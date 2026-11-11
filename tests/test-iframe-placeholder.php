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
	 * The marker text page content can know: it was fixed before markers carried a token.
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

	private function comment_link(): string {
		return '<a href="https://example.com/" title="' . self::STORED_MARKER . ' autofocus onfocus=alert(1) x=">hi</a>';
	}

	public function test_marker_in_page_content_is_not_expanded(): void {
		$html = '<html><head><style>' . Optml_Lazyload_Replacer::get_iframe_temp_comment() . '</style></head><body>' . self::IFRAME . $this->comment_link() . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertSame( 1, substr_count( $replaced, self::PLACEHOLDER_SELECTOR ) );
		$this->assertStringContainsString( self::PLACEHOLDER_SELECTOR, substr( $replaced, 0, stripos( $replaced, '</head>' ) ) );
	}

	public function test_marker_in_the_title_is_not_expanded(): void {
		$title = '<title>Search ' . self::STORED_MARKER . '</title>';
		$html  = '<html><head>' . $title . '<style>' . Optml_Lazyload_Replacer::get_iframe_temp_comment() . '</style></head><body>' . self::IFRAME . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $title, $replaced );
		$this->assertSame( 1, substr_count( $replaced, self::PLACEHOLDER_SELECTOR ) );
	}

	public function test_marker_in_partial_content_is_not_expanded(): void {
		$replaced = Optml_Manager::instance()->replace_content( self::IFRAME . $this->comment_link(), true );

		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertStringNotContainsString( self::PLACEHOLDER_SELECTOR, $replaced );
	}

	public function test_placeholder_css_cannot_close_an_attribute(): void {
		$this->assertDoesNotMatchRegularExpression( '/["\'<>&]/', Optml_Lazyload_Replacer::IFRAME_PLACEHOLDER_CLASS );
	}

	public function test_marker_is_expanded_when_head_end_tag_is_omitted(): void {
		$html = '<html><head><style>' . Optml_Lazyload_Replacer::get_iframe_temp_comment() . '</style><body class="post">' . self::IFRAME . $this->comment_link() . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertSame( 1, substr_count( $replaced, self::PLACEHOLDER_SELECTOR ) );
		$this->assertStringContainsString( self::PLACEHOLDER_SELECTOR, substr( $replaced, 0, strpos( $replaced, '<body' ) ) );
	}

	public function test_body_only_marker_is_not_expanded(): void {
		$html = '<html><head><title>t</title></head><body>' . self::IFRAME . $this->comment_link() . '</body></html>';

		$replaced = Optml_Manager::instance()->replace_content( $html );

		$this->assertStringContainsString( $this->comment_link(), $replaced );
		$this->assertStringContainsString( Optml_Lazyload_Replacer::IFRAME_PLACEHOLDER_STYLE . '</head>', $replaced );
	}
}
