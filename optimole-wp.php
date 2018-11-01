<?php
/**
 * Plugin Name:       Image optimization service by Optimole
 * Description:       Complete handling of your website images.
 * Version:           1.1.0
 * Author:            Optimole
 * Author URI:        https://optimole.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       optimole-wp
 * Domain Path:       /languages
 * WordPress Available:  yes
 * Requires License:    no
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}
/**
 * Autoloader function.
 *
 * @param string $class Class to load.
 */
function optml_autoload( $class ) {
	$prefix = 'Optml';
	if ( strpos( $class, $prefix ) !== 0 ) {
		return;
	}
	$file = str_replace( $prefix . '_', '', $class );

	$file = strtolower( $file );
	$file = dirname( __FILE__ ) . '/inc/' . $file . '.php';
	if ( file_exists( $file ) ) {
		require $file;
	}

}

/**
 * Initiate the Optimole plugin.
 *
 * @return Optml_Main Optimole instance.
 */
function optml() {
	define( 'OPTML_URL', plugin_dir_url( __FILE__ ) );
	define( 'OPTML_JS_CDN', 'd5jmkjjpb7yfg.cloudfront.net' );
	define( 'OPTML_PATH', plugin_dir_path( __FILE__ ) );
	define( 'OPTML_VERSION', '1.1.0' );
	define( 'OPTML_NAMESPACE', 'optml' );
	define( 'OPTML_BASEFILE', __FILE__ );
	if ( ! defined( 'OPTML_DEBUG' ) ) {
		define( 'OPTML_DEBUG', ( defined( 'TEST_GROUND' ) && TEST_GROUND ) ? true : false );
	}

	return Optml_Main::instance();
}

spl_autoload_register( 'optml_autoload' );

optml();
