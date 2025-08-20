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
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'facetwp/index.php' );
	}
	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'facetwp_ajax_response', [ $this, 'api_replacement_filter' ], 1, 2 );
		add_action( 'facetwp_inject_template', [ $this, 'api_replacement_action' ], 1 );
	}
	/**
	 * Add filter to replace images from api requests.
	 *
	 * Modifies template for a facetwp template.
	 * NOTE: $output needs to be json decoded before modification and re encoded before returning.
	 *
	 * @param string $output The output before optimization.
	 * @param array  $data More data about the request.
	 * @return string The output with the optimized images.
	 */
	public function api_replacement_filter( $output, $data ) {
		do_action( 'optml_replacer_setup' );

		$output = json_decode( $output );

		if ( isset( $output->template ) ) {
			$output->template = Optml_Main::instance()->manager->replace_content( $output->template, true );
		}

		// Ignore invalid UTF-8 characters in PHP 7.2+
		if ( version_compare( phpversion(), '7.2', '<' ) ) {
			$output = json_encode( $output );
		} else {
			$output = json_encode( $output, JSON_INVALID_UTF8_IGNORE );
		}
		$output = Optml_Main::instance()->manager->replace_content( $output, true );
		return $output;
	}
	/**
	 * Add action to replace images from facetwp api requests.
	 *
	 * Modifies template for a non-facetwp template.
	 *
	 * @param array $output The output before optimization.
	 * @return void
	 */
	public function api_replacement_action( $output ) {
		do_action( 'optml_replacer_setup' );

		if ( ! isset( $output['template'] ) || ! function_exists( 'FWP' ) ) {
			return;
		}
		$output['template'] = Optml_Main::instance()->manager->replace_content( $output['template'], true );
		FWP()->request->output = $output;
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
