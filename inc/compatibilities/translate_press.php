<?php

/**
 * Class Optml_translate_press.
 *
 * @reason Optml_translate_page we don't process translated images, need to hook translated content
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
		add_filter(
			'trp_translated_html',
			[ Optml_Main::instance()->manager, 'replace_content' ],
			10,
			4
		);
	}
}

