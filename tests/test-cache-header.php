<?php
/**
 * WordPress unit tests for the temporary Cache-Control header decision.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2026, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Cache_Header.
 */
class Test_Cache_Header extends WP_UnitTestCase {

	/**
	 * With no signals against it, the header is sent.
	 */
	public function test_sends_by_default() {
		$manager = Optml_Manager::instance();
		$this->assertTrue( $manager->should_send_temporary_cache_header( [], false ) );
	}

	/**
	 * Unrelated headers do not block it.
	 */
	public function test_unrelated_headers_do_not_block() {
		$manager = Optml_Manager::instance();
		$headers = [
			'Content-Type: text/html; charset=UTF-8',
			'X-Pingback: http://example.org/xmlrpc.php',
			'Pragma: no-cache',
		];
		$this->assertTrue( $manager->should_send_temporary_cache_header( $headers, false ) );
	}

	/**
	 * An existing Cache-Control header is never overridden, e.g. WooCommerce's
	 * nocache_headers() output on cart and checkout pages.
	 */
	public function test_existing_cache_control_blocks() {
		$manager = Optml_Manager::instance();
		$headers = [
			'Expires: Wed, 11 Jan 1984 05:00:00 GMT',
			'Cache-Control: no-cache, must-revalidate, max-age=0',
		];
		$this->assertFalse( $manager->should_send_temporary_cache_header( $headers, false ) );
	}

	/**
	 * The Cache-Control match is case-insensitive and value-agnostic — a longer
	 * max-age set by a cache plugin must not be downgraded either.
	 */
	public function test_existing_cache_control_case_and_value_agnostic() {
		$manager = Optml_Manager::instance();
		$this->assertFalse( $manager->should_send_temporary_cache_header( [ 'cache-control: public, max-age=31536000' ], false ) );
	}

	/**
	 * DONOTCACHEPAGE blocks the header.
	 */
	public function test_donotcachepage_blocks() {
		$manager = Optml_Manager::instance();
		$this->assertFalse( $manager->should_send_temporary_cache_header( [], true ) );
	}

	/**
	 * The filter can force the header off.
	 */
	public function test_filter_can_disable() {
		add_filter( 'optml_send_temporary_cache_header', '__return_false' );
		$manager = Optml_Manager::instance();
		$this->assertFalse( $manager->should_send_temporary_cache_header( [], false ) );
	}

	/**
	 * The filter can force the header on despite blocking signals.
	 */
	public function test_filter_can_force_enable() {
		add_filter( 'optml_send_temporary_cache_header', '__return_true' );
		$manager = Optml_Manager::instance();
		$this->assertTrue( $manager->should_send_temporary_cache_header( [ 'Cache-Control: no-cache' ], true ) );
	}

	/**
	 * A non-boolean filter return does not accidentally enable the header.
	 */
	public function test_non_boolean_filter_return_is_not_true() {
		add_filter(
			'optml_send_temporary_cache_header',
			function () {
				return 'yes';
			}
		);
		$manager = Optml_Manager::instance();
		$this->assertFalse( $manager->should_send_temporary_cache_header( [], false ) );
	}
}
