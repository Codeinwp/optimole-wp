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
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'wpfc_buffer_callback_filter', [ Optml_Main::instance()->manager, 'replace_content' ], 10, 2 );
	}
}


