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
  /**
   * @var int
   */
  private $attachment_id;

  /**
   * @var string
   */
  private $extension;

  /**
   * @var string
   */
  private $filename;

  /**
   * @var string
   */
  private $filename_no_ext;

  /**
   * @var string
   */
  private $filepath;

  /**
   * @var string
   */
  private $dir_path;

  /**
   * @var bool
   */
  private $is_scaled = false;

  /**
   * @var string
   */
  private $guid;

  /**
   * Constructor.
   * 
   * @param int $attachment_id Attachment ID.
   * @return void
   */
  public function __construct( int $attachment_id ) {
    $this->attachment_id = $attachment_id;

    $this->setup_vars();
  }

  /**
   * Setup vars.
   * 
   * @return void
   */
  private function setup_vars() {
    $post = get_post( $this->attachment_id );
    $file_path = get_attached_file( $this->attachment_id );

    $this->guid = $post->guid;
    $this->filepath = $file_path;
    $this->dir_path = dirname( $file_path );
    
    $filename = basename( $file_path );
    $this->filename = $filename;
    
    $file_parts = pathinfo( $filename );
    $this->extension = isset( $file_parts['extension'] ) ? $file_parts['extension'] : '';
    $this->filename_no_ext = isset( $file_parts['filename'] ) ? $file_parts['filename'] : $filename;

    $attachment_metadata = wp_get_attachment_metadata( $this->attachment_id );

    $this->is_scaled = strpos( $this->filename_no_ext, '-scaled' ) !== false && isset( $attachment_metadata['original_image'] );
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
   * Get filename.
   * 
   * @return string
   */
  public function get_filename() {
    return $this->filename;
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
   * Get extension.
   * 
   * @return string
   */
  public function get_extension() {
    return $this->extension;
  }

  /**
   * Get filepath.
   * 
   * @return string
   */
  public function get_filepath() {
    return $this->filepath;
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
   * Get guid.
   * 
   * @return string
   */
  public function get_guid() {
    return $this->guid;
  }
}