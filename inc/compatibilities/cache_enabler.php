<?php

/**
 * Class Optml_cache_enabler.
 *
 * @reason Cache_enabler stores the content of the page before Optimole starts replacing url's
 */
class Optml_cache_enabler extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'cache-enabler/cache-enabler.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		// www.keycdn.com/support/wordpress-cache-enabler-plugin#hooks

		add_filter( 'cache_enabler_page_contents_before_store', [ Optml_Main::instance()->manager, 'replace_content' ], PHP_INT_MAX, 1 );

		add_action(
			'optml_settings_updated',
			function () {
				do_action( 'cache_enabler_clear_site_cache' );
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
