<?php

/**
 * Class Optml_endurance_cache.
 *
 * @reason Clear cache on endurance.
 */
class Optml_endurance_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'endurance-page-cache/endurance-page-cache.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ], 10, 1 );
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
	 * Clear cache on endurance.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		global $epc;
		if ( $location === true && isset( $epc ) && method_exists( $epc, 'purge_all' ) ) {
			$epc->purge_all();
		}
		if ( is_string( $location ) && isset( $epc ) && method_exists( $epc, 'purge_single' ) ) {
			$epc->purge_single( $location );
		}
	}
}
