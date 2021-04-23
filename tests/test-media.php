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
		if ( strpos($url, 'example.i.optimole.com' ) !== false ) {
			return array
			(
				'headers' => new Requests_Utility_CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => 'some image data',
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
		if ($url === "https://generateurls-prod.i.optimole.com/upload" ) {

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
		$this->assertContains( '/w:150/h:150/q:75/rt:fill/g:ce/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $replaced_content );
		$this->assertContains( '/w:150/h:150/q:eco/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $replaced_content );
		
	}

	public function test_image_processed() {
		
		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		$my_size_image = wp_get_attachment_image_src(self::$sample_attachement, 'my_size_crop' );

		$this->assertContains( 'example.i.optimole.com', $my_size_image[0] );
		$this->assertContains('/w:200/h:200/q:90/rt:fill/g:nowe/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $my_size_image[0]);

		$this->assertContains( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertContains( 'w:150/h:150/q:90/rt:fill/g:ce/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $image_thumbnail_size[0] );

		$this->assertContains( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertContains( 'w:300/h:200/q:90/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $image_medium_size[0] );
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
		$this->assertContains( 'w:150/h:150/q:90/rt:fill/g:ce/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1.jpg', $image_thumbnail_size[0] );

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
	public function test_custom_post_image_extraction () {
		$content = '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" hundred_percent_height_scroll="no" align_content="stretch" flex_align_items="flex-start" flex_justify_content="flex-start" hundred_percent_height_center_content="yes" equal_height_columns="no" container_tag="div" hide_on_mobile="small-visibility,medium-visibility,large-visibility" status="published" border_style="solid" box_shadow="no" box_shadow_blur="0" box_shadow_spread="0" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" enable_mobile="no" parallax_speed="0.3" background_blend_mode="none" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" absolute="off" absolute_devices="small,medium,large" sticky="off" sticky_devices="small-visibility,medium-visibility,large-visibility" sticky_transition_offset="0" scroll_offset="0" animation_direction="left" animation_speed="0.3" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0"][fusion_builder_row][fusion_builder_column type="1_3" type="1_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all"][fusion_imageframe image_id="144|full" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" margin_top="" margin_right="" margin_bottom="" margin_left="" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" 
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg[/fusion_imageframe][/fusion_builder_column][fusion_builder_column type="2_3" type="2_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all" element_content=""][fusion_imageframe image_id="180|fusion-1200" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" 
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png[/fusion_imageframe][/fusion_builder_column][/fusion_builder_row]';
		$images  = Optml_Manager::instance()->extract_urls_from_content( $content );
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg', $images[0]);
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png', $images[1]);

	}
}