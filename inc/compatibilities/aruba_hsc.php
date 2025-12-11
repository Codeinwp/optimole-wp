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
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( ! class_exists( '\ArubaSPA\HiSpeedCache\Purger\WpPurger' ) || ! defined( 'AHSC_PURGER' ) ) {
			return;
		}

		// Initialize the purger.
		$purge = new \ArubaSPA\HiSpeedCache\Purger\WpPurger();
		$purge->setPurger( AHSC_PURGER );

		// Purge all cache when no location is provided.
		if ( $location === true && method_exists( $purge, 'purgeAll' ) ) {
			$purge->purgeAll();
			return;
		}

		// Purge single URL based on the location parameter.
		if ( ! method_exists( $purge, 'purgeUrl' ) ) {
			return;
		}

		$purge->purgeUrl( $location );
	}
}
