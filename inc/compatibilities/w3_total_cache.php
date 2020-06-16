<?php

/**
 * Class Optml_cache_enabler.
 *
 * @reason Cache_enabler stores the content of the page before Optimole starts replacing url's
 */
class Optml_w3_total_cache extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		$settings = new Optml_Settings();
		return $settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'w3-total-cache/w3-total-cache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'w3tc_minify_processed', [$this, 'optml_filter_w3tc_minify_processed'], 10, 1 );
	}
	/**
	 * Process buffer before caching.
	 */
	public function optml_filter_w3tc_minify_processed( $buffer ) {
		$buffer = Optml_Manager::instance()->replace_content( $buffer );
		return $buffer;
	}
}

