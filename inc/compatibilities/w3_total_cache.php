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
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'w3-total-cache/w3-total-cache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'w3tc_minify_processed', [ Optml_Main::instance()->manager, 'replace_content' ], 10, 1 );
	}
}

