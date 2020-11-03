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
	const IMG_TAGS = '<!-- wp:image {"id":5,"sizeSlug":"medium"} -->
<figure class="wp-block-image size-large"><img src="https://example.i.optimole.com/BOxmgcE-_HsxvBf4/w:300/h:200/q:90/id:b19fed3de30366eb76682becf7645c7b/1.jpg" alt="" class="wp-image-1339"/></figure>
<!-- /wp:image --> ';


	public static $sample_post;
	public static $sample_attachement;
	/**
	 * Mock the api calls and remote images requests
	 */
	public function filter_pre_http_request( $false, $r, $url ) {

		if ($url === "https://generateurls-dev.mh.optml.cloud/upload" ) {

			$body = '{"uploadUrl":"https://uploadUrl","tableId":"579c7f7707ce87caa65fdf50c238a117" }';

			if ( strpos($r['body'], '"getUrl":"true"') !== false ) {
				$body = '{"getUrl": "getUrl"}';
			}
			
			return array
			(
				'headers' => new Requests_Utility_CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => $body,
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}
		if ($url === "https://uploadUrl" ) {
			return array
			(
				'headers' => new Requests_Utility_CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => '',
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}
		
		if ($url === "getUrl" ) {
			//the get url is used by download_url to create a temporary image for wp_handle_sideload
			//since we can not mock the external image stream response we move it manually over the temporary empty file
			copy(OPTML_PATH . 'assets/img/1.jpg', $r['filename']);
			return array
			(
				'headers' => new Requests_Utility_CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => '',
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}

		return $false;
	}

	public function setUp() {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'example',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org' ],

		] );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'offload_media', 'enabled' );
		$settings->update( 'quality', 90 );
		$settings->update( 'cdn', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		new Optml_Media_Offload();

		add_filter( 'pre_http_request', array($this,'filter_pre_http_request'), 10, 3 );
		self::$sample_post        = self::factory()->post->create( [
				'post_title'   => 'Test post',
				'post_content' => self::IMG_TAGS,
			]
		);
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/1.jpg', self::$sample_post );
	}
	public function test_page_images_process() {
		
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertContains( 'example.i.optimole.com', $content );
		$settings = new Optml_Settings();
		$settings->update( 'quality', 75 );
		$settings->update( 'service_data', [
			'cdn_key'    => 'whatever',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		//workaround for defining costum domain inside tests
		Optml_Config::init(
			array(
				'key'    => 'whatever',
				'secret' => 'test',
				'domain' => 'https://my_costum_domain',
			)
		);
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals(  3, substr_count($replaced_content, 'https://my_costum_domain'));
		$this->assertContains( '/w:150/h:150/q:75/rt:fill/g:ce/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $replaced_content );
		$this->assertContains( '/w:150/h:150/q:eco/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $replaced_content );
		
	}

	public function test_image_processed() {
		
		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		$my_size_image = wp_get_attachment_image_src(self::$sample_attachement, 'my_size_crop' );

		$this->assertContains( 'example.i.optimole.com', $my_size_image[0] );
		$this->assertContains('/w:200/h:200/q:90/rt:fill/g:nowe/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $my_size_image[0]);

		$this->assertContains( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertContains( 'w:150/h:150/q:90/rt:fill/g:ce/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $image_thumbnail_size[0] );

		$this->assertContains( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertContains( 'w:300/h:200/q:90/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $image_medium_size[0] );
		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertContains( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );
	}

	public function test_image_sync() {
		Optml_Media_Offload::rollback_images( 100 );
		Optml_Media_Offload::upload_images( 100 );


		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertContains( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');

		$this->assertContains( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertContains( 'w:150/h:150/q:90/rt:fill/g:ce/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/1.jpg', $image_thumbnail_size[0] );

		$this->assertContains( 'example.i.optimole.com', $image_medium_size );
		$this->assertContains( 'w:300/h:200/q:90/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117', $image_medium_size[0] );
	}

	public function test_image_rollback() {

		Optml_Media_Offload::rollback_images( 100 );
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );
		
		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);
		
		$this->assertNotContains( 'id:', $image_meta['file'] );
		$this->assertContains( '-300x200', $image_meta['sizes']['medium']['file'] );
		$this->assertContains( '-150x150', $image_meta['sizes']['thumbnail']['file'] );
	}
}