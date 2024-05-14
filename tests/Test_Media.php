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
<figure class="wp-block-image size-large"><img src="https://example.i.optimole.com/BOxmgcE-_HsxvBf4/w:300/h:200/q:90/id:b19fed3de30366eb76682becf7645c7b/sample-test.jpg" alt="" class="wp-image-1339"/></figure>
<!-- /wp:image --> ';
	use Optml_Dam_Offload_Utils;
	public static $files = [
		'sample-test',
		'1PQ7p',
		'3000x3000',
		'special-characters-•⋿∀'
	];
	public static $sample_post;
	public static $sample_attachement;
	private static $sample_attachement_upper_case;
	private static $sample_attachment_scaled;
	public static $error_mark = false;
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
					'code' => self::$error_mark ? 500 : 200,
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
			copy(OPTML_PATH . 'tests/assets/sample-test.jpg', $r['filename']);
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

	public function setUp() : void {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'example',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org' ],

		] );
		$settings->update( 'no_script', 'enabled' );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'offload_media', 'enabled' );
		$settings->update( 'lazyload_placeholder', 'disabled' );
		$settings->update( 'quality', 90 );
		$settings->update( 'cdn', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		Optml_Media_Offload::instance();


		add_filter( 'pre_http_request', array($this,'filter_pre_http_request'), 10, 3 );
		self::$sample_post        = self::factory()->post->create( [
				'post_title'   => 'Test post',
				'post_content' => self::IMG_TAGS,
			]
		);

		self::unlinkRecursive(WP_CONTENT_DIR . '/uploads/**', self::$files);
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[0].'.jpg' );
		self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[1].'.jpg');
		self::$sample_attachement_upper_case = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[1].'.jpg' );
		self::$sample_attachment_scaled = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[2].'.jpg' );


	}
	public function set_editor(){
		return array( 'WP_Image_Editor_Mock' );
	}
	static function unlinkRecursive( $pattern, $files ) {
		global $wp_filesystem;
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
		foreach ( glob( $pattern ) as $file ) {
			if ( is_dir( $file ) ) {
				// Recursive call to handle subdirectories
				self::unlinkRecursive( $file . '/*', $files );
			} else {
				foreach ( $files as $file_name ) {
					if ( strpos( basename( $file ), $file_name ) !== false ) {
						$wp_filesystem->delete( $file );
					}
				}
			}
		}
	}
	public function test_retryable_error() {
		Optml_Media_Offload::instance()->rollback_and_update_images([self::$sample_attachement]);
		self::$error_mark = true;
		Optml_Media_Offload::instance()->upload_and_update_existing_images([self::$sample_attachement]);
		$this->assertEquals(1, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );

		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringNotContainsString( 'example.i.optimole.com', $content );
		Optml_Media_Offload::instance()->upload_images( 2 );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertEquals(2, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertStringNotContainsString( 'example.i.optimole.com', $content );
		self::$error_mark = false;
		Optml_Media_Offload::instance()->upload_images( 2 );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringContainsString( 'example.i.optimole.com', $content );
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true ) );
	}
	public function test_retryable_error_and_error() {
		Optml_Media_Offload::instance()->rollback_and_update_images([self::$sample_attachement]);
		self::$error_mark = true;
		Optml_Media_Offload::instance()->upload_and_update_existing_images([self::$sample_attachement]);
		$this->assertEquals(1, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringNotContainsString( 'example.i.optimole.com', $content );

		update_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, 5);
		Optml_Media_Offload::instance()->upload_images( 2 );

		$this->assertNotEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );
		self::$error_mark = false;
	}
	public function test_duplicated_image() {

		$content =  wp_get_attachment_image( self::$sample_attachement_upper_case );
		$this->assertStringContainsString(  "src=\"https://example.i.optimole.com/w:150/h:150/rt:fill/g:ce/q:mauto/process:". self::$sample_attachement_upper_case ."/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1PQ7p-2.jpg\" ", $content);

	}
	public function test_page_images_process() {
		Optml_Tag_Replacer::$lazyload_skipped_images = 4;
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringContainsString( 'example.i.optimole.com', $content );
		$settings = new Optml_Settings();
		$settings->update( 'quality', 75 );
		$settings->update( 'autoquality', 'disabled' );
		$settings->update( 'best_format', 'enabled' );
		$settings->update( 'avif', 'enabled' );
		$settings->update( 'service_data', [
			'cdn_key'    => 'whatever',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		Optml_Config::init(
			array(
				'key'    => 'whatever',
				'secret' => 'test',
				'domain' => 'my_costum_domain',
			)
		);
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals(  7, substr_count($replaced_content, 'https://my_costum_domain'));
		$this->assertStringContainsString( '/w:150/h:150/q:75/rt:fill/g:ce/f:best/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $replaced_content );
		$this->assertStringContainsString( '/w:150/h:150/q:eco/f:best/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $replaced_content );
	}

	public function test_image_processed() {

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		$my_size_image = wp_get_attachment_image_src(self::$sample_attachement, 'my_size_crop' );

		$this->assertStringContainsString( 'example.i.optimole.com', $my_size_image[0] );
		$this->assertStringContainsString('/w:200/h:200/rt:fill/g:nowe/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $my_size_image[0]);

		$this->assertStringContainsString( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertStringContainsString( '/w:150/h:150/rt:fill/g:ce/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_thumbnail_size[0] );

		$this->assertStringContainsString( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertStringContainsString( '/w:300/h:200/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_medium_size[0] );
		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringContainsString( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );
	}

	public function test_image_sync() {
		Optml_Media_Offload::instance()->rollback_images( 100 );
		Optml_Media_Offload::instance()->upload_images( 100 );


		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringContainsString( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');

		$this->assertStringContainsString( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertStringContainsString( '/w:150/h:150/rt:fill/g:ce/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_thumbnail_size[0] );


		$this->assertStringContainsString( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertStringContainsString( '/w:300/h:200/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117', $image_medium_size[0] );
	}

	public function test_image_rollback() {

		Optml_Media_Offload::instance()->rollback_images( 100 );
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringNotContainsString( 'id:', $image_meta['file'] );
		$this->assertStringContainsString( '-300x200', $image_meta['sizes']['medium']['file'] );
		$this->assertStringContainsString( '-150x150', $image_meta['sizes']['thumbnail']['file'] );
	}
	public function test_custom_post_image_extraction () {
		$content = '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" hundred_percent_height_scroll="no" align_content="stretch" flex_align_items="flex-start" flex_justify_content="flex-start" hundred_percent_height_center_content="yes" equal_height_columns="no" container_tag="div" hide_on_mobile="small-visibility,medium-visibility,large-visibility" status="published" border_style="solid" box_shadow="no" box_shadow_blur="0" box_shadow_spread="0" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" enable_mobile="no" parallax_speed="0.3" background_blend_mode="none" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" absolute="off" absolute_devices="small,medium,large" sticky="off" sticky_devices="small-visibility,medium-visibility,large-visibility" sticky_transition_offset="0" scroll_offset="0" animation_direction="left" animation_speed="0.3" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0"][fusion_builder_row][fusion_builder_column type="1_3" type="1_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all"][fusion_imageframe image_id="144|full" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" margin_top="" margin_right="" margin_bottom="" margin_left="" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100"
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg[/fusion_imageframe][/fusion_builder_column][fusion_builder_column type="2_3" type="2_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all" element_content=""][fusion_imageframe image_id="180|fusion-1200" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100"
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png[/fusion_imageframe][/fusion_builder_column][/fusion_builder_row]';
		$images  = Optml_Manager::instance()->extract_urls_from_content( $content );
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg', $images[0]);
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png', $images[1]);

	}

	/**
	 * Test images with special characters.
	 */
	public function test_special_characters_upload() : void {
		$special_character_attachment = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/special-characters-•⋿∀.jpg' );
		$content = wp_get_attachment_image( $special_character_attachment );

		$this->assertStringContainsString(  "src=\"https://example.i.optimole.com/w:150/h:150/rt:fill/g:ce/q:mauto/process:". $special_character_attachment ."/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/special-characters-•⋿∀.jpg\" ", $content );

	}

	/**
	 * Test if the svg upload works.
	 */
	public function test_svg_upload() : void {
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.svg' );
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );
	}

	public function test_maybe_strip_scaled() {
		$scaled_url = 'https://4d31-93-114-162-196.ngrok-free.app/wp-content/uploads/2023/10/wesley-tingey-NRwAGwJLGH4-unsplash-1-scaled.jpg';
		$normal_url = 'https://4d31-93-114-162-196.ngrok-free.app/wp-content/uploads/2023/10/wesley-tingey-NRwAGwJLGH4-unsplash-1.jpg';

		$this->assertEquals( Optml_Media_Offload::instance()->maybe_strip_scaled( $scaled_url ), $normal_url );
		$this->assertEquals( Optml_Media_Offload::instance()->maybe_strip_scaled( $normal_url ), $normal_url );
	}

	public function test_filter_saved_data() {
		// Sample Attachment:

		// Full size
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachement, 'full' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:700/h:467', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:700/h:467', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );

		// Medium size
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachement, 'medium' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:300/h:200', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:300/h:200', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('300x200.jpg', $replaced['post_content'] );

		// Scaled attachment:
		// Full size.
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachment_scaled, 'full' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:2560/h:2560', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:2560/h:2560', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('-scaled.jpg', $replaced['post_content'] );

		// Medium size.
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachment_scaled, 'medium' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:300/h:300', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:300/h:300', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('300x300.jpg', $replaced['post_content'] );
	}
	public function test_replace_alternative_domain(){
		$attachment = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[0].'.jpg' );
		$url = Optml_Media_Offload::get_original_url( $attachment );
		$this->assertEquals( $this->attachment_url_to_post_id( $url ), $attachment );
		$url = str_replace('example.org', 'www.example.org', $url);
		$this->assertEquals( $this->attachment_url_to_post_id( $url ), $attachment );

		$scaled_url = Optml_Media_Offload::get_original_url( self::$sample_attachment_scaled );

		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
		$scaled_url = str_replace('-scaled','',$scaled_url);

		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
		$scaled_url = str_replace('example.org', 'www.example.org', $scaled_url);
		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
	}
	public function test_replace_urls_in_editor_content() {
		Optml_Attachment_Cache::reset();
		// Sample attachment:
		$original_url = Optml_Media_Offload::get_original_url( self::$sample_attachement );
		$extension = pathinfo( $original_url, PATHINFO_EXTENSION );
		$medium_size_url = str_replace( '.' . $extension, '-300x200.' . $extension, $original_url );

		// Full size.
		$content_before_replace = sprintf( '<img src="%s" />', $original_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( 'w:auto/h:auto', $replaced_content );
		$this->assertStringNotContainsString( 'w:auto/h:auto', $content_before_replace );

		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced_content );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $content_before_replace );

		// Medium size.
		$content_before_replace = sprintf( '<img src="%s" />', $medium_size_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( 'w:300/h:200', $replaced_content );
		$this->assertStringNotContainsString( 'w:300/h:200', $content_before_replace );

		$this->assertStringContainsString( '300x200.jpg', $content_before_replace );
		$this->assertStringNotContainsString( '300x200.jpg', $replaced_content );

		// Scaled attachment:
		$original_url = Optml_Media_Offload::get_original_url( self::$sample_attachment_scaled );
		$extension = pathinfo( $original_url, PATHINFO_EXTENSION );

		$content_before_replace = sprintf( '<img src="%s" />', $original_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( '-scaled.'.$extension, $content_before_replace );
		$this->assertStringNotContainsString( '-scaled.'.$extension, $replaced_content );

	}

	public function test_alter_attachment_image_src() {
		$test_data = [
			'full' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'full', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:700/h:467'
				]
			],
			'medium' =>  [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'medium', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:300/h:200'
				]
			],
			'thumb' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'thumbnail', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:150/h:150',
					'rt:fill'
				]
			],
			'custom' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, [10, 20], false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:10/h:20',
					'rt:fill'
				]
			],
			'scaled-full' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, 'full', false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:2560/h:2560'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			],
			'scaled-thumb' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, 'thumbnail', false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:150/h:150',
					'rt:fill'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			],
			'scaled-custom' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, [10, 20], false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:10/h:20',
					'rt:fill'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			]
		];

		foreach ( $test_data as $args ) {
			$data = $args['data'];

			if( isset( $args['contains'] ) ) {
				foreach ( $args['contains'] as $string ) {
					$this->assertStringContainsString( $string, $data[0] );
				}
			}

			if( isset( $args['not_contain'] ) ) {
				foreach ( $args['not_contain'] as $string ) {
					$this->assertStringNotContainsString( $string, $data[0] );
				}
			}
		}
	}
}