<?php

/**
 * Class Optml_w3_total_cache.
 *
 * @reason Cache_w3_total_cache stores the content of the page before Optimole starts replacing url's
 */
class Optml_w3_total_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'w3-total-cache/w3-total-cache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		if ( Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' ) {
			add_filter( 'w3tc_minify_processed', [ Optml_Main::instance()->manager, 'replace_content' ], 10 );
		}

		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ], 10, 1 );
	}
	/**
	 * Clear cache on w3 total cache.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {

		if ( $location === true && function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		if ( is_string( $location ) && function_exists( 'w3tc_flush_url' ) ) {
			w3tc_flush_url( $location );
		}
	}
	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return true;
	}
}
