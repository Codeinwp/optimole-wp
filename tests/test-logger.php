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
	 * A log file is not stored under a name that only depends on its type.
	 */
	public function test_log_file_name_is_not_the_log_type() {
		array_map( 'unlink', glob( $this->log_dir() . 'offload*.log' ) );
		$this->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Named log message' );

		$this->assertFalse( file_exists( $this->log_dir() . 'offload.log' ) );
		$this->assertCount( 1, glob( $this->log_dir() . 'offload-*.log' ) );
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
	 * The log directory holds the files that keep a web server from listing or serving it.
	 */
	public function test_log_directory_is_protected() {
		array_map( 'unlink', array_filter( [ $this->log_dir() . '.htaccess', $this->log_dir() . 'index.html' ], 'file_exists' ) );

		$this->logger->protect_log_files();

		$this->assertStringContainsString( 'Require all denied', file_get_contents( $this->log_dir() . '.htaccess' ) );
		$this->assertTrue( file_exists( $this->log_dir() . 'index.html' ) );
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
	 * The directory the logs are stored in.
	 */
	private function log_dir(): string {
		return trailingslashit( wp_upload_dir()['basedir'] ) . 'optimole-logs/';
	}
}
