<?php
/**
 * Test class for Optml_Attachment_Db_Renamer.
 */
// Removed: require_once 'attachment_edit_utils.php';

/**
 * Class Test_Attachment_Db_Renamer.
 */
class Test_Attachment_Db_Renamer extends WP_UnitTestCase {
	// Removed: use Attachment_Edit_Utils;

	const POST_CONTENT = '<!-- wp:image {"id":77,"sizeSlug":"full","linkDestination":"none","align":"center"} -->
<figure class="wp-block-image aligncenter size-full"><img src="http://om-wp.test/wp-content/uploads/2025/03/optimole-logo.svg" alt="" class="wp-image-77"/></figure>
<!-- /wp:image -->

<!-- wp:video {"id":85} -->
<figure class="wp-block-video"><video controls src="http://om-wp.test/wp-content/uploads/2025/03/video-file.mp4"></video></figure>
<!-- /wp:video -->

<!-- wp:group {"layout":{"type":"grid","minimumColumnWidth":"19rem"}} -->
<div class="wp-block-group"><!-- wp:image {"id":89,"sizeSlug":"full","linkDestination":"none"} -->
<figure class="wp-block-image size-full"><img src="http://om-wp.test/wp-content/uploads/2025/03/asdfgh.jpg" alt="" class="wp-image-89"/><figcaption class="wp-element-caption">Picsum ID: 450</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":89,"sizeSlug":"large","linkDestination":"none"} -->
<figure class="wp-block-image size-large"><img src="http://om-wp.test/wp-content/uploads/2025/03/asdfgh-scaled.jpg" alt="" class="wp-image-89"/><figcaption class="wp-element-caption">Picsum ID: 450</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":89,"sizeSlug":"medium","linkDestination":"none"} -->
<figure class="wp-block-image size-medium"><img src="http://om-wp.test/wp-content/uploads/2025/03/asdfgh-300x200.jpg" alt="" class="wp-image-89"/><figcaption class="wp-element-caption">Picsum ID: 450</figcaption></figure>
<!-- /wp:image -->

<!-- wp:image {"id":89,"sizeSlug":"thumbnail","linkDestination":"none"} -->
<figure class="wp-block-image size-thumbnail"><img src="http://om-wp.test/wp-content/uploads/2025/03/asdfgh-150x150.jpg" alt="" class="wp-image-89"/><figcaption class="wp-element-caption">Picsum ID: 450</figcaption></figure>
<!-- /wp:image --></div>
<!-- /wp:group -->';

	protected static $attachment_id;
	protected static $attachment_model;
	protected static $post_id;
	protected static $replacer;
	protected static $replace_method; // ReflectionMethod
	protected static $replacer_skip_sizes;
	protected static $replace_skip_sizes_method; // ReflectionMethod

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$post_id = $factory->post->create( [
			'post_title'   => 'Test Post',
			'post_content' => self::POST_CONTENT,
		] );

		self::$attachment_id    = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' );
		self::$attachment_model = new Optml_Attachment_Model( self::$attachment_id );

		self::$replacer       = new Optml_Attachment_Db_Renamer();
		self::$replace_method = new ReflectionMethod( 'Optml_Attachment_Db_Renamer', 'replace_urls_in_value' );
		self::$replace_method->setAccessible( true );

		self::$replacer_skip_sizes       = new Optml_Attachment_Db_Renamer( true );
		self::$replace_skip_sizes_method = new ReflectionMethod( 'Optml_Attachment_Db_Renamer', 'replace_urls_in_value' );
		self::$replace_skip_sizes_method->setAccessible( true );
	}

	public static function tear_down_after_class() {
		wp_delete_post( self::$attachment_id, true );
		wp_delete_post( self::$post_id, true );
		parent::tear_down_after_class();
	}

	public function test_general() {
		$this->assertInstanceOf( 'WP_Post', get_post( self::$attachment_id ) );
		$this->assertInstanceOf( 'Optml_Attachment_Model', self::$attachment_model );
		$this->assertInstanceOf( 'Optml_Attachment_Db_Renamer', self::$replacer );
	}

	/**
	 * Test replace method with valid URLs
	 */
	public function test_replace() {
		$old_url = self::$attachment_model->get_main_url();
		$new_url = str_replace( 'sample-test', 'new-test-image', $old_url );

		$count = self::$replacer->replace( $old_url, $new_url );
		$this->assertGreaterThan( 0, $count );
	}

	/**
	 * Test replace method with identical URLs
	 */
	public function test_replace_with_identical_urls() {
		$url = self::$attachment_model->get_main_url();

		$count = self::$replacer->replace( $url, $url );
		$this->assertEquals( 0, $count );
	}

	/**
	 * Test replace method with empty URLs
	 */
	public function test_replace_with_empty_urls() {
		$count = self::$replacer->replace( '', '' );
		$this->assertEquals( 0, $count );

		$count = self::$replacer->replace( 'http://example.com', '' );
		$this->assertEquals( 0, $count );

		$count = self::$replacer->replace( '', 'http://example.com' );
		$this->assertEquals( 0, $count );
	}

	/**
	 * Test replace method with null URLs
	 */
	public function test_replace_with_null_urls() {
		$count = self::$replacer->replace( null, null );
		$this->assertEquals( 0, $count );

		$count = self::$replacer->replace( 'http://example.com', null );
		$this->assertEquals( 0, $count );

		$count = self::$replacer->replace( null, 'http://example.com' );
		$this->assertEquals( 0, $count );
	}

	public function test_simple_replacement() {
		$value   = 'http://example.com';
		$old_url = 'http://example.com';
		$new_url = 'http://example.org';

		$replaced = self::$replace_method->invoke( self::$replacer, $value, $old_url, $new_url );
		$this->assertEquals( $new_url, $replaced );
	}

	public function test_multiple_replacement() {
		$value   = 'http://example.com http://example.com http://example.com';
		$old_url = 'http://example.com';
		$new_url = 'http://example.org';

		$replaced = self::$replace_method->invoke( self::$replacer, $value, $old_url, $new_url );
		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );
	}

	public function test_replacing_scaled_urls() {
		$value = "<img src=\"http://example.com/wp-content/uploads/2020/01/image-150x150.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image-300x300.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image-scaled.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image.jpg\" />";

		$old_url = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';

		$replaced = self::$replace_method->invoke( self::$replacer, $value, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );

		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-150x150.jpg', $replaced );
		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-300x300.jpg', $replaced );
		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-scaled.jpg', $replaced );
	}

	public function test_static__replacing_serialized_content() {
		$array = [
			'image' => 'http://example.com/wp-content/uploads/2020/01/image.jpg',
			'thumb' => 'http://example.com/wp-content/uploads/2020/01/thumb.jpg',
		];
		$value = serialize( $array );
		// Validate serialized content.
		$this->assertCount( 2, unserialize( $value ) );

		$old_url = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';

		$replaced = self::$replace_method->invoke( self::$replacer, $value, $old_url, $new_url );
		// Validate serialized content.
		$this->assertCount( 2, unserialize( $replaced ) );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );
		$this->assertStringContainsString( 'thumb.jpg', $replaced );
	}

	public function test_static__replacing_in_json() {
		$array = [
			'image' => 'http://example.com/wp-content/uploads/2020/01/image.jpg',
			'thumb' => 'http://example.com/wp-content/uploads/2020/01/thumb.jpg',
		];

		$value = json_encode( $array );

		// Validate JSON.
		$this->assertCount( 2, json_decode( $value, true ) );

		$old_url   = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url   = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';
		$thumb_url = 'http://example.com/wp-content/uploads/2020/01/thumb.jpg';

		$expected_new_url = 'http:\/\/example.com\/wp-content\/uploads\/2020\/01\/new-url.jpg';

		$replaced = self::$replace_method->invoke( self::$replacer, $value, $old_url, $new_url );
		// Validate JSON.
		$this->assertCount( 2, json_decode( $replaced, true ) );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $expected_new_url, $replaced );
		$this->assertStringContainsString( 'thumb.jpg', $replaced );
		$this->assertEquals( 1, substr_count( $replaced, $expected_new_url ) );

		$replaced_2 = self::$replace_method->invoke( self::$replacer, $replaced, $thumb_url, $new_url );
		// Validate JSON.
		$this->assertCount( 2, json_decode( $replaced_2, true ) );

		$this->assertStringNotContainsString( $thumb_url, $replaced_2 );
		$this->assertStringContainsString( $expected_new_url, $replaced_2 );
		$this->assertEquals( 2, substr_count( $replaced_2, $expected_new_url ) );
	}

	public function test_static__skip_sizes() {
		$content = self::POST_CONTENT;

		$old_url = 'http://om-wp.test/wp-content/uploads/2025/03/asdfgh.jpg';
		$new_url = 'http://om-wp.test/wp-content/uploads/2025/03/new-asdfgh.jpg';

		$not_removed = [
			'http://om-wp.test/wp-content/uploads/2025/03/asdfgh-scaled.jpg',
			'http://om-wp.test/wp-content/uploads/2025/03/asdfgh-300x200.jpg',
			'http://om-wp.test/wp-content/uploads/2025/03/asdfgh-150x150.jpg',
		];

		$replaced = self::$replace_skip_sizes_method->invoke( self::$replacer_skip_sizes, $content, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );

		foreach ( $not_removed as $url ) {
			$this->assertStringContainsString( $url, $replaced );
		}
	}

	public function test_static__ensure_sizes() {
		$content = self::POST_CONTENT;

		$old_url = 'http://om-wp.test/wp-content/uploads/2025/03/asdfgh.jpg';
		$new_url = 'http://om-wp.test/wp-content/uploads/2025/03/properly_named_image.jpg';

		$replaced = self::$replace_method->invoke( self::$replacer, $content, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );

		$should_exist = [
			'properly_named_image-scaled.jpg',
			'properly_named_image-300x200.jpg',
			'properly_named_image-150x150.jpg',
		];

		$should_not_exist = [
			'asdfgh-scaled.jpg',
			'asdfgh-300x200.jpg',
			'asdfgh-150x150.jpg',
		];

		foreach ( $should_exist as $url ) {
			$this->assertStringContainsString( $url, $replaced );
		}

		foreach ( $should_not_exist as $url ) {
			$this->assertStringNotContainsString( $url, $replaced );
		}
	}

	public function test_db__post_content_video_replacement() {
		$initial_url = 'http://om-wp.test/wp-content/uploads/2025/03/video-file.mp4';
		$new_url     = 'http://example.com/wp-content/uploads/2020/01/new-home-made-tutorial.mp4';

		self::$replacer->replace( $initial_url, $new_url );

		$post = get_post( self::$post_id );

		$this->assertStringContainsString( $new_url, $post->post_content );
		$this->assertStringNotContainsString( $initial_url, $post->post_content );
	}

	public function test_db__svg_replacement() {
		$initial_url = 'http://om-wp.test/wp-content/uploads/2025/03/optimole-logo.svg';
		$new_url     = 'http://example.com/wp-content/uploads/2020/01/new-logo.svg';

		self::$replacer->replace( $initial_url, $new_url );

		$post = get_post( self::$post_id );

		$this->assertStringContainsString( $new_url, $post->post_content );
		$this->assertStringNotContainsString( $initial_url, $post->post_content );
	}

	public function test_db__post_content_sizes_replacement() {
		$initial_url = 'http://om-wp.test/wp-content/uploads/2025/03/asdfgh.jpg';
		$new_url     = 'http://example.com/wp-content/uploads/2020/01/new-proper_image.jpg';

		self::$replacer->replace( $initial_url, $new_url );

		$post = get_post( self::$post_id );

		$should_exist = [
			'new-proper_image-scaled.jpg',
			'new-proper_image-300x200.jpg',
			'new-proper_image-150x150.jpg',
		];

		$should_not_exist = [
			'asdfgh-scaled.jpg',
			'asdfgh-300x200.jpg',
			'asdfgh-150x150.jpg',
		];

		foreach ( $should_exist as $url ) {
			$this->assertStringContainsString( $url, $post->post_content );
		}

		foreach ( $should_not_exist as $url ) {
			$this->assertStringNotContainsString( $url, $post->post_content );
		}
	}
}
