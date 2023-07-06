<?php
/**
 * Optimole Constants.
 * 
 * Adds Optimole constants for PHPStan to use.
 */

define( 'OPTML_URL', plugin_dir_url( __FILE__ ) );
define( 'OPTML_PATH', plugin_dir_path( __FILE__ ) );
define( 'OPTML_VERSION', '3.7.0' );
define( 'OPTML_NAMESPACE', 'optml' );
define( 'OPTML_BASEFILE', __FILE__ );
if ( ! defined( 'OPTML_DEBUG' ) ) {
    define( 'OPTML_DEBUG', false );
}

if ( ! defined( 'OPTML_DEBUG' ) ) {
    define( 'OPTML_DEBUG', ( defined( 'TEST_GROUND' ) && TEST_GROUND ) ? true : false );

}
if ( ! defined( 'OPTML_DEBUG_MEDIA' ) ) {
    define( 'OPTML_DEBUG_MEDIA', false );
}

define( 'WP_PLUGIN_DIR', '/plugins' );
define( 'WP_CONTENT_DIR', '/wp-content' );