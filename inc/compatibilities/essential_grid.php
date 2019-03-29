<?php

/**
 * Class Optml_metaslider.
 *
 * @reason Metaslider behaves strange when the noscript tag is present near the image tag.
 */
class Optml_essential_grid extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'essential-grid/essential-grid.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_lazyload_bg_classes', [ $this, 'add_bg_class' ], PHP_INT_MAX, 1 );
	}

	/**
	 * Adds essential grid listener class.
	 *
	 * @param array $classes Old classes.
	 *
	 * @return array New classes.
	 */
	public function add_bg_class( $classes ) {
		$classes[] = 'esg-media-poster';

		return $classes;
	}


}
