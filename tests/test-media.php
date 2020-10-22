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
class Test_Media extends WP_UnitTestCase {
	const IMG_TAGS = '<!-- wp:image {"id":7,"sizeSlug":"medium"} -->
<figure class="wp-block-image size-large"><img src="https://mlynllm40tdp.i.optimole.com/BOxmgcE-_HsxvBf4/w:300/h:200/q:90/id:b19fed3de30366eb76682becf7645c7b/domain:example.org/1-5.jpg" alt="" class="wp-image-1339"/></figure>
<!-- /wp:image --> ';


	public static $sample_post;
	public static $sample_attachement;

	public function setUp() {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'mlynllm40tdp',
			//to do pass it to env variable
			'cdn_secret' => '',
			'whitelist'  => [ 'example.com', 'example.org' ],

		] );
		$settings->update( 'lazyload', 'disabled' );
		$settings->update( 'offload_media', 'enabled' );
		$settings->update( 'quality', 90 );
		$settings->update( 'cdn', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		new Optml_Media_Offload();

		self::$sample_post        = self::factory()->post->create( [
				'post_title'   => 'Test post',
				'post_content' => self::IMG_TAGS,
			]
		);
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/1.jpg', self::$sample_post );
	}


	public function test_image_processed() {

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		
		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_thumbnail_size );
		$this->assertContains( 'w:150/h:150/q:90/id:', $image_thumbnail_size );
		
		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_medium_size );
		$this->assertContains( 'w:300/h:200/q:90/id:', $image_medium_size );
		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);
		
		$this->assertContains( 'w:auto/h:auto/q:90/id:', $image_meta['file'] );
		
		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');

		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_thumbnail_size );
		$this->assertContains( 'w:150/h:150/q:90/id:', $image_thumbnail_size );

		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_medium_size );
		$this->assertContains( 'w:300/h:200/q:90/id:', $image_medium_size );
	}
	
	public function test_image_rollback() {

		Optml_Media_Offload::rollback_images(array(self::$sample_attachement));
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertNotContains( 'id:', $image_meta['file'] );
		$this->assertContains( '-300x200', $image_meta['sizes']['medium']['file'] );
		$this->assertContains( '-150x150', $image_meta['sizes']['thumbnail']['file'] );
	}
	public function test_image_sync() {
		
		Optml_Media_Offload::upload_images( array(self::$sample_attachement) );

		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );
		
		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);
		
		$this->assertContains( 'w:auto/h:auto/q:90/id:', $image_meta['file'] );
		
		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		
		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_thumbnail_size );
		$this->assertContains( 'w:150/h:150/q:90/id:', $image_thumbnail_size );
		
		$this->assertContains( 'mlynllm40tdp.i.optimole.com', $image_medium_size );
		$this->assertContains( 'w:300/h:200/q:90/id:', $image_medium_size );
	}
}