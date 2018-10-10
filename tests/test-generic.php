<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Generic.
 */
class Test_Generic extends WP_UnitTestCase {
	/**
	 * Check if the version is defined
	 */
	function test_version_exists() {
		$this->assertTrue( defined( 'OPTML_VERSION' ) );
		$this->assertTrue( ( version_compare( OPTML_VERSION, '0.0.1', '>' ) ) );
	}

	/**
	 * We should remove DEBUG mode in production.
	 */
	function test_debug_mode() {

		$this->assertFalse( OPTML_DEBUG );
	}
}
