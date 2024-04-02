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
	const IMG_TAGS_WITH_SRCSET_SCHEMALESS = '<img class="alignnone size-full wp-image-26" src="//example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg" alt="" width="1450" height="740" srcset="//example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg 1450w, //example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-300x153.jpg 300w, //example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-768x392.jpg 768w, //example.org/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-1024x523.jpg 1024w" sizes="(max-width: 1450px) 100vw, 1450px"> ';
	const IMG_TAGS_WITH_SRCSET_RELATIVE = '<img class="alignnone size-full wp-image-26" src="/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg" alt="" width="1450" height="740" srcset="/wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp.jpg 1450w, /wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-300x153.jpg 300w, /wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-768x392.jpg 768w, /wp-content/uploads/2019/01/september-2018-wordpress-news-w-codeinwp-1024x523.jpg 1024w" sizes="(max-width: 1450px) 100vw, 1450px"> ';
	const IMG_TAGS_PNG = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.png" width="2000" height="1200" alt="Test" /></div></div>';
	const IMG_TAGS_GIF = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif" width="2000" height="1200" alt="Test" /></div></div>';
	const IMG_TAGS_LIMIT_DIMENSIONS = [
		'portrait' => '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="3000" alt="Test"/></div>',
		'landscape' => '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="3000" height="2000" alt="Test"/></div>',
		'equal' => '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="1920" height="1080" alt="Test"/></div>',
		'small' => '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="150" height="150" alt="Test"/></div>',
	];
	const DECODED_UNICODE2 = "/wp-content/uploads/2018/05//umlau1ts_image_a\u0308o\u0308u\u0308.";
	const DECODED_UNICODE = "/wp-content/uploads/2018/05/umlau1ts_image_äöü";
	const NOROMAL_URL = "/wp-content/themes/test/assets/images/header";
	const IMG_URLS = '
	http://example.org/wp-content/themes/test/assets/images/header.png 
	http://example.org/wp-content/themes/test/assets/images/header.jpeg
	http://example.org/wp-content/plugins/optimole-wp/assets/img/logo1.png 
	http://example.org/wp-content/plugins/optimole-wp/assets/img/logo2.png?width=500&cr=small
	http://example.org/wp-content/plugins/optimole-wp/assets/img/logo3.png%3Fwidth%3D500%26cr%3Dsmall
	http://example.org/wp-content/uploads/2018/05/umlauts_image_äöü.jpg
	http://example.org/uploads/2018/05/umlauts_image_a\u0308o\u0308u\u0308.jpg
	//example.org/wp-content/themes/test/assets/images/header2.png 
	//example.org/wp-content/themes/test/assets/images/header2.jpeg
	//example.org/wp-content/plugins/optimole-wp/assets/img/logo4.png 
	//example.org/wp-content/plugins/optimole-wp/assets/img/logo2.png?width=500&cr=small
	//example.org/wp-content/plugins/optimole-wp/assets/img/logo3.png%3Fwidth%3D500%26cr%3Dsmall
	//example.org/wp-content/uploads/2018/05/umlauts_im4age_äöü.jpg
	//example.org/uploads/2018/05/umlauts_5image_a\u0308o\u0308u\u0308.jpg
	/wp-content/themes/test/assets/images/header4.png 
	/wp-content/themes/test/assets/images/header7.jpeg
	/wp-content/plugins/optimole-wp/assets/img/logo9.png 
	/wp-content/plugins/optimole-wp/assets/img/lo2go.png?width=500&cr=small
	/wp-content/plugins/optimole-wp/assets/img/log4.png%3Fwidth%3D500%26cr%3Dsmall
	/wp-content/uploads/2018/05/umlau1ts_image_äöü.jpg
	/wp-content/uploads/2018/05/umlau1ts_image_a\u0308o\u0308u\u0308.jpg
	http://example.org/wp-content/uploads/2021/04/test©-1024x683.jpg
	//example.org/wp-content/uploads/2021/04/test©-1024x683.jpg
	/wp-content/uploads/2021/04/test©-1024x683.jpg
	http://example.org/wp-content/uploads/2021/04/test©.jpg
	//example.org/wp-content/uploads/2021/04/test©.jpg
	/wp-content/uploads/2021/04/test©.jpg
	 ';
	const ASSETS_URL = '
	http://example.org/wp-content/themes/test/assets/header.css 
	http://example.org/wp-content/themes/test/assets/header.js
	//example.org/wp-content/themes/test/assets/images/header.css 
	//example.org/wp-content/themes/test/assets/images/header.js
	/wp-content/themes/test/assets/header.css
	/wp-content/themes/test/assets/header.js
	';
	const IMG_URLS_CAPITAL_EXTENSION = '
	<img src="http://example.org/wp-content/themes/assets/images/header.PNG">
	<img src="http://example.org/wp-content/themes/assets/images/header2.JPEG">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo1.JPG">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo2.JPE">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo3.WEBP">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo4.SVG">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo5.GIF">
	 ';
	const CSS_STYLE = '
	<style>
	.body{
		background-image:url("http://example.org/wp-content/themes/test/assets/images/header-300x300.png");
	}
	.body div {
		background-image:url("//example.org/wp-content/themes/test/assets/images/header3-300x300.png");
	}
	.body div {
		background-image:url("/wp-content/themes/test/assets/images/header2-300x300.png");
	}
	.body div {
		background-image:url(/wp-content/themes/test/assets/images/head1er2-300x300.png);
	}
	.body{
		background-image:url(http://example.org/wp-content/themes/test/assets/images/heade2r-300x300.png);
	}
	.body div {
		background-image:url(//example.org/wp-content/themes/test/assets/images/he3ader3-300x300.png);
	}
	</style>
	 ';
	const WRONG_EXTENSION = '   http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif   ';
	const IMAGE_SIZE_DATA = '
		http://example.org/wp-content/uploads/optimole-wp/assets/img/logo-282x123.png
		http://example.org/wp-content/plugins/optimole-wp/assets/img/test-282x123.png
		//example.org/wp-content/uploads/optimole-wp/assets/img/log2o-282x123.png
		//example.org/wp-content/plugins/optimole-wp/assets/img/tes3t-282x123.png
	';
	const IMAGE_SIZE_NO_CLASS = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header-100x100.png" alt="Test" /></div></div>';

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
	const DAM_LINKS = '
		https://cloudUrlTest.test/dam:1/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg
	';

	public static $sample_post;
	public static $sample_attachement;

	public function setUp(): void {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],

		] );
		$settings->update( 'lazyload', 'disabled' );
		$settings->update( 'cdn', 'enabled' );
		$settings->update( 'cache_buster_images', 'eFRn.20eff' );
		$settings->update( 'cache_buster_assets', 'eFRn.20eff' );
		$settings->update('filters', array(
			Optml_Settings::FILTER_TYPE_OPTIMIZE => array (
				Optml_Settings::FILTER_CLASS => array (
					'test' => true,
					'something' => true,
					'testing' => true,
				)
			)));
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
			'image'  => "https://www.example.org/wp-content/uploads/2018/05/brands.png",
			'image2' => "https://www.example.org/wp-content/uploads/2018/05/brands2.png?test=123",
			'image3' => "https://www.example.org/wp-content/uploads/2018/05/brands2.png?test=123&amp;new=val",
		];

		$html             = wp_json_encode( $html );
		$html             = _wp_specialchars( $html, ENT_QUOTES, 'UTF-8', true );
		$replaced_content = Optml_Manager::instance()->process_urls_from_content( $html );
		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );

		$replaced_content = wp_specialchars_decode( $replaced_content, ENT_QUOTES );
		$replaced_content = json_decode( $replaced_content, true );

		$this->assertArrayHasKey( 'image', $replaced_content );

	}

	public function test_resize () {
		$resize = new Optml_Resize([
			'enlarge' => true,
			'gravity' => [
				0.5, 0,
			],
			'type' => 'fill',
		]);

		$this->assertEquals( 'rt:fill/g:fp:0.5:0/el:1', $resize->toString() );

	}
	public function test_image_tags() {
		$settings = new Optml_Settings();
		$settings->update( 'limit_dimensions', 'disabled' );

		Optml_Url_Replacer::instance()->init();

		$found_images = Optml_Manager::parse_images_from_html( self::IMG_TAGS );

		$this->assertCount( 5, $found_images );
		$this->assertCount( 1, $found_images['img_url'] );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:2000/', $replaced_content );
		$this->assertStringContainsString( '/h:1200/', $replaced_content );
		$this->assertStringContainsString( 'http://example.org', $replaced_content );
		$this->assertStringContainsString( 'decoding=async', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::TEST_STAGING );
		$this->assertStringContainsString( 'decoding=async', $replaced_content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/https://www.example.org', $replaced_content );

	}

	public function test_optimization_url() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'http://example.org', $replaced_content );

		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_URLS );

		$this->assertEquals( 27, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_limit_dimensions_default() {
		$settings = new Optml_Settings();
		$settings->update( 'limit_dimensions', 'enabled' );

		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['portrait'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:720/', $replaced_content );
		$this->assertStringContainsString( '/h:1080/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['landscape'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:1620/', $replaced_content );
		$this->assertStringContainsString( '/h:1080/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['equal'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:1920/', $replaced_content );
		$this->assertStringContainsString( '/h:1080/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['small'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:150/', $replaced_content );
		$this->assertStringContainsString( '/h:150/', $replaced_content );
	}

	public function test_limit_dimensions_custom() {
		$settings = new Optml_Settings();
		$settings->update( 'limit_dimensions', 'enabled' );
		$settings->update( 'limit_height', 600 );
		$settings->update( 'limit_width', 800 );
		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['portrait'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:400/', $replaced_content );
		$this->assertStringContainsString( '/h:600/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['landscape'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:800/', $replaced_content );
		$this->assertStringContainsString( '/h:533/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['equal'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:800/', $replaced_content );
		$this->assertStringContainsString( '/h:450/', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_LIMIT_DIMENSIONS['small'] );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/w:150/', $replaced_content );
		$this->assertStringContainsString( '/h:150/', $replaced_content );
	}

	public function test_avif_disabled() {
		$settings = new Optml_Settings();
		$settings->update( 'avif', 'disabled' );
		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/ig:avif/', $replaced_content );
	}

	public function test_avif_enabled() {
		$settings = new Optml_Settings();
		$settings->update( 'avif', 'enabled' );
		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( '/ig:avif/', $replaced_content );
	}

	public function test_best_format_disabled() {
		$settings = new Optml_Settings();
		$settings->update('best_format', 'disabled' );
		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( '/f:best/', $replaced_content );
	}

	public function test_best_format_enabled() {
		$settings = new Optml_Settings();
		$settings->update('best_format', 'enabled' );
		Optml_Url_Replacer::instance()->init();

		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '/f:best/', $replaced_content );
	}

	public function test_assets_url() {
		$replaced_content = Optml_Manager::instance()->process_urls_from_content( self::ASSETS_URL );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'http://example.org', $replaced_content );

		$replaced_content = Optml_Manager::instance()->replace_content( self::ASSETS_URL );

		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/f:css/' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/f:js/' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/m:0/' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/m:1/' ) );
		$edge_case_urls = '
						https://example.org/wp-content/plugins/divi-bars/assets/js/snap.svg-min.js
						http://example.org/wp-content/plugins/divi-bars/assets/js/snap.svg-min.js
						http://example.org/wp-includes/js/hoverintent-js.min.png-random.css
						http://example.org/wp-includes/js/assets/whatever.jpg.png.css.js
						https://example.org/wp-includes/js/assets/whatever.jpg.jpg
						';
		$replaced_content = Optml_Manager::instance()->replace_content( $edge_case_urls );

		$this->assertStringContainsString( 'https://test123.i.optimole.com/cb:eFRn.20eff/f:js/q:mauto/m:0/https://example.org/wp-content/plugins/divi-bars/assets/js/snap.svg-min.js', $replaced_content );
		$this->assertStringContainsString( 'https://test123.i.optimole.com/cb:eFRn.20eff/f:js/q:mauto/m:0/http://example.org/wp-content/plugins/divi-bars/assets/js/snap.svg-min.js', $replaced_content );
		$this->assertStringContainsString( 'https://test123.i.optimole.com/cb:eFRn.20eff/f:css/q:mauto/m:1/http://example.org/wp-includes/js/hoverintent-js.min.png-random.css', $replaced_content );
		$this->assertStringContainsString( 'https://test123.i.optimole.com/cb:eFRn.20eff/f:js/q:mauto/m:0/http://example.org/wp-includes/js/assets/whatever.jpg.png.css.js', $replaced_content );
		$this->assertStringContainsString( 'https://test123.i.optimole.com/cb:eFRn.20eff/w:auto/h:auto/q:mauto/ig:avif/https://example.org/wp-includes/js/assets/whatever.jpg.jpg', $replaced_content );

		$settings = new Optml_Settings();
		$settings->update( 'css_minify', 'disabled' );
		Optml_Manager::instance()->init();
		$replaced_content = Optml_Manager::instance()->replace_content( self::ASSETS_URL );
		$this->assertEquals( 6, substr_count( $replaced_content, '/m:0/' ) );


	}
	public function test_capital_extensions() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_URLS_CAPITAL_EXTENSION );

		$this->assertEquals( 7, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 7, substr_count( $replaced_content, 'w:auto' ) );
		$this->assertEquals( 7, substr_count( $replaced_content, 'h:auto' ) );
		$this->assertEquals( 7, substr_count( $replaced_content, 'q:mauto' ) );
		$this->assertStringNotContainsString( 'm:0', $replaced_content );
		$this->assertStringNotContainsString( 'm:1', $replaced_content );
		$this->assertStringNotContainsString( 'f:css', $replaced_content );
		$this->assertStringNotContainsString( 'f:js', $replaced_content );
	}

	public function test_style_replacement() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::CSS_STYLE );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'http://example.org', $replaced_content );
		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );

	}

	public function test_replacement_non_whitelisted_urls() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::TEST_WRONG_URLS );

		$this->assertStringNotContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'https://www.codeinwp.org', $replaced_content );
	}

	public function test_replacement_remove_query_arg() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="https://www.example.org/wp-content/uploads/2018/05/brands.png?param=123&2782=dasda"> 
				</div>
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '?param=123', $replaced_content );
	}

	public function test_replacement_with_relative_url() {
		$content = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );

	}
	public function test_replacement_without_quotes() {
		$content = '<div  > 
					<p custom-attr=http://example.org/wp-content/uploads/2018/05/brands.png>  
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );

	}

	public function test_replacement_strange_chars() {
		$content          = '
		https://www.example.org/wp-content/uploads/2018/05/@brands.png
		https://www.example.org/wp-content/uploads/2018/05/%brands.png
		https://www.example.org/wp-content/uploads/2018/05/•brands.png
		https://www.example.org/wp-content/uploads/2018/05/⋿brands.png
		https://www.example.org/wp-content/uploads/2018/05/∀brands.png
		https://www.example.org/wp-content/uploads/2018/05/^brands.png
		';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );

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
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'http://www.example.org', $replaced_content );
	}

	public function test_non_allowed_extensions() {
		$replaced_content = Optml_Manager::instance()->replace_content( ( self::CSS_STYLE . self::IMG_TAGS . self::WRONG_EXTENSION ) );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		//Test if wrong extension is still present in the output.
		$this->assertStringContainsString( 'http://example.org/wp-content/themes/twentyseventeen/assets/images/header.gif', $replaced_content );
	}

	public function test_elementor_data() {
		$html             = self::get_html_array();
		$replaced_content = Optml_Manager::instance()->replace_content( json_encode( $html ) );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 54, substr_count( $replaced_content, 'i.optimole.com' ) );

		//Ensure the json is not corrupted after replacement.
		$this->assertTrue( is_array( json_decode( $replaced_content, true ) ) );

		//The content should be sucessfully processed.
		$this->assertStringNotContainsString( "\"https:\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertStringNotContainsString( "\"\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertStringNotContainsString( "\"\/wp-content", $replaced_content );
		$count_unicode = 0;
		$replaced_html = json_decode( $replaced_content, true );
		foreach ( $replaced_html as $value ) {
			if ( strpos( $value, self::DECODED_UNICODE ) !== false ) {
				$count_unicode ++;
			}
		}
		$this->assertEquals( $count_unicode, 27 );

	}

	public static function get_html_array() {

		$html = [];

		$html['relative_normal']  = self::NOROMAL_URL . 'a.jpg';
		$html['relative_unicode'] = self::DECODED_UNICODE . 'a.jpg';

		$html['schemaless_normal'] = "//example.org" . self::NOROMAL_URL . 'b.jpg';
		$html['unicode_normal']    = "//example.org" . self::DECODED_UNICODE . 'b.jpg';

		$html['full_normal']  = "http://example.org" . self::NOROMAL_URL . 'c.jpg';
		$html['full_unicode'] = "http://example.org" . self::DECODED_UNICODE . 'c.jpg';
		$i                    = 0;
		foreach ( $html as $key => $value ) {
			$i ++;
			$value                                      = str_replace( [ "a.jpg", "b.jpg", "c.jpg" ], [
				"a" . $i . ".jpg",
				"b" . $i . ".jpg",
				"c" . $i . ".jpg",
			], $value );
			$html[ $key . '_img_simple' ]               = '<img src="' . $value . '" > ';
			$html[ $key . '_img_with_alt' ]             = '<img alt="" src="' . $value . '" > ';
			$html[ $key . '_img_with_alt_near_src' ]    = '<img alt=""src="' . $value . '" > ';
			$html[ $key . '_img_with_ending' ]          = '<img src="' . $value . '" /> ';
			$html[ $key . '_img_with_ending_no_space' ] = '<img src="' . $value . '"/> ';
			$html[ $key . '_img_with_class' ]           = '<img class="one-class" src="' . $value . '" /> ';
			$html[ $key . '_img_anchor' ]               = '<a href="http://example.org/blog/how-to-monetize-a-blog/">                      <img class="one-class" src="' . $value . '" /> </a> ';
			$html[ $key . '_img_more_html' ]            = '<div class="before-footer">
				<div class="codeinwp-container"> 
				<img class="one-class" src="' . $value . '" /> 
				</div>
				</div> ';
		};

		return $html;
	}

	public function test_max_size_height() {
		$new_url = Optml_Manager::instance()->replace_content( ' http://example.org/wp-content/themes/test/assets/images/header.png ', [
			'width'  => 99999,
			'height' => 99999
		] );
		$this->assertStringContainsString( 'i.optimole.com', $new_url );
		$this->assertStringNotContainsString( '99999', $new_url );

	}

	public function test_cropping_sizes() {

		$attachement_url = wp_get_attachment_image_src( self::$sample_attachement, 'sample_size_crop' );

		$this->assertStringContainsString( 'w:96', $attachement_url[0] );
		$this->assertStringContainsString( 'h:96', $attachement_url[0] );
		$this->assertStringContainsString( 'rt:fill', $attachement_url[0] );
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
				$this->assertStringContainsString( 'rt:fill', $attachement_url[0] );
				if ( ! is_array( $gravity_key ) ) {
					$this->assertStringContainsString( 'g:' . $gravity_key, $attachement_url[0], sprintf( ' %s for X %s for Y should contain %s gravity', $x_value, $y_value, $gravity_key ) );
				} else {
					$this->assertStringContainsString( 'g:fp:' . $gravity_key[0] . ':' . $gravity_key[1], $attachement_url[0] );
				}
			}
		}

	}

	public function test_post_content() {
		$content = apply_filters( 'the_content', get_post_field( 'post_content', self::$sample_post ) );

		$this->assertStringContainsString( 'i.optimole.com', $content );
	}

	public function test_strip_image_size() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMAGE_SIZE_DATA );

		//Test fake sample image size.
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( '282x123', $replaced_content );
		$this->assertEquals( 4, substr_count( $replaced_content, 'i.optimole.com' ) );

		//Test valid wordpress image size, it should strip the size suffix.
		$attachement_url  = wp_get_attachment_image_src( self::$sample_attachement, 'medium' );
		$replaced_content = Optml_Manager::instance()->replace_content( $attachement_url[0] );

		$this->assertStringNotContainsString( '282x123', $replaced_content );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_custom_domain() {
		define( 'OPTML_SITE_MIRROR', 'https://mycnd.com' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_TAGS );

		//Test custom source.
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( 'http://example.org', $replaced_content );
		$this->assertStringNotContainsString( 'example.org', $replaced_content );
		$this->assertStringContainsString( 'mycnd.com', $replaced_content );

	}

	public function test_replace_on_feeds() {
		$this->go_to( '/?feed=rss2' );

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS_WITH_SRCSET );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'i.optimole.com' ) );

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS_WITH_SRCSET_SCHEMALESS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'i.optimole.com' ) );

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS_WITH_SRCSET_RELATIVE );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_double_replacement() {

		$replaced_content = Optml_Manager::instance()->replace_content( Test_Replacer::IMG_TAGS );

		$doubled_ccontent = Optml_Manager::instance()->replace_content( $replaced_content . Test_Replacer::IMG_TAGS );

		$this->assertStringContainsString( 'i.optimole.com', $doubled_ccontent );
		$this->assertEquals( 2, substr_count( $doubled_ccontent, 'i.optimole.com' ) );
	}

	public function test_image_size_2_crop() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMAGE_SIZE_NO_CLASS );

		$this->assertStringContainsString( 'rt:fill', $replaced_content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
	}

	public function test_replacement_with_image_size() {
		//Nasty hack to fetch old url from
		$attachement = wp_get_attachment_image_src( self::$sample_attachement, 'medium' );

		$old_url = explode( 'http://', $attachement[0] );
		$old_url = 'http://' . $old_url[1];

		//Adds possible image size format.
		$content = str_replace( '.png', '-300x300.png', $old_url );

		$replaced_content = Optml_Manager::instance()->replace_content( " " . $content . " " );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( '-300x300.png', $replaced_content );
		$this->assertStringContainsString( 'w:300', $replaced_content );
		$this->assertStringContainsString( 'h:300', $replaced_content );

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
		$this->assertStringContainsString( '(max-width: 1000px) 100vw, 1000px', $response );
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

	public function test_replacement_hebrew() {
		$content          = '<div class="codeinwp-container">
					<img src="https://www.example.org/wp-content/uploads/2018/05/ס@וככי-תבל-לוגו.jpg"> 
					<img src="https://www.example.org/wp-content/uploads/2018/05/סוtextדךי-תב700ל-לוגו.jpg"> 
					<img src="https://www.example.org/wp-content/uploads/2018/כwhateverכי-ת.png">
				</div>
			';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );

		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );

	}

	public function test_replacement_chinese() {
		$content          = '<div class="codeinwp-container">
					<img src="https://www.example.org/wp-content/uploads/2020/03/年轮钟2号-Annual-Rings-Clock-II-白背景.jpg"> 
					<img src="https://www.example.org/wp-content/uploads/2020/03/年轮钟2号-白背景.jpg"> 
					<img src="https://www.example.org/wp-content/uploads/2020/03/年轮钟3年轮钟.jpg"> 
				</div>
			';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );

		$this->assertEquals( 3, substr_count( $replaced_content, 'i.optimole.com' ) );

	}
	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function test_class_exclusion()
	{
		$content = '<div>
						<img class="skip-optimization" src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>;
						<img class="test whatever" src="http://example.org/wp-content/uploads/2019/09/img.jpg" alt=""/>;
						<img class="testing something class" src="http://example.org/img.png" alt=""/>;
						<img class="none" src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>;
					</div>';
		$replaced_content = Optml_Manager::instance()->process_images_from_content($content);
		$this->assertEquals( 0, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertStringNotContainsString('data-opt-src', $replaced_content);
	}

	public function test_extension_exclusion()
	{
		$settings = new Optml_Settings();
			$settings->update('filters', array(
				Optml_Settings::FILTER_TYPE_OPTIMIZE => array (
					Optml_Settings::FILTER_EXT => array (
						'png' => true,
						'webp' => true,
						'jpg' => true,
					)
				)));
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		$content = '<div>
						<img  src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>;
						<img src="http://example.org/wp-content/uploads/2019/09/img.jpg" alt=""/>;
						<img  src="http://example.org/img.webp" alt=""/>;
						<img src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt=""/>;
					</div>';
		$replaced_content = Optml_Manager::instance()->process_images_from_content($content);
		$this->assertEquals( 0, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertStringNotContainsString('data-opt-src', $replaced_content);
	}

	public function test_strip_metadata() {
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_URLS );
		$this->assertStringNotContainsString( '/sm:0/', $replaced_content );

		// To do same check but after we've disabled the strip metadata option.
		$settings = new Optml_Settings();
		$settings->update( 'strip_metadata', 'disabled' );
		Optml_Manager::instance()->init();
		$replaced_content = Optml_Manager::instance()->replace_content( self::IMG_URLS );
		$this->assertEquals( 27, substr_count( $replaced_content, '/sm:0/' ) );
	}

	public function test_tag_replacer_process_image_tags_missing_width_height() {
		$test_data = [
			[
				'content' => '<div><img src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt="" height="40"/></div>',
				'expected' => '/w:auto/h:40/',
			],
			[
				'content' => '<div><img src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt="" width="50"/></div>',
				'expected' => '/w:50/h:auto/',
			],
			[
				'content' => '<div><img src="http://example.org/wp-content/uploads/2019/09/Screenshot.png" alt="" height="60" width="70"/></div>',
				'expected' => '/w:70/h:60/',
			]
		];

		foreach ( $test_data as $data ) {
			$images           = Optml_Manager::parse_images_from_html( $data['content'] );
			$replaced_content = Optml_Tag_Replacer::instance()->process_image_tags( $data['content'], $images );

			$this->assertStringContainsString( $data['expected'], $replaced_content );
		}
	}

	public function test_dam_flag_removal() {
		$this->assertStringContainsString( 'dam:1', self::DAM_LINKS );
		$this->assertStringNotContainsString('q:mauto', self::DAM_LINKS);

		$replaced_content = Optml_Manager::instance()->replace_content( self::DAM_LINKS );

        var_dump($replaced_content);

		$this->assertStringNotContainsString('dam:1', $replaced_content);
		$this->assertStringContainsString('q:mauto', $replaced_content);
	}
}