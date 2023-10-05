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
		$this->assertTrue( $this->logger->add_log( 'offload', 'Test offload message' ) );
		$this->assertTrue( $this->logger->add_log( 'rollback', 'Test rollback message' ) );
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
		$this->assertContains( 'Test offload message', $this->logger->get_log( 'offload' ) );
		$this->assertContains( 'Test rollback message', $this->logger->get_log( 'rollback' ) );
	}
}