<?php

namespace OptimoleWP\Offload;

/**
 * Class Loader
 *
 * Handles the registration of custom image editors for the Optimole WordPress plugin.
 * This class is responsible for integrating Optimole's custom image processing
 * capabilities into WordPress's image editing system.
 *
 * @package OptimoleWP\Offload
 */
class Loader {

	/**
	 * Constructor for the Loader class.
	 */
	public function __construct() {
	}

	/**
	 * Register WordPress hooks needed for the image editor functionality.
	 *
	 * Adds filters to integrate the custom image editor into WordPress.
	 */
	public function register_hooks() {
		if ( has_filter( 'wp_image_editors', 'photon_subsizes_override_image_editors' ) ) {
			return;
		}
		add_filter( 'wp_image_editors', [ $this, 'register_image_editor' ] );
	}

	/**
	 * Register the custom ImageEditor class to WordPress's image editors list.
	 *
	 * Adds Optimole's custom ImageEditor to the beginning of WordPress's image editors array,
	 * giving it priority over the default editors.
	 *
	 * @param array $editors Array of image editor class names.
	 * @return array Modified array of image editor class names.
	 */
	public function register_image_editor( $editors ) {
		array_unshift( $editors, ImageEditor::class );
		return $editors;
	}
}
