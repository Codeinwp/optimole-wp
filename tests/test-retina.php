<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Generic.
 */
class Test_Retina extends WP_UnitTestCase {
	public static $sample_attachement;
	public function setUp(): void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->reset();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.org' ], 
		] );
		$settings->update( 'api_key', 'test123' );
		$settings->update( 'retina_images', 'enabled' ); 
		$settings->update( 'lazyload', 'enabled' ); 
		$settings->update( 'lazyload_type', 'all' ); 
		
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init(); 
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/1PQ7p.jpg' );
	}

	public function test_retina_images_no_sizes() {
		$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );
		 $content = wp_get_attachment_image( $sample_attachement, 'full' );
		 $this->assertStringContainsString( 'dpr:2', $content );
	} 
	public function test_retina_images_with_sizes() {
		$content = wp_get_attachment_image( self::$sample_attachement, 'thumbnail' );
		$this->assertStringContainsString( 'dpr:2', $content );
		$content = wp_get_attachment_image( self::$sample_attachement, 'medium' );
		$this->assertStringContainsString( 'dpr:2', $content );
		$content = wp_get_attachment_image( self::$sample_attachement, 'full' );
		$this->assertStringContainsString( 'dpr:2', $content );
	}

	//here the retina is generated by the js script.
	public function test_retina_images_with_lazyload() { 
		$content = wp_get_attachment_image( self::$sample_attachement, 'full', false );
		
		$replaced_content = Optml_Manager::instance()->replace_content( $content ); 
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );

		$this->assertStringNotContainsString( 'dpr:2', $replaced_content );

	} 

	public function test_retina_images_with_native_lazyload() {
		$settings = new Optml_Settings();
		$settings->update( 'native_lazyload', 'enabled' ); 
		$content = wp_get_attachment_image( self::$sample_attachement, 'full', false );
		
		$replaced_content = Optml_Manager::instance()->replace_content( $content ); 
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( 'loading="lazy"', $replaced_content );
		$this->assertStringContainsString( 'dpr:2', $replaced_content );
	}

	public static function tearDownAfterClass(): void
    {
		$settings = new Optml_Settings();
		$settings->update( 'retina_images', 'disabled' );  
		$settings->update( 'native_lazyload', 'disabled' ); 
		$settings->update( 'lazyload_type', 'fixed' );
    }
 
}
