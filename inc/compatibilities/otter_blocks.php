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
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

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
	}

}
