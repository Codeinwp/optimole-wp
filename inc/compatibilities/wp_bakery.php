<?php

/**
 * Class Optml_wp_bakery
 *
 * @reason Adding images from wpbakery pages to offload.
 */
class Optml_wp_bakery extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'js_composer/js_composer.php' ) && Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled';
	}

	/**
	 * Register integration details.
	 */
	public function register() {
			add_filter( 'optml_content_images_to_update', [ $this, 'add_images_from_wp_bakery_to_offload' ], PHP_INT_MAX, 2 );
	}

	/**
	 * Ads the product pages to the list of posts parents when querying images for offload.
	 *
	 * @param array  $found_images The found images from the default workflow.
	 * @param string $content Post content to look for images.
	 *
	 * @return array The images array with the specific bakery images.
	 */
	public function add_images_from_wp_bakery_to_offload( $found_images, $content ) {

		$regex = '/image="(\d+)"/Uu';
		preg_match_all(
			$regex,
			$content,
			$images_to_append
		);
		return array_merge( $found_images, $images_to_append[1] );
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
