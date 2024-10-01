<?php
/**
 * Development Helpers.
 *
 * @package ThemeIsle
 */

// This condition tries to detect if we're running PHPUnit. This is complex because the file is loaded via the
// composer autoloader. You can detect that 99% of the time because PHPUnit uses the constant
// PHPUNIT_COMPOSER_INSTALL to load the autoloader. That said, if you run a test in a separate process, it won't
// run that code again and the constant doesn't exist. As a fallback, we use the PHPUNIT_RUNNING environment variable
// defined in the phpunit.xml to detect if we're in a PHPUnit process. We can't always use the environment variable
// because by default PHPUnit loads the composer autoloader before it assigns those environment variables. That's
// why both conditions are needed.
if ( defined( 'PHPUNIT_COMPOSER_INSTALL' ) || getenv( 'PHPUNIT_RUNNING' ) ) {
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
