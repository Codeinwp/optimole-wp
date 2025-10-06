<?php

/**
 * Class Optml_breeze.
 *
 * @reason Clear cache on breeze.
 */
class Optml_breeze extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'breeze/breeze.php' );
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
	 * Clear cache on breeze.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true ) {
			do_action( 'breeze_clear_all_cache' );
		}

		if ( is_string( $location ) && class_exists( 'Breeze_CloudFlare_Helper' ) && function_exists( 'breeze_varnish_purge_cache' ) ) {
			Breeze_CloudFlare_Helper::purge_cloudflare_cache_urls( [ $location ] );
			breeze_varnish_purge_cache( $location );
		}
	}
}
