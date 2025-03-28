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
	/**
	 * Initialize the attachment edit class.
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'attachment_fields_to_edit', [ $this, 'add_attachment_fields' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'prepare_attachment_filename' ], 10, 2 );

		add_action( 'edit_attachment', [ $this, 'save_attachment_filename' ] );
		add_action( 'optml_after_attachment_url_replace', [ $this, 'bust_cached_assets' ], 10, 3 );
		add_action( 'wp_ajax_optml_replace_file', [ $this, 'replace_file' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $hook The hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'post.php' ) {
			return;
		}

		$id = sanitize_text_field( $_GET['post'] );

		if ( ! $id ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $id ) ) {
			return;
		}

		if ( get_post_type( $id ) !== 'attachment' ) {
			return;
		}

		$max_file_size = wp_max_upload_size();
		// translators: %s is the max file size in MB.
		$max_file_size_error = sprintf( __( 'File size is too large. Max file size is %sMB', 'optimole' ), $max_file_size / 1024 / 1024 );

		wp_enqueue_style( 'optml-attachment-edit', OPTML_URL . 'assets/css/single-attachment.css', [], OPTML_VERSION );

		wp_register_script( 'optml-attachment-edit', OPTML_URL . 'assets/js/single-attachment.js', [ 'jquery' ], OPTML_VERSION, true );
		wp_localize_script(
			'optml-attachment-edit',
			'OMAttachmentEdit',
			[
				'ajaxURL' => admin_url( 'admin-ajax.php' ),
				'maxFileSize' => $max_file_size,
				'attachmentId' => $id,
				'i18n' => [
					'maxFileSizeError' => $max_file_size_error,
					'replaceFileError' => __( 'Error replacing file', 'optimole' ),
				],
			]
		);
		wp_enqueue_script( 'optml-attachment-edit' );
	}

	/**
	 * Add fields to attachment edit form.
	 *
	 * @param array   $form_fields Array of form fields.
	 * @param WP_Post $post The post object.
	 * @return array Modified form fields.
	 */
	public function add_attachment_fields( $form_fields, $post ) {
		$screen = get_current_screen();

		$attachment = new Optml_Attachment_Model( $post->ID );

		if ( ! isset( $screen ) ) {
			return $form_fields;
		}

		if ( $screen->parent_base !== 'upload' ) {
			return $form_fields;
		}

		$form_fields['optml_rename_file'] = [
			'label' => __( 'Rename attached file', 'optimole' ),
			'input' => 'html',
			'html' => $this->get_rename_field( $attachment ),
		];

		$form_fields['optml_replace_file'] = [
			'label' => __( 'Replace file', 'optimole' ),
			'input' => 'html',
			'html' => $this->get_replace_field( $attachment ),
		];

		$form_fields['optml_footer_row'] = [
			'label' => '',
			'input' => 'html',
			'html' => $this->get_footer_html(),
		];

		$form_fields['optml_spacer_row'] = [
			'label' => '',
			'input' => 'html',
			'html' => '<div></div>',
		];

		return $form_fields;
	}

	/**
	 * Get the rename field HTML.
	 *
	 * @param \Optml_Attachment_Model $attachment The attachment model.
	 *
	 * @return string The HTML.
	 */
	private function get_rename_field( \Optml_Attachment_Model $attachment ) {
		$file_name_no_ext = $attachment->get_filename_no_ext();
		$file_ext = $attachment->get_extension();

		$html = '';

		$html .= '<div class="optml-rename-media-container">';

		$html .= '<div class="optml-rename-input">';
		$html .= '<input type="text" id="optml_rename_file" name="optml_rename_file" placeholder="' . esc_attr( $file_name_no_ext ) . '">';
		$html .= '<span class="optml-file-ext">.' . esc_html( $file_ext ) . '</span>';
		$html .= '</div>';
		$html .= '</div>';

		$html .= '<input type="hidden" name="optml_current_ext" value="' . esc_attr( $file_ext ) . '">';

		wp_nonce_field( 'optml_rename_media_nonce', 'optml_rename_nonce' );

		return $html;
	}

	/**
	 * Get the replace field HTML.
	 *
	 * @param \Optml_Attachment_Model $attachment The attachment model.
	 *
	 * @return string The HTML.
	 */
	private function get_replace_field( \Optml_Attachment_Model $attachment ) {
		$file_ext = $attachment->get_extension();

		$html = '<div class="optml-replace-section">';

		$html .= '<p class="optml-description">' . __( 'This will replace the current file with the new one. The new file will be uploaded to the media library and the old file will be deleted.', 'optimole' ) . '</p>';

		$html .= '<div class="optml-replace-input">';

		$html .= '<label for="optml-replace-file-field" id="optml-file-drop-area">';
		$html .= '<span class="label-text">' . __( 'Click to select a file or drag & drop here', 'optimole' ) . ' (.' . $file_ext . ')</span>';
		$html .= '<div class="optml-replace-file-preview"></div>';
		$html .= '</label>';

		$html .= '<input type="file" class="hidden" id="optml-replace-file-field" name="optml-replace-file-field" accept=".' . esc_attr( $file_ext ) . '">';

		$html .= '<div class="optml-replace-file-actions">';
		$html .= '<button disabled type="button" class="button optml-btn primary" id="optml-replace-file-btn">' . __( 'Replace file', 'optimole' ) . '</button>';
		$html .= '<button disabled type="button" class="button optml-btn destructive" id="optml-replace-clear-btn">' . __( 'Clear', 'optimole' ) . '</button>';
		$html .= '</div>';

		$html .= '<div class="optml-replace-file-error hidden"></div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Get the footer HTML.
	 *
	 * @return string The HTML.
	 */
	private function get_footer_html() {
		$html = '';
		$html .= '<div class="optml-logo-contianer">';
		$html .= '<img src="' . OPTML_URL . 'assets/img/logo.svg" alt="' . __( 'Optimole logo', 'optimole' ) . '"/>';
		// translators: %s is the 'Optimole'.
		$html .= '<span>' . sprintf( __( 'Powered by %s', 'optimole' ), '<strong>Optimole</strong>' ) . '</span>';
		$html .= '</div>';

		return $html;
	}


	/**
	 * Prepare the new filename before saving.
	 *
	 * @param array $post_data Array of post data.
	 * @param array $attachment Array of attachment data.
	 * @return array Modified post data.
	 */
	public function prepare_attachment_filename( array $post_data, array $attachment ) {
		if ( ! current_user_can( 'edit_post', $post_data['ID'] ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data['optml_rename_nonce'] ) || ! wp_verify_nonce( $post_data['optml_rename_nonce'], 'optml_rename_media_nonce' ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data['optml_rename_file'] ) || empty( $post_data['optml_rename_file'] ) ) {
			return $post_data;
		}

		/**
		 * Store filename for later, it will be used to rename the attachment.
		 *
		 * We do it this way because we don't want to rename the attachment immediately, during the attachment fields update as it will break things.
		 *
		 * @see Optml_Attachment_Edit::save_attachment_filename()
		 */
		update_post_meta( $post_data['ID'], '_optml_pending_rename', $post_data['optml_rename_file'] );

		return $post_data;
	}

	/**
	 * Save the new filename when attachment is updated
	 *
	 * @param int $post_id The post ID.
	 */
	public function save_attachment_filename( $post_id ) {
		$new_filename = get_post_meta( $post_id, '_optml_pending_rename', true );

		if ( empty( $new_filename ) ) {
			return;
		}

		// Delete the meta so we don't rename again
		delete_post_meta( $post_id, '_optml_pending_rename' );

		$renamer = new Optml_Attachment_Rename( $post_id, $new_filename );
		$renamer->rename();
	}

	/**
	 * Replace the file
	 */
	public function replace_file() {
		$id = sanitize_text_field( $_POST['attachment_id'] );

		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( __( 'You are not allowed to replace this file', 'optimole' ) );
		}

		if ( ! isset( $_FILES['file'] ) ) {
			wp_send_json_error( __( 'No file uploaded', 'optimole' ) );
		}

		$replacer = new Optml_Attachment_Replace( $id, $_FILES['file'] );

		$replaced = $replacer->replace();

		$is_error = is_wp_error( $replaced );

		$response = [
			'success' => ! $is_error,
			'message' => $is_error ? $replaced->get_error_message() : __( 'File replaced successfully', 'optimole' ),
		];

		wp_send_json( $response );
	}

	/**
	 * Bust cached assets
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $new_guid The new GUID.
	 * @param string $old_guid The old GUID.
	 */
	public function bust_cached_assets( $attachment_id, $new_guid, $old_guid ) {
		if (
			class_exists( '\ThemeIsle\GutenbergBlocks\Server\Dashboard_Server' ) &&
			is_callable( [ '\ThemeIsle\GutenbergBlocks\Server\Dashboard_Server', 'regenerate_styles' ] )
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
