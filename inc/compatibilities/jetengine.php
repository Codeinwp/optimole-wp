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
		Optml_Url_Replacer::instance()->init();
		add_filter(
			'jet-engine/ajax/listing_load_more/response',
			function ( $response ) {
				if ( isset( $response['html'] ) && ! empty( $response['html'] ) ) {
					$response['html'] = Optml_Main::instance()->manager->replace_content( $response['html'], true );
				}

				return $response;
			},
			10,
		);
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
