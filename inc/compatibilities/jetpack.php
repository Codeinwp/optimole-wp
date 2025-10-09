<?php

/**
 * Class Optml_jetpack
 *
 * @reason Add support for Jetpack plugin custom endpoints.
 */
class Optml_jetpack extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'jetpack/jetpack.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_filter(
			'jetpack_sync_before_send_jetpack_published_post',
			function ( $data ) {
				if ( ! is_array( $data ) ) {
					return $data;
				}

				foreach ( $data as &$value ) {
					if ( ! $value instanceof \WP_Post ) {
						continue;
					}

					if ( ! empty( $value->post_content ) ) {
						$value->post_content = Optml_Main::instance()->manager->replace_content( $value->post_content );
					}

					if ( ! empty( $value->post_excerpt ) ) {
						$value->post_excerpt = Optml_Main::instance()->manager->replace_content( $value->post_excerpt );
					}

					if ( isset( $value->featured_image ) && ! empty( $value->featured_image ) ) {
						$value->featured_image = apply_filters( 'optml_content_url', $value->featured_image );
					}
				}

				return $data;
			},
			10
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
