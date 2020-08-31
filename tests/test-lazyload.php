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

	public function setUp() {
		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com' ],

		] );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'native_lazyload', 'enabled' );
		$settings->update( 'video_lazyload', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Lazyload_Replacer::instance()->init();
		Optml_Manager::instance()->init();

		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.png' );
	}

	public function test_lazy_load() {

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( '/http://', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS . Test_Replacer::IMG_URLS );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content ); // Does not touch other URL's
		$this->assertContains( '/http://', $replaced_content );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_PNG );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_GIF );

		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'data:image/svg+xml', $replaced_content );
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

		$this->assertContains( '</noscript></a>', $replaced_content, 'Noscript tag should be inside the wrapper tag and after image tag' );
		$this->assertNotContains( '"http://example.org', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'q:eco' ) );
		$this->assertEquals( 2, substr_count( $replaced_content, 'old-srcset' ) );

	}
	public function test_lazyload_skip_standard_class() {
		$text = ' <img class="alignnone wp-image-36442 size-full skip-lazy" src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" >';

		$replaced_content = Optml_Manager::instance()->replace_content( $text );

		$this->assertNotContains( 'noscript', $replaced_content );

		$this->assertEquals( 1, substr_count( $replaced_content, 'i.optimole.com' ) );
	}
	public function test_lazyload_skip_standard_attr() {
		$text = ' <img class="alignnone wp-image-36442 size-full" data-skip-lazy src="http://example.org/wp-content/uploads/2018/06/start-a-blog-1-5.png" >';

		$replaced_content = Optml_Manager::instance()->replace_content( $text );

		$this->assertNotContains( 'noscript', $replaced_content );

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
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'i0.wp.com', $replaced_content );
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

		$this->assertNotContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( 'data-opt-src', $replaced_content );
	}

	public function test_lazy_dont_lazy_load_headers() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::HTML_TAGS_HEADER );

		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );
	}

	public function test_lazy_dont_lazy_load_headers_relative() {
		$content = '<div></div><header id="header">
						<div id="wp-custom-header" class="wp-custom-header">
							<img src="/wp-content/themes/twentyseventeen/assets/images/header2.jpg" width="2000" height="1200" alt="Test" />
						</div>
				    </header>
				    <div id="wp-custom-header" class="wp-custom-header">
				        <img src="http://example.org/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" />
				    </div>';

		$replaced_content = Optml_Manager::instance()->process_images_from_content( $content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'q:eco', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );
		$this->assertNotContains( 'src="/wp-content', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, 'data-opt-src' ) );
	}

	public function test_lazy_load_just_first_header() {
		$replaced_content = Optml_Manager::instance()->process_images_from_content( self::HTML_TAGS_HEADER_MULTIPLE );

		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'http://example.org', $replaced_content );

		$this->assertEquals( 3, substr_count( $replaced_content, 'data-opt-src' ) );
	}

	public function test_replacement_lazyload_with_relative_url() {
		$content          = '<div class="before-footer">
				<div class="codeinwp-container">
					<p class="featuredon">Featured On</p>
					<img src="/wp-content/uploads/2018/05/brands.png"> 
				</div>
			</div>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( '"/wp-content', $replaced_content );
		$this->assertContains( 'q:eco', $replaced_content );

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
		$this->assertContains( '</noscript></div>', $replaced_content);

	}

	public function test_lazy_load_preserve_image_size() {
		$html             = wp_get_attachment_image( self::$sample_attachement, 'sample_size_crop' );
		$replaced_content = Optml_Manager::instance()->replace_content( $html );

		$this->assertNotEquals( $replaced_content, $html );
		$this->assertNotContains( 'q:eco/rt:fill/g:ce', $replaced_content );
		$this->assertContains( '/rt:fill/g:ce', $replaced_content );
		$this->assertContains( '/w:100/h:100/q:eco/http://example.org/', $replaced_content );

	}

	public function test_check_with_no_script() {
		$content = '<img width="1612" height="1116" src="data:image/gif;base64,R0lGODdhAQABAPAAAP///wAAACwAAAAAAQABAEACAkQBADs=" data-lazy-src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" data-lazy-sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" />
<noscript><img width="1612" height="1116" src="http://example.org/wp-content/uploads/2018/11/gradient.png" class="attachment-twentyseventeen-featured-image size-twentyseventeen-featured-image wp-post-image" alt="" sizes="(max-width: 767px) 89vw, (max-width: 1000px) 54vw, (max-width: 1071px) 543px, 580px" /></noscript>	';

		$replaced_content = Optml_Manager::instance()->replace_content( $content );

		$this->assertContains( '<noscript>', $replaced_content );
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

		$this->assertContains( '<noscript>', $replaced_content );
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

		$this->assertContains( '<noscript>', $replaced_content );
		$this->assertEquals( 4, substr_count( $replaced_content, '<noscript>' ) );
		$this->assertEquals( 9, substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, 'data-opt-src' ) );
		$this->assertEquals( 3, substr_count( $replaced_content, '/q:eco/' ) );
		$this->assertEquals( 6, substr_count( $replaced_content, '/q:auto/' ) );
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

		$this->assertContains( '<noscript>', $replaced_content );
		$this->assertEquals( 1, substr_count( $replaced_content, '<noscript>' ) );
	}

	public function test_lazy_load_ignore_feed() {
		$this->go_to( '/?feed=rss2' );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_WITH_SRCSET );
		$this->assertNotContains( 'i.optimole.com', $replaced_content );
		$this->assertNotContains( 'data-opt-src', $replaced_content );
	}

	public function test_lazy_load_off() {

		define( 'OPTML_DISABLE_PNG_LAZYLOAD', true );

		$replaced_content = Optml_Manager::instance()->process_images_from_content( Test_Replacer::IMG_TAGS_PNG . Test_Replacer::IMG_TAGS );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
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
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertEquals( ( 6 + ( 3 * 48 ) ), substr_count( $replaced_content, 'i.optimole.com' ) );
		$this->assertTrue( is_array( json_decode( $replaced_content, true ) ) );
		$this->assertNotContains( "\"https:\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertNotContains( "\"\/\/www.example.org\/wp-content", $replaced_content );
		$this->assertNotContains( "\"\/wp-content", $replaced_content );
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
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'example.org', $replaced_content );
	}

	public function test_should_replace_non_latin_string_url() {
		$content          = '<img src="https://example.org/wp-content/uploads/2020/02/Herren-Halskette-Leder-50-cm-und-Anhänger-Thor-Hammer-aus-Edelstahl-k.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'example.org', $replaced_content );
	}

	public function test_should_add_loading () {
		$content          = '<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'loading="lazy"', $replaced_content );
	}

	public function test_should_not_add_loading () {
		$content          = '<img loading="eager" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>
                             <img loading="lazy" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>';
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertEquals( 2, substr_count( $replaced_content, 'loading="lazy"' ) );
		$this->assertContains( 'loading="eager"', $replaced_content );
	}

	public function test_json_should_add_loading () {
		$content          = [ '<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img loading="lazy" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>',
			'<img loading="eager" src="https://example.org/wp-content/uploads/2020/02/Herren.jpg" alt>'
		];
		$replaced_content = Optml_Manager::instance()->replace_content( json_encode($content) );
		$this->assertContains( 'i.optimole.com', $replaced_content );
		$this->assertContains( 'data-opt-src', $replaced_content );
		$this->assertContains( 'loading=\"eager\"', $replaced_content );
		$this->assertEquals(4, substr_count( $replaced_content, 'loading=\"lazy\"' ));
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
		$this->assertNotContains( 'src="about:blank"', $replaced_content );
		$this->assertNotContains( 'data-opt-src', $replaced_content );
		$this->assertNotContains( '<noscript>', $replaced_content );

	}
	public function test_lazyload_iframe_exclusion() {

		$content = '<figure class="wp-block-embed-youtube wp-block-embed is-type-video is-provider-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
					<div class="wp-block-embed__wrapper">
					<iframe id="gform_ajax_frame" title="test" width="640" height="360" src="https://www.youtube.com/embed/-HwJNxE7hd8?feature=oembed" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
					</div></figure>
					<iframe id="gform_ajax_frame_7" width="930" height="523" src="http://5c128bbdd3b4.ngrok.io/" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen=""></iframe>';
		
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertNotContains( 'src="about:blank"', $replaced_content );
		$this->assertNotContains( 'data-opt-src', $replaced_content );
		$this->assertNotContains( '<noscript>', $replaced_content );
		
	}
}
