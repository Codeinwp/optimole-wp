<?php

/**
 * Class Optml_swcfpc.
 *
 * @reason Clear cache on Super Page Cache for Cloudflare.
 */
class Optml_spc extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'wp-cloudflare-page-cache/wp-cloudflare-super-page-cache.php' ) || is_plugin_active( 'wp-super-page-cache-pro/wp-cloudflare-super-page-cache-pro.php' );
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
	 * Clear cache for Super Page Cache for Cloudflare.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( is_string( $location ) ) {
			do_action( 'swcfpc_purge_cache', [ $location ] );

			return;
		}

		do_action( 'swcfpc_purge_cache' );
	}
}
