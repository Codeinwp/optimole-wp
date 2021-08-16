<?php

/**
 * Class Optml_elementor_builder
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_woocommerce extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->use_lazyload() && is_plugin_active( 'woocommerce/woocommerce.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_should_ignore_image_tags', [$this, 'check_product_page'], 10, 1 );
	}
	/**
	 * Add ignore page lazyload flag.
	 *
	 * @param bool $old_value Previous returned value.
	 *
	 * @return bool If we should lazyload the page.
	 */
	public function check_product_page( $old_value ) {
		if ( is_product() ) {
			return true;
		}
		return $old_value;
	}
}
