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
		add_action( 'elementor/frontend/after_enqueue_styles', [$this, 'add_src'], PHP_INT_MIN, 1 );

		add_filter( 'elementor/frontend/builder_content/before_enqueue_css_file', [$this, 'add_src_filter'], PHP_INT_MIN, 1 );

		add_filter( 'elementor/frontend/builder_content/before_print_css', [$this, 'remove_src_filter'], PHP_INT_MIN, 1 );

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
	 * Remove filter for the image src after the css is saved by elementor.
	 *
	 * @param bool $with_css Flag used to determine if the css will be inline or not. Not used.
	 * @return bool
	 * @uses filter:elementor/frontend/builder_content/before_print_css
	 */
	public function remove_src_filter( $with_css ) {

		remove_filter( 'wp_get_attachment_image_src', [$this, 'optimize_src'], PHP_INT_MAX );

		return $with_css;
	}
	/**
	 * Add filter for the image src after the css is enqueued.
	 *
	 * @param object $css Elementor css info. Not used.
	 * @return object
	 * @uses filter:elementor/frontend/builder_content/before_enqueue_css_file
	 */
	public function add_src_filter( $css ) {

		// check if the filter was added to avoid duplication
		if ( has_filter( 'wp_get_attachment_image_src', [$this, 'optimize_src'] ) ) {
			return $css;
		}

		add_filter( 'wp_get_attachment_image_src', [$this, 'optimize_src'], PHP_INT_MAX, 4 );

		return $css;
	}

	/**
	 * Add action to add the filter for the image src.
	 *
	 * @return void
	 */
	public function add_src() {
		if ( ! has_filter( 'wp_get_attachment_image_src', [$this, 'optimize_src'] ) ) {
			add_filter( 'wp_get_attachment_image_src', [$this, 'optimize_src'], PHP_INT_MAX, 4 );
		}
	}

	/**
	 * Optimize the image src when it is requested in elementor.
	 *
	 * @param array        $image  Image data.
	 * @param  int          $attachment_id  Attachment id.
	 * @param string|int[] $size  Image size.
	 * @param bool         $icon  Whether to use icon or not.
	 * @return array
	 * @uses filter:wp_get_attachment_image_src
	 */
	public function optimize_src( $image, $attachment_id, $size, $icon ) {

		if ( ! isset( $image[0] ) ) {
			return $image;
		}
		// We can't run the replacer before the setup is done, otherwise it will throw errors.
		if ( ! did_action( 'optml_replacer_setup' ) ) {
			return $image;
		}
		$image[0] = Optml_Main::instance()->manager->url_replacer->build_url( $image[0] );

		return $image;

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
