<?php 

/**
 * Attachment rename class.
 */
class Optml_Attachment_Rename {
  /**
   * @var int
   */
  private $attachment_id;

  /**
   * @var string
   */
  private $new_filename;

  /**
   * @var Optml_Attachment_Model
   */
  private $attachment;

  /**
   * Constructor.
   * 
   * @param int $attachment_id Attachment ID.
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
    if( empty( $this->new_filename ) || sanitize_file_name( $this->new_filename ) === $this->attachment->get_filename_no_ext() ) {
      return;
    }
    
    // Get file path
    $file_path = get_attached_file( $this->attachment_id );
    $file_info = pathinfo( $file_path );
    $base_dir = trailingslashit( dirname( $file_path ) );
    
    // Create new file path
    $base_filename = $this->new_filename;
    
    // Check if file with this name already exists and get a unique name if needed
    if ( $this->attachment->is_scaled() ) {
      // First check uniqueness of the original (unscaled) filename
      $original_name = $base_filename . '.' . $file_info['extension'];
      $unique_original = wp_unique_filename( $base_dir, $original_name );
      
      // Update base_filename from the unique original name
      $base_filename = pathinfo( $unique_original, PATHINFO_FILENAME );
      
      // Create the scaled filename - no need to check uniqueness again
      $new_file_name = $base_filename . '-scaled.' . $file_info['extension'];
      $unique_filename = $new_file_name;
    } else {
      $new_file_name = $base_filename . '.' . $file_info['extension'];
      $unique_filename = wp_unique_filename( $base_dir, $new_file_name );
    }
    
    $new_file_path = $base_dir . $unique_filename;
    
    // Update the new_filename property to match the unique filename (without extension)
    $this->new_filename = pathinfo( $unique_filename, PATHINFO_FILENAME );
    if ( $this->attachment->is_scaled() ) {
      $this->new_filename = str_replace( '-scaled', '', $this->new_filename );
    }
    
    $this->init_filesystem();
    global $wp_filesystem;
    
    // Check if file exists
    if ( ! $wp_filesystem->exists( $file_path ) ) {
      return;
    }
    
    // Rename the file
    $renamed = $wp_filesystem->move( $file_path, $new_file_path, true );
    if ( ! $renamed ) {
      return;
    }
    
    // Update attachment metadata
    $this->update_attachment_metadata( $file_path, $new_file_path );

    $replacer = new Optml_Attachment_Db_Renamer();

		$count = $replacer->replace( $this->attachment->get_guid(), $this->get_new_guid() );

    if( $count > 0 ) {
      /**
       * Action triggered after the attachment file is renamed.
       * 
       * @param int $attachment_id Attachment ID.
       * @param string $new_guid New GUID (new image URL).
       * @param string $old_guid Old GUID (old image URL).
       */
      do_action( 'optml_after_attachment_url_replace', $this->attachment_id, $this->get_new_guid(), $this->attachment->get_guid() );
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
    $this->init_filesystem();
    global $wp_filesystem;

    // Update the attachment file path
    update_attached_file( $this->attachment_id, $new_path );
    
    // Get current attachment metadata
    $metadata = wp_get_attachment_metadata( $this->attachment_id );
    
    if ( ! empty( $metadata ) ) {
      $old_file = basename( $old_path );
      $new_file = basename( $new_path );
      
      // Handle scaled images
      if ( $this->attachment->is_scaled() && isset( $metadata['original_image'] ) ) {
        $old_original = $metadata['original_image'];
        $old_original_path = str_replace( basename( $old_path ), $old_original, $old_path );
        $new_original = $this->new_filename . '.' . $this->attachment->get_extension();
        $new_original_path = str_replace( basename( $old_path ), $new_original, $old_path );
        
        if ( $wp_filesystem->exists( $old_original_path ) ) {
          $wp_filesystem->move( $old_original_path, $new_original_path, true );
        }
        
        $metadata['original_image'] = $new_original;
        // Keep the -scaled suffix for the main file
        $metadata['file'] = str_replace( $old_file, $this->new_filename . '-scaled.' . $this->attachment->get_extension(), $metadata['file'] );
      } else {
        $metadata['file'] = str_replace( $old_file, $new_file, $metadata['file'] );
      }
      
      // Update thumbnails paths if they exist
      if ( isset( $metadata['sizes'] ) && is_array( $metadata['sizes'] ) ) {
        foreach ( $metadata['sizes'] as $size => $size_info ) {
          $old_thumb_filename = $size_info['file'];
          $file_info = pathinfo( $old_thumb_filename );
          $new_thumb_filename = $this->new_filename . '-' . $size_info['width'] . 'x' . $size_info['height'] . '.' . $file_info['extension'];
          
          // Create new thumbnail path
          $old_thumb_path = str_replace( basename( $old_path ), $old_thumb_filename, $old_path );
          $new_thumb_path = str_replace( basename( $old_path ), $new_thumb_filename, $old_path );
          
          // Rename the thumbnail file
          if ( $wp_filesystem->exists( $old_thumb_path ) ) {
            $wp_filesystem->move( $old_thumb_path, $new_thumb_path, true );
          }
          
          // Update metadata for this size
          $metadata['sizes'][$size]['file'] = $new_thumb_filename;
        }
      }
      
      // Save updated metadata
      wp_update_attachment_metadata( $this->attachment_id, $metadata );
    }
    
    // Update post GUID and post_name.
    $new_guid = $this->get_new_guid();
    
    global $wpdb;
    
    $wpdb->update(
      $wpdb->posts,
      ['guid' => $new_guid, 'post_name' => $this->new_filename],
      ['ID' => $this->attachment_id]
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
  private function get_new_guid() {
    $guid = $this->get_attachment_guid();
    return str_replace( 
      basename( $guid ), 
      $this->new_filename . '.' . $this->attachment->get_extension(), 
      $guid 
    );
  }
}
