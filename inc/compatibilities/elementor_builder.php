<?php

/**
 * Class Optml_elementor_builder
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_elementor_builder extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'elementor/elementor.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.elementor-widget-container';
				$all_watchers[] = '.elementor-background-slideshow__slide__image';
				return $all_watchers;
			}
		);
		add_action(
			'optml_settings_updated',
			function () {
				if ( did_action( 'elementor/loaded' ) ) {
					if ( class_exists( '\Elementor\Plugin' ) ) {
						\Elementor\Plugin::instance()->files_manager->clear_cache();
					}
				}
			}
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
