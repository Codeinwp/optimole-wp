<?php
/**
 * Test class for Optml_Attachment_Replace.
 */

/**
 * Class Test_Attachment_Replace.
 */
class Test_Attachment_Replace extends WP_UnitTestCase {
	protected static $scaled_unscaled_id; // scaled -> unscaled
	protected static $unscaled_scaled_id; // unscaled -> scaled
	protected static $scaled_scaled_id;   // scaled -> scaled
	protected static $unscaled_unscaled_id; // unscaled -> unscaled

	const FILESTASH = OPTML_PATH . 'tests/assets/filestash/';

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
		global $wp_filesystem;

		// Ensure filestash exists and is empty
		if ( $wp_filesystem->exists( self::FILESTASH ) ) {
			$wp_filesystem->rmdir( self::FILESTASH, true );
		}
		$wp_filesystem->mkdir( self::FILESTASH );

		// Copy replacement files
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/large-1.jpg', self::FILESTASH . 'replace-scaled.jpg' );
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/large-2.jpg', self::FILESTASH . 'replace-scaled-alt.jpg' ); // Different file for scaled->scaled
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/small-1.jpg', self::FILESTASH . 'replace-unscaled.jpg' );
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/small-2.jpg', self::FILESTASH . 'replace-unscaled-alt.jpg' ); // Different file for unscaled->unscaled


		// Create initial attachments for each test case
		self::$scaled_unscaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/3000x3000.jpg' ); // Scaled
		self::$unscaled_scaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' ); // Unscaled
		self::$scaled_scaled_id   = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/3000x3000.jpg' ); // Scaled
		self::$unscaled_unscaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' ); // Unscaled
	}

	public static function tear_down_after_class() {
		// Delete all created attachments
		wp_delete_post( self::$scaled_unscaled_id, true );
		wp_delete_post( self::$unscaled_scaled_id, true );
		wp_delete_post( self::$scaled_scaled_id, true );
		wp_delete_post( self::$unscaled_unscaled_id, true );

		global $wp_filesystem;
		$wp_filesystem->rmdir( self::FILESTASH, true );

		parent::tear_down_after_class();
	}

	public function test_barebones() {
		$this->assertInstanceOf( 'WP_Post', get_post( self::$scaled_unscaled_id ) );
		$this->assertInstanceOf( 'WP_Post', get_post( self::$unscaled_scaled_id ) );
		$this->assertInstanceOf( 'WP_Post', get_post( self::$scaled_scaled_id ) );
		$this->assertInstanceOf( 'WP_Post', get_post( self::$unscaled_unscaled_id ) );
	}

	public function test_replacements() {
		$this->test_replace_scaled_to_unscaled();
		$this->test_replace_unscaled_to_scaled();
		$this->test_replace_scaled_to_scaled();
		$this->test_replace_unscaled_to_unscaled();
	}

	private function test_replace_scaled_to_unscaled() {
		$replace_file = [
			'name' => 'replace-unscaled.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => self::FILESTASH . 'replace-unscaled.jpg',
		];

		$this->do_replace_test( self::$scaled_unscaled_id, $replace_file, true, false );
	}

	private function test_replace_unscaled_to_scaled() {
		$replace_file = [
			'name' => 'replace-scaled.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => self::FILESTASH . 'replace-scaled.jpg',
		];

		$this->do_replace_test( self::$unscaled_scaled_id, $replace_file, false, true );
	}

	private function test_replace_scaled_to_scaled() {
		$replace_file = [
			'name' => 'replace-scaled-alt.jpg', // Using the alt file
			'type' => 'image/jpeg',
			'tmp_name' => self::FILESTASH . 'replace-scaled-alt.jpg',
		];

		$this->do_replace_test( self::$scaled_scaled_id, $replace_file, true, true );
	}

	private function test_replace_unscaled_to_unscaled() {
		$replace_file = [
			'name' => 'replace-unscaled-alt.jpg', // Using the alt file
			'type' => 'image/jpeg',
			'tmp_name' => self::FILESTASH . 'replace-unscaled-alt.jpg',
		];

		$this->do_replace_test( self::$unscaled_unscaled_id, $replace_file, false, false );
	}

	private function do_replace_test( $id_to_replace, $replace_file, $source_scaled, $result_scaled ) {
		// Removed var_dump

		$model = new Optml_Attachment_Model( $id_to_replace );
		$metadata = $model->get_attachment_metadata();

		// Store original size for comparison
		$original_size = $metadata['filesize'];

		// Assert initial scaled status
		$this->assertTrue( $model->is_scaled() === $source_scaled, 'Initial scaled status mismatch.' );

		// Perform the replacement
		$replacer = new Optml_Attachment_Replace( $id_to_replace, $replace_file );
		$result = $replacer->replace();
		$this->assertTrue( $result, 'Replacement operation failed.' );

		// Get the model and metadata after replacement
		$new_model = new Optml_Attachment_Model( $id_to_replace );
		$new_metadata = $new_model->get_attachment_metadata();
		$new_size = $new_metadata['filesize'];

		// Assert final scaled status
		$this->assertTrue( $new_model->is_scaled() === $result_scaled, 'Resulting scaled status mismatch.' );

		// Assert file size changed (assuming replacement files have different sizes)
		$this->assertNotEquals( $original_size, $new_size, 'File size did not change after replacement.' );
	}
}
