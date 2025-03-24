<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2023, Hardeep Asrani
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Preloading.
 */
class Test_Preloading extends WP_UnitTestCase {
	public static $sample_attachement;

	public function setUp() : void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],

		] );
		$settings->update( 'lazyload', 'disabled' );
		$settings->update( 'native_lazyload', 'disabled' );
		$settings->update( 'video_lazyload', 'disabled' );
		$settings->update( 'lazyload_placeholder', 'disabled' );
		$settings->update( 'no_script', 'disabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );
	}

	public function test_preloading_header_image() {

		$content = get_post( self::$sample_attachement );
		$image   = wp_get_attachment_metadata( self::$sample_attachement );

		$header_image_data = (object) array(
			'attachment_id' => $content->ID,
			'url'           => $content->guid,
			'thumbnail_url' => $content->guid,
			'height'        => $image['height'],
			'width'         => $image['width'],
		);

		set_theme_mod( 'header_image', $header_image_data->url );
		set_theme_mod( 'header_image_data', $header_image_data );

		$header = get_header_image_tag();
		$this->assertStringContainsString( 'fetchpriority="high"', $header );

		// Test it doesn't add the attribute when called again.
		$header = get_header_image_tag();
		$this->assertStringNotContainsString( 'fetchpriority="high"', $header );
	}

	public function test_preloading_logo() {
		set_theme_mod( 'custom_logo', self::$sample_attachement );
		$logo = get_custom_logo();
		$this->assertStringContainsString( 'fetchpriority="high"', $logo );

		// Test it doesn't add the attribute when called again.
		$logo = get_custom_logo();
		$this->assertStringNotContainsString( 'fetchpriority="high"', $logo );
	}
}
