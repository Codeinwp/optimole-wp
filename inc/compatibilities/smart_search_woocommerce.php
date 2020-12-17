<?php

/**
 * Class Optml_smart_search_woocommerce.
 *
 * @reason Smart search by searchanise stores the content of the image urls before they are processed.
 */
class Optml_smart_search_woocommerce extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'smart-search-for-woocommerce/woocommerce-searchanise.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'se_get_product_image_post', [$this, 'filter_image_url'], 1, 3 );
	}

	/**
	 *  Process image url before is send to the server.
	 *
	 * @param string $image_url The original image url.
	 * @param int    $image_id The image id .
	 * @param int    $size The image size.
	 * @return string
	 */
	public function filter_image_url( $image_url, $image_id, $size ) {
		Optml_Url_Replacer::instance()->init();
		return apply_filters( 'optml_content_url', $image_url );
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
