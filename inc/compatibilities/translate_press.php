<?php

/**
 * Class Optml_translate_press.
 *
 * @reason Optml_translate_page conflict on buffer start need to hook earlier
 */
class Optml_translate_press extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return  is_plugin_active( 'translatepress-multilingual/index.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action(
			'init',
			[
				Optml_Main::instance()->manager,
				'process_template_redirect_content',
			],
			defined( 'OPTML_SITE_MIRROR' ) ? PHP_INT_MAX : PHP_INT_MIN
		);
	}
}

