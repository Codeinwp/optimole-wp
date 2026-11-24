<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
class Test_Logger extends WP_UnitTestCase {
	/**
	 * Logger instance.
	 * 
	 * @var Optml_Logger
	 */
	private $logger;

	public function setUp():void {
		parent::setUp();

		$this->logger = Optml_Logger::instance();
	}

	/**
	 * Put back the logger the other tests share, with the log files they wrote.
	 */
	public function tearDown(): void {
		$this->set_logger( $this->logger );
		parent::tearDown();
	}

	/**
	 * Test instance creation.
	 */
	public function test_instance_creation() {
		$this->assertInstanceOf( Optml_Logger::class, $this->logger );
	}

	/**
	 * Test adding a log.
	 */
	public function test_add_log() {
		$this->assertTrue( $this->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Test offload message' ) );
		$this->assertTrue( $this->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Test rollback message' ) );
	}

	/**
	 * Test checking if logs exist.
	 */
	public function test_has_logs() {
		$this->assertFalse( $this->logger->has_logs( 'invalid-type' ) );
		$this->assertTrue( $this->logger->has_logs( 'offload' ) );
	}

	/**
	 * Test getting the log content.
	 */
	public function test_get_log() {
		$this->assertStringContainsString( 'Test offload message', $this->logger->get_log( 'offload' ) );
		$this->assertStringContainsString( 'Test rollback message', $this->logger->get_log( 'rollback' ) );
	}

	/**
	 * A log file is stored under a name that holds a random token, created once and kept for the site.
	 */
	public function test_log_file_name_holds_a_stored_token() {
		delete_option( Optml_Logger::FILE_TOKEN_OPTION );
		$first = $this->new_logger();
		$first->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Named log message' );

		$token = (string) get_option( Optml_Logger::FILE_TOKEN_OPTION );
		$this->assertSame( 32, strlen( $token ) );
		$this->assertTrue( file_exists( $this->log_dir() . 'offload-' . $token . '.log' ) );
		$this->assertFalse( file_exists( $this->log_dir() . 'offload.log' ) );
		$this->assertTrue( $this->new_logger()->has_logs( Optml_Logger::LOG_TYPE_OFFLOAD ) );

		// A site without a token gets a token of its own, while a logger keeps the one it started with.
		delete_option( Optml_Logger::FILE_TOKEN_OPTION );
		$this->assertFalse( $this->new_logger()->has_logs( Optml_Logger::LOG_TYPE_OFFLOAD ) );
		$this->assertNotSame( $token, get_option( Optml_Logger::FILE_TOKEN_OPTION ) );
		$this->assertTrue( $first->has_logs( Optml_Logger::LOG_TYPE_OFFLOAD ) );

		unlink( $this->log_dir() . 'offload-' . $token . '.log' );
	}

	/**
	 * An empty stored token is replaced, so the next request finds the log.
	 */
	public function test_empty_log_file_token_is_replaced() {
		update_option( Optml_Logger::FILE_TOKEN_OPTION, '' );
		$this->new_logger()->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Message after an empty token' );

		$this->assertStringContainsString( 'Message after an empty token', (string) $this->new_logger()->get_log( Optml_Logger::LOG_TYPE_ROLLBACK ) );
		unlink( $this->log_dir() . 'rollback-' . get_option( Optml_Logger::FILE_TOKEN_OPTION ) . '.log' );
	}
	/**
	 * A log written under the earlier fixed name is read from its new name.
	 */
	public function test_log_written_under_the_earlier_name_is_moved() {
		array_map( 'unlink', glob( $this->log_dir() . 'rollback*.log' ) );
		file_put_contents( $this->log_dir() . 'rollback.log', "[Jan 01 00:00:00] Earlier rollback message\n" );

		$this->assertStringContainsString( 'Earlier rollback message', $this->logger->get_log( Optml_Logger::LOG_TYPE_ROLLBACK ) );
		$this->assertFalse( file_exists( $this->log_dir() . 'rollback.log' ) );
	}

	/**
	 * A log file is named after the stored token, whatever the site's salts are.
	 */
	public function test_log_file_name_does_not_depend_on_the_salts() {
		add_filter( 'salt', '__return_empty_string' );
		$this->new_logger()->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Message with other salts' );
		remove_filter( 'salt', '__return_empty_string' );

		$this->assertTrue( file_exists( $this->log_dir() . 'offload-' . get_option( 'optml_log_file_token' ) . '.log' ) );
	}

	/**
	 * Logs under the earlier fixed names are moved on the request that gives the site its token.
	 */
	public function test_log_under_the_earlier_name_is_moved_when_the_site_gets_its_token() {
		delete_option( 'optml_log_file_token' );
		file_put_contents( $this->log_dir() . 'offload.log', "[Jan 01 00:00:00] Earlier offload message\n" );

		$logger = $this->new_logger();

		$this->assertFalse( file_exists( $this->log_dir() . 'offload.log' ) );
		$this->assertStringContainsString( 'Earlier offload message', (string) $logger->get_log( Optml_Logger::LOG_TYPE_OFFLOAD ) );
		unlink( $this->log_dir() . 'offload-' . get_option( 'optml_log_file_token' ) . '.log' );
	}

	/**
	 * A log under the earlier fixed name is dropped when the log already exists under its new name.
	 */
	public function test_log_under_the_earlier_name_is_dropped_beside_the_new_one() {
		$this->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Kept rollback message' );
		file_put_contents( $this->log_dir() . 'rollback.log', "[Jan 01 00:00:00] Older rollback message\n" );

		$log = (string) $this->logger->get_log( Optml_Logger::LOG_TYPE_ROLLBACK );

		$this->assertFalse( file_exists( $this->log_dir() . 'rollback.log' ) );
		$this->assertStringContainsString( 'Kept rollback message', $log );
		$this->assertStringNotContainsString( 'Older rollback message', $log );
	}
	/**
	 * On admin requests the log directory gets the files that keep a web server from listing or serving it.
	 */
	public function test_log_directory_is_protected() {
		$this->assertSame( 10, has_action( 'admin_init', [ $this->logger, 'protect_log_files' ] ) );

		// Each file is added on its own, and one that exists is kept as it is.
		file_put_contents( $this->log_dir() . '.htaccess', '# kept' );
		array_map( 'unlink', array_filter( [ $this->log_dir() . 'index.html' ], 'file_exists' ) );
		$this->logger->protect_log_files();

		$this->assertSame( '# kept', file_get_contents( $this->log_dir() . '.htaccess' ) );
		$this->assertTrue( file_exists( $this->log_dir() . 'index.html' ) );

		unlink( $this->log_dir() . '.htaccess' );
		file_put_contents( $this->log_dir() . 'index.html', 'kept' );
		$this->logger->protect_log_files();

		$this->assertStringContainsString( 'Require all denied', file_get_contents( $this->log_dir() . '.htaccess' ) );
		$this->assertSame( 'kept', file_get_contents( $this->log_dir() . 'index.html' ) );
		file_put_contents( $this->log_dir() . 'index.html', '' );
	}
	/**
	 * The log reader answers administrators only.
	 */
	public function test_log_reader_answers_administrators_only() {
		$this->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Reader log message' );
		$_REQUEST['type'] = Optml_Logger::LOG_TYPE_OFFLOAD;

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->assertStringContainsString( 'Reader log message', $this->read_logs() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$this->assertStringNotContainsString( 'Reader log message', $this->read_logs() );

		unset( $_REQUEST['type'], $_REQUEST['nonce'] );
	}

	/**
	 * Call the log reader as the current user, with a valid nonce.
	 *
	 * @return string What the reader printed.
	 */
	private function read_logs(): string {
		$_REQUEST['nonce'] = wp_create_nonce( 'wp_rest' );

		ob_start();
		try {
			$this->logger->fetch_logs_ajax_handler();
		} catch ( WPDieException $e ) {
			// The reader always ends the request.
		}

		return (string) ob_get_clean();
	}

	/**
	 * Create a logger the way a new request does.
	 */
	private function new_logger(): Optml_Logger {
		$this->set_logger( null );

		return Optml_Logger::instance();
	}

	/**
	 * @param Optml_Logger|null $logger The logger the class hands out, or null to have a new one created.
	 */
	private function set_logger( $logger ): void {
		$instance = new ReflectionProperty( Optml_Logger::class, 'instance' );
		$instance->setAccessible( true );
		$instance->setValue( null, $logger );
	}

	/**
	 * The directory the logs are stored in.
	 */
	private function log_dir(): string {
		return trailingslashit( wp_upload_dir()['basedir'] ) . 'optimole-logs/';
	}
}
