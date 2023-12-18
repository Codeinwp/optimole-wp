<?php
/**
 * PHPUnit bootstrap file
 *
 * @package eyepatch-manager
 */

if ( getenv( 'WP_LOCAL_TESTING' ) === 'true' ) {
	require dirname(dirname(__FILE__)) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

/**
 * The path to the main file of the plugin to test.
 */
define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
define( 'OPTML_PHPUNIT_TESTING', true );
// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';
/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	//override the log action for testing
	//define('OPTML_DEBUG_MEDIA', true);
	add_action(
		'optml_log',
		function( $message ) {
			var_dump($message);
		}
	);
	global $_test_posssible_values_x_sizes;
	$_test_posssible_values_x_sizes = array(
		true,
		'left',
		'center',
		'right'
	);
	global $_test_posssible_values_y_sizes;
	$_test_posssible_values_y_sizes = array(
		true,
		'top',
		'center',
		'bottom'
	);
	add_image_size( 'my_size_crop', 200, 200, array('right','top') );
	add_image_size( 'sample_size_crop', 100, 100, true );
	foreach ( $_test_posssible_values_x_sizes as $x_value ) {
		foreach ( $_test_posssible_values_y_sizes as $y_value ) {
			if ( $x_value === true && $y_value === true ) {
				continue;
			}
			$crop    = array( $x_value, $y_value );
			$x_value = $x_value === true ? '' : $x_value;
			$y_value = $y_value === true ? '' : $y_value;
			add_image_size( 'sample_size_h_' . $x_value . $y_value, 120, 120, $crop );
		}
	}
	require dirname( dirname( __FILE__ ) ) . '/optimole-wp.php';
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
activate_plugin( 'optimole-wp/optimole-wp.php' );
global $current_user;
$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );
wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );