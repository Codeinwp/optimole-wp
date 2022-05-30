<?php

/**
 * Class Optml_thrive.
 *
 * @reason @reason Adding selectors for background lazyload
 */
class Optml_thrive extends Optml_compatibility {


	/**
	 * Should we load the integration logic.
	 *
	 * @return bool Should we load.
	 */
	function should_load() {
		include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		return is_plugin_active( 'thrive-visual-editor/thrive-visual-editor.php' );
	}

	/**
	 * Register integration details.
	 */
	public function register() {
		add_filter(
			'optml_lazyload_bg_selectors',
			function ( $all_watchers ) {
				$all_watchers[] = '.tve-content-box-background';
				$all_watchers[] = '.tve-page-section-out';
				$all_watchers[] = '.thrv_text_element';

				return $all_watchers;
			}
		);
		if ( Optml_Main::instance()->admin->settings->get( 'offload_media' ) === 'enabled' ) {
			add_action( 'optml_updated_post', [$this, 'update_trive_postmeta'], 1, 1 );
		}

	}
	/**
	 * Update tve_updated_post meta with the correct status for images: offloaded/rollback.
	 *
	 * @param int $post_id The post id to be updated.
	 */
	public function update_trive_postmeta( $post_id ) {

		$post_meta = tve_get_post_meta( $post_id, 'tve_updated_post' );

		$content = Optml_Media_Offload::instance()->filter_uploaded_images( ['post_content' => $post_meta ] );

		tve_update_post_meta( $post_id, 'tve_updated_post', $content['post_content'] );
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

