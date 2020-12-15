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

		add_filter( 'optml_should_ignore_image_tags', [$this, 'check_givewp_page'], 10, 1 );

		if ( Optml_Main::instance()->admin->settings->get( 'video_lazyload' ) === 'enabled' ) {
			add_filter( 'optml_iframe_lazyload_flags', [$this, 'add_ignore_lazyload_iframe'] );
		}
	}
	/**
	 * Add ignore page lazyload flag.
	 *
	 * @param bool $old_value Previous returned value.
	 *
	 * @return bool If we should lazyload the page.
	 */
	public function check_givewp_page( $old_value ) {
		if ( array_key_exists( 'giveDonationFormInIframe', $_GET ) && $_GET['giveDonationFormInIframe'] === '1' ) {
			return true;
		}
		return $old_value;
	}

	/**
	 * Add ignore lazyload iframe flag.
	 *
	 * @param array $flags Old flags.
	 *
	 * @return array New flags.
	 */
	public function add_ignore_lazyload_iframe( $flags = [] ) {
		$flags[] = 'give-embed-form';
		return $flags;
	}

}

