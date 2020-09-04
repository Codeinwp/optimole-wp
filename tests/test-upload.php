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
class Test_Disable_Image_Sizes_Generation extends WP_UnitTestCase
{
	public static $image_upload;
	public function setUp()	{
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		
		] );
		$settings->update( 'cdn', 'enabled' );
		$settings->update( 'disable_image_sizes', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		
		self::$image_upload = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/3.jpg' );
	}
	public function test_disable_image_sizes_generate_upload() {

		$image_sizes = get_intermediate_image_sizes();
		$this->assertEquals( 'thumbnail', $image_sizes[0] );
		$this->assertEquals( 'medium', $image_sizes[1] );
		$this->assertEquals( 'large', $image_sizes[3] );
		foreach ( $image_sizes as $image_size ) {
			$image = wp_get_attachment_image_src( self::$image_upload,  $image_size );
			$this->assertEquals( 'http://example.org/wp-content/uploads/2020/09/3.jpg', $image[0] );
			$this->assertEquals( false, $image[3] );
		}
		$uploaded_image = wp_get_attachment_image_src( self::$image_upload,  'full' );
		$this->assertEquals( 'http://example.org/wp-content/uploads/2020/09/3.jpg', $uploaded_image[0] );
		$this->assertEquals( 700, $uploaded_image[1] );
		$this->assertEquals( 465, $uploaded_image[2] );
	}

}
