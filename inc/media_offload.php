<?php
/**
 * Optml_Media_Offload class.
 *
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Admin
 */
class Optml_Media_Offload extends Optml_App_Replacer {

	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	public $settings;

	const KEYS = array(
		'uploaded'        => '/optml3_uploaded:true/',
	);
	/**
	 * Optml_Media_Offload constructor.
	 */
	public function __construct() {
		$this->settings = new Optml_Settings();
		if ( $this->settings->get( 'offload_media' ) === 'enabled' ) {
			add_filter( 'image_downsize', array($this, 'generate_filter_downsize_urls'), 10, 3 );
			add_filter( 'wp_generate_attachment_metadata', array($this, 'generate_image_meta'), 10, 2 );
			add_filter( 'wp_get_attachment_url', array($this, 'get_image_attachment_url'), -999, 2 );
			add_action( 'wp_insert_post_data', array($this, 'filter_uploaded_images') );
			add_action( 'delete_attachment', array($this, 'delete_image_from_server'), 10 );
			add_filter( 'handle_bulk_actions-upload', array($this, 'bulk_action_handler'), 10, 3 );
			add_filter( 'bulk_actions-upload', array($this, 'register_bulk_media_actions') );
			add_action( 'admin_notices', array($this, 'bulk_action_notices') );
			add_filter( 'media_row_actions', array($this, 'add_inline_media_action'), 10, 2 );
		}
	}

	public function is_uploaded_image( $src ) {
		return strpos( $src, self::KEYS['uploaded'] ) !== false;
	}
	/**
	 * Get the attachment ID from the image tag.
	 *
	 * @param string $image Image tag.
	 *
	 * @return int|false
	 */
	public function get_id_from_tag( $image ) {
		$attachment_id = false;
		if ( preg_match( '#class=["|\']?[^"\']*(wp-image-|wp-video-)([\d]+)[^"\']*["|\']?#i', $image, $found ) ) {
			$attachment_id = intval( $found[2] );
		}

		return $attachment_id;
	}
	/**
	 * Filter out the urls that are saved to our servers when saving to the DB.
	 *
	 * @param array $data The post data array to save.
	 *
	 * @return array
	 */
	public function filter_uploaded_images( $data ) {

		$content = trim( wp_unslash( $data['post_content'] ) );
		$images  = Optml_Manager::parse_images_from_html( $content );
		if ( ! isset( $images[0] ) ) {
			return $data;
		}
		foreach ( $images[0] as $index => $tag ) {
			$url           = $images['img_url'][ $index ];
			$attachment_id = $this->get_id_from_tag( $tag );
			if ( false === $attachment_id || ! wp_attachment_is_image( $attachment_id ) ) {
				continue;
			}
			$size = $this->parse_dimensions_from_filename( $url );
			$optimized_url = wp_get_attachment_image_src( $attachment_id, $size );
			if ( ! isset( $optimized_url[0] ) ) {
				continue;
			}
			$content = str_replace( $url, $optimized_url[0], $content );
		}
		$data['post_content'] = wp_slash( $content );
		return $data;
	}
	/**
	 * Trigger an update on content that contains the same attachment ID as the modified image.
	 *
	 * @param int $attachment_id The attachment id of the image to init an update.
	 */
	public function update_content( $attachment_id ) {
		$content = new \WP_Query( array( 's' => 'wp-image-' . $attachment_id, 'fields' => 'ids', 'posts_per_page' => 500 ) );
		if ( ! empty( $content->found_posts ) ) {
			$content_posts = array_unique( $content->get_posts() );
			foreach ( $content_posts as $content_id ) {
				wp_update_post( array( 'ID' => $content_id ) );  // existing filters should take care of providing optimized urls
			}
		}
	}
	/**
	 * Add inline action to push to our servers.
	 *
	 * @param array    $actions All actions.
	 * @param \WP_Post $post    The current post image object.
	 *
	 * @return array
	 */
	public function add_inline_media_action( $actions, $post ) {
		$file = wp_get_attachment_metadata( $post->ID )['file'];
		if ( wp_check_filetype( $file, Optml_Config::$all_extensions )['ext'] === false || ! current_user_can( 'delete_post', $post->ID )
			|| $this->is_uploaded_image( $file ) ) {
			return $actions;
		}
		$action_url = add_query_arg(
			array(
				'action' => 'optimole_upload_image',
				'media[]' => $post->ID,
				'_wpnonce' => wp_create_nonce( 'bulk-media' ),
			),
			'upload.php'
		);

		$actions['optimole_upload_image'] = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			$action_url,
			esc_attr__( 'Push to Optimole', 'optimole-wp' ),
			esc_html__( 'Push to Optimole', 'optimole-wp' )
		);
		return $actions;
	}
	public function upload_and_update_existing_images( $image_ids ) {
		$success_up = 0;
		foreach ( $image_ids as $id ) {
			if ( $this->is_uploaded_image( wp_get_attachment_metadata( $id )['file'] ) ) {
				$success_up ++;
				continue;
			}
			$meta = $this->generate_image_meta( wp_get_attachment_metadata( $id ), $id );
			if ( isset( $meta['file'] ) && $this->is_uploaded_image( $meta['file'] ) ) {
				$success_up ++;
				wp_update_attachment_metadata( $id, $meta );
				$this->update_content( $id );
			}
		}
		return $success_up;
	}
	public function bulk_action_handler( $redirect, $doaction, $image_ids ) {
		if ( empty( $image_ids ) ) {
			return $redirect;
		}
		$redirect = remove_query_arg( array( 'optimole_offload_images_succes', 'optimole_offload_images_failed', 'optimole_back_to_media' ), $redirect );

		if ( $doaction === 'optimole_upload_image' ) {
			$success_up = self::upload_and_update_existing_images( $image_ids );
			$redirect = add_query_arg( 'optimole_offload_images_succes', $success_up, $redirect );
			$failed_up = count( $image_ids ) - $success_up;
			if ( $failed_up > 0 ) {
				$redirect = add_query_arg( 'optimole_offload_images_failed', $failed_up, $redirect );
			}
		}

		if ( $doaction === 'optimole_back_to_media' ) {
			$redirect = add_query_arg( 'optimole_back_to_media', count( $image_ids ), $redirect );
		}
		return $redirect;

	}
	public function register_bulk_media_actions( $bulk_array ) {

		$bulk_array['optimole_upload_image'] = __( 'Push Image to Optimole', 'optimole-wp' );
		$bulk_array['optimole_back_to_media'] = __( 'Bring image back to media library', 'optimole-wp' );
		return $bulk_array;

	}
	public function bulk_action_notices() {
		if ( ! empty( $_REQUEST['optimole_offload_images_succes'] ) ) {

			printf(
				'<div id="message" class="updated notice is-dismissible"><p>' .
				_n(
					'%s image was stored on our server.',
					'%s images were stored on our servers.',
					intval( $_REQUEST['optimole_offload_images_succes'] ),
					'optimole-wp'
				) . '</p></div>',
				intval( $_REQUEST['optimole_offload_images_succes'] )
			);

		}
		if ( ! empty( $_REQUEST['optimole_offload_images_failed'] ) ) {
			printf(
				'<div id="message" class="updated notice is-dismissible"><p>' .
				_n(
					'%s image failed while uploading to our servers, please try again later.',
					'%s images failed while uploading to our servers, please try again later',
					intval( $_REQUEST['optimole_offload_images_failed'] ) .
					'optimole-wp'
				) . '</p></div>',
				intval( $_REQUEST['optimole_offload_images_failed'] )
			);

		}
		if ( ! empty( $_REQUEST['optimole_back_to_media'] ) ) {
			printf(
				'<div id="message" class="updated notice is-dismissible"><p>' .
				_n(
					'%s image was stored back on media library.',
					'%s image were stored back on media library.',
					intval( $_REQUEST['optimole_back_to_media'] ),
					'optimole-wp'
				) . '</p></div>',
				intval( $_REQUEST['optimole_back_to_media'] )
			);

		}
	}
	/**
	 * Get options for the signed urls api call.
	 *
	 * @param string $file_name Image name.
	 * @param string $quality Quality to optimize at.
	 * @param string $domain Image domain.
	 * @param string $delete Whether to delete a bucket object or not(ie. generate signed upload url).
	 * @return array
	 */
	private function set_api_call_options( $file_name, $quality = 'auto', $domain = '', $delete = 'false' ) {
		$body = [
			'apiKeyMD5' => $this->settings->get( 'api_key' ),
			'userKey' => Optml_Config::$key,
			'domain' => $domain,
			'filename' => $file_name,
			'quality' => $quality,
			'deleteUrl' => $delete,
		];
		$body = wp_json_encode( $body );

		$options = [
			'body'        => $body,
			'headers'     => [
				'Content-Type' => 'application/json',
			],
			'timeout'     => 60,
			'blocking'    => true,
			'httpversion' => '1.0',
			'sslverify'   => false,
			'data_format' => 'body',
		];
		return $options;
	}
	/**
	 * Delete an image from our servers after it is removed from media.
	 *
	 * @param int $post_id The deleted post id.
	 */
	public function delete_image_from_server( $post_id ) {
		$file = wp_get_attachment_metadata( $post_id )['file'];
		if ( $this->is_uploaded_image( $file ) ) {
			$domain = array();
			preg_match( '/\/domain:(.*)\//', $file, $domain );
			if ( ! isset( $domain[1] ) ) {
				return;
			}

			$temp = explode( '/', $file );
			$file_name = end( $temp );

			$options = $this->set_api_call_options( $file_name, 'auto', $domain[1], 'true' );

			$delete_response = wp_remote_post( constant( 'OPTML_SIGNED_URLS' ), $options );

			if ( is_wp_error( $delete_response ) || wp_remote_retrieve_response_code( $delete_response ) !== 200 ) {
				// should add some routine to retry delete once if delete fails
			}
		}
	}

	/**
	 * Get optimized URL for an attachment image if it is uploaded to our servers.
	 *
	 * @param string $url The current url.
	 * @param int    $attachment_id The attachment image id.
	 * @return string Optimole cdn URL.
	 * @uses filter:wp_get_attachment_url
	 */
	public function get_image_attachment_url( $url, $attachment_id ) {
		$file = wp_get_attachment_metadata( $attachment_id )['file'];
		if ( $this->is_uploaded_image( $file ) ) {
			$resources = new Optml_Image( $url, ['width' => 'auto', 'height' => 'auto'], $this->settings->get( 'cache_buster' ) );
			return Optml_Config::$service_url . '/' . $resources->get_domain_token() . $resources->get_cache_buster() . '/' . $file;
		}
		return $url;
	}
	/**
	 * Filter the requested image url.
	 *
	 * @param null         $image         The previous image value (null).
	 * @param int          $attachment_id The ID of the attachment.
	 * @param string|array $size          Requested size of image. Image size name, or array of width and height values (in that order).
	 *
	 * @return array The image sizes and optimized url.
	 * @uses filter:image_downsize
	 */
	public function generate_filter_downsize_urls( $image, $attachment_id, $size ) {
		if ( wp_attachment_is( 'video', $attachment_id ) ) {
			return $image;
		}
		$data      = image_get_intermediate_size( $attachment_id, $size );
		if ( ! isset( $data['url'] ) || ! isset( $data['width'] ) || ! isset( $data['height'] ) || ! $this->is_uploaded_image( $data['url'] ) ) {
			return $image;
		}
		$to_replace = array('w:auto', 'h:auto');
		$scaled_dimensions = array('w:' . $data['width'], 'h:' . $data['height']);
		$data['url'] = str_replace( $to_replace, $scaled_dimensions, $data['url'] );
		$image = array(
			$data['url'],
			$data['width'],
			$data['height'],
			true,
		);
		return $image;
	}
	/**
	 * Get image extension.
	 *
	 * @param string $path  Image path.
	 *
	 * @return string
	 */
	private function get_ext( $path ) {
		return pathinfo( $path, PATHINFO_EXTENSION );
	}
	/**
	 * Update image meta with optimized cdn path.
	 *
	 * @param array $meta    Meta information of the image.
	 * @param int   $attachment_id The image attachment ID.
	 *
	 * @return array
	 * @uses filter:wp_generate_attachment_metadata
	 */
	public function generate_image_meta( $meta, $attachment_id ) {

		if ( is_array( $meta ) ) {
			if ( isset( $meta_data['optimole_offload'] ) ) {
				return $meta;
			}
		}
		$meta_data['optimole_offload'] = 'true';
		$parts  = parse_url( wp_get_attachment_url( $attachment_id ) );
		$domain = isset( $parts['host'] ) ? str_replace( 'www.', '', $parts['host'] ) : '';

		if ( $domain === '' ) {
			return $meta;
		}

		$local_file = get_attached_file( $attachment_id );
		$extension = $this->get_ext( $local_file );

		if ( ! isset( Optml_Config::$image_extensions [ $extension ] ) || ! defined( 'OPTML_SIGNED_URLS' ) ) {
			return $meta;
		}

		$content_type = Optml_Config::$image_extensions [ $extension ];
		$temp = explode( '/', $local_file );
		$file_name = end( $temp );

		$options = $this->set_api_call_options( $file_name, $this->settings->get_numeric_quality(), $domain, 'false' );

		$generate_url_response = wp_remote_post( constant( 'OPTML_SIGNED_URLS' ), $options );

		if ( is_wp_error( $generate_url_response ) || wp_remote_retrieve_response_code( $generate_url_response ) !== 200 ) {
			return $meta;
		}

		$cdn_url = json_decode( $generate_url_response['body'], true )['cdnUrl'];
		$upload_signed_url = json_decode( $generate_url_response['body'], true )['uploadUrl'];

		$upload_args = array(
			'method'    => 'PUT',
			'headers'    => array(
				'content-type'  => $content_type,
			),
			'timeout'     => 30,
			'body' => file_get_contents( $local_file ),
		);
		$result = wp_remote_request( $upload_signed_url, $upload_args );
		if ( is_wp_error( $result ) || wp_remote_retrieve_response_code( $result ) !== 200 ) {
			return $meta;
		}
		file_exists( $local_file ) && unlink( $local_file );
		update_post_meta( $attachment_id, 'optimole_offload', 'true' );
		$meta['file'] = $cdn_url;
		if ( isset( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $key => $value ) {
				$generated_image_size_path = str_replace( $file_name, $meta['sizes'][ $key ]['file'], $local_file );
				file_exists( $generated_image_size_path ) && unlink( $generated_image_size_path );
				$meta['sizes'][ $key ]['file'] = $file_name;
			}
		}
		return $meta;
	}
	public static function upload_images( $batch ) {
		$args = array(
			'post_type'           => 'attachment',
			'post_mime_type'      => array( 'image', 'video' ),
			'post_status'         => 'inherit',
			'posts_per_page'      => $batch,
			'fields'              => 'ids',
			'meta_query'          => array(
				'relation' => 'AND',
				array(
					'key'     => 'optimole_offload',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => 'optimole_offload_error',
					'compare' => 'NOT EXISTS',
				),
			),
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
		);
		$attachments = new \WP_Query( $args );
		$ids         = $attachments->get_posts();
		$media_offload = new Optml_Media_Offload();
		return $media_offload->upload_and_update_existing_images( $ids );
	}

	public static function number_of_library_images() {
		$total_images_by_mime = wp_count_attachments( 'image' );
		$img_number = 0;
		foreach ( $total_images_by_mime as $value ) {
			$img_number += $value;
		}
		return  $img_number;
	}
}
