<?php

/**
 * Class Optml_shortcode_ultimate.
 *
 * @reason The gallery output contains a different src attribute used for lazyload
 * which prevented optimole to parse the tag.
 */
class Optml_foogallery extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'foogallery/foogallery.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_possible_src_attributes', [ $this, 'add_lazysrc' ], 10, 3 );
		add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_lazysrc' ], 10, 3 );
	}

	/**
	 * Add foogallery lazysrc attribute.
	 *
	 * @param array $attributes Old src attributes.
	 *
	 * @return array New src attributes.
	 */
	function add_lazysrc( $attributes = [] ) {

		$attributes[] = 'data-src-fg';

		return $attributes;
	}
}
