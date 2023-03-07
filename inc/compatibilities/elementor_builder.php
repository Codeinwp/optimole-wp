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
		add_filter('elementor/frontend/builder_content/before_enqueue_css_file', [$this, 'replace_css'], PHP_INT_MAX, 1);
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
	public function replace_css ( $css_data ) {
		if ( ! is_object( $css_data ) ) {
			return $css_data;
		}

		// this is currently the only way to get the css path
		// as the data is private and has no getter
		// could not find any other filters to filter the css
		// this is also an open issue on elementor
		$obj = (array)($css_data);
		$css_path = false;
		foreach ($obj as $key => $value) {
			if (is_string($value) && strpos($value, '/css/') !== false) {
				$css_path = $value;
			}
		}
		if ( $css_path === false ) {
			return $css_data;
		}

		$css_contents = file_get_contents($css_path);

		$modified_css_contents = Optml_Main::instance()->manager->process_urls_from_content( $css_contents );

		file_put_contents($css_path, $modified_css_contents);

		return $css_data;
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
