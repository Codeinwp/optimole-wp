<?php

/**
 * Optml_Attachment_Replace
 *
 * @since 4.0.0
 */
class Optml_Attachment_Replace {

	/**
	 * Attachment ID.
	 *
	 * @var int
	 */
	private $attachment_id;

	/**
	 * File.
	 *
	 * @var array
	 */
	private $file;

	/**
	 * Attachment.
	 *
	 * @var \Optml_Attachment_Model
	 */
	private $attachment;

	/**
	 * New attachment.
	 *
	 * @var \Optml_Attachment_Model
	 */
	private $new_attachment;

	/**
	 * Constructor.
	 *
	 * @param int   $attachment_id Attachment ID.
	 * @param array $file File.
	 */
	public function __construct( $attachment_id, $file ) {
		$this->attachment_id = $attachment_id;
		$this->file          = $file;
		$this->attachment    = new Optml_Attachment_Model( $attachment_id );

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
	}

	/**
	 * Replace the attachment.
	 *
	 * @return bool|WP_Error
	 */
	public function replace() {
		if ( ! file_exists( $this->file['tmp_name'] ) ) {
			return new WP_Error( 'file_error', __( 'Error uploading file.', 'optimole-wp' ) );
		}

		$original_file  = $this->attachment->get_source_file_path();
		$old_sizes_urls = $this->attachment->get_all_image_sizes_urls();

		if ( ! file_exists( $original_file ) ) {
			return new WP_Error( 'file_error', __( 'Original file does not exist.', 'optimole-wp' ) );
		}

		$original_filetype = wp_check_filetype( $original_file );
		$uploaded_filetype = wp_check_filetype( $this->file['name'] );

		if ( $original_filetype['type'] !== $uploaded_filetype['type'] ) {
			return new WP_Error( 'file_error', __( 'The uploaded file type does not match the original file type.', 'optimole-wp' ) );
		}

		global $wp_filesystem;

		if ( ! $wp_filesystem->move( $this->file['tmp_name'], $original_file, true ) ) {
			return new WP_Error( 'file_error', __( 'Could not move file.', 'optimole-wp' ) );
		}

		$permissions_normalized = $this->normalize_file_permissions( $original_file );

		$this->remove_all_image_sizes();

		clean_attachment_cache( $this->attachment_id );

		$metadata = wp_generate_attachment_metadata( $this->attachment_id, $original_file );

		if ( isset( $metadata['sizes'] ) ) {
			$this->replace_image_sizes_links( $metadata['sizes'], $old_sizes_urls );
		}

		wp_update_attachment_metadata( $this->attachment_id, $metadata );
		$this->new_attachment = new Optml_Attachment_Model( $this->attachment_id );

		$this->handle_scaled_images();

		do_action( 'optml_attachment_replaced', $this->attachment_id );

		if ( ! $permissions_normalized ) {
			return new WP_Error(
				'file_permissions_error',
				__( 'The permissions may not be updated, so it isn\'t publicly accessible. Please update the permissions.', 'optimole-wp' )
			);
		}

		return true;
	}

	/**
	 * Normalize the permissions of the replaced file.
	 *
	 * @param string $file File path.
	 *
	 * @return bool Whether the file ended up with the expected permissions.
	 */
	private function normalize_file_permissions( $file ) {
		global $wp_filesystem;

		$mode = defined( 'FS_CHMOD_FILE' ) ? FS_CHMOD_FILE : 0644;

		$applied = $wp_filesystem->chmod( $file, $mode );

		$reason = '';

		if ( ! $applied || ! $this->has_permissions( $file, $mode ) ) {
			// Fallback for transports where the filesystem abstraction can't chmod.
			set_error_handler(
				function ( $errno, $errstr ) use ( &$reason ) {
					$reason = $errstr;

					return true;
				}
			);

			$applied = chmod( $file, $mode );

			restore_error_handler();
		}

		if ( $applied && $this->has_permissions( $file, $mode ) ) {
			return true;
		}

		if ( OPTML_DEBUG ) {
			do_action(
				'optml_log',
				sprintf( 'Could not normalize permissions to %o for replaced file %s. %s', $mode, $file, $reason )
			);
		}

		return false;
	}

	/**
	 * Check the current permissions of a file against an expected mode.
	 *
	 * @param string $file File path.
	 * @param int    $mode Expected mode.
	 *
	 * @return bool
	 */
	private function has_permissions( $file, $mode ) {
		clearstatcache( true, $file );

		$perms = fileperms( $file );

		return false !== $perms && ( $perms & 0777 ) === ( $mode & 0777 );
	}

	/**
	 * Remove all image sizes files.
	 *
	 * @return void
	 */
	private function remove_all_image_sizes() {
		$all_image_sizes_paths = $this->attachment->get_all_image_sizes_paths();
		global $wp_filesystem;

		foreach ( $all_image_sizes_paths as $path ) {
			if ( file_exists( $path ) ) {
				$wp_filesystem->delete( $path );
			}
		}
	}

	/**
	 * Handle scaled images.
	 *
	 * @return bool
	 */
	private function handle_scaled_images() {
		global $wp_filesystem;

		$old_scaled = $this->attachment->is_scaled();
		$new_scaled = $this->new_attachment->is_scaled();
		$replacer   = new Optml_Attachment_Db_Renamer( true );

		$new_file_path = $this->new_attachment->get_source_file_path();
		$file          = apply_filters( 'update_attached_file', $new_file_path, $this->attachment_id );

		// New is scaled, but old is not scaled. We don't replace anything.
		if ( $old_scaled === $new_scaled || ( ! $old_scaled && $new_scaled ) ) {
			return true;
		}

		// Delete the old scaled version and replace scaled URLs with non-scaled URLs.
		if ( $old_scaled && ! $new_scaled ) {
			$main_file_url   = $this->attachment->get_main_url();
			$unscaled_file   = $this->attachment->get_filename_with_ext();
			$old_scaled_file = $this->attachment->get_filename_with_ext( true );
			$old_scaled_url  = str_replace( $unscaled_file, $old_scaled_file, $main_file_url );

			$replacer->replace( $old_scaled_url, $main_file_url );

			// replace the old year/month/item-scaled.ext with the new year/month/item.ext
			$scaled_path = str_replace( $unscaled_file, $old_scaled_file, $this->attachment->get_source_file_path() );
			if ( file_exists( $scaled_path ) ) {
				$wp_filesystem->delete( $scaled_path );
			}

			update_attached_file( $this->attachment_id, sprintf( '%s/%s', $this->attachment->get_metadata_prefix_path(), $unscaled_file ) );
		}

		return true;
	}

	/**
	 * Replace image sizes links.
	 *
	 * @param array $new_sizes New sizes.
	 * @param array $old_sizes_urls Old sizes URLs.
	 *
	 * @return void
	 */
	private function replace_image_sizes_links( $new_sizes, $old_sizes_urls ) {
		$replacer = new Optml_Attachment_Db_Renamer( true );

		foreach ( $old_sizes_urls as $size => $old_url ) {
			// If the size is not in the new sizes, we need to use the original URL.
			if ( ! isset( $new_sizes[ $size ], $new_sizes[ $size ]['file'] ) ) {
				$replacer->replace( $old_url, $this->attachment->get_main_url() );
				continue;
			}

			// If the size is in the new sizes, we need to use the new URL.
			$new_url = str_replace( $this->attachment->get_filename_with_ext(), $new_sizes[ $size ]['file'], $this->attachment->get_main_url() );
			$replacer->replace( $old_url, $new_url );
		}
	}
}
