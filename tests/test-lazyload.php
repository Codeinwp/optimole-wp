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
class Test_Lazyload extends WP_UnitTestCase {
	const HTML_TAGS_HEADER = 'Test sample <header id="header"><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header2.jpg" width="2000" height="1200" alt="Test" /></div></div> </header><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" /></div></div>';
	const HTML_TAGS_HEADER_MULTIPLE = 'Test sample <header id="header"><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header2.jpg" width="2000" height="1200" alt="Test" /></div></div> </header><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" /></div></div>Test sample <header id="header"><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header3.jpg" width="2000" height="1200" alt="Test" /></div></div> </header><div id="wp-custom-header" class="wp-custom-header"><img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header4.jpg" width="2000" height="1200" alt="Test" /></div></div>';
	public static $sample_attachement;
	const IMG_TAGS_FOR_SKIP_LAZY = '
	<img src="http://example.org/wp-content/themes/assets/images/header.png">
	<img src="http://example.org/wp-content/themes/assets/images/header2.JPEG">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo1.JPG">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo2.JPE">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo3.WEBP">
	<img src="http://example.org/wp-content/plugins/optimole-wp/assets/img/logo4.svg">
	<img src="http://example.org/wp-content/optimole-wp/assets/img/logo5.gif">
	 ';
	const DAM_IMG_TAG = '<img width="100" height="200" src="https://cloudUrlTest.test/dam:1/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg">';
	const DAM_IMG_TAG_NO_WIDTH = '<img src="https://cloudUrlTest.test/w:200/h:300/process:20202/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg">';
	public function setUp() : void {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],

		] );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'native_lazyload', 'disabled' );
		$settings->update( 'video_lazyload', 'enabled' );
		$settings->update( 'lazyload_placeholder', 'disabled' );
		$settings->update( 'no_script', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );
	}

	public function test_image_tags_skip_lazy() {
		Optml_Tag_Replacer::$lazyload_skipped_images = 0;
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::IMG_TAGS_FOR_SKIP_LAZY );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertEquals( 12, substr_count( $replaced_content, 'decoding=async' ) );

	}
	public function test_lazy_load() {

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( '/http://', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS . Test_Replacer::IMG_URLS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( 'http://example.org', $replaced_content ); // Does not touch other URL's
		$this->assertStringContainsString( '/http://', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_PNG );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_GIF );

		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( 'data:image/svg+xml', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );

	}

	public function test_lazyload_json_data_valid() {

		$some_html_content = [
			'html' => '<a href="http://example.org/blog/how-to-monetize-a-blog/"><img class="alignnone wp-image-36442 size-full" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" alt="How to monetize a blog" width="490" height="256"></a> http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png '
		];

		$content          = wp_json_encode( $some_html_content );
		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$replaced_content2 = Optml_Manager::instance()->replace_content( $replaced_content );

		$this->assertEquals( $replaced_content, $replaced_content2 );

		$this->assertArrayHasKey( 'html', json_decode( $replaced_content2, true ) );

		$this->assertEquals( 1, substr_count( $replaced_content, 'q:eco' ) );
		$this->assertEquals( 4, substr_count( $replaced_content2, '/http:\/\/' ) );
	}


	public function test_lazyload_tag_sanity_check() {
		$text = ' <a href="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png"><img class="alignnone wp-image-36442 size-full" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png"  srcset="testsrcset" data-srcset="another" data-plugin-src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" alt="How to monetize a blog" width="490" height="256"></a>';

		$replaced_content = Optml_Manager::instance()->replace_content( $text );

		$this->assertStringContainsString( '</noscript></a>', $replaced_content, 'Noscript tag should be inside the wrapper tag and after image tag' );
		$this->assertStringNotContainsString( '"http://example.org', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'q:eco' ) );
		$this->assertEquals( 1, substr_count( $replaced_content, 'old-srcset' ) );

	}
	public function test_lazyload_skip_standard_class() {
		$text = ' <img class="alignnone wp-image-36442 size-full skip-lazy" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" >';

		$replaced_content = Optml_Manager::instance()->replace_content( $text );

		$this->assertStringNotContainsString( 'noscript', $replaced_content );

		$this->assertEquals( 1, substr_count( $replaced_content, 'i.optimole.com' ) );
	}
	public function test_lazyload_skip_standard_attr() {
		$text = ' <img class="alignnone wp-image-36442 size-full" data-skip-lazy src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" >';

		$replaced_content = Optml_Manager::instance()->replace_content( $text );

		$this->assertStringNotContainsString( 'noscript', $replaced_content );

		$this->assertEquals( 1, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_replacement_with_jetpack_photon() {
		$content = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="http://i0.wp.com/www.example.org/wp-content/uploads/2018/05/brands.png"> http://i0.wp.com/www.example.org/wp-content/uploads/2018/05/brands.png
				</div>
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'i0.wp.com', $replaced_content );
	}

	public function test_lazyload_only_gif() {
		$content = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img class="sample-class" src="http://www.example.org/wp-content/uploads/2018/05/brands.gif">
					<img src="http://www.example.org/wp-content/uploads/2018/05/brands2.gif">
					<img src="http://www.example.org/wp-content/uploads/2018/05/brands.svg">
				</div>
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 4, substr_count( $replaced_content, 'optimole-lazy-only' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, 'data:image/svg+xml' ) );
	}

	public function test_lazyload_only_with_json() {
		$some_html_content = [
			'html' => '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img class="sample-class" src="http://www.example.org/wp-content/uploads/2018/05/brands.gif">
					<img src="http://www.example.org/wp-content/uploads/2018/05/brands2.gif">
					<img src="http://www.example.org/wp-content/uploads/2018/05/brands.svg">
				</div>
			</div>'
		];
		$content           = wp_json_encode( $some_html_content );
		$replaced_content  = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( $replaced_content, $replaced_content );
		$this->assertArrayHasKey( 'html', json_decode( $replaced_content, true ) );
		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 4, substr_count( $replaced_content, 'optimole-lazy-only' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, 'data:image/svg+xml' ) );

	}

	public function test_replacement_wrong_extension_with_query_string() {
		$content = ' 
					<img src="http://example.org/wp-content/plugins/test/generate-qr-code.php?3CRMB6qM1DvLswN6nxKjppX6W5ycjXpeZp">
				 ';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringNotContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content );
	}
 


	public function test_replacement_lazyload_with_relative_url() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( '"/wp-content', $replaced_content );
		$this->assertStringContainsString( 'q:eco', $replaced_content );

	}
public function test_replacement_without_quotes() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src=http://example.org/wp-content/uploads/2018/11/gradient.png></div>
					<img src=http://example.org/wp-content/uploads/2018/11/gradient.png alt=""> 
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( 6, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 2, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertStringContainsString( '</noscript></div>', $replaced_content);

	}

	public function test_lazy_load_preserve_image_size() {
		$settings = new Optml_Settings();
		$settings->update( 'skip_lazyload_images', 0 );
		Optml_Manager::instance()->init();
		$html             = wp_get_attachment_image( self::$sample_attachement, 'sample_size_crop' );
		$replaced_content = Optml_Manager::instance()->replace_content( $html );

		$this->assertNotEquals( $replaced_content, $html );
		$this->assertStringNotContainsString( 'q:eco/rt:fill/g:ce', $replaced_content );
		$this->assertStringContainsString( '/rt:fill/g:ce', $replaced_content );
		$this->assertStringContainsString( '/w:96/h:96/q:eco/f:best/dpr:2/http://example.org/', $replaced_content );

	}

	public function test_width_100() {
 
		$settings = new Optml_Settings();
		$settings->update( 'skip_lazyload_images', 0 );
		Optml_Manager::instance()->init();
		$content = '<img height="100%" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="at0px" width="100%"/>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertEquals( '<img decoding=async data-opt-id=1786304581  data-opt-src="https://test123.i.optimole.com/w:auto/h:auto/q:mauto/f:best/http://example.org/wp-content/uploads/2018/11/gradient.png"  height="100%" src="https://test123.i.optimole.com/w:auto/h:auto/q:eco/f:best/http://example.org/wp-content/uploads/2018/11/gradient.png" class="at0px" width="100%"/><noscript><img decoding=async data-opt-id=1786304581  height="100%" src="https://test123.i.optimole.com/w:auto/h:auto/q:mauto/f:best/http://example.org/wp-content/uploads/2018/11/gradient.png" class="at0px" width="100%"/></noscript>', $replaced_content );

	}
	public function test_check_with_no_script() {
		$content = '<img width="1612" height="1116" src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" data-lazy-sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
<noscript><img width="1612" height="1116" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" /></noscript>	';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( '<noscript>', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, '<noscript>' ) );
		$this->assertEquals( 2, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_check_with_multiple_images_in_no_script() {
		$content = '<img width="1612" height="1116" src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" data-lazy-sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
					<noscript>
						<img width="1612" height="1116" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
						<img width="1612" height="1116" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
						<img width="1612" height="1116" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
					</noscript>	';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( '<noscript>', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, '<noscript>' ) );
		$this->assertEquals( 4, substr_count( $replaced_content, 'i.optimole.com' ) );
	}

	public function test_check_lazy_load_after_no_script() {
		$content = '
<noscript>
<img height="1" width="1" style="display:none" alt="fbpx"
src="https://www.facebook.com/tr?id=472300923567306&ev=PageView&noscript=1" />
</noscript>
			<a href="/project/test-one"><span class="et_pb_image_wrap"><img src="http://example.org/wp-content/uploads/2018/11/gradient.png" alt="" /></span></a>
			<a href="/project/test-two"><span class="et_pb_image_wrap"><img src="http://example.org/wp-content/uploads/2018/11/gradient.png" alt="" /></span></a>
			<a href="/project/test-three"><span class="et_pb_image_wrap"><img src="http://example.org/wp-content/uploads/2018/11/gradient.png" alt="" /></span></a>
		';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( '<noscript>', $replaced_content );
		$this->assertEquals( 4, substr_count( $replaced_content, '<noscript>' ) );
		$this->assertEquals( 9, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/q:eco/' ) );
		$this->assertEquals( 6, substr_count( $replaced_content, '/q:mauto/' ) );
	}

	public function test_replacement_with_data_attr() {
		$content = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="http://www.example.org/wp-content/uploads/2018/05/brands.png" data-src="http://www.example.org/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( 5, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );
	}

	public function test_check_no_script() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::HTML_TAGS_HEADER );
		
		$this->assertStringContainsString( '<noscript>', $replaced_content );
		$this->assertEquals( 2, substr_count( $replaced_content, '<noscript>' ) );
	}

	public function test_lazy_load_ignore_feed() {
		$this->go_to( '/?feed=rss2' );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_WITH_SRCSET );
		$this->assertStringNotContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content );
	}

	public function test_lazy_load_off() {

		define( 'OPTML_DISABLE_PNG_LAZYLOAD', true );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_PNG . Test_Replacer::IMG_TAGS );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );

	}

	public function test_lazyload_json_data_disabled() {

		$some_html_content = [
			'html' => '<a href="http://example.org/blog/how-to-monetize-a-blog/"><img class="alignnone wp-image-36442 size-full" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" alt="How to monetize a blog" width="490" height="256"></a> http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png '
		];
		$content           = wp_json_encode( $some_html_content );
		$replaced_content  = Optml_Manager::instance()->replace_content( $content );

		$replaced_content2 = Optml_Manager::instance()->replace_content( $replaced_content );

		$this->assertEquals( $replaced_content, $replaced_content2 );
		$this->assertArrayHasKey( 'html', json_decode( $replaced_content2, true ) );

		$this->assertEquals( 2, substr_count( $replaced_content2, '/http:\/\/example.org' ) );
	}

	public function test_json_lazyload_replacement() {
		$html = Test_Replacer::get_html_array();

		$replaced_content = Optml_Manager::instance()->replace_content( json_encode( $html ) );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertEquals( ( 6 + ( 3 * 48 ) ), substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertTrue( is_array( json_decode( $replaced_content, true ) ) );
		$this->assertStringNotContainsString( "\"https:\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertStringNotContainsString( "\"\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertStringNotContainsString( "\"\/wp-content", $replaced_content );
		$count_unicode = 0;
		$replaced_html = json_decode( $replaced_content, true );

		foreach ( $replaced_html as $value ) {
			$count_unicode += substr_count( $value, Test_Replacer::DECODED_UNICODE );

		}

		$this->assertEquals( $count_unicode, ( ( 24 * 3 ) + 3 ) );
	}

	public function test_should_replace_query_string_url() {
		$content          = '<img src="https://example.org/photos/814499/pexels-photo-814499.jpeg?auto=compress&cs=tinysrgb&dpr=1&w=500" alt="">';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( 'example.org', $replaced_content );
	}

	public function test_should_replace_non_latin_string_url() {
		$content          = '<img src="https://example.org/wp-content/uploads/2020/02/Herren-Halskette-Leder-50-cm-und-Anhänger-Thor-Hammer-aus-Edelstahl-k.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( 'example.org', $replaced_content );
	}

	public function test_should_add_loading () {

		$settings = new Optml_Settings();
		$settings->update( 'native_lazyload', 'enabled' );
		Optml_Manager::instance()->init();

		$content          = '<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'loading="lazy"', $replaced_content );
	}

	public function test_should_not_add_loading () {
		$content          = '<img loading="eager" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>
							 <img loading="lazy" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertEquals( 2, substr_count( $replaced_content, 'loading="lazy"' ) );
		$this->assertStringContainsString( 'loading="eager"', $replaced_content );
	}

	public function test_json_should_add_loading () {

		$settings = new Optml_Settings();
		$settings->update( 'native_lazyload', 'enabled' );
		Optml_Manager::instance()->init();

		$content          = [ '<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img loading="lazy" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img loading="eager" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>'
		];
		$replaced_content = Optml_Manager::instance()->replace_content( json_encode($content) );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
		$this->assertStringContainsString( 'loading=\"eager\"', $replaced_content );
		$this->assertEquals(3, substr_count( $replaced_content, 'loading=\"lazy\"' ));
	}

	public function test_lazyload_iframe() {

		$content = '<figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
					<div class="wp-block-embed__wrapper">
					<iframe title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div></figure>
					<iframe width="930" height="523" src="http://5c128bbdd3b4.ngrok.io/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals( 2, substr_count( $replaced_content, 'src="about:blank"' ) );
		$this->assertEquals( 2, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertEquals( 2, substr_count( $replaced_content, '<noscript>' ) );

	}
	public function test_lazyload_video() {

		$content = '<figure class="wp-block-video"><video controls="" src="https://file-examples-com.github.io/uploads/2017/04/file_example_MP4_480_1_5MG.mp4" data-origwidth="0" data-origheight="0" style="width: 580px;"></video></figure>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringContainsString( '<noscript>', $replaced_content );
	}
	public function test_lazyload_video_with_source_and_no_src() {

		$content = '<video controls="">
					<source type="video/mp4" src="https://test.com/wp-content/video.mp4">
					</video>
					<video controls="" preload="none">
					<source type="video/mp4" src="https://test.com/wp-content/video.mp4">
					</video>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertStringContainsString( 'preload="metadata"', $replaced_content );
	}
	public function test_lazyload_video_source() {

		$content = '<figure class="wp-block-video"><video controls=""><source type="video/mp4" src="https://file-examples-com.github.io/uploads/2017/04/file_example_MP4_480_1_5MG.mp4"></video>
					</figure>';
		$content_skip = '<figure class="wp-block-video"><video controls="" preload="auto"><source type="video/mp4" src="https://file-examples-com.github.io/uploads/2017/04/file_example_MP4_480_1_5MG.mp4"></video></figure>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$replaced_content_skip = Optml_Manager::instance()->replace_content( $content_skip );
		$this->assertStringContainsString( 'preload="metadata"', $replaced_content );
		$this->assertStringNotContainsString( '<noscript>', $replaced_content );
		$this->assertStringNotContainsString( 'preload="metadata"', $replaced_content_skip );
		$this->assertStringNotContainsString( '<noscript>', $replaced_content_skip );
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content_skip );
	}
	public function test_lazyload_iframe_noscript_ignore() {

		$content = '<noscript><iframe width="930" height="523" src="http://5c128bbdd3b4.ngrok.io/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
					</noscript>
					<noscript><iframe width="930" height="523" src="http://5c128bbdd3b4.ngrok.io/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
					</noscript>

					<noscript>
					<div style="width: 302px; height: 422px; position: relative;">
					<div style="width: 302px; height: 422px; position: absolute;">
					<iframe src="https://www.google.com/recaptcha/api/fallback?k=" frameborder="0" scrolling="no" style="width: 302px; height:422px; border-style: none;"></iframe>
					</div>
					</div>
					</noscript>;';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringNotContainsString( 'src="about:blank"', $replaced_content );
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content );
	}
	public function test_lazyload_iframe_exclusion() {

		$content = '<figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
					<div class="wp-block-embed__wrapper">
					<iframe random-src="private" title="test" width="640" height="360" src="https://www.youtube.com/" allowfullscreen>
					<iframe -src="https://www.youtube.com/" allowfullscreen>
					<iframe data-cli-src="something" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					<iframe id="gform_ajax_frame" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					<iframe id="skip-lazy" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					<iframe id="data-skip-lazy" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div></figure>
					<iframe id="gform_ajax_frame_7" width="930" height="523" src="http://5c128bbdd3b4.ngrok.io/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>
		<video data-cli-src="something" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></video>';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertStringNotContainsString( 'src="about:blank"', $replaced_content );
		$this->assertStringNotContainsString( 'data-opt-src', $replaced_content );
		$this->assertStringNotContainsString( '<noscript>', $replaced_content );
	}

	public function test_generic_placeholder() {
		$settings = new Optml_Settings();

		Optml_Manager::instance()->init();
		$svg = Optml_Manager::instance()->lazyload_replacer->get_svg_for( 1200, 1300, 'http://example.org/testimage.png' );
		$decoded = urldecode( $svg );

		$this->assertStringContainsString( 'width="1200"', $decoded );
		$this->assertStringContainsString( 'height="1300"', $decoded );
		$this->assertStringContainsString( 'fill="transparent"', $decoded );


		$settings->update( 'placeholder_color', '#bada55' );

		Optml_Manager::instance()->init();
		$svg = Optml_Manager::instance()->lazyload_replacer->get_svg_for( '', '', 'http://example.org/testimage.png' );
		$decoded = urldecode( $svg );

		$this->assertStringContainsString( 'width="100%"', $decoded );
		$this->assertStringContainsString( 'height="100%"', $decoded );
		$this->assertStringContainsString( 'fill="#bada55"', $decoded );
	}

	public function test_dam_lazyloading() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::DAM_IMG_TAG );
		$this->assertStringContainsString( 'data-opt-src="https://cloudUrlTest.test/w:100/h:200/rt:fill/g:ce/f:best/q:mauto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg"', $replaced_content );
	}
	public function test_dam_lazyloading_no_wh_attributes() {
		add_filter('optml_lazyload_images_skip','__return_zero');
		Optml_Manager::instance()->lazyload_replacer->settings->update('lazyload_type','fixed');
		Optml_Manager::instance()->lazyload_replacer->settings->update('lazyload_placeholder','enabled');
		Optml_Manager::instance()->lazyload_replacer->init();
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::DAM_IMG_TAG_NO_WIDTH );
		$svg = Optml_Manager::instance()->lazyload_replacer->get_svg_for( 200, 300, 'http://example.org/testimage.png' );
		$this->assertStringContainsString( $svg, $replaced_content );
		remove_filter('optml_lazyload_images_skip','__return_zero');
	}
}
