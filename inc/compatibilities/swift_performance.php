<?php

/**
 * Class Optml_swift_performance.
 *
 * @reason Cache_swift_performance stores the content of the page before Optimole starts replacing url's
 */
class Optml_swift_performance extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'swift-performance-lite/performance.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'swift_performance_buffer', [ Optml_Main::instance()->manager, 'replace_content' ], 10 );
	}
}
