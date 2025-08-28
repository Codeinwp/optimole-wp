<?php
/**
 * Optimole Constants.
 *
 * Adds Optimole constants for PHPStan to use.
 */
define( 'OPTML_BASEFILE', dirname( __FILE__, 4 ) . '/optimole-wp.php' );
define( 'OPTML_URL', plugin_dir_url( OPTML_BASEFILE ) );
define( 'OPTML_PATH', plugin_dir_path( OPTML_BASEFILE ) );
define( 'OPTML_VERSION', '3.7.0' );
define( 'OPTML_NAMESPACE', 'optml' );
define( 'OPTML_PRODUCT_SLUG', basename( OPTML_PATH ) );

if ( ! defined( 'OPTML_DEBUG' ) ) {
    define( 'OPTML_DEBUG', false );
}

if ( ! defined( 'OPTML_DEBUG' ) ) {
    define( 'OPTML_DEBUG', ( defined( 'TEST_GROUND' ) && TEST_GROUND ) ? true : false );

}
if ( ! defined( 'OPTML_DEBUG_MEDIA' ) ) {
    define( 'OPTML_DEBUG_MEDIA', false );
}
