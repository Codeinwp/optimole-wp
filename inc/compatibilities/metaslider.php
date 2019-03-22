<?php

/**
 * Class Optml_metaslider.
 *
 * @reason Metaslider behaves strange when the noscript tag is present near the image tag.
 */
class Optml_metaslider extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'ml-slider/ml-slider.php' ) || is_plugin_active( 'ml-slider-pro/ml-slider-pro.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_ignore_noscript_on', [ $this, 'add_noscript_flags' ], PHP_INT_MAX, 1 );
		add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_ignore_lazyload' ], PHP_INT_MAX, 1 );
		add_filter( 'optml_watcher_lz_classes', [ $this, 'add_watcher_class' ], 10, 1 );
		add_filter( 'metaslider_coin_slider_image_attributes', [ $this, 'setup_listner' ], PHP_INT_MAX, 1 );
	}

	/**
	 * Disable lazyload on coinslider type.
	 *
	 * @param array $attributes Old attributes.
	 *
	 * @return mixed Slider attributes.
	 */
	public function setup_listner( $attributes ) {
		$attributes['class'] .= 'no-optml-lazyload';

		return $attributes;
	}

	/**
	 * Add ignore lazyload flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_ignore_lazyload( $flags = array() ) {
		$flags[] = 'no-optml-lazyload';

		return $flags;
	}

	/**
	 * Add nive slider watcher class.
	 *
	 * @param array $classes Old watcher.
	 *
	 * @return array New watchers.
	 */
	public function add_watcher_class( $classes ) {
		$classes[] = 'nivo-main-image';

		return $classes;
	}


	/**
	 * Return metaslider flags.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_noscript_flags( $flags = array() ) {
		$flags[] = 'slide-';

		return $flags;
	}

}
