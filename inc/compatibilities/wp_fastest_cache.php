<?php

/**
 * Class Optml_wp_fastest_cache.
 *
 * @reason Wp fastest cache stores the content of the page before Optimole starts replacing url's for the minified css/js files
 */
class Optml_wp_fastest_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ], 10, 1 );
		add_filter( 'wpfc_buffer_callback_filter', [ Optml_Main::instance()->manager, 'replace_content' ], 10 );
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
	 * Clear cache on wp fastest cache.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true ) {
			do_action( 'wpfc_clear_all_cache', true );
		}

		if ( is_string( $location ) ) {
			// TODO Right now the plugin doesn't support clearing cache for a particular url.
		}
	}
}
