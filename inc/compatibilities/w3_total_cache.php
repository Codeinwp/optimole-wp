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
		return  is_plugin_active( 'w3-total-cache/w3-total-cache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		if ( Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' ) {
			add_filter( 'w3tc_minify_processed', [Optml_Main::instance()->manager, 'replace_content'], 10 );
		}

		add_action(
			'optml_settings_updated',
			function () {
				if ( function_exists( 'w3tc_flush_all' ) ) {
					w3tc_flush_all();
				}
			}
		);
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

