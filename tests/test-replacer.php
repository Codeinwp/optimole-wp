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
class Test_Replacer extends WP_UnitTestCase {
	const IMG_TAGS = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" /></div></div> ';
	const IMG_TAGS_WITH_SRCSET = '<img class="alignnone size-full wp-image-26" src="http://example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg" alt="" width="1450" height="740" srcset="http://example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg 1450w, http://example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-300x153.jpg 300w, http://example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-768x392.jpg 768w, http://example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-1024x523.jpg 1024w" sizes="(max-width: 1450px) 100vw, 1450px"> ';
	const IMG_TAGS_PNG = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.png" width="2000" height="1200" alt="Test" /></div></div>';
	const IMG_URLS = '
	http://example.org/wp-content/themes/test/assets/images/header.png 
	http://example.org/wp-content/themes/test/assets/images/header.jpeg
	http://example.org/wp-content/plugins/optimole-wp/assets/img/logo.png 
	 ';
	const CSS_STYLE = '
	<style>
	.body{
	
		background-image:url("http://example.org/wp-content/themes/test/assets/images/header-300x300.png");
	}</style>
	 ';
	const WRONG_EXTENSION = '   http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif   ';
	const IMAGE_SIZE_DATA = '
		http://example.org/wp-content/uploads/optimole-wp/assets/img/logo-282x123.png
		http://example.org/wp-content/plugins/optimole-wp/assets/img/test-282x123.png
	';
	const IMAGE_SIZE_NO_CLASS = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header-100x100.png" alt="Test" /></div></div>';

	const ELEMENTOR_DATA = '[{"id":"428f250c","elType":"section","settings":{"structure":"<img alt=\"\"src=\"https:\/\/www.example.org\/wp-content\/uploads\/2018\/05\/test2.png\" \/>","content_width":{"unit":"px","size":1140},"content_position":"middle","gap":"extended","padding":{"unit":"px","top":"10","right":"0","bottom":"10","left":"0","isLinked":false},"padding_mobile":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"1b041a88","elType":"column","settings":{"_column_size":25,"_inline_size":20.66,"_inline_size_tablet":25,"_inline_size_mobile":50,"content_position":"top"},"elements":[{"id":"34d685ef","elType":"widget","settings":{"image":{"id":36009,"url":"https:\/\/www.example.org\/wp-content\/uploads\/2018\/05\/codeinwp-logo.svg"},"image_size":"full","link_to":"custom","link":{"url":"https:\/\/www.example.org\/","is_external":"","nofollow":""},"align":"left","width":{"unit":"px","size":120},"space":{"unit":"%","size":100},"opacity":{"unit":"px","size":1},"_margin":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"_element_id":"logo"},"elements":[],"widgetType":"image"}],"isInner":false},{"id":"437f5756","elType":"column","settings":{"_column_size":50,"_inline_size":71.992000000000004,"_inline_size_tablet":70,"_inline_size_mobile":40,"padding_mobile":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"3c7d3ebf","elType":"widget","settings":{"align_items":"right","pointer":"none","color_menu_item":"#0a4266","menu_typography_typography":"custom","menu_typography_font_weight":"bold","menu_typography_text_transform":"lowercase","color_menu_item_hover":"#ec4646","color_menu_item_active":"#ec4646","menu_typography_font_size":{"unit":"px","size":18},"_margin":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"indicator":"none","dropdown":"mobile","full_width":"stretch","menu_typography_font_size_tablet":{"unit":"px","size":18},"padding_horizontal_menu_item_tablet":{"unit":"px","size":14},"toggle_size":{"unit":"px","size":25},"_padding":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"color_dropdown_item":"#0a4266","color_dropdown_item_hover":"#ec4646","background_color_dropdown_item_hover":"rgba(0,0,0,0)","dropdown_typography_typography":"custom","dropdown_typography_font_family":"proxima-nova","dropdown_typography_font_size":{"unit":"px","size":16},"menu_typography_font_size_mobile":{"unit":"px","size":18},"dropdown_typography_font_size_mobile":{"unit":"px","size":25},"dropdown_typography_text_transform":"lowercase","menu":"main-menu-homepage"},"elements":[],"widgetType":"nav-menu"}],"isInner":false},{"id":"7fafd26c","elType":"column","settings":{"_column_size":25,"_inline_size":7.3479999999999999,"_inline_size_tablet":5,"_inline_size_mobile":2},"elements":[{"id":"8f16004","elType":"widget","settings":{"image":{"url":"https:\/\/www.example.org\/wp-content\/uploads\/2018\/05\/test.png","id":36135},"image_size":"custom","width":{"unit":"px","size":30},"space":{"unit":"%","size":30},"_element_id":"header-trigger"},"elements":[],"widgetType":"image"}],"isInner":false}],"isInner":false}]';

	const TEST_STAGING = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="https://www.example.org/wp-content/uploads/2018/05/brands.png">
				</div>
			</div>';
	const TEST_WRONG_URLS = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="https://www.codeinwp.org/wp-content/uploads/2018/05/brands.png">https://www.codeinwp.org/wp-content/uploads/2018/05/brands.png
				</div>
			</div>';

	public static $sample_post;
	public static $sample_attachement;


	public function setUp() {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],

		] );
		$settings->update( 'lazyload', 'disabled' );

		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_post        = self::factory()->post->create( [
				'post_title'   => 'Test post',
				'post_content' => self::IMG_TAGS
			]
		);
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );

	}

	public function test_wc_json_replacement() {
		$html = [
			'image' => "https://www.example.org/wp-content/uploads/2018/05/brands.png",
			'image2' => "https://www.example.org/wp-content/uploads/2018/05/brands.png?test=123",
			'image3' => "https://www.example.org/wp-content/uploads/2018/05/brands.png?test=123&anther=test",
		];


		$html = wp_json_encode( $html );
		$html = _wp_specialchars( $html, ENT_QUOTES, 'UTF-8', true );

		$replaced_content = Optml_Manager::instance()->process_urls_from_content( $html );
		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );

		$replaced_content = wp_specialchars_decode( $replaced_content, ENT_QUOTES );
		$replaced_content = json_decode( $replaced_content, true );

		$this->assertArrayHasKey( 'image', $replaced_content );

	}

	public function test_image_tags() {

		$found_images = Optml_Manager::parse_images_from_html( self::IMG_TAGS );

		$this->assertCount( 5, $found_images );
		$this->assertCount( 1, $found_images['img_url'] );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( '/w:2000/', $replaced_content );
		$this->assertContains( '/h:1200/', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::TEST_STAGING );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( '/https://www.example.org', $replaced_content );

	}

	public function test_optimization_url() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );

		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_URLS );

		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_style_replacement() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::CSS_STYLE );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );

	}

	public function test_replacement_non_whitelisted_urls() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::TEST_WRONG_URLS );

		$this->assertNotContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'https://www.codeinwp.org', $replaced_content );
	}

	public function test_replacement_remove_query_arg() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="https://www.example.org/wp-content/uploads/2018/05/brands.png?param=123"> 
				</div>
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( '?param=123', $replaced_content );
	}

	public function test_replacement_with_relative_url() {
		$content = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertContains( 'i.optimole.com', $replaced_content );

	}

	// TODO We need to extend this to single url replacement. If we make the url extractor regex with option scheme, the parsing will take huge amount of time. We need to think alternatives.
	public function test_replacement_without_scheme() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="//www.example.org/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://www.example.org', $replaced_content );;
	}

	public function test_non_allowed_extensions() {
		$replaced_content = Optml_Manager::instance()->replace_content( ( self::CSS_STYLE . self::IMG_TAGS . self::WRONG_EXTENSION ) );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		//Test if wrong extension is still present in the output.
		$this->assertContains( 'http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif', $replaced_content );
	}

	public function test_elementor_data() {
		$replaced_content = Optml_Manager::instance()->process_urls_from_json( ( self::ELEMENTOR_DATA ) );

		$this->assertContains( 'i.optimole.com', $replaced_content );

		//Ensure the json is not corrupted after replacement.
		$this->assertTrue( is_array( json_decode( $replaced_content ) ) );

		//Make sure the image tag is unprocessed.
		$this->assertContains( "src=\\\"https:\/\/www.example.org\/wp-content", $replaced_content );

		//Do the html replacement.
		$replaced_content = Optml_Manager::instance()->replace_content( ( $replaced_content ) );

		//The content should be sucessfully processed.
		$this->assertNotContains( "\"https:\/\/www.example.org\/wp-content", $replaced_content );

	}


	public function test_max_size_height() {
		$new_url = Optml_Manager::instance()->replace_content( 'http://example.org/wp-content/themes/test/assets/images/header.png', [
			'width'  => 99999,
			'height' => 99999
		] );
		$this->assertContains( 'i.optimole.com', $new_url );
		$this->assertNotContains( '99999', $new_url );

	}

	public function test_cropping_sizes() {

		$attachement_url = wp_get_attachment_image_src( self::$sample_attachement, 'sample_size_crop' );

		$this->assertContains( 'w:100', $attachement_url[0] );
		$this->assertContains( 'h:100', $attachement_url[0] );
		$this->assertContains( 'rt:fill', $attachement_url[0] );
		global $_test_posssible_values_y_sizes;
		global $_test_posssible_values_x_sizes;
		$allowed_gravities = array(
			'left'         => Optml_Resize::GRAVITY_WEST,
			'right'        => Optml_Resize::GRAVITY_EAST,
			'top'          => Optml_Resize::GRAVITY_NORTH,
			'bottom'       => Optml_Resize::GRAVITY_SOUTH,
			'lefttop'      => Optml_Resize::GRAVITY_NORTH_WEST,
			'leftbottom'   => Optml_Resize::GRAVITY_SOUTH_WEST,
			'righttop'     => Optml_Resize::GRAVITY_NORTH_EAST,
			'rightbottom'  => Optml_Resize::GRAVITY_SOUTH_EAST,
			'centertop'    => array( 0.5, 0 ),
			'centerbottom' => array( 0.5, 1 ),
			'leftcenter'   => array( 0, 0.5 ),
			'rightcenter'  => array( 1, 0.5 ),
		);

		foreach ( $_test_posssible_values_x_sizes as $x_value ) {
			foreach ( $_test_posssible_values_y_sizes as $y_value ) {
				if ( $x_value === true && $y_value === true ) {
					continue;
				}
				$x_value = $x_value === true ? '' : $x_value;
				$y_value = $y_value === true ? '' : $y_value;

				if ( ! isset( $allowed_gravities[ $x_value . $y_value ] ) ) {
					$gravity_key = Optml_Resize::GRAVITY_CENTER;
				} else {
					$gravity_key = $allowed_gravities[ $x_value . $y_value ];
				}

				$attachement_url = wp_get_attachment_image_src( self::$sample_attachement, 'sample_size_h_' . $x_value . $y_value );
				$this->assertContains( 'rt:fill', $attachement_url[0] );
				if ( ! is_array( $gravity_key ) ) {
					$this->assertContains( 'g:' . $gravity_key, $attachement_url[0], sprintf( ' %s for X %s for Y should contain %s gravity', $x_value, $y_value, $gravity_key ) );
				} else {
					$this->assertContains( 'g:fp:' . $gravity_key[0] . ':' . $gravity_key[1], $attachement_url[0] );
				}
			}
		}

	}

	public function test_post_content() {
		$content = apply_filters( 'the_content', get_post_field( 'post_content', self::$sample_post ) );

		$this->assertContains( 'i.optimole.com', $content );
	}

	public function test_strip_image_size() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMAGE_SIZE_DATA );

		//Test fake sample image size.
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( '282x123', $replaced_content );

		//Test valid wordpress image size, it should strip the size suffix.
		$attachement_url  = wp_get_attachment_image_src( self::$sample_attachement, 'medium' );
		$replaced_content = Optml_Manager::instance()->replace_content( $attachement_url[0] );

		$this->assertNotContains( '282x123', $replaced_content );
	}

	/**
	 * @runInSeparateProcess
	 */
	public function test_custom_domain() {
		define( 'OPTML_SITE_MIRROR', 'https://mycnd.com' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_TAGS );

		//Test custom source.
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( 'http://example.org', $replaced_content );
		$this->assertNotContains( 'example.org', $replaced_content );
		$this->assertContains( 'mycnd.com', $replaced_content );

	}

	public function test_replace_on_feeds() {
		$this->go_to( '/?feed=rss2' );

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS_WITH_SRCSET );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_double_replacement() {

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS );

		$doubled_ccontent = Optml_Manager::instance()->replace_content( $replaced_content . Test_Replacer::IMG_TAGS );

		$this->assertContains( 'i.optimole.com', $doubled_ccontent );
		$this->assertEquals( 2, substr_count( $doubled_ccontent, 'i.optimole.com' ) );
	}

	public function test_image_size_2_crop() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMAGE_SIZE_NO_CLASS );

		$this->assertContains( 'rt:fill', $replaced_content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
	}

	public function test_replacement_with_image_size() {
		//Nasty hack to fetch old url from
		$attachement = wp_get_attachment_image_src( self::$sample_attachement, 'medium' );

		$old_url = explode( 'http://', $attachement[0] );
		$old_url = 'http://' . $old_url[1];

		//Adds possible image size format.
		$content = str_replace( '.png', '-300x300.png', $old_url );

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( '-300x300.png', $replaced_content );
		$this->assertContains( 'w:300', $replaced_content );
		$this->assertContains( 'h:300', $replaced_content );

	}

	public function test_parse_json_data_disabled() {

		$some_html_content = [
			'html' => '<a href="http://example.org/blog/how-to-monetize-a-blog/"><img class="alignnone wp-image-36442 size-full" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" alt="How to monetize a blog" width="490" height="256"></a> http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png '
		];

		$content           = wp_json_encode( $some_html_content );
		$replaced_content  = Optml_Manager::instance()->replace_content( $content );
		$replaced_content2 = Optml_Manager::instance()->replace_content( $replaced_content );

		$this->assertEquals( $replaced_content, $replaced_content2 );
		$this->assertArrayHasKey( 'html', json_decode( $replaced_content2, true ) );

		$this->assertEquals( 2, substr_count( $replaced_content2, '/http:\/\/' ) );
	}

	public function test_filter_sizes_attr() {

		global $wp_current_filter;
		$wp_current_filter = array( 'the_content' );

		$sizes    = array(
			'width'  => 1000,
			'height' => 1000
		);
		$response = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 10000 ) );
		$this->assertContains( '(max-width: 1000px) 100vw, 1000px', $response );
		$wp_current_filter = array();
		$response          = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 10000 ) );
		$this->assertTrue( ! empty( $response ) );
		$this->assertTrue( is_array( $response ) );

		global $content_width;
		$content_width = 5000;
		$response      = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 1 ) );
		$this->assertTrue( ! empty( $response ) );
		$this->assertTrue( is_array( $response ) );
	}

}