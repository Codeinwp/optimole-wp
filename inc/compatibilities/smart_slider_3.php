<?php

/**
 * Class Optml_smart_slider_3.
 *
 * @reason Added classes to watch for background lazyload
 */
class Optml_smart_slider_3 extends Optml_compatibility
{


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load()
	{
		include_once(ABSPATH . 'wp-admin/includes/plugin.php');

		return is_plugin_active('smart-slider-3/smart-slider-3.php');
	}

	/**
	 * Register integration details.
	 */
	public function register()
	{
		add_filter(
			'optml_lazyload_bg_selectors',
			function ($all_watchers) {
				$all_watchers = array_merge($all_watchers, ['.n2-ss-slide-background-image']);
				return $all_watchers;
			}
		);
	}
}
