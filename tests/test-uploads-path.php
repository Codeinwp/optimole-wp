<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 */

use OptimoleWP\Media\UploadsPath;

/**
 * Attachment file paths are accepted only inside the uploads directory.
 */
class Test_Uploads_Path extends WP_UnitTestCase {

	public function test_file_in_the_uploads_directory_is_accepted(): void {
		$this->assertTrue( UploadsPath::contains( wp_get_upload_dir()['basedir'] . '/photo.jpg' ) );
	}

	public function test_file_reached_with_dot_dot_is_refused(): void {
		$this->assertFalse( UploadsPath::contains( wp_get_upload_dir()['basedir'] . '/../wp-config.php' ) );
	}

	public function test_file_in_a_folder_that_does_not_exist_is_refused(): void {
		$this->assertFalse( UploadsPath::contains( wp_get_upload_dir()['basedir'] . '/optml-missing-folder/photo.jpg' ) );
	}
}
