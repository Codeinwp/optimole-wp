<?php

/**
 * Class Optml_shortcode_ultimate.
 *
 * @reason The gallery output contains a different src attribute used for lazyload
 * which prevented optimole to parse the tag.
 */
class Optml_envira extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return ( is_plugin_active( 'envira-gallery-lite/envira-gallery-lite.php' ) || is_plugin_active( 'envira-gallery/envira-gallery.php' ) );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_possible_lazyload_flags', [ $this, 'add_lazyflag' ], 10, 2 );
		add_filter( 'optml_parse_resize_from_tag', [ $this, 'check_resize_tag' ], 10, 2 );
		add_filter( 'envira_gallery_image_src', [ $this, 'revert_src' ], 10, 1 );
	}

	/**
	 * Revert the optimole url to the original state in
	 * order to allow to be parsed by the image tag parser.
	 *
	 * @param string $image Image url.
	 *
	 * @return string Original url.
	 */
	function revert_src( $image ) {

		if ( ( $pos = strpos( $image, '/http' ) ) !== false ) {
			return ltrim( substr( $image, $pos ), '/' );
		}

		return $image;
	}

	/**
	 * Alter default resize for image tag parsing.
	 *
	 * @param array  $old_resize Old array, if any.
	 * @param string $tag Image tag.
	 *
	 * @return array Resize conf.
	 */
	function check_resize_tag( $old_resize, $tag ) {
		if ( preg_match( '/(_c)\.(?:' . implode( '|', array_keys( Optml_Config::$image_extensions ) ) . ')/i', $tag, $match ) ) {
			return [
				'type'    => Optml_Resize::RESIZE_FILL,
				'gravity' => Optml_Resize::GRAVITY_CENTER,
			];
		}

		return [];
	}

	/**
	 * Add envira lazyload flag.
	 *
	 * @param array $strings Old strings.
	 *
	 * @return array New flags.
	 */
	function add_lazyflag( $strings = array() ) {

		$strings[] = 'envira-gallery-image';

		return $strings;
	}
}
