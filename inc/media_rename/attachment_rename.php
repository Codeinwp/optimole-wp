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
	}

	/**
	 * Rename the attachment
	 *
	 * @return bool|WP_Error
	 */
	public function rename() {
		if ( empty( $this->new_filename ) || sanitize_file_name( $this->new_filename ) === $this->attachment->get_filename_no_ext() ) {
			return;
		}

		$extension = $this->attachment->get_extension();

		$base_dir = trailingslashit( $this->attachment->get_dir_path() );
		$file_path = $this->attachment->get_source_file_path();

		$new_file_with_ext = sprintf( '%s.%s', $this->new_filename, $extension );
		$new_unique_filename = wp_unique_filename( $base_dir, $new_file_with_ext );
		$new_file_path = $base_dir . $new_unique_filename;

		$this->init_filesystem();
		global $wp_filesystem;

		// Bail if original file doesn't exist.
		if ( ! $wp_filesystem->exists( $file_path ) ) {
			return;
		}

		// Rename the file (move) - moves the original, not the scaled image.
		$renamed = $wp_filesystem->move( $file_path, $new_file_path );
		if ( ! $renamed ) {
			return;
		}

		// Move the scaled image if it exists.
		if ( $this->attachment->is_scaled() ) {
			$new_unique_filename_no_ext = pathinfo( $new_unique_filename, PATHINFO_FILENAME );

			$scaled_old_file_path = sprintf( '%s/%s-scaled.%s', $this->attachment->get_dir_path(), $this->attachment->get_filename_no_ext(), $extension );
			$scaled_new_file_with_ext = sprintf( '%s-scaled.%s', $new_unique_filename_no_ext, $extension );

			$new_scaled_file_path = $base_dir . $scaled_new_file_with_ext;
			// Move the scaled image. We also override any leftover scaled files.
			$move = $wp_filesystem->move( $scaled_old_file_path, $new_scaled_file_path, true );
		}

		// Update attachment metadata
		$this->update_attachment_metadata( $file_path, $new_file_path );

		$replacer = new Optml_Attachment_Db_Renamer();

		$count = $replacer->replace( $this->attachment->get_guid(), $this->get_new_guid( $new_unique_filename ) );

		if ( $count > 0 ) {
			/**
			 * Action triggered after the attachment file is renamed.
			 *
			 * @param int $attachment_id Attachment ID.
			 * @param string $new_guid New GUID (new image URL).
			 * @param string $old_guid Old GUID (old image URL).
			 */
			do_action( 'optml_after_attachment_url_replace', $this->attachment_id, $this->get_new_guid( $new_unique_filename ), $this->attachment->get_guid() );
		}

		return true;
	}

	/**
	 * Update attachment metadata.
	 *
	 * @param string $old_path Old path.
	 * @param string $new_path New path.
	 * @return void
	 */
	private function update_attachment_metadata( $old_path, $new_path ) {
		global $wp_filesystem;

		$new_file_name_no_ext = pathinfo( $new_path, PATHINFO_FILENAME );
		$extension = $this->attachment->get_extension();

		if ( $this->attachment->is_scaled() ) {
			$new_path = sprintf( '%s/%s-scaled.%s', $this->attachment->get_metadata_prefix_path(), $new_file_name_no_ext, $extension );
		}

		$attached_update = update_attached_file( $this->attachment_id, $new_path );

		if ( ! $attached_update ) {
			return;
		}

		// Get current attachment metadata
		$metadata = $this->attachment->get_attachment_metadata();

		if ( empty( $metadata ) ) {
			return;
		}

		// Update file path in metadata
		$original_image = sprintf( '%s.%s', $new_file_name_no_ext, $extension );
		$meta_file = $original_image; 
    
    if( $this->attachment->is_scaled() ) {
      $meta_file = sprintf( '%s-scaled.%s', $new_file_name_no_ext, $extension );
      $metadata['original_image'] = $original_image;
    }

		$metadata['file'] = sprintf( '%s/%s', $this->attachment->get_metadata_prefix_path(), $meta_file );
    

		// Update image sizes if they exist
		if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
			foreach ( $metadata['sizes'] as $size => $size_data ) {
				if ( ! isset( $size_data['file'] ) ) {
					continue;
				}
				$size_suffix = $size_data['width'] . 'x' . $size_data['height'];
				$new_size_file = sprintf( '%s-%s.%s', $new_file_name_no_ext, $size_suffix, $extension );

				$old_size_file_path = sprintf( '%s/%s', $this->attachment->get_dir_path(), $size_data['file'] );
				$new_size_file_path = sprintf( '%s/%s', $this->attachment->get_dir_path(), $new_size_file );

				$move = $wp_filesystem->move( $old_size_file_path, $new_size_file_path );

				if ( $move ) {
					$metadata['sizes'][ $size ]['file'] = $new_size_file;
				}
			}
		}

		wp_update_attachment_metadata( $this->attachment_id, $metadata );

		global $wpdb;

		$wpdb->update(
			$wpdb->posts,
			[
				'guid' => $this->get_new_guid( $original_image ),
			],
			[
				'ID' => $this->attachment_id,
			]
		);
	}

	/**
	 * Initialize filesystem.
	 *
	 * @return void
	 */
	private function init_filesystem() {
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();
	}

	/**
	 * Get attachment guid.
	 *
	 * @return string
	 */
	private function get_attachment_guid() {
		$post = get_post( $this->attachment_id );
		return $post->guid;
	}

	/**
	 * Get the new guid (main image URL).
	 *
	 * @return string
	 */
	private function get_new_guid( $filename ) {
		$guid = $this->attachment->get_guid();

		return str_replace(
			basename( $guid ),
			$filename,
			$guid
		);
	}
}
