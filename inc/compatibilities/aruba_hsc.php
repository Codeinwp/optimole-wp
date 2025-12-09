<?php

/**
 * Class Optml_aruba_hsc.
 *
 * @reason Clear cache on Aruba Hispeed Cache.
 */
class Optml_aruba_hsc extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'aruba-hispeed-cache/aruba-hispeed-cache.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ] );
	}


	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return true;
	}

	/**
	 * Clear cache for Aruba Hispeed Cache.
	 *
	 * @return void
	 */
	public function add_clear_cache_action() {
		if ( ! class_exists( '\ArubaSPA\HiSpeedCache\Purger\WpPurger' ) || ! defined( 'AHSC_PURGER' ) ) {
			return;
		}

		$purge = new \ArubaSPA\HiSpeedCache\Purger\WpPurger();
		$purge->setPurger( AHSC_PURGER );
		$purge->purgeAll();
	}
}
