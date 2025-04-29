<?php

/**
 * Class Optml_wpsp
 *
 * @reason WP Show Posts is using an image resizer that is not compatible with Optimole.
 */
class Optml_wpsp extends Optml_compatibility {
	/**
	 * The last attribute used.
	 *
	 * @var array
	 */
	private static $last_attribute = [];

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		return is_plugin_active( 'wp-show-posts/wp-show-posts.php' );
	}

	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return true;
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		// This is a hack to fix the breaking images, we can reconsider when this is merged https://github.com/tomusborne/wp-show-posts/issues/50
		add_filter(
			'wpsp_image_attributes',
			function ( $attribute ) {
				self::$last_attribute = $attribute;

				return '';
			}
		);
		add_filter(
			'wpsp_default_image_size',
			function ( $size ) {
				return isset( self::$last_attribute['width'], self::$last_attribute['height'] ) ? [
					self::$last_attribute['width'],
					self::$last_attribute['height'],
				] : 'full';
			}
		);
	}
}
