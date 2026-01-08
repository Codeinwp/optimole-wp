<?php

/**
 * Class Optml_otter_blocks.
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_otter_blocks extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'otter-blocks/otter-blocks.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.o-flip-front';
				$all_watchers[] = '.o-flip-back';
				$all_watchers[] = '.wp-block-themeisle-blocks-advanced-columns';
				$all_watchers[] = '.wp-block-themeisle-blocks-advanced-columns-overlay';
				$all_watchers[] = '.wp-block-themeisle-blocks-advanced-column';
				$all_watchers[] = '.wp-block-themeisle-blocks-advanced-column-overlay';

				return $all_watchers;
			}
		);

		// Replace the image URL with the optimized one in Otter-generated CSS.
		add_filter( 'otter_apply_dynamic_image', [ Optml_Main::instance()->manager->url_replacer, 'build_url' ], 99 );

		// Ensure replacer is initialized for Otter REST API routes (where register_hooks isn't called).
		add_filter( 'rest_pre_dispatch', [ $this, 'maybe_init_replacer_for_rest' ], 10, 3 );
	}

	/**
	 * Initialize replacer for Otter REST API routes.
	 *
	 * @param mixed            $result  Response to replace the requested version with.
	 * @param \WP_REST_Server  $server  Server instance.
	 * @param \WP_REST_Request $request Request used to generate the response.
	 * @return mixed Unmodified result.
	 */
	public function maybe_init_replacer_for_rest( $result, \WP_REST_Server $server, \WP_REST_Request $request ) {
		$route = $request->get_route();

		// Only initialize for Otter styles REST routes.
		if ( strpos( $route, '/otter/v1/post_styles' ) === false
			&& strpos( $route, '/otter/v1/widget_styles' ) === false
			&& strpos( $route, '/otter/v1/block_styles' ) === false
		) {
			return $result;
		}

		if ( ! did_action( 'optml_replacer_setup' ) ) {
			do_action( 'optml_replacer_setup' );
		}

		return $result;
	}

	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility early.
	 */
	public function should_load_early() {
		return true;
	}
}
