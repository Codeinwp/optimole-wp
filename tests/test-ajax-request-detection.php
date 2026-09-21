<?php
/**
 * WordPress unit tests for AJAX request detection.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2026, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Ajax_Request_Detection.
 */
class Test_Ajax_Request_Detection extends WP_UnitTestCase {

	/**
	 * Whether the action key was present before the test.
	 *
	 * @var bool
	 */
	private $had_action = false;

	/**
	 * The original action value, when there was one.
	 *
	 * @var mixed
	 */
	private $original_action = null;

	public function setUp(): void {
		parent::setUp();

		$this->had_action = isset( $_REQUEST['action'] );

		if ( $this->had_action ) {
			$this->original_action = $_REQUEST['action'];
		}

		// DOING_AJAX cannot be defined per test, the filter is the supported way in.
		add_filter( 'wp_doing_ajax', '__return_true' );

		wp_set_current_user( 0 );
	}

	public function tearDown(): void {
		remove_filter( 'wp_doing_ajax', '__return_true' );

		if ( $this->had_action ) {
			$_REQUEST['action'] = $this->original_action;
		} else {
			unset( $_REQUEST['action'] );
		}

		$this->had_action      = false;
		$this->original_action = null;

		parent::tearDown();
	}

	/**
	 * An array-valued action, i.e. `action[]=wpmdb`, must not fatal.
	 */
	public function test_array_action_does_not_fatal() {
		$_REQUEST['action'] = [ 'wpmdb' ];

		$this->assertTrue( Optml_Manager::is_ajax_request() );
	}

	/**
	 * A nested array action is handled the same way.
	 */
	public function test_nested_array_action_does_not_fatal() {
		$_REQUEST['action'] = [ 'a' => [ 'wpmdb' ] ];

		$this->assertTrue( Optml_Manager::is_ajax_request() );
	}

	/**
	 * WP Migrate DB requests stay excluded, the guarantee from 02df0774.
	 */
	public function test_wpmdb_action_still_excluded() {
		$_REQUEST['action'] = 'wpmdb_verify_connection_to_remote_site';

		$this->assertFalse( Optml_Manager::is_ajax_request() );
	}

	/**
	 * An unrelated AJAX action is still treated as a replaceable request.
	 */
	public function test_unrelated_action_is_ajax_request() {
		$_REQUEST['action'] = 'woocommerce_get_refreshed_fragments';

		$this->assertTrue( Optml_Manager::is_ajax_request() );
	}

	/**
	 * A request with no action at all is still treated as a replaceable request.
	 */
	public function test_missing_action_is_ajax_request() {
		unset( $_REQUEST['action'] );

		$this->assertTrue( Optml_Manager::is_ajax_request() );
	}
}
