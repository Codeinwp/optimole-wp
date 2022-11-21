<?php

/**
 * Class Optml_wpml
 *
 * @reason Wpml duplicates everything so we need to offload/update every image/page attachment.
 */
class Optml_wpml extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled';
	}

	/**
	 * Register integration details.
	 */
	public function register() {
			add_filter( 'optml_offload_duplicated_images', [ $this, 'wpml_get_duplicates' ], PHP_INT_MAX, 2 );
	}

	/**
	 * Ads the product pages to the list of posts parents when querying images for offload.
	 *
	 * @param array  $found_images The found images from the default workflow.
	 * @param string $content Post content to look for images.
	 *
	 * @return array The images array with the specific bakery images.
	 */
	public function wpml_get_duplicates( $duplicated_attachments, $attachment_id ) {

		// Get the TRID (Translation ID) from element. REF: https://wpml.org/wpml-hook/wpml_element_trid/
		$trid = apply_filters( 'wpml_element_trid', null, $attachment_id, 'post_attachment' );

		// Get all translations (elements with the same TRID). REF: https://wpml.org/wpml-hook/wpml_get_element_translations/
		$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_attachment' );

		foreach ( $translations as $translation ) {
			if ( isset( $translation->element_id ) ) {
				$duplicated_attachments[] = $translation->element_id;
			}
		}
		return $duplicated_attachments;
	}
	/**
	 * Should we early load the compatibility?
	 *
	 * @return bool Whether to load the compatibility or not.
	 */
	public function should_load_early() {
		return true;
	}
}
