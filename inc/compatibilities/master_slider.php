<?php

/**
 * Class Optml_master_slider.
 *
 * @reason Added classes to watch for background lazyload,
 * we can only hook the css before storing in db ('masterslider_get_all_custom_css'), but we can not reliably regenerate it when settings are changing
 */
class Optml_master_slider extends Optml_compatibility {



	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'master-slider/master-slider.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.master-slider';
				return $all_watchers;
			}
		);
		add_filter(
			'optml_dont_replace_url',
			function ( $old, $url = null ) {
				if ( strpos( $url, 'blank.gif' ) !== false ) {
					return true;
				}
					return $old;
			},
			10,
			2
		);
	}
}
