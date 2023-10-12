<?php

trait Optml_Dam_Offload_Utils {
	/**
	 * Checks that the attachment is a DAM image.
	 *
	 * @param int $post_id The attachment ID.
	 *
	 * @return bool
	 */
	private function is_dam_imported_image( $post_id ) {
		$meta = get_post_meta( $post_id, Optml_Dam::OM_DAM_IMPORTED_FLAG, true );

		if ( empty( $meta ) ) {
			return false;
		}

		return true;
	}

	private function is_offloaded_attachment( $id ) {
		$meta = get_post_meta( $id, self::OM_OFFLOADED_FLAG, true );

		if ( empty( $meta ) ) {
			return false;
		}

		return true;
	}

	private function is_legacy_offloaded_attachment($id) {
		return ! $this->is_offloaded_attachment( $id ) && ! empty( get_post_meta( $id, Optml_Media_Offload::META_KEYS['offloaded'] ) );
	}

	/**
	 * Get all registered image sizes.
	 *
	 * @return array
	 */
	private function get_all_image_sizes() {
		$additional_sizes = wp_get_additional_image_sizes();
		$intermediate     = get_intermediate_image_sizes();
		$all              = [];

		foreach ( $intermediate as $size ) {
			if ( isset( $additional_sizes[ $size ] ) ) {
				$all[ $size ] = [
					'width'  => $additional_sizes[ $size ]['width'],
					'height' => $additional_sizes[ $size ]['height'],
					'crop'   => isset( $additional_sizes[ $size ]['crop'] ) ? $additional_sizes[ $size ]['crop'] : false,
				];
			} else {
				$all[ $size ] = [
					'width'  => (int) get_option( $size . '_size_w' ),
					'height' => (int) get_option( $size . '_size_h' ),
					'crop'   => (bool) get_option( $size . '_crop' ),
				];
			}

			if ( ! empty( $additional_sizes[ $size ]['crop'] ) ) {
				$all[ $size ]['crop'] = $additional_sizes[ $size ]['crop'];
			} else {
				$all[ $size ]['crop'] = (bool) get_option( $size . '_crop' );
			}
		}

		return $all;
	}

	/**
	 *  Get the dimension from optimized url.
	 *
	 * @param string $url The image url.
	 * @return array Contains the width and height values in this order.
	 */
	private function parse_dimension_from_optimized_url( $url ) {
		$catch = [];
		$height = 'auto';
		$width = 'auto';
		preg_match( '/\/w:(.*)\/h:(.*)\/q:/', $url, $catch );
		if ( isset( $catch[1] ) && isset( $catch[2] ) ) {
			$width = $catch[1];
			$height = $catch[2];
		}
		return [$width, $height];
	}

	/**
	 * Check if we're in the attachment edit page.
	 *
	 * /wp-admin/post.php?post=<id>&action=edit
	 *
	 * Send whatever comes from the DAM.
	 *
	 * @param int $attachment_id attachment id.
	 *
	 * @return bool
	 */
	private function is_attachment_edit_page( $attachment_id ) {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! isset( $screen->base ) ) {
			return false;
		}

		if ( $screen->base !== 'post' ) {
			return false;
		}

		if ( $screen->post_type !== 'attachment' ) {
			return false;
		}

		if ( $screen->id !== 'attachment' ) {
			return false;
		}

		if ( ! isset( $_GET['post'] ) ) {
			return false;
		}

		if ( (int) sanitize_text_field( $_GET['post'] ) !== $attachment_id ) {
			return false;
		}

		return true;
	}

	/**
	 * Is media library list page.
	 *
	 * @return bool
	 */
	private function is_media_library_list_admin_page() {
		if ( ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! isset( $screen->base ) ) {
			return false;
		}

		if ( $screen->base !== 'upload' ) {
			return false;
		}

		return true;
	}
}
