<?php

/**
 * Class Optml_jetengine
 *
 * @reason Add support for JetEngine plugin custom endpoints.
 */
class Optml_jetengine extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'jet-engine/jet-engine.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'jet-engine/listings/ajax/load-more', [ $this, 'setup_tag_replacer' ], PHP_INT_MIN, 1 );
	}


	/**
	 * Setup the URL replacer for Load More ajax calls for "Listings Grid" block.
	 *
	 * The request is with "POST" method, so the usual checks in should_replace() will fail.
	 *
	 * @see ajax:jet_engine_ajax with action:listing_load_more
	 *
	 * @return void
	 */
	public function setup_tag_replacer() {
		if ( did_action( 'optml_replacer_setup' ) ) {
			return;
		}
		do_action( 'optml_replacer_setup' );
	}

	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return true;
	}
}
