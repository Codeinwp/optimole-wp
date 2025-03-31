<?php
/**
 * Test class for Optml_Attachment_Rename.
 */

require_once 'attachment_edit_utils.php';

/**
 * Class Test_Attachment_Rename.
 */
class Test_Attachment_Replace extends WP_UnitTestCase {
	use Attachment_Edit_Utils;

	const FILESTASH = OPTML_PATH . 'tests/assets/filestash/';

	public function set_up() {
		parent::set_up();

		if( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		global $wp_filesystem;

		$wp_filesystem->mkdir( OPTML_PATH . 'tests/assets/filestash' );

		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/rename-scaled.jpg', self::FILESTASH . 'scaled.jpg' );
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/rename-unscaled.jpg', self::FILESTASH . 'unscaled.jpg' );

		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/rename-scaled.jpg', self::FILESTASH . 'scaled-1.jpg' );
		$wp_filesystem->copy( OPTML_PATH . 'tests/assets/rename-unscaled.jpg', self::FILESTASH . 'unscaled-1.jpg' );
	}

	/**
	 * @dataProvider scaled_to_unscaled_provider, unscaled_to_scaled_provider, scaled_to_scaled_provider, unscaled_to_unscaled_provider
	 */
	public function test_replace( $id_to_replace, $replace_file, $source_scaled, $result_scaled ) {
		$model = new Optml_Attachment_Model( $id_to_replace );

		$metadata = $model->get_attachment_metadata();

		$size = $metadata['filesize'];
		$source_contents = file_get_contents( $model->get_source_file_path() );
		$this->assertTrue( $model->is_scaled() === $source_scaled );

		$replacer = new Optml_Attachment_Replace( $id_to_replace, $replace_file );
		$result = $replacer->replace();
		$this->assertTrue( $result );

		$new_model = new Optml_Attachment_Model( $id_to_replace );
		$new_size = $new_model->get_attachment_metadata()['filesize'];
		$this->assertTrue( $new_model->is_scaled() === $result_scaled );

		$this->assertNotEquals( $size, $new_size );
	}

	private function scaled_to_unscaled_provider() {
		$initial_attachment = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/3000x3000.jpg' );

		$replace_file_path = self::FILESTASH . 'unscaled.jpg';
		$replace_file = [
			'name' => 'unscaled.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => $replace_file_path,
		];

		return [
			[ $initial_attachment, $replace_file, true, false ],
		];
	}

	private function unscaled_to_scaled_provider() {
		$scaled_attachment = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/sample-test.jpg' );

		$replace_file_path = self::FILESTASH . 'scaled.jpg';
		$replace_file = [
			'name' => 'scaled.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => $replace_file_path,
		];

		return [
			[ $scaled_attachment, $replace_file, false, true ],
		];
	}

	private function scaled_to_scaled_provider() {
		$scaled_attachment = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/3000x3000.jpg' );

		$replace_file_path = self::FILESTASH . 'scaled-1.jpg';
		$replace_file = [
			'name' => 'scaled-1.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => $replace_file_path,
		];

		return [
			[ $scaled_attachment, $replace_file, true, true ],
		];
	}

	private function unscaled_to_unscaled_provider() {
		$initial_attachment = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/sample-test.jpg' );

		$replace_file_path = self::FILESTASH . 'unscaled-1.jpg';
		$replace_file = [
			'name' => 'unscaled-1.jpg',
			'type' => 'image/jpeg',
			'tmp_name' => $replace_file_path,
		];

		return [
			[ $initial_attachment, $replace_file, false, false ],
		];
	}
}
