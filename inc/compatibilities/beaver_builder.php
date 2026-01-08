<?php

/**
 * Class Optml_beaver_builder.
 *
 * @reason Beaver builder offload the CSS in a separate file, which we access and process the image urls.
 */
class Optml_beaver_builder extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'bb-plugin/fl-builder.php' ) || is_plugin_active( 'beaver-builder-lite-version/fl-builder.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.fl-col-content';
				$all_watchers[] = '.fl-row-bg-photo > .fl-row-content-wrap';
				$all_watchers[] = '.fl-module-box';

				return $all_watchers;
			}
		);
		add_filter( 'fl_builder_render_css', [ $this, 'replace_static_content' ], PHP_INT_MAX, 1 );
		add_filter( 'fl_builder_render_js', [ $this, 'replace_static_content' ], PHP_INT_MAX, 1 );
	}

	/**
	 * Replace urls in static content.
	 *
	 * @param string $content Content to replace.
	 *
	 * @return string Altered content.
	 */
	public function replace_static_content( $content ) {
		return Optml_Main::instance()->manager->replace_content( $content, true );
	}
}
