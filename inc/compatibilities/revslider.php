<?php

/**
 * Class Optml_revslider.
 *
 * @reason The slider output dont needs the data-opt-src and uses a background lazyload approach.
 */
class Optml_revslider extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'revslider/revslider.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_ignore_data_opt_flag', [ $this, 'add_data_ignore' ], 10, 3 );
		add_filter( 'optml_lazyload_bg_classes', [ $this, 'add_bg_class' ], 10, 1 );
	}

	/**
	 * Add classes for lazyload on background.
	 *
	 * @param string $classes Old classes.
	 *
	 * @return array New classes.
	 */
	public function add_bg_class( $classes = array() ) {
		$classes[] = 'tp-bgimg';

		return $classes;
	}

	/**
	 * Adds flag that should ignore applying the data-opt-src
	 *
	 * @param string $flags Flag that should ignore.
	 *
	 * @return array New flags.
	 */
	public function add_data_ignore( $flags = array() ) {
		$flags[] = 'rev-slidebg';

		return $flags;
	}

}
