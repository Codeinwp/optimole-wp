<?php
/**
 * Test class for Optml_Attachment_Edit.
 */

/**
 * Class Test_Attachment_Edit.
 *
 * Extends WP_Ajax_UnitTestCase so that:
 * - DOING_AJAX is true, ensuring wp_send_json_* calls wp_die() rather than die()
 * - wp_die() is overridden to throw WPAjaxDieContinueException / WPAjaxDieStopException
 *   after storing the response in $this->_last_response
 * - _handleAjax() dispatches wp_ajax_* actions and captures JSON output cleanly
 */
class Test_Attachment_Edit extends WP_Ajax_UnitTestCase {
	/**
	 * Test instance
	 *
	 * @var Optml_Attachment_Edit
	 */
	private $instance;

	/**
	 * A real JPEG attachment (file on disk) shared across replace_file tests.
	 *
	 * @var int
	 */
	private static $jpeg_attachment_id;

	/**
	 * Create a real JPEG attachment once for the whole class.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$jpeg_attachment_id = $factory->attachment->create_upload_object(
			OPTML_PATH . 'tests/assets/sample-test.jpg'
		);
	}

	/**
	 * Clean up the shared attachment after all tests in this class run.
	 */
	public static function tear_down_after_class() {
		wp_delete_post( self::$jpeg_attachment_id, true );
		parent::tear_down_after_class();
	}

	/**
	 * Setup test
	 */
	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( 1 );

		$this->instance = new Optml_Attachment_Edit();

		// Register the wp_ajax_optml_replace_file action so _handleAjax() can
		// invoke replace_file() through the normal WordPress AJAX dispatch path.
		$this->instance->init();
	}

	/**
	 * Reset $_FILES after each test (WP_Ajax_UnitTestCase resets $_POST/$_GET).
	 */
	public function tearDown(): void {
		$_FILES = [];
		parent::tearDown();
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

	/**
	 * Dispatch the optml_replace_file AJAX action with the given POST data and
	 * $_FILES, catch the wp_die() exception, and return the decoded JSON.
	 *
	 * _handleAjax() starts its own output buffer and the die handler stores
	 * the captured JSON in $this->_last_response before throwing, so no manual
	 * ob_start() is needed here.
	 *
	 * @param array $post  Contents for $_POST (nonce and attachment_id).
	 * @param array $files Contents for $_FILES['file'], or empty to omit.
	 * @return array Decoded JSON response array, or empty array.
	 */
	private function call_replace_file( array $post, array $files = [] ): array {
		$_POST  = $post;
		$_FILES = $files;

		try {
			$this->_handleAjax( 'optml_replace_file' );
		} catch ( WPAjaxDieContinueException $e ) {
			// Normal path: wp_send_json_* echoed JSON then called wp_die().
		} catch ( WPAjaxDieStopException $e ) {
			// Fallback: wp_die() called with a plain string (e.g. '-1' from a
			// nonce failure when $die = true). Treat as a failed response.
		}

		return ! empty( $this->_last_response )
			? ( json_decode( $this->_last_response, true ) ?? [] )
			: [];
	}

	/**
	 * A request with no nonce must be rejected with a security error.
	 */
	public function test_replace_file_no_nonce() {
		$response = $this->call_replace_file( [ 'attachment_id' => (string) self::$jpeg_attachment_id ] );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'Security check', $response['data'] );
	}

	/**
	 * A request with a syntactically valid but wrong nonce must be rejected.
	 */
	public function test_replace_file_bad_nonce() {
		$response = $this->call_replace_file( [
			'attachment_id'       => (string) self::$jpeg_attachment_id,
			'optml_replace_nonce' => 'not_a_real_nonce',
		] );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'Security check', $response['data'] );
	}

	/**
	 * attachment_id = 0 must be rejected before any file or capability check.
	 */
	public function test_replace_file_zero_id() {
		$response = $this->call_replace_file( [
			'attachment_id'       => '0',
			'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
		] );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'Invalid attachment ID', $response['data'] );
	}

	/**
	 * An ID that belongs to a non-attachment post type must be rejected.
	 */
	public function test_replace_file_non_attachment_post() {
		$post_id = self::factory()->post->create( [ 'post_type' => 'post' ] );

		$response = $this->call_replace_file( [
			'attachment_id'       => (string) $post_id,
			'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
		] );

		wp_delete_post( $post_id, true );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'Invalid attachment ID', $response['data'] );
	}

	/**
	 * A request with no $_FILES['file'] entry must be rejected.
	 */
	public function test_replace_file_no_file_uploaded() {
		$response = $this->call_replace_file( [
			'attachment_id'       => (string) self::$jpeg_attachment_id,
			'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
		] );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'No file uploaded', $response['data'] );
	}

	/**
	 * A binary blob named .jpg whose content is not a real image must be
	 * rejected: wp_check_filetype_and_ext() uses getimagesize() for image/*
	 * types and returns an empty type when the magic bytes do not match.
	 */
	public function test_replace_file_unrecognizable_mime() {
		$tmp = tempnam( sys_get_temp_dir(), 'optml_test_' );
		file_put_contents( $tmp, 'this is not image data @@##$$%%' );

		$response = $this->call_replace_file(
			[
				'attachment_id'       => (string) self::$jpeg_attachment_id,
				'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
			],
			[
				'file' => [
					'name'     => 'fake.jpg',
					'type'     => 'image/jpeg',
					'tmp_name' => $tmp,
					'error'    => 0,
					'size'     => filesize( $tmp ),
				],
			]
		);

		@unlink( $tmp );

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'determine', $response['data'] );
	}

	/**
	 * Uploading a file whose real MIME type differs from the original attachment
	 * (SVG against a JPEG attachment) must be rejected by the MIME-match check.
	 */
	public function test_replace_file_mime_mismatch() {
		$response = $this->call_replace_file(
			[
				'attachment_id'       => (string) self::$jpeg_attachment_id,
				'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
			],
			[
				'file' => [
					'name'     => 'sample.svg',
					'type'     => 'image/svg+xml',
					'tmp_name' => OPTML_PATH . 'tests/assets/sample.svg',
					'error'    => 0,
					'size'     => filesize( OPTML_PATH . 'tests/assets/sample.svg' ),
				],
			]
		);

		$this->assertFalse( $response['success'] );
		$this->assertStringContainsString( 'does not match', $response['data'] );
	}

	/**
	 * A valid nonce, valid attachment ID, and a replacement whose MIME type
	 * matches the original must pass all validation and succeed.
	 *
	 * A fresh attachment is created so the shared fixture is not consumed by
	 * the actual file-move that Optml_Attachment_Replace performs.
	 */
	public function test_replace_file_valid_jpeg_replacement() {
		$attachment_id = self::factory()->attachment->create_upload_object(
			OPTML_PATH . 'tests/assets/sample-test.jpg'
		);

		// Optml_Attachment_Replace moves (not copies) the tmp file.
		$tmp = tempnam( sys_get_temp_dir(), 'optml_repl_' ) . '.jpg';
		copy( OPTML_PATH . 'tests/assets/small-1.jpg', $tmp );

		$response = $this->call_replace_file(
			[
				'attachment_id'       => (string) $attachment_id,
				'optml_replace_nonce' => wp_create_nonce( 'optml_replace_media_nonce' ),
			],
			[
				'file' => [
					'name'     => 'small-1.jpg',
					'type'     => 'image/jpeg',
					'tmp_name' => $tmp,
					'error'    => 0,
					'size'     => filesize( $tmp ),
				],
			]
		);

		if ( file_exists( $tmp ) ) {
			@unlink( $tmp );
		}
		wp_delete_post( $attachment_id, true );

		$this->assertTrue( $response['success'] );
	}
}
