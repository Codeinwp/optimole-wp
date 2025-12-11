<?php
/**
 * PHPUnit bootstrap file
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
    $_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

// Forward custom PHPUnit Polyfills configuration to PHPUnit bootstrap file.
$_phpunit_polyfills_path = getenv( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH' );
if ( false !== $_phpunit_polyfills_path ) {
    define( 'WP_TESTS_PHPUNIT_POLYFILLS_PATH', $_phpunit_polyfills_path );
}

if ( ! file_exists( "{$_tests_dir}/includes/functions.php" ) ) {
    echo "Could not find {$_tests_dir}/includes/functions.php, have you run bin/install-wp-tests.sh ?" . PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    exit( 1 );
}

define( 'WP_USE_THEMES', false );
define( 'WP_TESTS_FORCE_KNOWN_BUGS', true );
define( 'OPTML_PHPUNIT_TESTING', true );
// Give access to tests_add_filter() function.
require_once "{$_tests_dir}/includes/functions.php";

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
	
	// Prevent cache clearing actions during tests to avoid errors from cache compatibility classes
	// This filter prevents optml_settings_updated and optml_clear_cache from being triggered
	add_filter( 'optml_dont_trigger_settings_updated', '__return_true' );
}

tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

// If PHPUnit is run from the project directory, it loads the themeisle SDK via the composer autoload file at the
// beginning of the script execution. This is too early because the SDK needs WordPress to be loaded. This code checks
// if the SDK was actually loaded and forces it to load again if it wasn't loaded properly.
if ( ! function_exists( 'tsdk_translate_link' ) ) {
    require __DIR__ . '/../vendor/codeinwp/themeisle-sdk/load.php';
}

// Activate Optimole plugin
activate_plugin( 'optimole-wp/optimole-wp.php' );


// Set up the current logged in user
global $current_user;

$current_user = new WP_User( 1 );
$current_user->set_role( 'administrator' );

wp_update_user( array( 'ID' => 1, 'first_name' => 'Admin', 'last_name' => 'User' ) );