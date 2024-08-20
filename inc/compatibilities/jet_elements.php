<?php

/**
 * Class Optml_jet_elements.
 *
 * @reason Disable lazyload on jetelements slider.
 */
class Optml_jet_elements extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'jet-elements/jet-elements.php' );
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
		$flags[] = 'sp-image';

		return $flags;
	}
}
