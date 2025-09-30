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
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_attachment_fields' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'prepare_attachment_filename' ], 10, 2 );

		add_action( 'edit_attachment', [ $this, 'save_attachment_filename' ] );
		add_action( 'optml_after_attachment_url_replace', [ $this, 'bust_cache_on_rename' ], 10, 3 );
		add_action( 'optml_attachment_replaced', [ $this, 'bust_cache_on_replace' ] );
		add_action( 'wp_ajax_optml_replace_file', [ $this, 'replace_file' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'media_row_actions', [ $this, 'add_replace_rename_action' ], 10, 2 );
	}

	/**
	 * Add Replace or Rename action in media library list view.
	 *
	 * @param string[] $actions Array of row action links.
	 * @param WP_Post  $post The post object.
	 * @return string[]
	 */
	public function add_replace_rename_action( $actions, $post ) {
		if ( get_post_type( $post->ID ) !== 'attachment' ) {
			return $actions;
		}

		$edit_url = admin_url( 'post.php?post=' . $post->ID . '&action=edit' );
		$actions['replace_rename'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( $edit_url ),
			esc_attr__( 'Replace or Rename', 'optimole-wp' ),
			esc_html__( 'Replace or Rename', 'optimole-wp' )
		);

		return $actions;
	}

	/**
	 * Enqueue scripts.
	 *
	 * @param string $hook The hook.
	 */
	public function enqueue_scripts( $hook ) {
		if ( $hook !== 'post.php' && $hook !== 'upload.php' ) {
			return;
		}

		if ( $hook === 'post.php' ) {
			$id = (int) sanitize_text_field( $_GET['post'] );

			if ( ! $id ) {
				return;
			}

			if ( ! current_user_can( 'edit_post', $id ) ) {
				return;
			}

			if ( get_post_type( $id ) !== 'attachment' ) {
				return;
			}

			$mime_type = get_post_mime_type( $id );

			$max_file_size = wp_max_upload_size();
			// translators: %s is the max file size in MB.
			$max_file_size_error = sprintf( __( 'File size is too large. Max file size is %sMB', 'optimole-wp' ), $max_file_size / 1024 / 1024 );

			wp_enqueue_style( 'optml-attachment-edit', OPTML_URL . 'assets/css/single-attachment.css', [], OPTML_VERSION );

			wp_register_script( 'optml-attachment-edit', OPTML_URL . 'assets/js/single-attachment.js', [ 'jquery' ], OPTML_VERSION, true );
			wp_localize_script(
				'optml-attachment-edit',
				'OMAttachmentEdit',
				[
					'ajaxURL'      => admin_url( 'admin-ajax.php' ),
					'maxFileSize'  => $max_file_size,
					'attachmentId' => $id,
					'mimeType'     => $mime_type,
					'i18n'         => [
						'maxFileSizeError' => $max_file_size_error,
						'replaceFileError' => __( 'Error replacing file', 'optimole-wp' ),
					],
				]
			);
			wp_enqueue_script( 'optml-attachment-edit' );
		} elseif ( $hook === 'upload.php' ) {
			wp_enqueue_script(
				'optml-modal-attachment',
				OPTML_URL . 'assets/js/modal-attachment.js',
				[ 'jquery', 'media-views', 'media-models' ],
				OPTML_VERSION,
				true
			);

			wp_localize_script(
				'optml-modal-attachment',
				'OptimoleModalAttachment',
				[
					'editPostURL' => admin_url( 'post.php' ),
					'i18n' => [
						'replaceOrRename' => __( 'Replace or Rename', 'optimole-wp' ),
					],
				]
			);
		}
	}

	/**
	 * Add fields to attachment edit form.
	 *
	 * @param array   $form_fields Array of form fields.
	 * @param WP_Post $post The post object.
	 *
	 * @return array Modified form fields.
	 */
	public function add_attachment_fields( $form_fields, $post ) {
		if ( ! function_exists( 'get_current_screen' ) ) {
			return $form_fields;
		}

		$screen = get_current_screen();

		$attachment = new Optml_Attachment_Model( $post->ID );

		if ( ! $attachment->can_be_renamed_or_replaced() ) {
			return $form_fields;
		}

		if ( ! isset( $screen ) ) {
			return $form_fields;
		}

		if ( $screen->parent_base !== 'upload' ) {
			return $form_fields;
		}

		$form_fields['optml_rename_file'] = [
			'label' => __( 'Rename attached file', 'optimole-wp' ),
			'input' => 'html',
			'html'  => $this->get_rename_field( $attachment ),
		];

		$form_fields['optml_replace_file'] = [
			'label' => __( 'Replace file', 'optimole-wp' ),
			'input' => 'html',
			'html'  => $this->get_replace_field( $attachment ),
		];

		$form_fields['optml_footer_row'] = [
			'label' => '',
			'input' => 'html',
			'html'  => $this->get_footer_html(),
		];

		$form_fields['optml_spacer_row'] = [
			'label' => '',
			'input' => 'html',
			'html'  => '<div></div>',
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
		$file_ext         = $attachment->get_extension();

		$html = '';

		$html .= '<div class="optml-rename-media-container">';

		$html .= '<div class="optml-rename-input">';
		$html .= '<input type="text" id="optml_rename_file" name="optml_rename_file" placeholder="' . esc_attr( $file_name_no_ext ) . '">';
		$html .= '<span class="optml-file-ext">.' . esc_html( $file_ext ) . '</span>';
		$html .= '</div>';

		$html .= '<button type="button" disabled class="button optml-btn primary" id="optml-rename-file-btn">' . __( 'Rename', 'optimole-wp' ) . '</button>';
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
		$file_ext = in_array( $file_ext, [ 'jpg', 'jpeg' ], true ) ? [ '.jpg', '.jpeg' ] : [ '.' . $file_ext ];
		$html     = '<div class="optml-replace-section">';
		$html     .= '<div class="optml-replace-input">';
		$html     .= '<label for="optml-replace-file-field" id="optml-file-drop-area">';
		$html     .= '<span class="label-text">' . __( 'Click to select a file or drag & drop here', 'optimole-wp' ) . ' (' . implode( ',', $file_ext ) . ')</span>';
		$html     .= '<div class="optml-replace-file-preview"></div>';
		$html     .= '</label>';

		$html .= '<input type="file" class="hidden" id="optml-replace-file-field" name="optml-replace-file-field" accept="' . implode( ',', $file_ext ) . '">';

		$html .= '<div class="optml-replace-file-actions">';
		$html .= '<button disabled type="button" class="button optml-btn primary" id="optml-replace-file-btn">' . __( 'Replace file', 'optimole-wp' ) . '</button>';
		$html .= '<button disabled type="button" class="button optml-btn destructive" id="optml-replace-clear-btn">' . __( 'Clear', 'optimole-wp' ) . '</button>';
		$html .= $this->get_svg_loader();
		$html .= '<p class="optml-description">' . __( 'This will replace the current file with the new one. This action cannot be undone.', 'optimole-wp' ) . '</p>';
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
		$html .= '<img src="' . OPTML_URL . 'assets/img/logo.svg" alt="' . __( 'Optimole logo', 'optimole-wp' ) . '"/>';
		// translators: %s is the 'Optimole'.
		$html .= '<span>' . sprintf( __( 'Powered by %s', 'optimole-wp' ), '<strong>Optimole</strong>' ) . '</span>';
		$html .= '</div>';

		return $html;
	}


	/**
	 * Prepare the new filename before saving.
	 *
	 * @param array $post_data Array of post data.
	 * @param array $attachment Array of attachment data.
	 *
	 * @return array Modified post data.
	 */
	public function prepare_attachment_filename( array $post_data, array $attachment ) {
		if ( ! current_user_can( 'edit_post', $post_data['ID'] ) ) {
			return $post_data;
		}

		if ( ! isset( $post_data['post_type'] ) || $post_data['post_type'] !== 'attachment' ) {
			return $post_data;
		}

		if ( ! isset( $post_data['optml_rename_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( $post_data['optml_rename_nonce'] ), 'optml_rename_media_nonce' ) ) {
			return $post_data;
		}

		$new_name = sanitize_text_field( $post_data['optml_rename_file'] );

		$new_name = trim( $new_name );

		if ( empty( $new_name ) ) {
			return $post_data;
		}

		if ( strlen( $new_name ) > 100 ) {
			return $post_data;
		}

		/**
		 * Store filename for later, it will be used to rename the attachment.
		 *
		 * We do it this way because we don't want to rename the attachment immediately, during the attachment fields update as it will break things.
		 *
		 * @see Optml_Attachment_Edit::save_attachment_filename()
		 */
		update_post_meta( $post_data['ID'], '_optml_pending_rename', $new_name );

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
		$status  = $renamer->rename();

		if ( is_wp_error( $status ) ) {
			wp_die( $status->get_error_message() );
		}
	}

	/**
	 * Replace the file
	 */
	public function replace_file() {
		$id = (int) sanitize_text_field( $_POST['attachment_id'] );

		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( __( 'You are not allowed to replace this file', 'optimole-wp' ) );
		}

		if ( ! isset( $_FILES['file'] ) ) {
			wp_send_json_error( __( 'No file uploaded', 'optimole-wp' ) );
		}

		$replacer = new Optml_Attachment_Replace( $id, $_FILES['file'] );

		$replaced = $replacer->replace();

		$is_error = is_wp_error( $replaced );

		$response = [
			'success' => ! $is_error,
			'message' => $is_error ? $replaced->get_error_message() : __( 'File replaced successfully', 'optimole-wp' ),
		];

		wp_send_json( $response );
	}

	/**
	 * Bust cached assets when an attachment is renamed.
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $old_url The old attachment URL.
	 * @param string $new_url The new attachment URL.
	 */
	public function bust_cache_on_rename( $attachment_id, $old_url, $new_url ) {
		$this->clear_cache();
	}

	/**
	 * Bust cached assets when an attachment is replaced.
	 *
	 * @param int $attachment_id The attachment ID.
	 *
	 * @return void
	 */
	public function bust_cache_on_replace( $attachment_id ) {
		$this->clear_cache();
	}

	/**
	 * Clear the cache for third-party plugins.
	 *
	 * @return void
	 */
	private function clear_cache() {
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

	/**
	 * Get the SVG loader.
	 *
	 * @return string The SVG loader.
	 */
	private function get_svg_loader() {
		return '<svg style="display: none;" class="optml-svg-loader" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>';
	}
}
