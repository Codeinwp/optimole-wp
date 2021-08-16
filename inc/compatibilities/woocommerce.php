<?php

/**
 * Class Optml_woocommerce
 *
 * @reason Adding flags for ignoring the lazyloaded tags.
 */
class Optml_woocommerce extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->use_lazyload() && is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_ignore_lazyload' ], PHP_INT_MAX, 1 );
	}
	/**
	 * Add ignore lazyload flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_ignore_lazyload( $flags = [] ) {
		$flags[] = 'data-large_image';

		return $flags;
	}
}
