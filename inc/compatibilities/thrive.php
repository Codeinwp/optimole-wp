<?php

/**
 * Class Optml_thrive.
 *
 * @reason @reason Adding selectors for background lazyload
 */
class Optml_thrive extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.tve-content-box-background';
				$all_watchers[] = '.tve-page-section-out';
				$all_watchers[] = '.thrv_text_element';

				return $all_watchers;
			}
		);
	}
}

