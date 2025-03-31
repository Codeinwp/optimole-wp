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
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->attachment_id = $this->create_attachment_get_id(OPTML_PATH . 'tests/assets/sample-test.jpg');
	}

	/**
	 * Clean up after each test
	 */
	public function tearDown(): void {
		if ($this->attachment_id) {
			$this->delete_attachment($this->attachment_id);
		}
		parent::tearDown();
	}

	/**
	 * Test basic URL replacement
	 */
	public function test_basic_url_replacement() {
		$attachment = new Optml_Attachment_Model($this->attachment_id);
		$old_url = $attachment->get_main_url();
		$new_url = str_replace('sample-test', 'new-test-image', $old_url);

		$replacer = new Optml_Attachment_Db_Renamer();
		$count = $replacer->replace($old_url, $new_url);

		$this->assertGreaterThan(0, $count);
	}

	/**
	 * Test replacement with same URLs
	 */
	public function test_same_url_replacement() {
		$attachment = new Optml_Attachment_Model($this->attachment_id);
		$url = $attachment->get_main_url();

		$replacer = new Optml_Attachment_Db_Renamer();
		$count = $replacer->replace($url, $url);

		$this->assertEquals(0, $count);
	}

	/**
	 * Test replacement with image sizes
	 */
//	public function test_image_sizes_replacement() {
//		$attachment = new Optml_Attachment_Model($this->attachment_id);
//		$old_url = $attachment->get_guid();
//		$new_url = str_replace('sample-test', 'new-test-image', $old_url);
//
//		// Create a post with image sizes
//		$post_id = $this->factory->post->create();
//		$content = sprintf(
//			'<img src="%s" class="wp-image-%d" />',
//			$old_url,
//			$this->attachment_id
//		);
//		wp_update_post(['ID' => $post_id, 'post_content' => $content]);
//
//		$replacer = new Optml_Attachment_Db_Renamer();
//		$count = $replacer->replace($old_url, $new_url);
//
//		$this->assertGreaterThan(0, $count);
//
//		$updated_post = get_post($post_id);
//		$this->assertStringContainsString($new_url, $updated_post->post_content);
//	}

	/**
	 * Test replacement with scaled images
	 */
//	public function test_scaled_image_replacement() {
//		$scaled_attachment = $this->create_attachment_get_id(OPTML_PATH . 'tests/assets/3000x3000.jpg');
//		$attachment = new Optml_Attachment_Model($scaled_attachment);
//		$old_url = $attachment->get_guid();
//		$new_url = str_replace('3000x3000', 'new-scaled-image', $old_url);
//
//		// Create a post with scaled image
//		$post_id = $this->factory->post->create();
//		$content = sprintf(
//			'<img src="%s" class="wp-image-%d" />',
//			$old_url,
//			$scaled_attachment
//		);
//		wp_update_post(['ID' => $post_id, 'post_content' => $content]);
//
//		$replacer = new Optml_Attachment_Db_Renamer();
//		$count = $replacer->replace($old_url, $new_url);
//
//		$this->assertGreaterThan(0, $count);
//
//		$updated_post = get_post($post_id);
//		$this->assertStringContainsString($new_url, $updated_post->post_content);
//
//		$this->delete_attachment($scaled_attachment);
//	}

	/**
	 * Test replacement with serialized data
	 */
//	public function test_serialized_data_replacement() {
//		$attachment = new Optml_Attachment_Model($this->attachment_id);
//		$old_url = $attachment->get_guid();
//		$new_url = str_replace('sample-test', 'new-test-image', $old_url);
//
//		// Create a post with serialized data
//		$post_id = $this->factory->post->create();
//		$meta_data = [
//			'image_url' => $old_url,
//			'sizes' => [
//				'thumbnail' => str_replace('.jpg', '-150x150.jpg', $old_url),
//				'medium' => str_replace('.jpg', '-300x300.jpg', $old_url)
//			]
//		];
//		update_post_meta($post_id, 'test_meta', $meta_data);
//
//		$replacer = new Optml_Attachment_Db_Renamer();
//		$count = $replacer->replace($old_url, $new_url);
//
//		$this->assertGreaterThan(0, $count);
//
//		$updated_meta = get_post_meta($post_id, 'test_meta', true);
//		$this->assertEquals($new_url, $updated_meta['image_url']);
//		$this->assertStringContainsString($new_url, $updated_meta['sizes']['thumbnail']);
//		$this->assertStringContainsString($new_url, $updated_meta['sizes']['medium']);
//	}
}
