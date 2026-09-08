<?php

/**
 * Class Optml_jetpack_photon_compatibility.
 *
 * @reason Register Jetpack as a conflicting plugin when its image Site Accelerator is active.
 */
class Optml_jetpack_photon_compatibility extends Optml_compatibility {
	/**
	 * Jetpack conflict key.
	 */
	const CONFLICT_KEY = 'jetpack_Photon';

	/**
	 * Jetpack plugin file.
	 */
	const PLUGIN_FILE = 'jetpack/jetpack.php';

	/**
	 * Check whether Jetpack's image Site Accelerator is active.
	 *
	 * @return bool Whether to load the compatibility.
	 */
	public function should_load() {
		return class_exists( '\Jetpack', false ) && \Jetpack::is_module_active( 'photon' );
	}

	/**
	 * Register the Jetpack conflict integration.
	 *
	 * @return void
	 */
	public function register() {
		add_filter( 'optml_conflicting_defined_plugins', [ $this, 'add_conflicting_plugin' ] );
	}

	/**
	 * Add Jetpack to the conflicting plugin definitions.
	 *
	 * @param array<string, string> $plugins Conflicting plugin definitions.
	 * @return array<string, string>
	 */
	public function add_conflicting_plugin( $plugins ) {
		$plugins[ self::CONFLICT_KEY ] = self::PLUGIN_FILE;

		return $plugins;
	}

	/**
	 * Register before the generic conflict notice is evaluated.
	 *
	 * @return bool
	 */
	public function should_load_early() {
		return true;
	}
}
