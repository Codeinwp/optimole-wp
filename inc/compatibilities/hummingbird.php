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
	 * @return void
	 */
	public function add_clear_cache_action() {
		do_action( 'wphb_clear_page_cache' );
	}
}
