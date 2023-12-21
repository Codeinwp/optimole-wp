<?php
/**
 * Development Helpers.
 *
 * @package ThemeIsle
 */
if ( ! empty( getenv( 'WP_TESTS_DIR' ) ) ) {
	return;
}
if ( ! defined( 'ENABLE_OPTIMOLE_WP_DEV' ) ) {
	define( 'ENABLE_OPTIMOLE_WP_DEV', true );
}

if ( ENABLE_OPTIMOLE_WP_DEV ) {
	$optiml_constants = [
		'OPTIML_API_ROOT'         => 'https://staging-dashboard.optimole.com/api/',
		'OPTIML_UPLOAD_API_ROOT'  => 'https://generateurls-dev.b.optml.cloud/upload',
		'OPTIML_ONBOARD_API_ROOT' => 'https://onboard.b.optml.cloud/onboard_api/',
		'OPTML_BASE_DOMAIN'       => 'b.optml.cloud',
		'OPTML_DAM_ENDPOINT'      => 'https://staging-dashboard.optimole.com/dam/',
	];

	foreach ( $optiml_constants as $key => $value ) {
		if ( ! defined( $key ) ) {
			define( $key, $value );
		}
	}
}
