<?php

/**
 * Class Optml_litespeed.
 *
 * @reason Clear cache on litespeed.
 */
class Optml_litespeed_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'litespeed-cache/litespeed-cache.php' );
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
	 * Clear cache on litespeed.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true && class_exists( 'LiteSpeed\Purge' ) ) {
			LiteSpeed\Purge::purge_all( 'Clear cache from Optimole settings change.' );
		}

		if ( is_string( $location ) && class_exists( 'LiteSpeed\Purge' ) ) {
			do_action( 'litespeed_purge_url', $location );
		}
	}
}
