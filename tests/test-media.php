<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

use WpOrg\Requests\Utility\CaseInsensitiveDictionary;

/**
 * Class Test_Generic.
 */
class Test_Media extends WP_UnitTestCase {
	const IMG_TAGS = '<!-- wp:image {"id":5,"sizeSlug":"medium"} -->
<figure class="wp-block-image size-large"><img src="https://example.i.optimole.com/BOxmgcE-_HsxvBf4/w:300/h:200/q:90/id:b19fed3de30366eb76682becf7645c7b/sample-test.jpg" alt="" class="wp-image-1339"/></figure>
<!-- /wp:image --> ';
	use Optml_Dam_Offload_Utils;
	public static $files = [
		'sample-test',
		'1PQ7p',
		'3000x3000',
		'special-characters-•⋿∀'
	];
	public static $sample_post;
	public static $sample_attachement;
	private static $sample_attachement_upper_case;
	private static $sample_attachment_scaled;
	public static $error_mark = false;
	/**
	 * Offloaded images the mocked account reports, out of an offload limit of 5000.
	 *
	 * @var int
	 */
	public static $offloaded_images = 50;
	/**
	 * Mock the api calls and remote images requests
	 */
	public function filter_pre_http_request( $false, $r, $url ) {
		if ( strpos($url, 'example.i.optimole.com' ) !== false ) {
			return array
			(
				'headers' => new CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => 'some image data',
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}
		if ( $url === "https://generateurls-prod.i.optimole.com/upload" ) {

			$body = '{"uploadUrl":"https://uploadUrl?Content-Type=image/jpeg","tableId":"579c7f7707ce87caa65fdf50c238a117" }';

			if ( strpos($r['body'], '"getUrl":"true"') !== false ) {
				$body = '{"getUrl": "getUrl"}';
			}
			
			return array
			(
				'headers' => new CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => $body,
				'response' => array
				(
					'code' => self::$error_mark ? 500 : 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}
		if ( str_starts_with( $url, "https://uploadUrl" ) ) {
			return array
			(
				'headers' => new CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => '',
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}
		if ( strpos($url, 'dashboard.optimole.com/api/optml/v2/account/details' ) !== false ) {
			return array
			(
				'headers' => new CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => json_encode(array(
					'data' => array(
						'cdn_key' => 'cdn_key',
						'cdn_secret' => 'cdn_secret',
						'offloaded_images' => self::$offloaded_images,
						'offload_limit' => 5000,
					),
				)),

				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),

				'cookies' => array
				(),

				'filename' => '',
			);
		}
		
		if ($url === "getUrl" ) {
			//the get url is used by download_url to create a temporary image for wp_handle_sideload
			//since we can not mock the external image stream response we move it manually over the temporary empty file
			copy(OPTML_PATH . 'tests/assets/sample-test.jpg', $r['filename']);
			return array
			(
				'headers' => new CaseInsensitiveDictionary (
					array(
						'content-type' => 'application/json',
						'content-length' => 1182,
						'date' => 'Fri, 23 Oct 2020 11:42:49 GMT',
					)),
				'body' => '',
				'response' => array
				(
					'code' => 200,
					'message' => 'OK'
				),
				
				'cookies' => array
				(),
				
				'filename' => '',
			);
		}

		return $false;
	}

	public function setUp() : void {


		parent::setUp();
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'example',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org','external.com' ],

		] );
		$settings->update( 'no_script', 'enabled' );
		$settings->update( 'lazyload', 'enabled' );
		$settings->update( 'offload_media', 'enabled' );
		$settings->update( 'lazyload_placeholder', 'disabled' );
		$settings->update( 'quality', 90 );
		$settings->update( 'cdn', 'enabled' );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		Optml_Media_Offload::instance();


		add_filter( 'pre_http_request', array($this,'filter_pre_http_request'), 10, 3 );
		self::$sample_post        = self::factory()->post->create( [
				'post_title'   => 'Test post',
				'post_content' => self::IMG_TAGS,
			]
		);

		self::unlinkRecursive(WP_CONTENT_DIR . '/uploads/**', self::$files);
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[0].'.jpg' );
		self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[1].'.jpg');
		self::$sample_attachement_upper_case = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[1].'.jpg' );
		self::$sample_attachment_scaled = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[2].'.jpg' );


	}
	public function set_editor(){
		return array( 'WP_Image_Editor_Mock' );
	}
	static function unlinkRecursive( $pattern, $files ) {
		global $wp_filesystem;
		require_once( ABSPATH . '/wp-admin/includes/file.php' );
		WP_Filesystem();
		foreach ( glob( $pattern ) as $file ) {
			if ( is_dir( $file ) ) {
				// Recursive call to handle subdirectories
				self::unlinkRecursive( $file . '/*', $files );
			} else {
				foreach ( $files as $file_name ) {
					if ( strpos( basename( $file ), $file_name ) !== false ) {
						$wp_filesystem->delete( $file );
					}
				}
			}
		}
	}
	/**
	 * While a rollback runs (offload_media off, rollback_status on), offloaded images are still
	 * served from Optimole, but a new upload stays in the media library.
	 */
	public function test_upload_during_rollback_stays_local(): void {
		$settings = new Optml_Settings();
		$settings->update( 'offload_media', 'disabled' );
		$settings->update( 'rollback_status', 'enabled' );

		// The hooks of this request, registered with the rollback state.
		remove_all_filters( 'wp_generate_attachment_metadata' );
		remove_all_filters( 'wp_insert_attachment_data' );
		Optml_Media_Offload::instance();

		$this->assertFalse( has_filter( 'wp_generate_attachment_metadata' ) );
		$this->assertFalse( has_filter( 'wp_insert_attachment_data' ) );
		$this->assertNotFalse( has_filter( 'wp_get_attachment_url' ), 'Offloaded images are still served from Optimole.' );

		$attachment_id = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/' . self::$files[0] . '.jpg' );
		$meta          = wp_get_attachment_metadata( $attachment_id );

		$this->assertFalse( Optml_Media_Offload::is_uploaded_image( $meta['file'] ) );
		$this->assertFileExists( get_attached_file( $attachment_id ) );
	}

	public function test_retryable_error() {
		Optml_Media_Offload::instance()->rollback_and_update_images([self::$sample_attachement]);
		self::$error_mark = true;
		Optml_Media_Offload::instance()->upload_and_update_existing_images([self::$sample_attachement]);
		$this->assertEquals(1, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );

		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringNotContainsString( 'id:', $content );
		Optml_Media_Offload::instance()->upload_images( 2 );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertEquals(2, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertStringNotContainsString( 'id:', $content );
		self::$error_mark = false;
		Optml_Media_Offload::instance()->upload_images( 2 );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringContainsString( 'id:', $content );
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true ) );
	}
	public function test_retryable_error_and_error() {
		Optml_Media_Offload::instance()->rollback_and_update_images([self::$sample_attachement]);
		self::$error_mark = true;
		Optml_Media_Offload::instance()->upload_and_update_existing_images([self::$sample_attachement]);
		$this->assertEquals(1, (int)get_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, true));
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringNotContainsString( 'id:', $content );

		update_post_meta(self::$sample_attachement, Optml_Media_Offload::RETRYABLE_META_COUNTER, 5);
		Optml_Media_Offload::instance()->upload_images( 2 );

		$this->assertNotEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::META_KEYS['offload_error'], true ) );
		self::$error_mark = false;
	}
	public function test_duplicated_image() {

		$content =  wp_get_attachment_image( self::$sample_attachement_upper_case );
		$this->assertStringContainsString(  "src=\"https://example.i.optimole.com/w:150/h:150/rt:fill/g:ce/q:mauto/process:". self::$sample_attachement_upper_case ."/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/1PQ7p-2.jpg\" ", $content);

	}
	public function test_page_images_process() {
		Optml_Tag_Replacer::$lazyload_skipped_images = 4;
		$content =  wp_get_attachment_image( self::$sample_attachement );
		$this->assertStringContainsString( 'example.i.optimole.com', $content );
		$settings = new Optml_Settings();
		$settings->update( 'quality', 75 );
		$settings->update( 'autoquality', 'disabled' );
		$settings->update( 'best_format', 'enabled' );
		$settings->update( 'avif', 'enabled' );
		$settings->update( 'service_data', [
			'cdn_key'    => 'whatever',
			'cdn_secret' => 'test',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Url_Replacer::instance()->init();
		Optml_Tag_Replacer::instance()->init();
		Optml_Manager::instance()->init();
		Optml_Config::init(
			array(
				'key'    => 'whatever',
				'secret' => 'test',
				'domain' => 'my_costum_domain',
			)
		);
		$replaced_content = Optml_Manager::instance()->replace_content( $content );
		$this->assertEquals(  7, substr_count($replaced_content, 'https://my_costum_domain'));
		$this->assertStringContainsString( '/w:150/h:150/q:75/rt:fill/g:ce/f:best/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $replaced_content );
		$this->assertStringContainsString( '/w:150/h:150/q:eco/f:best/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $replaced_content );
	}

	public function test_image_processed() {

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');
		$my_size_image = wp_get_attachment_image_src(self::$sample_attachement, 'my_size_crop' );

		$this->assertStringContainsString( 'example.i.optimole.com', $my_size_image[0] );
		$this->assertStringContainsString('/w:200/h:200/rt:fill/g:nowe/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $my_size_image[0]);

		$this->assertStringContainsString( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertStringContainsString( '/w:150/h:150/rt:fill/g:ce/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_thumbnail_size[0] );

		$this->assertStringContainsString( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertStringContainsString( '/w:300/h:200/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_medium_size[0] );
		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringContainsString( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );
	}
	public function test_retina_images() {
		Optml_Media_Offload::instance()->settings->update( 'retina_images', 'enabled' ); 
		$image_medium_size = wp_get_attachment_image(self::$sample_attachement, 'medium');
		$this->assertStringContainsString( '/dpr:2/', $image_medium_size );
		Optml_Media_Offload::instance()->settings->update( 'retina_images', 'disabled' ); 
		$image_medium_size = wp_get_attachment_image(self::$sample_attachement, 'medium');
		$this->assertStringNotContainsString( '/dpr:2/', $image_medium_size );
	}

	public function test_image_sync() {
		Optml_Media_Offload::instance()->rollback_images( 100 );
		Optml_Media_Offload::instance()->upload_images( 100 );


		$this->assertFalse( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringContainsString( '/id:579c7f7707ce87caa65fdf50c238a117', $image_meta['file'] );

		$image_medium_size = wp_get_attachment_image_src(self::$sample_attachement, 'medium');
		$image_thumbnail_size = wp_get_attachment_image_src(self::$sample_attachement, 'thumbnail');

		$this->assertStringContainsString( 'example.i.optimole.com', $image_thumbnail_size[0] );
		$this->assertStringContainsString( '/w:150/h:150/rt:fill/g:ce/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/sample-test.jpg', $image_thumbnail_size[0] );


		$this->assertStringContainsString( 'example.i.optimole.com', $image_medium_size[0] );
		$this->assertStringContainsString( '/w:300/h:200/q:mauto/process:' . self::$sample_attachement .'/id:579c7f7707ce87caa65fdf50c238a117', $image_medium_size[0] );
	}

	/**
	 * "Transfer back" in the dashboard turns offload_media off and starts the rollback; WP-CLI
	 * `wp optimole media rollback_images` must still be able to continue it.
	 *
	 * @dataProvider transfer_gate_provider
	 */
	public function test_can_transfer_images( $offload_media, $rollback_status, $action, $expected ) {
		$settings = new Optml_Settings();
		$settings->update( 'offload_media', $offload_media );
		$settings->update( 'rollback_status', $rollback_status );

		$this->assertSame( $expected, $settings->can_transfer_images( $action ) );
	}

	/**
	 * @return array<string, array{0: string, 1: string, 2: string, 3: bool}>
	 */
	public function transfer_gate_provider() {
		return [
			'offload with offload on'            => [ 'enabled', 'disabled', 'offload', true ],
			'rollback with offload on'           => [ 'enabled', 'disabled', 'rollback', true ],
			'rollback while a rollback runs'     => [ 'disabled', 'enabled', 'rollback', true ],
			'offload while a rollback runs'      => [ 'disabled', 'enabled', 'offload', false ],
			'rollback with offload off and idle' => [ 'disabled', 'disabled', 'rollback', false ],
			'offload with offload off and idle'  => [ 'disabled', 'disabled', 'offload', false ],
		];
	}

	/**
	 * Run a `wp optimole media` command with the WP-CLI stand-ins.
	 *
	 * @param string $command The command method, 'rollback_images' or 'move_to_optimole'.
	 * @return string[] The lines the command printed.
	 */
	private function run_cli_media_command( $command ) {
		require_once __DIR__ . '/wp-cli-stub.php';
		WP_CLI::$lines = [];

		( new Optml_Cli_Media() )->$command();

		return WP_CLI::$lines;
	}

	public function test_cli_rollback_continues_a_rollback_started_from_the_dashboard() {
		// "Transfer back from Optimole": offload_media goes off, the rollback is in progress.
		$settings = new Optml_Settings();
		$settings->update( 'offload_media', 'disabled' );
		$settings->update( 'rollback_status', 'enabled' );

		$lines = $this->run_cli_media_command( 'rollback_images' );

		$this->assertContains( 'All images have been uploaded to your media library', $lines );
		$this->assertStringNotContainsString( 'id:', wp_get_attachment_metadata( self::$sample_attachement )['file'] );
		$this->assertFileExists( get_attached_file( self::$sample_attachement ) );
	}

	public function test_cli_rollback_is_not_stopped_by_the_offload_limit() {
		$settings = new Optml_Settings();
		$settings->update( 'offload_limit_reached', 'enabled' );

		$lines = $this->run_cli_media_command( 'rollback_images' );

		$this->assertContains( 'All images have been uploaded to your media library', $lines );
		$this->assertStringNotContainsString( 'id:', wp_get_attachment_metadata( self::$sample_attachement )['file'] );
	}

	public function test_cli_offload_stops_at_the_offload_limit() {
		// Bring the images back first, so there is something to offload; the account is at its limit.
		Optml_Media_Offload::instance()->rollback_images( 100 );
		self::$offloaded_images = 5000;
		require_once __DIR__ . '/wp-cli-stub.php';

		try {
			$this->run_cli_media_command( 'move_to_optimole' );
			$this->fail( 'The offload was not stopped at the offload limit.' );
		} catch ( Optml_Test_WP_CLI_Error $error ) {
			$this->assertStringContainsString( 'maximum offloading limit', $error->getMessage() );
		} finally {
			self::$offloaded_images = 50;
		}

		$this->assert_moving_images_lock_is_free();
	}

	/**
	 * A second database connection: for advisory locks, another process.
	 *
	 * @return wpdb
	 */
	private function other_connection(): wpdb {
		return new wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
	}

	/**
	 * Hold or release the "moving images" lock from another connection.
	 *
	 * @param wpdb $connection The other connection.
	 * @param bool $hold       True to take the lock, false to release it.
	 * @return void
	 */
	private function moving_images_lock_elsewhere( wpdb $connection, bool $hold ): void {
		$query = $hold ? 'SELECT GET_LOCK( %s, 0 )' : 'SELECT RELEASE_LOCK( %s )';

		$this->assertSame( '1', $connection->get_var( $connection->prepare( $query, Optml_Media_Offload::get_moving_images_lock_name() ) ) );
	}

	/**
	 * @return void
	 */
	private function assert_moving_images_lock_is_free(): void {
		global $wpdb;

		$this->assertSame( '1', $wpdb->get_var( $wpdb->prepare( 'SELECT IS_FREE_LOCK( %s )', Optml_Media_Offload::get_moving_images_lock_name() ) ), 'The "moving images" lock was not released.' );
	}

	/**
	 * A background step is moving images at this moment. The command must not move the same images
	 * at the same time; it runs once that step is done.
	 */
	public function test_cli_rollback_waits_for_a_run_that_is_moving_images(): void {
		$other     = $this->other_connection();
		$offloaded = Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' );
		$this->moving_images_lock_elsewhere( $other, true );
		require_once __DIR__ . '/wp-cli-stub.php';

		try {
			$this->run_cli_media_command( 'rollback_images' );
			$this->fail( 'The command ran while another run was moving images.' );
		} catch ( Optml_Test_WP_CLI_Error $error ) {
			$this->assertStringContainsString( 'Another image transfer is running', $error->getMessage() );
		}

		$this->assertGreaterThan( 0, $offloaded );
		$this->assertSame( $offloaded, Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' ), 'The command moved no image.' );

		$this->moving_images_lock_elsewhere( $other, false );
		$lines = $this->run_cli_media_command( 'rollback_images' );

		$this->assertContains( 'All images have been uploaded to your media library', $lines );
		$this->assertSame( 0, Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' ) );
	}

	/**
	 * While the command (another connection here) moves the images, a background step that fires
	 * does nothing: it moves no image and does not continue the chain. Once the lock is free, the
	 * step runs and schedules the next one.
	 */
	public function test_background_step_does_nothing_while_another_run_moves_images(): void {
		( new Optml_Settings() )->update( 'rollback_status', 'enabled' );
		$other     = $this->other_connection();
		$token     = Optml_Media_Offload::acquire_transfer_lock( 'rollback_images' );
		$offloaded = Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' );
		$this->moving_images_lock_elsewhere( $other, true );

		Optml_Media_Offload::instance()->start_processing_images( 'rollback_images', 10, 1, 5, 1, $token );

		$this->assertGreaterThan( 0, $offloaded );
		$this->assertSame( $offloaded, Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' ), 'The step moved no image.' );
		$this->assertSame( 0, $this->count_scheduled_transfer_steps() );

		$this->moving_images_lock_elsewhere( $other, false );
		Optml_Media_Offload::instance()->start_processing_images( 'rollback_images', 10, 1, 5, 1, $token );

		$this->assertSame( 1, $this->count_scheduled_transfer_steps() );
		$this->assert_moving_images_lock_is_free();
	}

	/**
	 * @return int The background transfer steps waiting in WP-Cron.
	 */
	private function count_scheduled_transfer_steps(): int {
		$count = 0;

		foreach ( (array) _get_cron_array() as $hooks ) {
			$count += isset( $hooks['optml_start_processing_images'] ) ? count( $hooks['optml_start_processing_images'] ) : 0;
		}

		return $count;
	}

	/**
	 * Where cron is stuck, the background transfer is scheduled and holds the transfer lock, but no
	 * step runs. The command does the work, and leaves the transfer lock to the chain.
	 */
	public function test_cli_rollback_runs_while_a_background_transfer_is_only_scheduled(): void {
		$scheduled = Optml_Media_Offload::acquire_transfer_lock( 'rollback_images' );

		$lines = $this->run_cli_media_command( 'rollback_images' );

		$this->assertContains( 'All images have been uploaded to your media library', $lines );
		$this->assertSame( 0, Optml_Media_Offload::number_of_images_and_pages( 'rollback_images' ) );
		$this->assert_moving_images_lock_is_free();
		$this->assertTrue( Optml_Media_Offload::renew_transfer_lock( $scheduled, 'rollback_images' ) );
	}

	public function test_cli_releases_the_moving_images_lock_when_there_is_nothing_to_move(): void {
		// Every image is offloaded already.
		$lines = $this->run_cli_media_command( 'move_to_optimole' );

		$this->assertContains( 'All images have been uploaded to Optimole Cloud', $lines );
		$this->assert_moving_images_lock_is_free();
	}

	/**
	 * A fatal error skips the cleanup of the run. The lock is then released when the request shuts
	 * down, which matters on a persistent connection.
	 */
	public function test_moving_images_lock_is_also_released_at_shutdown(): void {
		$this->assertTrue( Optml_Media_Offload::acquire_moving_images_lock() );

		$this->assertNotFalse( has_action( 'shutdown', [ Optml_Media_Offload::class, 'release_moving_images_lock' ] ) );

		Optml_Media_Offload::release_moving_images_lock();
		$this->assert_moving_images_lock_is_free();
	}

	/**
	 * With several database servers the lock must be asked from the primary by every run. HyperDB
	 * and LudicrousDB route the following reads there with send_reads_to_masters().
	 */
	public function test_moving_images_lock_is_taken_on_the_primary_database_server(): void {
		global $wpdb;

		$original = $wpdb;
		$wpdb     = new class( $original ) {
			/**
			 * @var string[] The calls, in order.
			 */
			public $calls = [];

			/**
			 * @var string
			 */
			public $prefix;

			/**
			 * @var wpdb
			 */
			private $db;

			public function __construct( wpdb $db ) {
				$this->db     = $db;
				$this->prefix = $db->prefix;
			}

			public function send_reads_to_masters(): void {
				$this->calls[] = 'send_reads_to_masters';
			}

			public function suppress_errors( $suppress = true ) {
				return $this->db->suppress_errors( $suppress );
			}

			public function prepare( $query, ...$args ) {
				return $this->db->prepare( $query, ...$args );
			}

			public function get_var( $query ) {
				$this->calls[] = false !== strpos( $query, 'GET_LOCK' ) ? 'GET_LOCK' : 'query';

				return $this->db->get_var( $query );
			}
		};

		try {
			$this->assertTrue( Optml_Media_Offload::acquire_moving_images_lock() );
			$calls = $wpdb->calls;
		} finally {
			$wpdb = $original;
			Optml_Media_Offload::release_moving_images_lock();
		}

		$this->assertSame( [ 'send_reads_to_masters', 'GET_LOCK' ], $calls );
	}

	/**
	 * A database without advisory locks (the query fails): transfers run as before.
	 */
	public function test_moving_images_lock_is_granted_where_the_database_has_no_advisory_locks(): void {
		$break_get_lock = static function ( string $query ): string {
			return false !== strpos( $query, 'GET_LOCK' ) ? 'SELECT optml_no_such_function()' : $query;
		};
		add_filter( 'query', $break_get_lock );

		try {
			$this->assertTrue( Optml_Media_Offload::acquire_moving_images_lock() );
		} finally {
			remove_filter( 'query', $break_get_lock );
		}
	}

	public function test_cli_rollback_is_refused_when_nothing_was_offloaded() {
		$settings = new Optml_Settings();
		$settings->update( 'offload_media', 'disabled' );
		$settings->update( 'rollback_status', 'disabled' );
		require_once __DIR__ . '/wp-cli-stub.php';

		$this->expectException( Optml_Test_WP_CLI_Error::class );
		$this->expectExceptionMessage( 'You need to have the offload_media option enabled' );

		$this->run_cli_media_command( 'rollback_images' );
	}

	public function test_image_rollback() {

		Optml_Media_Offload::instance()->rollback_images( 100 );
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );

		$image_meta  = wp_get_attachment_metadata( self::$sample_attachement);

		$this->assertStringNotContainsString( 'id:', $image_meta['file'] );
		$this->assertStringContainsString( '-300x200', $image_meta['sizes']['medium']['file'] );
		$this->assertStringContainsString( '-150x150', $image_meta['sizes']['thumbnail']['file'] );
	}

	/**
	 * A rollback that died after moving the file into uploads leaves that file behind. The retry
	 * must write over it, so the restored image keeps its name instead of getting a "-1" suffix.
	 */
	public function test_rollback_retry_replaces_file_left_by_interrupted_attempt() {
		$attachment_id = self::$sample_attachement;
		$uploads       = wp_upload_dir( get_the_date( 'Y/m', $attachment_id ) );
		$orphan        = $uploads['path'] . '/' . self::$files[0] . '.jpg';
		wp_mkdir_p( $uploads['path'] );
		file_put_contents( $orphan, 'left by an interrupted rollback' );
		update_post_meta( $attachment_id, Optml_Media_Offload::ROLLBACK_PENDING_FILE, $orphan );

		Optml_Media_Offload::instance()->rollback_images( 100 );

		$attached = get_attached_file( $attachment_id );
		$this->assertSame( $orphan, $attached );
		$this->assertTrue( file_exists( $attached ) );
		$this->assertNotSame( 'left by an interrupted rollback', file_get_contents( $attached ) );
		$this->assertEmpty( get_post_meta( $attachment_id, Optml_Media_Offload::ROLLBACK_PENDING_FILE, true ) );
	}

	/**
	 * A request that dies while the sub-sizes are generated has already pointed the attachment at
	 * the restored file, and remembers that file so a retry can clean it up.
	 */
	public function test_rollback_dying_during_subsizes_keeps_attachment_on_restored_file() {
		// Another file already uses the name, so the restored file gets a suffixed one.
		$uploads = wp_upload_dir( get_the_date( 'Y/m', self::$sample_attachement ) );
		$taken   = $uploads['path'] . '/' . self::$files[0] . '.jpg';
		wp_mkdir_p( $uploads['path'] );
		file_put_contents( $taken, 'another file' );

		$died = function () {
			throw new RuntimeException( 'request died' );
		};
		add_filter( 'intermediate_image_sizes_advanced', $died );

		try {
			Optml_Media_Offload::instance()->rollback_and_update_images( [ self::$sample_attachement ] );
			$this->fail( 'The rollback was expected to stop.' );
		} catch ( RuntimeException $e ) {
			$this->assertSame( 'request died', $e->getMessage() );
		} finally {
			remove_filter( 'intermediate_image_sizes_advanced', $died );
		}

		$restored = get_post_meta( self::$sample_attachement, Optml_Media_Offload::ROLLBACK_PENDING_FILE, true );
		$this->assertNotEmpty( $restored );
		$this->assertTrue( file_exists( $restored ) );
		$this->assertNotSame( $taken, $restored );
		$this->assertSame( $restored, get_attached_file( self::$sample_attachement, true ) );
		unlink( $taken );
	}

	/**
	 * Only a file inside the uploads directory is used, whatever the meta says.
	 */
	public function test_pending_rollback_file_outside_uploads_is_ignored() {
		$attachment_id = self::factory()->post->create( [ 'post_type' => 'attachment' ] );
		$outside       = get_temp_dir() . 'optml-outside-uploads.jpg';
		file_put_contents( $outside, 'not ours' );
		update_post_meta( $attachment_id, Optml_Media_Offload::ROLLBACK_PENDING_FILE, $outside );

		$this->assertNull( Optml_Media_Offload::get_pending_rollback_file( $attachment_id ) );
		$this->assertSame( 'not ours', file_get_contents( $outside ) );
		unlink( $outside );
	}

	/**
	 * A retry whose copy fails partway (full disk, file size limit) keeps the file the attachment points to.
	 */
	public function test_failed_copy_keeps_the_pending_file() {
		if ( ! in_array( 'optml-failing-read', stream_get_wrappers(), true ) ) {
			stream_wrapper_register( 'optml-failing-read', Optml_Test_Failing_Read_Stream::class );
		}
		$uploads = wp_get_upload_dir();
		$pending = $uploads['basedir'] . '/optml-pending-copy.jpg';
		file_put_contents( $pending, 'restored image' );

		$result = Optml_Media_Offload::replace_pending_rollback_file( $pending, 'optml-failing-read://download', 'image/jpeg' );

		$this->assertArrayHasKey( 'error', $result );
		$this->assertSame( 'restored image', file_get_contents( $pending ) );
		$this->assertFileDoesNotExist( $pending . '.optml-partial' );
		unlink( $pending );
	}

	/**
	 * A retry that ends in the branch for attachments whose local metadata was already saved
	 * clears the pending file too.
	 */
	public function test_retry_after_local_metadata_was_saved_clears_pending_file() {
		$attachment_id = self::factory()->post->create( [ 'post_type' => 'attachment' ] );
		wp_update_attachment_metadata( $attachment_id, [ 'file' => '2026/10/restored.jpg' ] );
		update_post_meta( $attachment_id, Optml_Media_Offload::ROLLBACK_PENDING_FILE, '/some/where/restored.jpg' );

		Optml_Media_Offload::instance()->rollback_and_update_images( [ $attachment_id ] );

		$this->assertEmpty( get_post_meta( $attachment_id, Optml_Media_Offload::ROLLBACK_PENDING_FILE, true ) );
	}

	/**
	 * A rollback that completes leaves no pending file behind, so a later retry deletes nothing.
	 */
	public function test_completed_rollback_clears_pending_file() {
		Optml_Media_Offload::instance()->rollback_images( 100 );

		$this->assertTrue( file_exists( get_attached_file( self::$sample_attachement ) ) );
		$this->assertEmpty( get_post_meta( self::$sample_attachement, Optml_Media_Offload::ROLLBACK_PENDING_FILE, true ) );
	}

	public function test_rollback_retry_clears_errors() {

		// Use a dedicated attachment with fully controlled meta so the assertions do
		// not depend on the offload state produced during setUp.
		$attachment_id = self::factory()->post->create(
			[
				'post_type'      => 'attachment',
				'post_mime_type' => 'image/jpeg',
				'post_status'    => 'inherit',
			]
		);

		// Mark it as offloaded so it is eligible for rollback.
		update_post_meta( $attachment_id, Optml_Media_Offload::META_KEYS['offloaded'], 'true' );

		$rollback_query = function () use ( $attachment_id ) {
			$args             = Optml_Media_Offload::get_images_or_pages_query_args( -1, 'rollback_images', true );
			$args['post__in'] = [ $attachment_id ];

			return ( new WP_Query( $args ) )->posts;
		};

		// Without a rollback error the attachment is eligible for restore.
		$this->assertContains( $attachment_id, $rollback_query() );

		// Simulate a failed rollback attempt which should exclude it from selection.
		update_post_meta( $attachment_id, Optml_Media_Offload::META_KEYS['rollback_error'], 'true' );
		$this->assertNotContains( $attachment_id, $rollback_query() );

		// Retrying the rollback should clear the rollback errors so the attachment is eligible again.
		Optml_Media_Offload::clear_rollback_errors_meta();
		$this->assertEmpty( get_post_meta( $attachment_id, Optml_Media_Offload::META_KEYS['rollback_error'], true ) );
		$this->assertContains( $attachment_id, $rollback_query() );
	}
	public function test_custom_post_image_extraction () {
		$content = '[fusion_builder_container type="flex" hundred_percent="no" hundred_percent_height="no" hundred_percent_height_scroll="no" align_content="stretch" flex_align_items="flex-start" flex_justify_content="flex-start" hundred_percent_height_center_content="yes" equal_height_columns="no" container_tag="div" hide_on_mobile="small-visibility,medium-visibility,large-visibility" status="published" border_style="solid" box_shadow="no" box_shadow_blur="0" box_shadow_spread="0" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_position="center center" background_repeat="no-repeat" fade="no" background_parallax="none" enable_mobile="no" parallax_speed="0.3" background_blend_mode="none" video_aspect_ratio="16:9" video_loop="yes" video_mute="yes" absolute="off" absolute_devices="small,medium,large" sticky="off" sticky_devices="small-visibility,medium-visibility,large-visibility" sticky_transition_offset="0" scroll_offset="0" animation_direction="left" animation_speed="0.3" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0"][fusion_builder_row][fusion_builder_column type="1_3" type="1_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all"][fusion_imageframe image_id="144|full" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" margin_top="" margin_right="" margin_bottom="" margin_left="" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100"
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg[/fusion_imageframe][/fusion_builder_column][fusion_builder_column type="2_3" type="2_3" align_self="auto" content_layout="column" align_content="flex-start" valign_content="flex-start" content_wrap="wrap" spacing="" center_content="no" link="" target="_self" min_height="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" type_medium="" type_small="" order_medium="0" order_small="0" dimension_spacing_medium="" dimension_spacing_small="" dimension_spacing="" dimension_margin_medium="" dimension_margin_small="" margin_top="" margin_bottom="" padding_medium="" padding_small="" padding_top="" padding_right="" padding_bottom="" padding_left="" hover_type="none" border_sizes="" border_color="" border_style="solid" border_radius="" box_shadow="no" dimension_box_shadow="" box_shadow_blur="0" box_shadow_spread="0" box_shadow_color="" box_shadow_style="" background_type="single" gradient_start_color="" gradient_end_color="" gradient_start_position="0" gradient_end_position="100" gradient_type="linear" radial_direction="center center" linear_angle="180" background_color="" background_image="" background_image_id="" background_position="left top" background_repeat="no-repeat" background_blend_mode="none" render_logics="" filter_type="regular" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100" filter_blur_hover="0" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" last="no" border_position="all" element_content=""][fusion_imageframe image_id="180|fusion-1200" max_width="" sticky_max_width="" style_type="" blur="" stylecolor="" hover_type="none" bordersize="" bordercolor="" borderradius="" align_medium="none" align_small="none" align="none" lightbox="no" gallery_id="" lightbox_image="" lightbox_image_id="" alt="" link="" linktarget="_self" animation_type="" animation_direction="left" animation_speed="0.3" animation_offset="" hide_on_mobile="small-visibility,medium-visibility,large-visibility" sticky_display="normal,sticky" class="" id="" filter_hue="0" filter_saturation="100" filter_brightness="100" filter_contrast="100" filter_invert="0" filter_sepia="0" filter_opacity="100" filter_blur="0" filter_hue_hover="0" filter_saturation_hover="100" filter_brightness_hover="100" filter_contrast_hover="100" filter_invert_hover="0" filter_sepia_hover="0" filter_opacity_hover="100"
		filter_blur_hover="0"]http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png[/fusion_imageframe][/fusion_builder_column][/fusion_builder_row]';
		$images  = Optml_Manager::instance()->extract_urls_from_content( $content );
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/2AAPaNcjDJQ.jpg', $images[0]);
		$this->assertEquals('http://35f86c81ba7c.ngrok.io/wp-content/uploads/2021/04/Screenshot-2021-02-15-at-10.42.07-1200x675.png', $images[1]);

	}

	/**
	 * Test images with special characters.
	 */
	public function test_special_characters_upload() : void {
		$special_character_attachment = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/special-characters-•⋿∀.jpg' );
		$content = wp_get_attachment_image( $special_character_attachment );

		$this->assertStringContainsString(  "src=\"https://example.i.optimole.com/w:150/h:150/rt:fill/g:ce/q:mauto/process:". $special_character_attachment ."/id:579c7f7707ce87caa65fdf50c238a117/http://example.org/special-characters-•⋿∀.jpg\" ", $content );

	}

	/**
	 * Test if the svg upload works.
	 */
	public function test_svg_upload() : void {
		self::$sample_attachement = self::factory()->attachment->create_upload_object( OPTML_PATH . 'assets/img/logo.svg' );
		$this->assertTrue( file_exists( get_attached_file(self::$sample_attachement) ) );
	}

	public function test_maybe_strip_scaled() {
		$scaled_url = 'https://4d31-93-114-162-196.ngrok-free.app/wp-content/uploads/2023/10/wesley-tingey-NRwAGwJLGH4-unsplash-1-scaled.jpg';
		$normal_url = 'https://4d31-93-114-162-196.ngrok-free.app/wp-content/uploads/2023/10/wesley-tingey-NRwAGwJLGH4-unsplash-1.jpg';

		$this->assertEquals( Optml_Media_Offload::instance()->maybe_strip_scaled( $scaled_url ), $normal_url );
		$this->assertEquals( Optml_Media_Offload::instance()->maybe_strip_scaled( $normal_url ), $normal_url );
	}

	public function test_filter_saved_data() {
		// Sample Attachment:

		// Full size
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachement, 'full' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:700/h:467', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:700/h:467', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );

		// Medium size
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachement, 'medium' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:300/h:200', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:300/h:200', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('300x200.jpg', $replaced['post_content'] );

		// Scaled attachment:
		// Full size.
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachment_scaled, 'full' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:2560/h:2560', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:2560/h:2560', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('-scaled.jpg', $replaced['post_content'] );

		// Medium size.
		$content = sprintf( '<img src="%s" />', wp_get_attachment_image_url( self::$sample_attachment_scaled, 'medium' ) );
		$this->assertStringContainsString( 'example.i.optimole.com/w:300/h:300', $content );
		$this->assertStringContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $content );

		$replaced = Optml_Media_Offload::instance()->filter_saved_data( ['post_content' => $content], ['post_status' => 'published' ], [], false );
		$this->assertStringNotContainsString( 'example.i.optimole.com/w:300/h:300', $replaced['post_content'] );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachment_scaled . '/id:', $replaced['post_content'] );
		$this->assertStringContainsString('/wp-content/uploads', $replaced['post_content'] );
		$this->assertStringContainsString('300x300.jpg', $replaced['post_content'] );

		// Skip replacement of attachments without any correspondent.
		$content  = '<img src="https://example.i.optimole.com/nfYKCuM-pmHIfRg3/w:auto/h:auto/q:auto/process:32/id:8bbc41853bc0770fb1dd34759e1cb1e2/https://optimole.com/PIA23769.JPG" />';
		$replaced = Optml_Media_Offload::instance()->filter_saved_data( [ 'post_content' => $content ], [ 'post_status' => 'published' ], [], false );
		$this->assertEquals( $content, $replaced['post_content'] );
	}
	public function test_replace_alternative_domain(){
		$attachment = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/'.self::$files[0].'.jpg' );
		$url = Optml_Media_Offload::get_original_url( $attachment );
		$this->assertEquals( $this->attachment_url_to_post_id( $url ), $attachment );
		$url = str_replace('example.org', 'www.example.org', $url);
		$this->assertEquals( $this->attachment_url_to_post_id( $url ), $attachment );

		$scaled_url = Optml_Media_Offload::get_original_url( self::$sample_attachment_scaled );

		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
		$scaled_url = str_replace('-scaled','',$scaled_url);

		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
		$scaled_url = str_replace('example.org', 'www.example.org', $scaled_url);
		$this->assertEquals( $this->attachment_url_to_post_id( $scaled_url ), self::$sample_attachment_scaled );
	}
	public function test_replace_urls_in_editor_content() {
		Optml_Attachment_Cache::reset();
		// Sample attachment:
		$original_url = Optml_Media_Offload::get_original_url( self::$sample_attachement );
		$extension = pathinfo( $original_url, PATHINFO_EXTENSION );
		$medium_size_url = str_replace( '.' . $extension, '-300x200.' . $extension, $original_url );

		// Full size.
		$content_before_replace = sprintf( '<img src="%s" />', $original_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( 'w:auto/h:auto', $replaced_content );
		$this->assertStringNotContainsString( 'w:auto/h:auto', $content_before_replace );

		$this->assertStringContainsString( 'process:' . self::$sample_attachement . '/id:', $replaced_content );
		$this->assertStringNotContainsString( 'process:' . self::$sample_attachement . '/id:', $content_before_replace );

		// Medium size.
		$content_before_replace = sprintf( '<img src="%s" />', $medium_size_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( 'w:300/h:200', $replaced_content );
		$this->assertStringNotContainsString( 'w:300/h:200', $content_before_replace );

		$this->assertStringContainsString( '300x200.jpg', $content_before_replace );
		$this->assertStringNotContainsString( '300x200.jpg', $replaced_content );

		// Scaled attachment:
		$original_url = Optml_Media_Offload::get_original_url( self::$sample_attachment_scaled );
		$extension = pathinfo( $original_url, PATHINFO_EXTENSION );

		$content_before_replace = sprintf( '<img src="%s" />', $original_url );
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringNotContainsString( $original_url, $replaced_content );

		$this->assertStringContainsString('wp-content/uploads', $content_before_replace);
		$this->assertStringNotContainsString( 'wp-content/uploads', $replaced_content );

		$this->assertStringContainsString( '-scaled.'.$extension, $content_before_replace );
		$this->assertStringNotContainsString( '-scaled.'.$extension, $replaced_content );

	}
	public function test_replacement_editor_with_external_images(){
		Optml_Attachment_Cache::reset();
		$original_url = "https://external.com/image.png" ;
		$content_before_replace = sprintf( '<img src="%s" />', $original_url);
		$replaced_content = Optml_Media_Offload::instance()->replace_urls_in_editor_content( $content_before_replace );

		$this->assertStringContainsString( $original_url, $replaced_content );
		$this->assertStringContainsString( 'i.optimole.com', $replaced_content );
	}
	public function test_alter_attachment_image_src() {
		Optml_Attachment_Cache::reset();
		$test_data = [
			'full' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'full', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:700/h:467'
				]
			],
			'medium' =>  [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'medium', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:300/h:200'
				]
			],
			'thumb' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, 'thumbnail', false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:150/h:150',
					'rt:fill'
				]
			],
			'custom' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachement, [10, 20], false ),
				'contains' => [
					'process:' . self::$sample_attachement . '/id:',
					'w:10/h:7'
				]
			],
			'scaled-full' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, 'full', false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:2560/h:2560'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			],
			'scaled-thumb' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, 'thumbnail', false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:150/h:150',
					'rt:fill'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			],
			'scaled-custom' => [
				'data' => Optml_Media_Offload::instance()->alter_attachment_image_src( [], self::$sample_attachment_scaled, [10, 20], false ),
				'contains' => [
					'process:' . self::$sample_attachment_scaled . '/id:',
					'w:10/h:10'
				],
				'not_contain' => [
					'-scaled.jpg'
				]
			]
		];

		foreach ( $test_data as $args ) {
			$data = $args['data'];

			if( isset( $args['contains'] ) ) {
				foreach ( $args['contains'] as $string ) {
					$this->assertStringContainsString( $string, $data[0] );
				}
			}

			if( isset( $args['not_contain'] ) ) {
				foreach ( $args['not_contain'] as $string ) {
					$this->assertStringNotContainsString( $string, $data[0] );
				}
			}
		}
	}

	/**
	 * Two overlapping "start transfer" requests must not be able to claim the transfer lock.
	 */
	public function test_acquire_transfer_lock_blocks_concurrent_start() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );

		$first  = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );
		$second = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );

		$this->assertNotFalse( $first );
		$this->assertFalse( $second );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * Two requests that both read the lock as free must not both take it: the one whose write comes
	 * second gets false, and the lock keeps the first one's token and an expiry.
	 */
	public function test_acquire_transfer_lock_fails_when_another_request_took_it_after_the_read() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
		global $wpdb;

		// This request reads the lock as missing, then another request stores it before our write.
		$this->assertFalse( get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT ) );
		$wpdb->insert(
			$wpdb->options,
			[
				'option_name'  => '_transient_' . Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT,
				'option_value' => maybe_serialize( [ 'token' => 'other-request', 'action' => 'offload_images' ] ),
				'autoload'     => 'no',
			]
		);

		$this->assertFalse( Optml_Media_Offload::acquire_transfer_lock( 'offload_images' ) );

		$lock = get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
		$this->assertSame( 'other-request', $lock['token'] );
		$this->assertGreaterThan( time(), (int) get_option( '_transient_timeout_' . Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT ) );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * Another request's release can delete the expiry row between our first write and the insert.
	 * The lock we then store must still get an expiry, or a dead worker would block transfers forever.
	 */
	public function test_acquire_transfer_lock_restores_expiry_removed_before_the_insert() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
		global $wpdb;
		$timeout = '_transient_timeout_' . Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT;

		$release = function ( $query ) use ( &$release, $wpdb, $timeout ) {
			if ( false !== strpos( $query, 'INSERT IGNORE' ) ) {
				remove_filter( 'query', $release );
				$wpdb->delete( $wpdb->options, [ 'option_name' => $timeout ] );
			}

			return $query;
		};
		add_filter( 'query', $release );
		$token = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );
		remove_filter( 'query', $release );

		$this->assertNotFalse( $token );
		$stored = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $timeout ) );
		$this->assertNotNull( $stored );
		$this->assertGreaterThan( time(), (int) $stored );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * A lock left behind by a worker that died mid-batch (fatal error, killed process) must
	 * eventually become reclaimable, otherwise a crashed transfer would be stuck forever.
	 */
	public function test_acquire_transfer_lock_reclaims_expired_lock() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );

		$first = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );
		$this->assertNotFalse( $first );

		// Simulate a worker that died mid-batch: force the transient's timeout into the past.
		update_option( '_transient_timeout_' . Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT, time() - 10 );

		$this->assertFalse( false !== get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT ) );

		$second = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );

		$this->assertNotFalse( $second );
		$this->assertNotEquals( $first, $second );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * Only the current lock owner (matching token) may renew it - otherwise a worker that
	 * already lost the lock to a reclaiming worker could revive its stale ownership.
	 */
	public function test_renew_transfer_lock_requires_owned_token() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );

		$token = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );
		$this->assertNotFalse( $token );

		$this->assertFalse( Optml_Media_Offload::renew_transfer_lock( 'not-the-owner', 'offload_images' ) );

		$this->assertTrue( Optml_Media_Offload::renew_transfer_lock( $token, 'offload_images' ) );

		$lock_after = get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
		$this->assertSame( $token, $lock_after['token'] );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * Only the current lock owner (matching token) may release it - a stray/late release call
	 * from a worker that already lost ownership must not tear down another worker's lock.
	 */
	public function test_release_transfer_lock_only_releases_owned_lock() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );

		$token = Optml_Media_Offload::acquire_transfer_lock( 'offload_images' );
		$this->assertNotFalse( $token );

		Optml_Media_Offload::release_transfer_lock( 'not-the-owner' );
		$this->assertTrue( false !== get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT ) );

		Optml_Media_Offload::release_transfer_lock( $token );
		$this->assertFalse( false !== get_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT ) );
	}

	/**
	 * Test that two overlapping move_images() calls do not schedule two processing chains for the same transfer.
	 */
	public function test_move_images_does_not_schedule_duplicate_processing_chain() {
		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );

		Optml_Media_Offload::instance()->settings->update( 'rollback_status', 'enabled' );
		Optml_Media_Offload::instance()->settings->update( 'offloading_status', 'disabled' );

		Optml_Media_Offload::move_images( 'rollback_images', false );
		Optml_Media_Offload::move_images( 'rollback_images', false );

		// Count the number of scheduled processing chains for the transfer. There should be exactly one.
		$scheduled_count = 0;
		foreach ( _get_cron_array() as $timestamp => $hooks ) {
			if ( isset( $hooks['optml_start_processing_images'] ) ) {
				$scheduled_count += count( $hooks['optml_start_processing_images'] );
			}
		}

		$this->assertEquals( 1, $scheduled_count );

		delete_transient( Optml_Media_Offload::TRANSFER_LOCK_TRANSIENT );
	}

	/**
	 * The account mock reports 50 of 5000 images offloaded, and 4990 images are recorded as still to
	 * be moved. They count toward the limit of an upload only while an offload is moving them: not
	 * for a rollback, which records its progress in the same place, and not once the offload is over.
	 *
	 * @dataProvider pending_transfer_provider
	 *
	 * @param array{count: int, remaining: int, start_time: int, action?: string, foreground?: int} $process_meta      The recorded transfer progress.
	 * @param string                                                                                 $offloading_status The dashboard offload status.
	 * @param bool                                                                                   $limit_reached     Whether the upload trips the limit.
	 * @param int|null                                                                               $mark_age          Seconds since the last activity of a WP-CLI run, null without one.
	 */
	public function test_only_a_running_offload_counts_toward_the_offload_limit( array $process_meta, string $offloading_status, bool $limit_reached, ?int $mark_age = null ): void {
		// The providers run when the suite is built, long before this test: the mark is dated here.
		if ( null !== $mark_age ) {
			$process_meta['foreground'] = time() - $mark_age;
		}

		$settings = new Optml_Settings();
		$settings->update( 'offloading_status', $offloading_status );
		$settings->update( 'offload_limit_reached', 'disabled' );
		update_option( 'optml_process_meta', $process_meta );

		$attachment_id = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/' . self::$files[0] . '.jpg' );
		$meta          = wp_get_attachment_metadata( $attachment_id );
		$settings      = new Optml_Settings();

		$this->assertSame( ! $limit_reached, Optml_Media_Offload::is_uploaded_image( $meta['file'] ) );
		$this->assertSame( $limit_reached, $settings->is_offload_limit_reached() );

		if ( $limit_reached ) {
			// The limit of the account is stored with the warning, not the 50,000 default.
			$this->assertSame( 5000, (int) $settings->get( 'offload_limit' ) );
		}
	}

	/**
	 * @return array<string, array{0: array{count: int, remaining: int, start_time: int, action?: string, foreground?: int}, 1: string, 2: bool, 3?: int}>
	 */
	public function pending_transfer_provider(): array {
		$progress = [
			'count'      => 4990,
			'remaining'  => 4990,
			'start_time' => time(),
		];
		$offload  = $progress + [ 'action' => 'offload_images' ];

		return [
			'rollback'                                                 => [ $progress + [ 'action' => 'rollback_images' ], 'disabled', false ],
			'rollback, with a stale offload status'                    => [ $progress + [ 'action' => 'rollback_images' ], 'enabled', false ],
			'dashboard offload that is running'                        => [ $offload + [ 'foreground' => 0 ], 'enabled', true ],
			'dashboard offload that finished or stopped at the limit'  => [ $offload + [ 'foreground' => 0 ], 'disabled', false ],
			'WP-CLI offload that is running'                           => [ $offload, 'disabled', true, 0 ],
			'WP-CLI offload that ended'                                => [ $offload + [ 'foreground' => 0 ], 'disabled', false ],
			'WP-CLI offload interrupted an hour ago (Ctrl+C)'          => [ $offload, 'disabled', false, HOUR_IN_SECONDS ],
			'recorded before the transfer was stored, idle'            => [ $progress, 'disabled', false ],
			'recorded before the transfer was stored, offload running' => [ $progress, 'enabled', true ],
		];
	}

	public function test_a_wp_cli_run_marks_its_progress_until_it_ends(): void {
		Optml_Media_Offload::record_process_meta( 12, 'offload_images', true );
		$this->assertGreaterThanOrEqual( time() - 5, get_option( 'optml_process_meta' )['foreground'] );

		Optml_Media_Offload::end_foreground_process_meta();

		$meta = get_option( 'optml_process_meta' );
		$this->assertSame( 0, $meta['foreground'] );
		$this->assertSame( 12, $meta['remaining'] );
	}

	/**
	 * The mark of a WP-CLI run is the time of its last activity: each offloaded image refreshes it,
	 * so it goes stale by itself when the command is interrupted.
	 */
	public function test_each_offloaded_image_refreshes_the_mark_of_a_wp_cli_run(): void {
		update_option(
			'optml_process_meta',
			[
				'count'      => 12,
				'remaining'  => 12,
				'start_time' => time() - 500,
				'action'     => 'offload_images',
				'foreground' => time() - 500,
			]
		);

		Optml_Media_Offload::decrement_process_meta_remaining();

		$meta = get_option( 'optml_process_meta' );
		$this->assertSame( 11, $meta['remaining'] );
		$this->assertGreaterThanOrEqual( time() - 5, $meta['foreground'] );
	}

	/**
	 * Every offloaded image updates the progress, also an ordinary upload long after a WP-CLI run was
	 * interrupted. It must not bring the stale mark of that run back.
	 */
	public function test_a_later_upload_does_not_revive_the_mark_of_an_interrupted_wp_cli_run(): void {
		update_option(
			'optml_process_meta',
			[
				'count'      => 4990,
				'remaining'  => 4990,
				'start_time' => time() - HOUR_IN_SECONDS,
				'action'     => 'offload_images',
				'foreground' => time() - HOUR_IN_SECONDS,
			]
		);
		$settings = new Optml_Settings();
		$settings->update( 'offloading_status', 'disabled' );
		$settings->update( 'offload_limit_reached', 'disabled' );

		// Two ordinary uploads: the first one updates the progress, the second one must still be offloaded.
		self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/' . self::$files[0] . '.jpg' );
		$attachment_id = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/' . self::$files[1] . '.jpg' );

		$this->assertSame( 0, get_option( 'optml_process_meta' )['foreground'] );
		$this->assertTrue( Optml_Media_Offload::is_uploaded_image( wp_get_attachment_metadata( $attachment_id )['file'] ) );
		$this->assertFalse( ( new Optml_Settings() )->is_offload_limit_reached() );
	}

	public function test_offloading_an_image_does_not_mark_a_dashboard_offload_as_a_wp_cli_run(): void {
		Optml_Media_Offload::record_process_meta( 12, 'offload_images' );

		Optml_Media_Offload::decrement_process_meta_remaining();

		$this->assertSame( 0, get_option( 'optml_process_meta' )['foreground'] );
	}

	/**
	 * @dataProvider transfer_action_provider
	 */
	public function test_a_bulk_transfer_records_which_transfer_its_progress_belongs_to( string $action ): void {
		Optml_Media_Offload::move_images( $action, false );

		$meta = get_option( 'optml_process_meta' );
		$this->assertSame( $action, $meta['action'] );
		$this->assertSame( 0, $meta['foreground'] );
	}

	/**
	 * @return array<string, array{0: string}>
	 */
	public function transfer_action_provider(): array {
		return [
			'offload'  => [ 'offload_images' ],
			'rollback' => [ 'rollback_images' ],
		];
	}
}

/**
 * A stream that returns some bytes, then fails, like a copy that runs out of disk space partway.
 */
class Optml_Test_Failing_Read_Stream {
	/**
	 * Stream context, set by PHP.
	 *
	 * @var resource|null
	 */
	public $context;

	/**
	 * Reads done so far.
	 *
	 * @var int
	 */
	private $reads = 0;

	/**
	 * Open the stream.
	 *
	 * @param string      $path The URL.
	 * @param string      $mode The mode.
	 * @param int         $options The options.
	 * @param string|null $opened_path The opened path.
	 *
	 * @return bool
	 */
	public function stream_open( $path, $mode, $options, &$opened_path ) {
		return true;
	}

	/**
	 * Return a few bytes the first time, then fail.
	 *
	 * @param int $count The bytes asked for.
	 *
	 * @return string|false
	 */
	public function stream_read( $count ) {
		++$this->reads;

		return 1 === $this->reads ? 'partial' : false;
	}

	/**
	 * The stream never reaches its end.
	 *
	 * @return bool
	 */
	public function stream_eof() {
		return false;
	}

	/**
	 * Stream stats.
	 *
	 * @return array<string, int>
	 */
	public function stream_stat() {
		return [];
	}

	/**
	 * URL stats.
	 *
	 * @param string $path The URL.
	 * @param int    $flags The flags.
	 *
	 * @return array<string, int>
	 */
	public function url_stat( $path, $flags ) {
		return [ 'size' => 100 ];
	}
}
