<?php

/**
 * Class Optml_shortcode_ultimate.
 *
 * @reason We need to turn on the image replacement on quick view ajax response, even if the user is logged in.
 */
class Optml_yith_quick_view extends Optml_compatibility {

	/**
	 * Optml_yith_quick_view constructor.
	 */
	public function __construct() {
		add_filter( 'optml_force_replacement_on', [ $this, 'force_replacement' ] );
	}

	/**
	 * Allow image replacement even if we are logged in.
	 *
	 * @param bool $status Old status.
	 *
	 * @return bool Replacement status.
	 */
	public function force_replacement( $status ) {
		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] === 'yith_load_product_quick_view' ) {
			return true;
		}

		return false;
	}

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return ( is_plugin_active( 'yith-woocommerce-quick-view/init.php' ) );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action(
			'wp_ajax_yith_load_product_quick_view',
			[
				Optml_Main::instance()->manager,
				'process_template_redirect_content',
			],
			PHP_INT_MIN
		);
	}

}
