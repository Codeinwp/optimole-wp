<?php

/**
 * Class Optml_sg_optimizer.
 *
 * @reason Sg_optimizer has ob_start on init
 */
class Optml_sg_optimizer extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'sg-cachepress/sg-cachepress.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_action(
			'init',
			[
				Optml_Main::instance()->manager,
				'process_template_redirect_content',
			],
			defined( 'OPTML_SITE_MIRROR' ) ? PHP_INT_MAX : PHP_INT_MIN
		);
		add_action( 'optml_clear_cache', [ $this, 'add_clear_cache_action' ], 10, 1 );
	}
	/**
	 * Clear cache on sg optimizer.
	 *
	 * @param string|bool $location The location to clear the cache for. If true, clear the cache globally. If a string, clear the cache for a particular url.
	 * @return void
	 */
	public function add_clear_cache_action( $location ) {
		if ( $location === true && function_exists( 'sg_cachepress_purge_everything' ) ) {
			sg_cachepress_purge_everything();
		}
		if ( is_string( $location ) && function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache( $location );
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
