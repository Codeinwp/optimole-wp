<?php

namespace OptimoleWP\Integrations;

/**
 * Jetpack integration status.
 *
 * @package OptimoleWP\Integrations
 */
final class JetpackStatus {
	/**
	 * Jetpack plugin file.
	 */
	private const PLUGIN_FILE = 'jetpack/jetpack.php';

	/**
	 * Jetpack image Site Accelerator module.
	 */
	private const PHOTON_MODULE = 'photon';

	/**
	 * Check whether Jetpack's image Site Accelerator is active.
	 *
	 * @return bool
	 */
	public static function is_photon_active(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( ! is_plugin_active( self::PLUGIN_FILE ) ) {
			return false;
		}

		if ( ! class_exists( '\Jetpack', false ) ) {
			return false;
		}

		return \Jetpack::is_module_active( self::PHOTON_MODULE );
	}
}
