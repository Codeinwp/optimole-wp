<?php

/**
 * Class Optml_wp_rest_cache.
 *
 * @reason Wp rest cache stores the api response before Optimole starts replacing urls
 */
class Optml_wp_rest_cache extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'wp-rest-cache/wp-rest-cache.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'rest_pre_echo_response', [$this, 'api_optimization'], 10, 3 );
	}

	/**
	 * Replace the urls in the api response.
	 *
	 * @param array           $result An array containing the result before our optimization.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 * @return array The decoded result with the replaced urls.
	 */
	public function api_optimization( $result, $server, $request ) {
		$result = json_decode( Optml_Main::instance()->manager->process_urls_from_content( json_encode( $result ) ) );
		return $result;
	}
}
