<?php
/**
 * Test class for Optml_Attachment_Edit.
 */

/**
 * Class Test_Attachment_Edit.
 */
class Test_Attachment_Edit extends WP_UnitTestCase {
	/**
	 * Test instance
	 *
	 * @var Optml_Attachment_Edit
	 */
	private $instance;

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		$this->instance = new Optml_Attachment_Edit();
	}

	/**
	 * Test prepare attachment filename
	 */
	public function test_prepare_attachment_filename() {
		$attachment = self::factory()->post->create_and_get( [
			'post_type'      => 'attachment',
			'post_mime_type' => 'image/jpeg',
		] );

		$post_data = [
			'ID'                 => $attachment->ID,
			'optml_rename_nonce' => wp_create_nonce( 'optml_rename_media_nonce' ),
			'optml_rename_file'  => 'test-file',
			'post_type'          => 'attachment',
		];

		$result = $this->instance->prepare_attachment_filename( $post_data, (array) $attachment );

		foreach ( $post_data as $key => $value ) {
			if ( $key == 'optml_rename_nonce' ) {
				continue;
			}
			$this->assertEquals( $value, $result[ $key ] );
		}

		$this->assertEquals( 'test-file', get_post_meta( $attachment->ID, '_optml_pending_rename', true ) );
	}
}
