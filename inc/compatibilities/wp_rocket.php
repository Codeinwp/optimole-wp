<?php

/**
 * Class Optml_wp_rocket.
 *
 * @reason Clear cache on wprocket.
 */
class Optml_wp_rocket extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'wp-rocket/wp-rocket.php' ) && function_exists( 'rocket_clean_domain' ) && function_exists( 'rocket_clean_files' );
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
	 * Clear cache on wprocket.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true && function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}

		if ( is_string( $location ) && function_exists( 'rocket_clean_files' ) ) {
			rocket_clean_files( $location );
		}
	}
}
