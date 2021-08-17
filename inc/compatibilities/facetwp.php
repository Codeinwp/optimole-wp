<?php

/**
 * Class Optml_facetwp
 *
 * @reason Adding filter to force replacement on api request.
 */
class Optml_facetwp extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'facetwp/index.php' );
	}
	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'facetwp_ajax_response', [ $this, 'api_replacement' ], 1, 2 );
	}
	/**
	 * Add filter to replace images from api requests.
	 *
	 * @param string $output The output before optimization.
	 * @param array  $data More data about the request.
	 * @return string The output with the optimized images.
	 */
	public function api_replacement( $output, $data ) {
		do_action( 'optml_replacer_setup' );
		$output = Optml_Main::instance()->manager->replace_content( $output );
		return $output;
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
