<?php

/**
 * Class Optml_kadence_blocks.
 *
 * @reason @reason Adding selectors for background lazyload
 */
class Optml_kadence_blocks extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'kadence-blocks/kadence-blocks.php' );
	}

	/**
	 * Register integration details.
	 *
	 * @return void
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.kt-row-has-bg';
				$all_watchers[] = '.kt-row-layout-overlay';
				$all_watchers[] = '.kt-inside-inner-col';

				return $all_watchers;
			}
		);
	}
}
