<?php
/**
 * Test class for Optml_Attachment_Rename.
 */

/**
 * Class Test_Attachment_Rename.
 */

require_once 'attachment_edit_utils.php';

class Test_Attachment_Rename extends WP_UnitTestCase {
	use Attachment_Edit_Utils;

	/**
	 * @dataProvider rename_provider
	 */
	public function test_rename( $id, $model, $new_filename, $scaled = false ) {
		$renamer = new Optml_Attachment_Rename( $id, $new_filename );
		$result = $renamer->rename();

		$this->assertTrue( $result );

		$new_model = new Optml_Attachment_Model( $id );

		$this->assertStringContainsString( $new_filename,  $new_model->get_filename_no_ext() );
		$this->check_rename_with_models( $new_model, $model, $scaled );
	}

	public function rename_provider( $callee ) {
		$unscaled = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );
		$scaled   = $this->create_attachment_get_id( OPTML_PATH . 'tests/assets/rename-scaled.jpg' );

		$unscaled_model = new Optml_Attachment_Model( $unscaled );
		$scaled_model   = new Optml_Attachment_Model( $scaled );

		return [
			[ $unscaled, $unscaled_model, 'renamed-image' ],
			[ $scaled, $scaled_model, 'big-file-rename', true ],
		];
	}

	private function check_rename_with_models( $new, $old, $scaled = false ) {
		$this->assertNotEquals( $old->get_filename_no_ext(), $new->get_filename_no_ext() );

		$old_meta = $old->get_attachment_metadata();
		$new_meta = $new->get_attachment_metadata();

		$this->assertStringContainsString( $new->get_filename_with_ext($scaled), $new_meta['file'] );
		$this->assertStringContainsString( $old->get_filename_with_ext($scaled), $old_meta['file'] );

		foreach ( $old_meta['sizes'] as $id => $size_args ) {
			$this->assertArrayHasKey( $id, $new_meta['sizes'] );
			$this->assertNotEquals( $size_args['file'], $new_meta['sizes'][ $id ]['file'] );
			$this->assertStringContainsString( $new->get_filename_no_ext(), $new_meta['sizes'][ $id ]['file'] );
			$this->assertStringContainsString( $old->get_filename_no_ext(), $old_meta['sizes'][ $id ]['file'] );
		}

		// check actual files.
		$new_src = $new->get_source_file_path();
		$old_src = $old->get_source_file_path();

		$this->assertTrue( file_exists( $new_src ) );
		$this->assertFalse( file_exists( $old_src ) );
	}
}
