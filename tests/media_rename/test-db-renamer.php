<?php
/**
 * Test class for Optml_Attachment_Db_Renamer.
 */
require_once 'attachment_edit_utils.php';

/**
 * Class Test_Attachment_Db_Renamer.
 */
class Test_Attachment_Db_Renamer extends WP_UnitTestCase {
	use Attachment_Edit_Utils;

	/**
	 * @var int
	 */
	private $attachment_id;

	/**
	 * @var Optml_Attachment_Db_Renamer
	 */
	private $replacer;

	private $replace_method;

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->attachment_id = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/sample-test.jpg' );
		$this->replacer      = new Optml_Attachment_Db_Renamer();

		$this->replace_method = new ReflectionMethod( 'Optml_Attachment_Db_Renamer', 'replace_urls_in_value' );
		$this->replace_method->setAccessible( true );
	}

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		if ( $this->attachment_id ) {
			$this->delete_attachment( $this->attachment_id );
		}
		parent::tearDown();
	}

	/**
	 * Test replace method with valid URLs
	 */
	public function test_replace() {
		$attachment = new Optml_Attachment_Model( $this->attachment_id );
		$old_url    = $attachment->get_main_url();
		$new_url    = str_replace( 'sample-test', 'new-test-image', $old_url );

		$count = $this->replacer->replace( $old_url, $new_url );
		$this->assertGreaterThan( 0, $count );
	}

	/**
	 * Test replace method with identical URLs
	 */
	public function test_replace_with_identical_urls() {
		$attachment = new Optml_Attachment_Model( $this->attachment_id );
		$url        = $attachment->get_main_url();

		$count = $this->replacer->replace( $url, $url );
		$this->assertEquals( 0, $count );
	}

	/**
	 * Test replace method with empty URLs
	 */
	public function test_replace_with_empty_urls() {
		$count = $this->replacer->replace( '', '' );
		$this->assertEquals( 0, $count );

		$count = $this->replacer->replace( 'http://example.com', '' );
		$this->assertEquals( 0, $count );

		$count = $this->replacer->replace( '', 'http://example.com' );
		$this->assertEquals( 0, $count );
	}

	/**
	 * Test replace method with null URLs
	 */
	public function test_replace_with_null_urls() {
		$count = $this->replacer->replace( null, null );
		$this->assertEquals( 0, $count );

		$count = $this->replacer->replace( 'http://example.com', null );
		$this->assertEquals( 0, $count );

		$count = $this->replacer->replace( null, 'http://example.com' );
		$this->assertEquals( 0, $count );
	}

	public function test_simple_replacement() {
		$value   = 'http://example.com';
		$old_url = 'http://example.com';
		$new_url = 'http://example.org';

		$replaced = $this->replace_method->invoke( $this->replacer, $value, $old_url, $new_url );
		$this->assertEquals( $new_url, $replaced );
	}

	public function test_multiple_replacement() {
		$value = 'http://example.com http://example.com http://example.com';
		$old_url = 'http://example.com';
		$new_url = 'http://example.org';

		$replaced = $this->replace_method->invoke( $this->replacer, $value, $old_url, $new_url );
		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );
	}

	public function test_replacing_scaled_urls() {
		$value = "<img src=\"http://example.com/wp-content/uploads/2020/01/image-150x150.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image-300x300.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image-scaled.jpg\" /><img src=\"http://example.com/wp-content/uploads/2020/01/image.jpg\" />";

		$old_url = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';

		$replaced = $this->replace_method->invoke( $this->replacer, $value, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );

		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-150x150.jpg', $replaced );
		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-300x300.jpg', $replaced );
		$this->assertStringContainsString( 'http://example.com/wp-content/uploads/2020/01/new-url-scaled.jpg', $replaced );
	}

	public function test_replacing_serialized_content() {
		$value = 'a:2:{s:5:"image";s:63:"http://example.com/wp-content/uploads/2020/01/image.jpg";s:5:"thumb";s:63:"http://example.com/wp-content/uploads/2020/01/thumb.jpg";}';

		$old_url = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';

		$replaced = $this->replace_method->invoke( $this->replacer, $value, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );
		$this->assertStringContainsString( 'thumb.jpg', $replaced );
	}

	public function test_replacing_json_content() {
		$value = '{"image":"http://example.com/wp-content/uploads/2020/01/image.jpg","thumb":"http://example.com/wp-content/uploads/2020/01/thumb.jpg"}';

		$old_url = 'http://example.com/wp-content/uploads/2020/01/image.jpg';
		$new_url = 'http://example.com/wp-content/uploads/2020/01/new-url.jpg';

		$replaced = $this->replace_method->invoke( $this->replacer, $value, $old_url, $new_url );

		$this->assertStringNotContainsString( $old_url, $replaced );
		$this->assertStringContainsString( $new_url, $replaced );
		$this->assertStringContainsString( 'thumb.jpg', $replaced );
	}
}
