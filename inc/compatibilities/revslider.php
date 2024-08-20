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
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'revslider/revslider.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {

		add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_lazyflag' ], 10 );
		add_filter( 'optml_ignore_data_opt_flag', [ $this, 'add_data_ignore' ], 10 );
		add_filter( 'optml_lazyload_bg_classes', [ $this, 'add_bg_class' ], 10 );
	}

	/**
	 * Add Slider Revolution lazyload flag.
	 *
	 * @param array $strings Old strings.
	 *
	 * @return array New flags.
	 */
	public function add_lazyflag( $strings = [] ) {

		$strings[] = 'rev-slidebg';
		$strings[] = 'rs-lazyload';

		return $strings;
	}

	/**
	 * Add classes for lazyload on background.
	 *
	 * @param array $classes Old classes.
	 *
	 * @return array New classes.
	 */
	public function add_bg_class( $classes = [] ) {
		$classes[] = 'tp-bgimg';

		return $classes;
	}

	/**
	 * Adds flag that should ignore applying the data-opt-src
	 *
	 * @param array $flags Flag that should ignore.
	 *
	 * @return array New flags.
	 */
	public function add_data_ignore( $flags = [] ) {
		$flags[] = 'rev-slidebg';
		$flags[] = 'rs-lazyload';
		return $flags;
	}
}
