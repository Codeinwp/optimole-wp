<?php

/**
 * Class Optml_essential_blocks
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_essential_blocks extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'essential-blocks/essential-blocks.php' );
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
				// Class starts with `eb-` and ends with `-container`
				$all_watchers[] = '[class^="eb-"][class*="-container"]';

				return $all_watchers;
			}
		);
	}
}
