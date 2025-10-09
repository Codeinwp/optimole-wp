<?php

/**
 * Class Optml_rocketnet.
 *
 * @reason Clear cache on rocketnet.
 */
class Optml_rocketnet extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {

		return class_exists( '\CDN_Clear_Cache_Api' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ], 99, 1 );
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
	 * Clear cache on rocketnet.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true && class_exists( '\CDN_Clear_Cache_Api' ) && method_exists( '\CDN_Clear_Cache_Api', 'cache_api_call' ) ) { // @phpstan-ignore-line
			\CDN_Clear_Cache_Api::cache_api_call( [], 'purge_everything' );
		}

		if ( is_string( $location ) && class_exists( '\CDN_Clear_Cache_Api' ) && method_exists( '\CDN_Clear_Cache_Api', 'cache_api_call' ) ) { // @phpstan-ignore-line
			$url_parts = parse_url( $location );
			$location  = ( isset( $url_parts['path'] ) ) ? rtrim( $url_parts['path'], '/' ) : '/';
			if ( ! empty( $url_parts['query'] ) ) {
				$location .= '?' . $url_parts['query'];
			}
			\CDN_Clear_Cache_Api::cache_api_call( [ $location ], 'purge' );
		}
	}
}
