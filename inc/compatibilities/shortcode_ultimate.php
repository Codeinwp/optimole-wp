<?php

/**
 * Class Optml_shortcode_ultimate.
 *
 * @reason Shortcode Ultimate uses a strange image resizing feature
 * which has by default the hard cropping on. As we are following the WordPress
 * image size defaults, we need to change the default cropping
 * for shortcode's output.
 */
class Optml_shortcode_ultimate extends Optml_compatibility {
	/**
	 * Tags where we subscribe the compatibility.
	 *
	 * @var array Allowed tags.
	 */
	private $allowed_tags = [
		'su_slider'         => true,
		'su_carousel'       => true,
		'su_custom_gallery' => true,
	];

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'shortcodes-ultimate/shortcodes-ultimate.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'do_shortcode_tag', [ $this, 'alter_shortcode_output' ], 10, 3 );
	}

	/**
	 * Alter shortcode output by replacing the image urls.
	 *
	 * @param string $output Previous shortcode output.
	 * @param string $tag Shortcode tag.
	 * @param array  $attr Shortcode attrs.
	 *
	 * @return mixed New output.
	 */
	function alter_shortcode_output( $output, $tag, $attr ) {

		if ( ! isset( $this->allowed_tags[ $tag ] ) ) {
			return $output;
		}

		add_filter( 'optml_default_crop', [ $this, 'change_default_crop' ] );
		add_filter( 'optml_parse_resize_from_tag', [ $this, 'change_default_crop' ] );

		$output = Optml_Main::instance()->manager->process_images_from_content( $output );

		remove_filter( 'optml_default_crop', [ $this, 'change_default_crop' ] );
		remove_filter( 'optml_parse_resize_from_tag', [ $this, 'change_default_crop' ] );

		return $output;
	}

	/**
	 * Change default crop.
	 *
	 * @return array New default cropping.
	 */
	public function change_default_crop() {
		return [
			'type'    => Optml_Resize::RESIZE_FILL,
			'gravity' => Optml_Resize::GRAVITY_CENTER,
		];
	}
}
