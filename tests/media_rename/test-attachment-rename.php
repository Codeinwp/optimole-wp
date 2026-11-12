<?php

require_once __DIR__ . '/../outside-uploads-files.php';
/**
 * Test class for Optml_Attachment_Rename.
 */

/**
 * Class Test_Attachment_Rename.
 */
class Test_Attachment_Rename extends WP_UnitTestCase {
	use Optml_Test_Outside_Uploads;

	protected static $scaled_id;
	protected static $unscaled_id;

	protected static $scaled_model;
	protected static $unscaled_model;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$scaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-scaled.jpg' );
		self::$unscaled_id = $factory->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );

		self::$scaled_model = new Optml_Attachment_Model( self::$scaled_id );
		self::$unscaled_model = new Optml_Attachment_Model( self::$unscaled_id );
	}

	public static function tear_down_after_class() {
		wp_delete_post( self::$scaled_id, true );
		wp_delete_post( self::$unscaled_id, true );
		parent::tear_down_after_class();
	}

	public function test_barebones() {
		$this->assertInstanceOf( 'WP_Post' , get_post( self::$scaled_id ) );
		$this->assertInstanceOf( 'WP_Post' , get_post( self::$unscaled_id ) );

		$this->assertEquals('attachment', get_post_type( self::$scaled_id ) );
		$this->assertEquals('attachment', get_post_type( self::$unscaled_id ) );
	}

	public function test_renames() {
		$this->test_rename( self::$unscaled_id, self::$unscaled_model, 'renamed-image' );
		$this->test_rename( self::$scaled_id, self::$scaled_model, 'big-file-rename', true );
	}

	public function test_rename_leaves_size_files_outside_uploads_in_place(): void {
		$id      = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );
		$outside = self::outside_uploads_file( 'optml-outside-size.txt' );
		$meta    = wp_get_attachment_metadata( $id );
		$meta['sizes']['thumbnail']['file'] = self::relative_from( ( new Optml_Attachment_Model( $id ) )->get_dir_path(), $outside );
		wp_update_attachment_metadata( $id, $meta );

		( new Optml_Attachment_Rename( $id, 'outside-size-renamed' ) )->rename();

		$this->assertFileExists( $outside, 'A size file outside uploads must not be moved.' );
		wp_delete_post( $id, true );
		unlink( $outside );
	}

	public function test_rename_keeps_links_to_a_size_left_outside_uploads(): void {
		$id            = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );
		$thumbnail_url = wp_get_attachment_image_url( $id, 'thumbnail' );
		$medium_url    = wp_get_attachment_image_url( $id, 'medium' );
		$post_id       = $this->post_linking( $thumbnail_url, $medium_url );
		$outside       = self::outside_uploads_file( 'optml-outside-linked-size.txt' );
		$meta          = wp_get_attachment_metadata( $id );
		$thumbnail     = $meta['sizes']['thumbnail']['file'];
		$meta['sizes']['thumbnail']['file'] = self::relative_from( ( new Optml_Attachment_Model( $id ) )->get_dir_path(), $outside );
		wp_update_attachment_metadata( $id, $meta );

		( new Optml_Attachment_Rename( $id, 'outside-linked-renamed' ) )->rename();
		clean_post_cache( $post_id );

		$content = get_post( $post_id )->post_content;
		$this->assertStringContainsString( $thumbnail_url, $content );
		$this->assertStringContainsString( wp_json_encode( $thumbnail_url ), $content );
		$this->assertStringNotContainsString( $medium_url, $content );
		$this->assertFileExists( ( new Optml_Attachment_Model( $id ) )->get_dir_path() . '/' . $thumbnail );
		wp_delete_post( $id, true );
		unlink( $outside );
	}

	public function test_rename_updates_links_to_a_size_moved_under_another_name(): void {
		$id            = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );
		$thumbnail_url = wp_get_attachment_image_url( $id, 'thumbnail' );
		$post_id       = $this->post_linking( $thumbnail_url );
		$outside       = self::outside_uploads_file( 'optml-outside-twin-size.txt' );
		$meta          = wp_get_attachment_metadata( $id );
		$meta['sizes']['optml_twin']        = $meta['sizes']['thumbnail'];
		$meta['sizes']['thumbnail']['file'] = self::relative_from( ( new Optml_Attachment_Model( $id ) )->get_dir_path(), $outside );
		wp_update_attachment_metadata( $id, $meta );

		( new Optml_Attachment_Rename( $id, 'outside-twin-renamed' ) )->rename();
		clean_post_cache( $post_id );

		$this->assertStringNotContainsString( $thumbnail_url, get_post( $post_id )->post_content );
		wp_delete_post( $id, true );
		unlink( $outside );
	}

	/**
	 * Create a post that links each URL as an image and in JSON block attributes.
	 *
	 * The content is written directly, because saving a post strips the escaped slashes of the JSON.
	 *
	 * @param string ...$urls Image URLs.
	 *
	 * @return int The post ID.
	 */
	private function post_linking( string ...$urls ): int {
		global $wpdb;

		$content = '';
		foreach ( $urls as $url ) {
			$content .= '<!-- wp:image ' . wp_json_encode( [ 'url' => $url ] ) . ' --><img src="' . $url . '"/><!-- /wp:image -->';
		}
		$post_id = self::factory()->post->create();
		$wpdb->update( $wpdb->posts, [ 'post_content' => $content ], [ 'ID' => $post_id ] );

		return $post_id;
	}

	public function test_rename_refuses_a_main_file_outside_uploads(): void {
		$id      = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/rename-unscaled.jpg' );
		$outside = self::outside_uploads_file( 'optml-outside-main.jpg' );
		update_post_meta( $id, '_wp_attached_file', self::relative_from( wp_get_upload_dir()['basedir'], $outside ) );

		$result = ( new Optml_Attachment_Rename( $id, 'outside-main-renamed' ) )->rename();

		$this->assertWPError( $result );
		$this->assertFileExists( $outside, 'A main file outside uploads must not be moved.' );
		wp_delete_post( $id, true );
		unlink( $outside );
	}

	private function test_rename( $id, $model, $new_filename, $scaled = false ) {
		$renamer = new Optml_Attachment_Rename( $id, $new_filename );
		$result = $renamer->rename();

		$this->assertTrue( $result );

		$new_model = new Optml_Attachment_Model( $id );

		$this->assertStringContainsString( $new_filename,  $new_model->get_filename_no_ext() );
		$this->check_rename_with_models( $new_model, $model, $scaled );
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
