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
	public function should_load() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
		return is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled';
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter( 'optml_offload_duplicated_images', [ $this, 'wpml_get_duplicates' ], PHP_INT_MAX, 2 );
		add_filter( 'optml_ensure_source_attachment_id', [ $this, 'get_source_attachment' ], PHP_INT_MAX );
	}

	/**
	 * Ads the duplicated pages/images when offloading.
	 *
	 * @param array        $duplicated_attachments The duplicated attachments array.
	 * @param string | int $attachment_id The attachment id that is first offloaded.
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
	 * Get the source attachment ID for the given attachment ID that might be a duplicate.
	 *
	 * @param int $attachment_id the attachment ID.
	 *
	 * @return int
	 */
	public function get_source_attachment( $attachment_id ) {
		if ( $this->is_offload_source( $attachment_id ) ) {
			return $attachment_id;
		}

		$duplicates = $this->wpml_get_duplicates( [], $attachment_id );

		foreach ( $duplicates as $duplicate_id ) {
			if ( $this->is_offload_source( $duplicate_id ) ) {

				return (int) $duplicate_id;
			}
		}

		return $attachment_id;
	}

	/**
	 * Checks if the given attachment is the source of the initial offload.
	 * On some instances, it seems that WPML meta is faulty.
	 * We are sure that the main attachment that has been offloaded has the proper attachment_meta.
	 *
	 * @param int $id The attachment ID.
	 *
	 * @return bool
	 */
	private function is_offload_source( $id ) {
		return ! empty( get_post_meta( (int) $id, Optml_Media_Offload::META_KEYS['offloaded'], true ) );
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
