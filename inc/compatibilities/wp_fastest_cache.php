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
		$settings = new Optml_Settings();
		return $settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'wp-fastest-cache/wpFastestCache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'wpfc_buffer_callback_filter', [$this, 'optml_process_content'], 10, 2 );
	}
	/**
	 * Process the buffer befor caching.
	 */
	public function optml_process_content( $buffer ) {
		$buffer = Optml_Manager::instance()->replace_content( $buffer );
		return $buffer;
	}
}


