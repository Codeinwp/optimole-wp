<?php

/**
 * Class Optml_hummingbird.
 *
 * @reason Clear cache on Hummingbird.
 */
class Optml_hummingbird extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'hummingbird-performance/wp-hummingbird.php' );
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
	 * Clear cache for Hummingbird.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		// @phpstan-ignore-next-line - we need to check that method exists explicitly as it might change in the future.
		if ( ! class_exists( '\Hummingbird\Core\Utils' ) || ! method_exists( '\Hummingbird\Core\Utils', 'get_module' ) ) { // @phpstan-ignore-line
			return;
		}

		$page_cache = \Hummingbird\Core\Utils::get_module( 'page_cache' );

		if ( ! $page_cache ) {
			return;
		}

		// Clear all cache
		if ( true === $location ) {
			$page_cache->clear_cache();
			return;
		}

		// Clear specific URL
		if ( ! is_string( $location ) || empty( $location ) ) {
			return;
		}

		$url_path = wp_parse_url( $location, PHP_URL_PATH );
		if ( $url_path ) {
			$page_cache->clear_cache( trailingslashit( $url_path ), true );
		}
	}
}
