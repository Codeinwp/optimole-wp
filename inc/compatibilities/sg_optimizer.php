<?php

/**
 * Class Optml_sg_optimizer.
 *
 * @reason Sg_optimizer has ob_start on init
 */
class Optml_sg_optimizer extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->get( 'cdn' ) === 'enabled' && is_plugin_active( 'sg-cachepress/sg-cachepress.php' );
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
