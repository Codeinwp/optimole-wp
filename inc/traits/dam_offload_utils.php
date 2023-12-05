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

	/**
	 * Checks if the attachment is offloaded using the old method.
	 *
	 * @param int $id The attachment ID.
	 *
	 * @return bool
	 */
	private function is_legacy_offloaded_attachment( $id ) {
		$id = apply_filters( 'optml_ensure_source_attachment_id', $id );

		return ! $this->is_new_offloaded_attachment( $id ) && ! empty( get_post_meta( $id, Optml_Media_Offload::META_KEYS['offloaded'] ) );
	}

	/**
	 * Check if it's a newly offloaded attachment
	 *
	 * @param int $id The attachment ID.
	 *
	 * @return bool
	 */
	private function is_new_offloaded_attachment( $id ) {
		$id = apply_filters( 'optml_ensure_source_attachment_id', $id );

		return ! empty( get_post_meta( $id, Optml_Media_Offload::OM_OFFLOADED_FLAG, true ) );
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
					'crop'   => get_option( $size . '_crop' ),
				];
			}

			if ( ! empty( $additional_sizes[ $size ]['crop'] ) ) {
				$all[ $size ]['crop'] = is_array( $additional_sizes[ $size ]['crop'] ) ? $additional_sizes[ $size ]['crop'] : (bool) $additional_sizes[ $size ]['crop'];

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
	 *
	 * @return array Contains the width and height values in this order.
	 */
	private function parse_dimension_from_optimized_url( $url ) {
		$catch  = [];
		$height = 'auto';
		$width  = 'auto';
		preg_match( '/\/w:(.*)\/h:(.*)\/q:/', $url, $catch );
		if ( isset( $catch[1] ) && isset( $catch[2] ) ) {
			$width  = $catch[1];
			$height = $catch[2];
		}
		return [ $width, $height ];
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

		if ( ! function_exists( 'get_current_screen' ) ) {
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
	 * Used to filter the image metadata. Adds optimized image url for all image sizes.
	 *
	 * @param array $metadata The attachment metadata.
	 * @param int   $id The attachment id.
	 *
	 * @return mixed
	 */
	private function get_altered_metadata_for_remote_images( $metadata, $id ) {
		$sizes = $this->get_all_image_sizes();

		$post = get_post( $id );

		$sizes_meta = [];

		// SVG files don't have a width/height so we add a dummy one. These are vector images so it doesn't matter.
		$is_svg = ( $post->post_mime_type === Optml_Config::$image_extensions['svg'] );

		if ( $is_svg ) {
			$metadata['width']  = 150;
			$metadata['height'] = 150;
		}

		if ( ! isset( $metadata['height'] ) || ! isset( $metadata['width'] ) ) {
			return $metadata;
		}

		foreach ( $sizes as $size => $args ) {
			// check if the image is portrait or landscape using attachment metadata.
			$is_portrait = $metadata['height'] > $metadata['width'];

			// proportionally set the width/height based on this if image is uncropped.
			if ( ! (bool) $args['crop'] ) {
				if ( $is_portrait ) {
					$args['width'] = (int) ( $args['height'] * round( $metadata['width'] / $metadata['height'] ) );
				} else {
					$args['height'] = (int) ( $args['width'] * round( $metadata['height'] / $metadata['width'] ) );
				}
			}

			$sizes_meta[ $size ] = [
				'file'      => $metadata['file'],
				'width'     => $args['width'],
				'height'    => $args['height'],
				'mime-type' => $post->post_mime_type,
			];
		}

		$metadata['sizes'] = $sizes_meta;

		return $metadata;
	}

	/**
	 * Get the scaled URL.
	 *
	 * @param string $url Original URL.
	 *
	 * @return string
	 */
	private function get_scaled_url( $url ) {
		$extension = pathinfo( $url, PATHINFO_EXTENSION );

		return str_replace( '.' . $extension, '-scaled.' . $extension, $url );
	}
	/**
	 * Check if the URL is a scaled image.
	 *
	 * @param string $url The URL to check.
	 *
	 * @return bool
	 */
	private function is_scaled_url( $url ) {
		return strpos( $url, '-scaled.' ) !== false;
	}
	/**
	 * Get the attachment ID from URL.
	 *
	 * @param string $url The attachment URL.
	 *
	 * @return int
	 */
	private function attachment_url_to_post_id( $url ) {
		$cached = Optml_Attachment_Cache::get_cached_attachment_id( $url );

		if ( $cached !== false ) {
			return $cached;
		}

		$url = $this->strip_image_size( $url );

		$attachment_id = attachment_url_to_postid( $url );

		if ( $attachment_id === 0 && ! $this->is_scaled_url( $url ) ) {
			$scaled_url = $this->get_scaled_url( $url );

			$attachment_id = attachment_url_to_postid( $scaled_url );
		}

		Optml_Attachment_Cache::set_cached_attachment_id( $url, $attachment_id );

		return $attachment_id;
	}

	/**
	 * Strips the image size from the URL.
	 *
	 * @param string $url URL to strip.
	 *
	 * @return string
	 */
	private function strip_image_size( $url ) {
		if ( preg_match( '#(-\d+x\d+(?:_c)?|(@2x))\.(' . implode( '|', array_keys( Optml_Config::$image_extensions ) ) . '){1}$#i', $url, $src_parts ) ) {
			$url = str_replace( $src_parts[1], '', $url );
		}

		return $url;

	}
}
