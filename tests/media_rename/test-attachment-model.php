<?php
/**
 * Test class for Optml_Attachment_Model.
 */

/**
 * Class Test_Attachment_Model.
 */
class Test_Attachment_Model extends WP_UnitTestCase {
	protected static $unscaled_id;
	protected static $scaled_id;
	protected static $remote_id;

	protected static $unscaled_model;
	protected static $scaled_model;
	protected static $remote_model;

	const MOCK_REMOTE_ATTACHMENT = [
		'url'  => 'https://cloudUrlTest.test/w:auto/h:auto/q:auto/id:b1b12ee03bf3945d9d9bb963ce79cd4f/https://test-site.test/9.jpg',
		'meta' =>
			[
				'originalHeight' => 1800,
				'originalWidth'  => 1200,
				'updateTime'     => 1688553629048,
				'resourceS3'     => 'randomHashForImage1',
				'mimeType'       => 'image/jpeg',
				'userKey'        => 'mlckcuxuuuyb',
				'fileSize'       => 171114,
				'originURL'      => 'https://test-site.test/wp-content/uploads/2023/07/9.jpg',
				'domain_hash'    => 'dWwtcG9sZWNhdC15dWtpLmluc3Rhd3AueHl6',
			],
	];

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$unscaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample-test.jpg' );
		self::$scaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/3000x3000.jpg' );
		
		$plugin = Optml_Main::instance();
		self::$remote_id = $plugin->dam->insert_attachments( [ self::MOCK_REMOTE_ATTACHMENT ] )[0];

		self::$unscaled_model = new Optml_Attachment_Model( self::$unscaled_id );
		self::$scaled_model = new Optml_Attachment_Model( self::$scaled_id );
		self::$remote_model = new Optml_Attachment_Model( self::$remote_id );
	}

	public static function tear_down_after_class() {
		wp_delete_post( self::$unscaled_id, true );
		wp_delete_post( self::$scaled_id, true );
		wp_delete_post( self::$remote_id, true );
		parent::tear_down_after_class();
	}

	public function test_barebones() {
		$this->assertInstanceOf( 'WP_Post', get_post( self::$unscaled_id ) );
		$this->assertInstanceOf( 'WP_Post', get_post( self::$scaled_id ) );
		$this->assertInstanceOf( 'WP_Post', get_post( self::$remote_id ) );
	}

	public function test_models() {
		$this->test_model( self::$unscaled_id, self::$unscaled_model );
		$this->test_model( self::$scaled_id, self::$scaled_model, true );
		$this->test_model( self::$remote_id, self::$remote_model, false, true );
	}

	private function test_model( $id, $model, $scaled = false, $remote = false ) {
		$this->test_basic_getters( $id, $model );
		$this->test_filename_methods( $model );
		$this->test_image_sizes_methods( $model );
		$this->test_metadata_prefix_path( $model );
		$this->assertEquals( $scaled, $model->is_scaled() );
		$this->assertIsBool( $model->can_be_renamed_or_replaced() );
		$this->assertEquals( ! $remote, $model->can_be_renamed_or_replaced() );
	}

	/**
	 * Test basic model getters.
	 *
	 * @param int $id Post ID.
	 * @param Optml_Attachment_Model $model The model to test.
	 *
	 * @return void
	 */
	private function test_basic_getters( $id, $model ) {
		$this->assertEquals( $id, $model->get_attachment_id() );
		$this->assertNotEmpty( $model->get_attachment_metadata() );

		if( $model->can_be_renamed_or_replaced() ) {
			$this->assertNotEmpty( $model->get_main_url() );
			$this->assertNotEmpty( $model->get_source_file_path() );
			$this->assertNotEmpty( $model->get_dir_path() );
			$this->assertEquals( 'jpg', $model->get_extension() );
		} else {
			$this->assertFalse( $model->get_main_url() );
			$this->assertEmpty( $model->get_source_file_path() );
			$this->assertEmpty( $model->get_dir_path() );
			$this->assertEmpty( $model->get_extension() );
		}
	}

	private function test_filename_methods( $model ) {
		if( ! $model->can_be_renamed_or_replaced() ) {
			return;
		}
		$this->assertNotEmpty( $model->get_filename_no_ext() );
		$this->assertEquals( $model->get_filename_with_ext(), $model->get_filename_no_ext() . '.' . $model->get_extension() );
		$this->assertEquals( $model->get_filename_with_ext( true ), $model->get_filename_no_ext() . '-scaled.' . $model->get_extension() );
		$this->assertNotEmpty( $model->get_filename_with_ext() );
		$this->assertStringContainsString( '-scaled', $model->get_filename_with_ext(true) );
	}

	private function test_image_sizes_methods( $model ) {
		$sizes_paths = $model->get_all_image_sizes_paths();
		$sizes_urls  = $model->get_all_image_sizes_urls();

		$this->assertIsArray( $sizes_paths );
		$this->assertIsArray( $sizes_urls );

		foreach ( $sizes_paths as $size => $path ) {
			$this->assertNotEmpty( $path );
			$this->assertArrayHasKey( $size, $sizes_urls );
			$this->assertNotEmpty( $sizes_urls[ $size ] );

			$this->assertArrayHasKey( $size, $sizes_paths );
			$this->assertNotEmpty( $path );
		}
	}

	private function test_metadata_prefix_path( $model ) {
		$prefix_path = $model->get_metadata_prefix_path();
		$this->assertNotEmpty( $prefix_path );
		$this->assertIsString( $prefix_path );

		$metadata = $model->get_attachment_metadata();
		$this->assertArrayHasKey( 'file', $metadata );
		$this->assertStringContainsString( $prefix_path, $metadata['file'] );
	}
}
