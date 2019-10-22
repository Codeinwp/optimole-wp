<?php

/**
 * Class Optml_elementor_builder
 *
 * @reason Adding selectors for background lazyload
 */
class Optml_divi_builder extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		return strcmp( wp_get_theme(), 'Divi' ) === 0;
	}
	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers = array_merge( $all_watchers, ['.et_pb_slides > .et_pb_slide', '.et_pb_module'] );
				return $all_watchers;
			}
		);
	}

}
