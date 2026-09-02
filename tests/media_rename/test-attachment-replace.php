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

	/**
	 * A 0600 upload tmp file must not leave the replaced attachment unreadable to the web server.
	 */
	public function test_replace_normalizes_permissions_of_restricted_tmp_file() {
		global $wp_filesystem;

		$id = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' );

		$tmp_file = self::FILESTASH . 'replace-restricted.jpg';
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/small-1.jpg', $tmp_file, true );
		chmod( $tmp_file, 0600 );

		$model     = new Optml_Attachment_Model( $id );
		$file_path = $model->get_source_file_path();

		$replacer = new Optml_Attachment_Replace(
			$id,
			[
				'name'     => 'replace-restricted.jpg',
				'type'     => 'image/jpeg',
				'tmp_name' => $tmp_file,
			]
		);

		$result = $replacer->replace();

		clearstatcache( true, $file_path );

		$this->assertTrue( $result, 'Replacement operation failed.' );
		$this->assertSame( FS_CHMOD_FILE & 0777, fileperms( $file_path ) & 0777, 'Replaced file kept the restrictive tmp file permissions.' );

		wp_delete_post( $id, true );
	}

	/**
	 * When the filesystem abstraction can't chmod, the native fallback must still fix the file.
	 */
	public function test_replace_falls_back_to_native_chmod() {
		global $wp_filesystem;

		$real_filesystem = $wp_filesystem;

		$id        = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' );
		$model     = new Optml_Attachment_Model( $id );
		$file_path = $model->get_source_file_path();

		$tmp_file = self::FILESTASH . 'replace-fallback.jpg';
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/small-1.jpg', $tmp_file, true );
		chmod( $tmp_file, 0600 );

		$replacer = new Optml_Attachment_Replace(
			$id,
			[
				'name'     => 'replace-fallback.jpg',
				'type'     => 'image/jpeg',
				'tmp_name' => $tmp_file,
			]
		);

		// After the constructor: it calls WP_Filesystem(), which reassigns the global.
		$wp_filesystem = self::failing_chmod_filesystem();

		try {
			$result = $replacer->replace();
		} finally {
			$wp_filesystem = $real_filesystem;
		}

		clearstatcache( true, $file_path );

		$this->assertTrue( $result, 'Replacement operation failed.' );
		$this->assertSame( FS_CHMOD_FILE & 0777, fileperms( $file_path ) & 0777, 'The native chmod fallback did not normalize the permissions.' );

		wp_delete_post( $id, true );
	}

	/**
	 * With both chmod attempts failing, the replacement must not be reported as a plain success.
	 */
	public function test_replace_reports_error_when_permissions_cannot_be_normalized() {
		global $wp_filesystem;

		$real_filesystem = $wp_filesystem;

		$id        = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' );
		$model     = new Optml_Attachment_Model( $id );
		$file_path = $model->get_source_file_path();

		$tmp_file = self::FILESTASH . 'replace-unfixable.jpg';
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/small-1.jpg', $tmp_file, true );
		chmod( $tmp_file, 0600 );

		$replacer = new Optml_Attachment_Replace(
			$id,
			[
				'name'     => 'replace-unfixable.jpg',
				'type'     => 'image/jpeg',
				'tmp_name' => $tmp_file,
			]
		);

		// After the constructor: it calls WP_Filesystem(), which reassigns the global.
		$wp_filesystem = self::unfixable_filesystem();

		// The metadata step warns about the intentionally missing file; PHPUnit turns that into an error.
		set_error_handler( '__return_true' );

		try {
			$result = $replacer->replace();
		} finally {
			restore_error_handler();
			$wp_filesystem = $real_filesystem;
		}

		clearstatcache( true, $file_path );

		$this->assertWPError( $result, 'Replacement was reported as a success.' );
		$this->assertSame( 'file_permissions_error', $result->get_error_code() );
		$this->assertFileDoesNotExist( $file_path, 'The test did not exercise an unfixable file.' );

		wp_delete_post( $id, true );
	}

	/**
	 * A direct filesystem whose chmod always fails, as an FTP/SSH transport can.
	 *
	 * @return WP_Filesystem_Direct
	 */
	private static function failing_chmod_filesystem() {
		return new class( null ) extends WP_Filesystem_Direct {
			public function chmod( $file, $mode = false, $recursive = false ) {
				return false;
			}
		};
	}

	/**
	 * A filesystem that reports a successful move but leaves nothing at the destination.
	 *
	 * Both chmod attempts then fail with ENOENT, which is the only way to reach the failure
	 * branch without root: a file the test user owns can always be chmod'ed natively.
	 *
	 * @return WP_Filesystem_Direct
	 */
	private static function unfixable_filesystem() {
		return new class( null ) extends WP_Filesystem_Direct {
			public function move( $source, $destination, $overwrite = false ) {
				@unlink( $source );
				@unlink( $destination );

				return true;
			}

			public function chmod( $file, $mode = false, $recursive = false ) {
				return false;
			}
		};
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
