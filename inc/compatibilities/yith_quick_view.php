<?php

/**
 * Class Optml_yith_quick_view.
 *
 * @reason We need to turn on the image replacement on quick view ajax response, to do this we hook the product image filter.
 */
class Optml_yith_quick_view extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return  Optml_Main::instance()->admin->settings->is_connected() && is_plugin_active( 'yith-woocommerce-quick-view/init.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		Optml_Url_Replacer::instance()->init();
		add_filter( 'woocommerce_single_product_image_thumbnail_html', [ Optml_Main::instance()->manager, 'replace_content' ] );
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
