<?php
/**
 * Attachment edit class.
 */

 /**
	* Optml_Attachment_Edit
	*
	* @since      4.0.0
	*/
class Optml_Attachment_Edit {

	private static $error_message = '';

	const REPLACE_FILE_PAGE = 'optml-replace-file';

	public function init() {
		add_action( 'attachment_fields_to_edit', [ $this, 'add_attachment_fields' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'prepare_attachment_filename' ], 10, 2 );
		
		add_action( 'add_meta_boxes', [ $this, 'add_metabox' ], 10, 2 );
		add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		add_action('submenu_file', [$this, 'hide_sub_menu']);

		add_action( 'edit_attachment', [ $this, 'save_attachment_filename' ] );
		add_action( 'optml_after_attachment_url_replace', [$this, 'bust_cached_assets'], 10, 3 );
	}

	public function add_metabox( string $post_type, \WP_Post $post ) {
		if( $post_type !== 'attachment' ) {
			return;
		}

		$label = '<div class="optml-utils-header">';
		$label .= '<img src="' . OPTML_URL . 'assets/img/logo.svg" alt="' . __( 'Optimole logo', 'optimole' ) . '" />';
		$label .= '<span>' . __( 'Optimole utilities', 'optimole' ) . '</span>';
		$label .= '</div>';

		add_meta_box( 'optml_utilities', $label, [ $this, 'render_metabox' ], 'attachment', 'side' );
	}

	public function render_metabox( \WP_Post $post ) {
		$html = '<div class="optml-stack">';
		$html .= '<label>' . __( 'Replace attachment file:', 'optimole' ) . '</label>';
		$html .= '<a href="' . esc_url( add_query_arg( ['page' => self::REPLACE_FILE_PAGE, 'attachment_id' => $post->ID], admin_url( 'admin.php' ) ) ) . '" class="button button-primary optml-btn">';
		$html .= __( 'Replace file', 'optimole' );
		$html .= '</a>';
		$html .= '</div>';

		echo $html;
	}

	/**
	 * Add fields to attachment edit form
	 * 
	 * @param array $form_fields Array of form fields
	 * @param WP_Post $post The post object
	 * @return array Modified form fields
	 */
	public function add_attachment_fields( $form_fields, $post ) {
		$screen 								= get_current_screen();

		if( ! isset( $screen ) ) {
			return $form_fields;
		}
	
		if ( $screen->parent_base !== 'upload' ) return $form_fields;

		$file_path = get_attached_file( $post->ID );
		$file_name = basename( $file_path );
		$file_name_no_ext = pathinfo( $file_name, PATHINFO_FILENAME );
		$file_ext = pathinfo( $file_name, PATHINFO_EXTENSION );
		$attachment_metadata = wp_get_attachment_metadata( $post->ID );

		$is_scaled = strpos( $file_name_no_ext, '-scaled' ) !== false && isset( $attachment_metadata['original_image'] );

		$html = '<style>
			.compat-attachment-fields {
				width: 100% !important;
			}
				
			.optml-stack {
				display: flex;
				flex-direction: column;
				gap: 5px;
			}

			.optml-btn {
				background: #577BF9 !important;
				text-align: center;
			}

			.optml-btn:hover {
				background: #4564d2 !important;
			}

			.optml-utils-header {
				display: flex;
				align-items: center;
				gap: 8px;
			}

			.optml-utils-header img {
				width: 25px;
				height: 25px;
			}

			.optml-utils-header span {
				font-size: 13px;
				font-weight: 600;
				color: #1d2327;
			}

			.compat-field-optml_utilities {
				border-radius: 6px !important;
				border: 1px solid #577BF9;
				box-shadow: 0 0 0 1px #577BF9;
				overflow: hidden;
			}

			.compat-field-optml_utilities td, 
			.compat-field-optml_utilities th {
				padding: 10px 20px;
			}

			.optml-utils-section {
				margin: 15px 0;
			}

			.optml-mu-rename-input:focus-within {
					box-shadow: 0 0 0 1px #577BF9;
			}


			.optml-rename-media-container label {
				font-size: 13px;
				font-weight: 500;
				margin-bottom: 5px;
				display: block;
				color: #2c3338;
			}
				
			.optml-mu-rename-input {
				display: flex;
				align-items: center;
				border-radius: 6px;
				border: 1px solid #577BF9;
				overflow: hidden;
				background: #fff;
			}

			.optml-mu-rename-input #optml-new-filename {
				border: 0;
				border-radius: 0;
				flex-grow: 1;
				box-shadow: none;
				background: transparent;
			}

			.optml-mu-rename-input .optml-file-ext {
				min-height: 30px;
				padding: 0 10px;
				display: flex;
				align-items: center;
				font-weight: 600;
				background-color: #577BF9;
				color: #fff;
			}
		</style>';
		
		$html .= '<div class="optml-utils-section">';
		$html .= '<div class="optml-rename-media-container">';
		$html .= '<div>';

		$html .= '<label for="optml-new-filename">' . __( 'Change file name:', 'optimole' ) . '</label>';
		$html .= '<div class="optml-mu-rename-input">';
		$html .= '<input type="text" id="optml-new-filename" name="optml_new_filename" placeholder="' . esc_attr( $file_name_no_ext ) . '">';
		$html .= '<span class="optml-file-ext">.' . esc_html( $file_ext ) . '</span>';
		$html .= '</div>';

		if( $is_scaled ) {
			$html .= '<br>';
			$html .= '<p class="description">' . __( 'This is a scaled image. The original image will be renamed to the new name and the scaled image which is used in the media library will have the suffix -scaled. There is no need to add the -scaled suffix to the new name as it will be added automatically.', 'optimole' ) . '</p>';
		}

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		
		$html .= '<input type="hidden" name="optml_current_ext" value="' . esc_attr( $file_ext ) . '">';

		wp_nonce_field( 'optml_rename_media_nonce', 'optml_rename_nonce' );


		$label = '';
		$label .= '<div class="optml-utils-header">';
		$label .= '<img src="' . OPTML_URL . 'assets/img/logo.svg" alt="' . __( 'Optimole logo', 'optimole' ) . '" />';
		$label .= '<span>' . __( 'Rename attached file', 'optimole' ) . '</span>';
		$label .= '</div>';
		
		$form_fields['optml_utilities'] = [
			'label' => $label,
			'input' => 'html',
			'html' => $html,
		];

		return $form_fields;
	}

	public function add_admin_page() {
		add_submenu_page(
			'upload.php',
			__('Replace file', 'optimole'),
			__('Replace file', 'optimole'),
			'edit_posts',
			self::REPLACE_FILE_PAGE,
			[ $this, 'render_admin_page' ],
		);
	}

	public function render_admin_page() {
		echo 'Hello';
	}

	/**
	 * Hide the submenu item
	 */
	public function hide_sub_menu($submenu_file) {
		 global $plugin_page;

			if ( $plugin_page && $plugin_page === self::REPLACE_FILE_PAGE ) {
					$submenu_file = 'upload.php';
			}

			remove_submenu_page( 'upload.php', self::REPLACE_FILE_PAGE );

			return $submenu_file;
	}

	/**
	 * Prepare the new filename before saving
	 *
	 * @param array $post_data Array of post data
	 * @param array $attachment Array of attachment data
	 * @return array Modified post data
	 */
	public function prepare_attachment_filename( array $post_data, array $attachment ) {
		if( ! current_user_can( 'edit_post', $post_data['ID'] ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data['optml_rename_nonce'] ) || ! wp_verify_nonce( $post_data['optml_rename_nonce'], 'optml_rename_media_nonce' ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data['optml_new_filename'] ) || empty( $post_data['optml_new_filename'] ) ) {
			return $post_data;
		}

		// Store filename for later
		update_post_meta( $post_data['ID'], '_optml_pending_rename', $post_data['optml_new_filename'] );
		
		return $post_data;
	}

	/**
	 * Save the new filename when attachment is updated
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_attachment_filename( $post_id ) {
		$new_filename = get_post_meta( $post_id, '_optml_pending_rename', true );
		
		if( empty( $new_filename ) ) {
			return;
		}
		
		// Delete the meta so we don't rename again
		delete_post_meta( $post_id, '_optml_pending_rename' );
		
		$renamer = new Optml_Attachment_Rename( $post_id, $new_filename );
		$renamer->rename();
	}

	/**
	 * Bust cached assets
	 * 
	 * @param int $attachment_id The attachment ID
	 * @param string $new_guid The new GUID
	 * @param string $old_guid The old GUID 
	 */
	public function bust_cached_assets( $attachment_id, $new_guid, $old_guid ) {
		if (
			class_exists('\ThemeIsle\GutenbergBlocks\Server\Dashboard_Server') &&
			is_callable(['\ThemeIsle\GutenbergBlocks\Server\Dashboard_Server', 'regenerate_styles'])
		) {
			\ThemeIsle\GutenbergBlocks\Server\Dashboard_Server::regenerate_styles();
		}

		if ( did_action( 'elementor/loaded' ) ) {
			if ( class_exists( '\Elementor\Plugin' ) ) {
				\Elementor\Plugin::instance()->files_manager->clear_cache();
			}
		}
	}
}