<?php

/**
 * Class Optml_woocommerce.
 *
 * @reason Zoom image on product pages uses a the lqip placeholder instead of the full size image.
 */
class Optml_woocommerce extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_watcher_lz_classes', [ $this, 'add_watcher_class' ], 10, 1 );
	}

	/**
	 * Add classes for watching for late lazyload.
	 *
	 * @param string $classes Old classes.
	 *
	 * @return array New classes.
	 */
	public function add_watcher_class( $classes = array() ) {
		$classes[] = 'zoomImg';

		return $classes;
	}

}
