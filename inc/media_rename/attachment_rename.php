<?php

/**
 * Attachment rename class.
 */
class Optml_Attachment_Rename {
	/**
	 * The attachment ID.
	 *
	 * @var int
	 */
	private $attachment_id;

	/**
	 * The new filename.
	 *
	 * @var string
	 */
	private $new_filename;

	/**
	 * The attachment model.
	 *
	 * @var Optml_Attachment_Model
	 */
	private $attachment;

	/**
	 * Constructor.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $new_filename New filename.
	 * @return void
	 */
	public function __construct( int $attachment_id, string $new_filename ) {
		$this->attachment_id = $attachment_id;
		$this->attachment = new Optml_Attachment_Model( $attachment_id );
		$this->new_filename = $new_filename;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
	}

	/**
	 * Rename the attachment
	 *
	 * @return bool|WP_Error
	 */
	public function rename() {
		if ( empty( $this->new_filename ) || sanitize_file_name( $this->new_filename ) === $this->attachment->get_filename_no_ext() ) {
			return true;
		}

		$extension = $this->attachment->get_extension();

		$base_dir = trailingslashit( $this->attachment->get_dir_path() );
		$file_path = $this->attachment->get_source_file_path();

		$new_file_with_ext = sprintf( '%s.%s', $this->new_filename, $extension );
		$new_unique_filename = wp_unique_filename( $base_dir, $new_file_with_ext );
		$new_file_path = $base_dir . $new_unique_filename;

		global $wp_filesystem;

		// Bail if original file doesn't exist.
		if ( ! $wp_filesystem->exists( $file_path ) ) {
			return new WP_Error( 'optml_attachment_file_not_found', __( 'Error renaming file.', 'optimole-wp' ) );
		}

		// Rename the file (move) - moves the original, not the scaled image.
		$moved = $wp_filesystem->move( $file_path, $new_file_path );
		if ( ! $moved ) {
			return new WP_Error( 'optml_attachment_rename_failed', __( 'Error renaming file.', 'optimole-wp' ) );
		}

		$wp_filesystem->chmod( $new_file_path, FS_CHMOD_FILE );

		// Move the scaled image if it exists.
		if ( $this->attachment->is_scaled() ) {
			$new_unique_filename_no_ext = pathinfo( $new_unique_filename, PATHINFO_FILENAME );

			$scaled_old_file_path = sprintf( '%s/%s-scaled.%s', $this->attachment->get_dir_path(), $this->attachment->get_filename_no_ext(), $extension );
			$scaled_new_file_with_ext = sprintf( '%s-scaled.%s', $new_unique_filename_no_ext, $extension );

			$new_scaled_file_path = $base_dir . $scaled_new_file_with_ext;
			// Move the scaled image. We also override any leftover scaled files.
			$wp_filesystem->move( $scaled_old_file_path, $new_scaled_file_path, true );
		}

		// Update attachment metadata
		$metadata_update = $this->update_attachment_metadata( $new_file_path );

		if ( $metadata_update === false ) {
			return new WP_Error( 'optml_attachment_metadata_update_failed', __( 'Error renaming file.', 'optimole-wp' ) );
		}

		try {
			$skip_sizes = ! $this->attachment->is_image();

			$replacer = new Optml_Attachment_Db_Renamer( $skip_sizes );
			$old_url = $this->attachment->get_main_url();
			$new_url = $this->get_new_url( $new_unique_filename );

			$count = $replacer->replace( $old_url, $new_url );

			if ( $count > 0 ) {
				/**
				 * Action triggered after the attachment file is renamed.
				 *
				 * @param int $attachment_id Attachment ID.
				 * @param string $old_url Old attachment URL.
				 * @param string $new_url New attachment URL.
				 */
				do_action( 'optml_after_attachment_url_replace', $this->attachment_id, $old_url, $new_url );
			}
		} catch ( Exception $e ) {
			return new WP_Error( 'optml_attachment_url_replace_failed', __( 'Error renaming file.', 'optimole-wp' ) );
		}

		do_action( 'optml_attachment_renamed', $this->attachment_id );

		return true;
	}

	/**
	 * Update attachment metadata.
	 *
	 * @param string $new_path New path.
	 * @return bool
	 */
	private function update_attachment_metadata( $new_path ) {
		global $wp_filesystem;

		$new_file_name_no_ext = pathinfo( $new_path, PATHINFO_FILENAME );
		$extension = $this->attachment->get_extension();

		if ( $this->attachment->is_scaled() ) {
			$new_path = sprintf( '%s/%s-scaled.%s', $this->attachment->get_metadata_prefix_path(), $new_file_name_no_ext, $extension );
		}

		$attached_update = update_attached_file( $this->attachment_id, $new_path );

		if ( ! $attached_update ) {
			return false;
		}

		// We should bail for non-image attachments here.
		// No need for additional metadata update.
		if ( ! $this->attachment->is_image() ) {
			return true;
		}

		// Get current attachment metadata
		$metadata = $this->attachment->get_attachment_metadata();

		if ( empty( $metadata ) ) {
			return false;
		}

		// Update file path in metadata
		$original_image = sprintf( '%s.%s', $new_file_name_no_ext, $extension );
		$meta_file = $original_image;

		if ( $this->attachment->is_scaled() ) {
			$meta_file = sprintf( '%s-scaled.%s', $new_file_name_no_ext, $extension );
			$metadata['original_image'] = $original_image;
		}

		if ( isset( $metadata['file'] ) ) {
			$metadata['file'] = sprintf( '%s/%s', $this->attachment->get_metadata_prefix_path(), $meta_file );
		}

		// Update image sizes if they exist
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			$already_moved_paths = [];

			foreach ( $metadata['sizes'] as $size => $size_data ) {
				if ( ! isset( $size_data['file'] ) ) {
					continue;
				}
				$size_suffix = $size_data['width'] . 'x' . $size_data['height'];
				$new_size_file = sprintf( '%s-%s.%s', $new_file_name_no_ext, $size_suffix, $extension );

				$old_size_file_path = sprintf( '%s/%s', $this->attachment->get_dir_path(), $size_data['file'] );
				$new_size_file_path = sprintf( '%s/%s', $this->attachment->get_dir_path(), $new_size_file );

				$move = $wp_filesystem->move( $old_size_file_path, $new_size_file_path );

				if ( $move || in_array( $old_size_file_path, $already_moved_paths, true ) ) {
					$already_moved_paths[] = $old_size_file_path;
					$metadata['sizes'][ $size ]['file'] = $new_size_file;
					$already_moved_paths = array_unique( $already_moved_paths );
				}
			}
		}

		$metadata_update = wp_update_attachment_metadata( $this->attachment_id, $metadata );

		if ( ! $metadata_update ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the new main attached file URL.
	 *
	 * @return string
	 */
	private function get_new_url( $filename ) {
		$url = $this->attachment->get_main_url();

		return str_replace(
			basename( $url ),
			$filename,
			$url
		);
	}
}
