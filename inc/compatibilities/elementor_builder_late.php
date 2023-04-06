<?php

/**
 * Class Optml_elementor_builder_late
 *
 * @reason Adding action for elementor meta replacement
 */
class Optml_elementor_builder_late extends Optml_compatibility {

	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'elementor/elementor.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_action( 'get_post_metadata', [ $this, 'replace_meta' ], PHP_INT_MAX, 4 );
	}
	/**
	 * Replace urls in post meta values.
	 *
	 * @param mixed  $metadata Metadata.
	 * @param int    $object_id Post id.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Is single.
	 *
	 * @return mixed Altered meta.
	 */
	public function replace_meta( $metadata, $object_id, $meta_key, $single ) {

		$meta_needed = '_elementor_data';

		if ( isset( $meta_key ) && $meta_needed === $meta_key ) {
			remove_filter( 'get_post_metadata', [ $this, 'replace_meta' ], PHP_INT_MAX );

			$current_meta = get_post_meta( $object_id, $meta_needed, $single );
			add_filter( 'get_post_metadata', [ $this, 'replace_meta' ], PHP_INT_MAX, 4 );

			if ( ! is_string( $current_meta ) ) {
				return $metadata;
			}

			return Optml_Main::instance()->manager->replace_content( $current_meta );
		}

		// Return original if the check does not pass
		return $metadata;
	}
}
