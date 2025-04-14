<?php
/**
 * Attachment model class.
 */

/**
 * Optml_Attachment_Model
 *
 * @since      4.0.0
 */
class Optml_Attachment_Model {
	use Optml_Dam_Offload_Utils;

	/**
	 * The attachment ID.
	 *
	 * @var int
	 */
	private $attachment_id;

	/**
	 * The attachment file extension.
	 *
	 * @var string
	 */
	private $extension;

	/**
	 * The attachment file name including extension.
	 *
	 * @var string
	 */
	private $original_attached_file_name;

	/**
	 * The attachment file name without extension.
	 *
	 * @var string
	 */
	private $filename_no_ext;

	/**
	 * The original attached full file path (even if it's scaled).
	 *
	 * @var string
	 */
	private $origianal_attached_file_path;

	/**
	 * The original attached file directory path.
	 *
	 * @var string
	 */
	private $dir_path;

	/**
	 * Whether the attachment is scaled.
	 *
	 * @var bool
	 */
	private $is_scaled = false;

	/**
	 * The attached file main URL.
	 *
	 * @var string
	 */
	private $main_attachment_url;

	/**
	 * Whether the attachment is remote - offloaded, imported from DAM or legacy offloaded.
	 *
	 * @var bool
	 */
	private $is_remote_attachment;

	/**
	 * The attachment mime type.
	 *
	 * @var string
	 */
	private $mime_type;

	/**
	 * The attachment metadata.
	 *
	 * @var array
	 */
	public $attachment_metadata;

	/**
	 * Constructor.
	 *
	 * @param int $attachment_id Attachment ID.
	 *
	 * @return void
	 */
	public function __construct( int $attachment_id ) {
		$this->attachment_id = $attachment_id;
		$this->attachment_metadata = wp_get_attachment_metadata( $this->attachment_id );
		$this->mime_type = get_post_mime_type( $attachment_id );

		$this->main_attachment_url          = $this->is_image() ? wp_get_original_image_url( $attachment_id ) : wp_get_attachment_url( $attachment_id );
		$this->origianal_attached_file_path = $this->setup_original_attached_file();
		$this->dir_path = dirname( $this->origianal_attached_file_path );
		$this->is_scaled = isset( $this->attachment_metadata['original_image'] );

		$filename = $this->is_scaled && isset( $this->attachment_metadata['original_image'] ) ?
		$this->attachment_metadata['original_image'] :
		basename( $this->origianal_attached_file_path );

		$this->original_attached_file_name = $filename;

		$file_parts = pathinfo( $filename );

		$this->extension = isset( $file_parts['extension'] ) ? $file_parts['extension'] : '';
		$this->filename_no_ext = $file_parts['filename'];
		$this->is_remote_attachment = $this->is_dam_imported_image( $this->attachment_id ) ||
		$this->is_legacy_offloaded_attachment( $this->attachment_id ) ||
		$this->is_new_offloaded_attachment( $this->attachment_id );
	}

	/**
	 * Check if the attachment is scaled.
	 *
	 * @return bool
	 */
	public function is_scaled() {
		return $this->is_scaled;
	}

	/**
	 * Get attachment ID.
	 *
	 * @return int
	 */
	public function get_attachment_id() {
		return $this->attachment_id;
	}

	/**
	 * Get filename no extension.
	 *
	 * @return string
	 */
	public function get_filename_no_ext() {
		return $this->filename_no_ext;
	}

	/**
	 * Get filename with extension.
	 *
	 * @param bool $scaled Whether the filename is scaled.
	 *
	 * @return string
	 */
	public function get_filename_with_ext( $scaled = false ) {
		if ( $scaled ) {
			return sprintf( '%s-scaled.%s', $this->filename_no_ext, $this->extension );
		}

		return sprintf( '%s.%s', $this->filename_no_ext, $this->extension );
	}

	/**
	 * Get extension.
	 *
	 * @return string
	 */
	public function get_extension() {
		return $this->extension;
	}

	/**
	 * Get source file path.
	 *
	 * Returns the original attached file path, if the attachment is scaled, it will return the unscaled file path.
	 *
	 * @return string
	 */
	public function get_source_file_path() {
		return $this->origianal_attached_file_path;
	}

	/**
	 * Get dir path.
	 *
	 * @return string
	 */
	public function get_dir_path() {
		return $this->dir_path;
	}

	/**
	 * Get the attachment file main url.
	 *
	 * @return string
	 */
	public function get_main_url() {
		return $this->main_attachment_url;
	}

	/**
	 * Get attachment metadata.
	 *
	 * @return array
	 */
	public function get_attachment_metadata() {
		return $this->attachment_metadata;
	}

	/**
	 * Get all image sizes paths.
	 *
	 * @return array
	 */
	public function get_all_image_sizes_paths() {
		$paths = [];

		if ( ! isset( $this->attachment_metadata['sizes'] ) ) {
			return [];
		}

		foreach ( $this->attachment_metadata['sizes'] as $size => $size_data ) {
			$paths[ $size ] = $this->dir_path . '/' . $size_data['file'];
		}

		return $paths;
	}

	/**
	 * Get all image sizes URLs.
	 *
	 * @return array
	 */
	public function get_all_image_sizes_urls() {
		$attachment_metadata = $this->attachment_metadata;

		$links = [];

		if ( ! isset( $attachment_metadata['sizes'] ) ) {
			return [];
		}

		foreach ( $attachment_metadata['sizes'] as $size => $size_data ) {
			$links[ $size ] = str_replace( $this->original_attached_file_name, $size_data['file'], $this->get_main_url() );
		}

		return $links;
	}

	/**
	 * Get attached file.
	 *
	 * @return string
	 */
	private function setup_original_attached_file() {
		$attachment_metadata = wp_get_attachment_metadata( $this->attachment_id );
		$attached_file = get_attached_file( $this->attachment_id );
		$file_name = basename( $attached_file );

		if ( isset( $attachment_metadata['original_image'] ) ) {
			return str_replace( $file_name, $attachment_metadata['original_image'], $attached_file );
		}

		return $attached_file;
	}

	/**
	 * Get metadata 'file' key prefix path.
	 *
	 * @return string
	 */
	public function get_metadata_prefix_path() {
		if ( ! isset( $this->attachment_metadata['file'] ) ) {
			$attached_file = get_post_meta( $this->attachment_id, '_wp_attached_file', true );

			return dirname( $attached_file );
		}

		$file_path = $this->attachment_metadata['file'];

		return dirname( $file_path );
	}

	/**
	 * Check if can be renamed/replaced.
	 *
	 * @return bool
	 */
	public function can_be_renamed_or_replaced() {
		return ! $this->is_remote_attachment;
	}

	/**
	 * Get the attachment mime type.
	 */
	public function get_attachment_mimetype() {
		return $this->mime_type;
	}

	/**
	 * Check if the attachment is an image.
	 */
	public function is_image() {
		return strpos( $this->mime_type, 'image' ) !== false;
	}
}
