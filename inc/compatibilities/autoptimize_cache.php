<?php

/**
 * Class Optml_autoptimize_cache.
 *
 * @reason Clear cache on autoptimize.
 */
class Optml_autoptimize_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'autoptimize/autoptimize.php' );
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
	 * Clear cache on autoptimize.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true && class_exists( 'autoptimizeCache' ) ) {
			autoptimizeCache::clearall();
		}
		// todo: add support for clearing cache for a particular url, not supported yet.
	}
}
