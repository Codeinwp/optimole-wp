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
	const IMG_TAGS_PNG = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.png" width="2000" height="1200" alt="Test" /></div></div>';
	const IMG_URLS = '
	http://example.org/wp-content/themes/test/assets/images/header.png 
	http://example.org/wp-content/themes/test/assets/images/header.jpeg
	http://example.org/wp-content/plugins/optimole-wp/assets/img/logo.png 
	 ';
	const CSS_STYLE = '
	<style>
	.body{
	
		background-image:url("http://example.org/wp-content/themes/test/assets/images/header.png");
	}</style>
	 ';
	const WRONG_EXTENSION = '   http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif   ';
	const IMAGE_SIZE_DATA = '
		http://example.org/wp-content/uploads/optimole-wp/assets/img/logo-282x123.png
		http://example.org/wp-content/plugins/optimole-wp/assets/img/test-282x123.png
	';

	const ELEMENTOR_DATA = '[{"id":"428f250c","elType":"section","settings":{"structure":"33","content_width":{"unit":"px","size":1140},"content_position":"middle","gap":"extended","padding":{"unit":"px","top":"10","right":"0","bottom":"10","left":"0","isLinked":false},"padding_mobile":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"1b041a88","elType":"column","settings":{"_column_size":25,"_inline_size":20.66,"_inline_size_tablet":25,"_inline_size_mobile":50,"content_position":"top"},"elements":[{"id":"34d685ef","elType":"widget","settings":{"image":{"id":36009,"url":"https:\/\/www.codeinwp.com\/wp-content\/uploads\/2018\/05\/codeinwp-logo.svg"},"image_size":"full","link_to":"custom","link":{"url":"https:\/\/www.codeinwp.com\/","is_external":"","nofollow":""},"align":"left","width":{"unit":"px","size":120},"space":{"unit":"%","size":100},"opacity":{"unit":"px","size":1},"_margin":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"_element_id":"logo"},"elements":[],"widgetType":"image"}],"isInner":false},{"id":"437f5756","elType":"column","settings":{"_column_size":50,"_inline_size":71.992000000000004,"_inline_size_tablet":70,"_inline_size_mobile":40,"padding_mobile":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":true}},"elements":[{"id":"3c7d3ebf","elType":"widget","settings":{"align_items":"right","pointer":"none","color_menu_item":"#0a4266","menu_typography_typography":"custom","menu_typography_font_weight":"bold","menu_typography_text_transform":"lowercase","color_menu_item_hover":"#ec4646","color_menu_item_active":"#ec4646","menu_typography_font_size":{"unit":"px","size":18},"_margin":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"indicator":"none","dropdown":"mobile","full_width":"stretch","menu_typography_font_size_tablet":{"unit":"px","size":18},"padding_horizontal_menu_item_tablet":{"unit":"px","size":14},"toggle_size":{"unit":"px","size":25},"_padding":{"unit":"px","top":"0","right":"0","bottom":"0","left":"0","isLinked":false},"color_dropdown_item":"#0a4266","color_dropdown_item_hover":"#ec4646","background_color_dropdown_item_hover":"rgba(0,0,0,0)","dropdown_typography_typography":"custom","dropdown_typography_font_family":"proxima-nova","dropdown_typography_font_size":{"unit":"px","size":16},"menu_typography_font_size_mobile":{"unit":"px","size":18},"dropdown_typography_font_size_mobile":{"unit":"px","size":25},"dropdown_typography_text_transform":"lowercase","menu":"main-menu-homepage"},"elements":[],"widgetType":"nav-menu"}],"isInner":false},{"id":"7fafd26c","elType":"column","settings":{"_column_size":25,"_inline_size":7.3479999999999999,"_inline_size_tablet":5,"_inline_size_mobile":2},"elements":[{"id":"8f16004","elType":"widget","settings":{"image":{"url":"https:\/\/www.codeinwp.com\/wp-content\/uploads\/2018\/05\/test.png","id":36135},"image_size":"custom","width":{"unit":"px","size":30},"space":{"unit":"%","size":30},"_element_id":"header-trigger"},"elements":[],"widgetType":"image"}],"isInner":false}],"isInner":false}]';

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

	public function test_image_tags() {

		$found_images = Optml_Manager::parse_images_from_html( self::IMG_TAGS );

		$this->assertCount( 4, $found_images );
		$this->assertCount( 1, $found_images['img_url'] );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( '/w:2000/', $replaced_content );
		$this->assertContains( '/h:1200/', $replaced_content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );
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

	public function test_non_allowed_extensions() {
		$replaced_content = Optml_Manager::instance()->replace_content( ( self::CSS_STYLE . self::IMG_TAGS . self::WRONG_EXTENSION ) );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		//Test if wrong extension is still present in the output.
		$this->assertContains( 'http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif', $replaced_content );
	}

	public function test_elementor_data() {
		$replaced_content = Optml_Manager::instance()->replace_content( ( self::ELEMENTOR_DATA ), 'elementor' );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		//Test if wrong extension is still present in the output.
		$this->assertNotContains( "https:\/\/www.codeinwp.com\/wp-content", $replaced_content );
	}

	public function test_max_size_height() {
		$new_url = Optml_Manager::instance()->replace_content( 'http://example.org/wp-content/themes/test/assets/images/header.png', [
			'width'  => 99999,
			'height' => 99999
		] );
		$this->assertContains( 'i.optimole.com', $new_url );
		//Test if wrong extension is still present in the output.
		$this->assertNotContains( '99999', $new_url );

	}
//
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

	public function test_filter_sizes_attr() {

		global $wp_current_filter;
		$wp_current_filter = array( 'the_content' );

		$sizes = array(
			'width' => 1000,
			'height' => 1000
		);
		$response = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 10000 ) );
		$this->assertContains('(max-width: 1000px) 100vw, 1000px', $response);
		$wp_current_filter = array();
		$response = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 10000 ) );
		$this->assertTrue( ! empty( $response ) );
		$this->assertTrue( is_array( $response ) );

		global $content_width;
		$content_width = 5000;
		$response = apply_filters( 'wp_calculate_image_sizes', $sizes, array( 1 ) );
		$this->assertTrue( ! empty( $response ) );
		$this->assertTrue( is_array( $response ) );
	}

}