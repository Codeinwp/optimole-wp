<?php

/**
 * Class Optml_elementor_builder
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_give_wp extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return Optml_Main::instance()->admin->settings->use_lazyload() && is_plugin_active( 'give/give.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {

		add_filter( 'optml_page_lazyload_flags', [$this, 'add_page_lazyload_flag'], 10, 1 );

		if ( Optml_Main::instance()->admin->settings->get( 'video_lazyload' ) === 'enabled' ) {
			add_filter( 'optml_iframe_lazyload_flags', [$this, 'add_ignore_lazyload_iframe'] );
		}
	}
	/**
	 * Add ignore page lazyload flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_page_lazyload_flag( $flags = array() ) {
		$flags['?giveDonationFormInIframe=1'] = true;
		return $flags;
	}

	/**
	 * Add ignore lazyload iframe flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_ignore_lazyload_iframe( $flags = array() ) {
		$flags[] = 'give-embed-form';
		return $flags;
	}

}

