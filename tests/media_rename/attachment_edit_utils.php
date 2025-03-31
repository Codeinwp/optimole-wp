<?php
/**
 * Test class for Optml_Attachment_Rename.
 */

/**
 * Class Test_Attachment_Rename.
 */
trait Attachment_Edit_Utils {
	protected function delete_attachment( $id ) {
		wp_delete_attachment( $id, true );
	}

	protected function create_attachment_get_id( $file ) {
		return self::factory()->attachment->create_upload_object( $file );
	}
}