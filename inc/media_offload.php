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
	use Optml_Normalizer;
	use Optml_Dam_Offload_Utils;


	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	public $settings;
	/**
	 * Cached object instance.
	 *
	 * @var Optml_Media_Offload
	 */
	private static $instance = null;

	/**
	 * Hold the logger object.
	 *
	 * @var Optml_Logger
	 */
	public $logger;

	const KEYS = [
		'uploaded_flag'        => 'id:',
		'not_processed_flag'        => 'process:',
	];
	const META_KEYS = [
		'offloaded'     => 'optimole_offload',
		'offload_error' => 'optimole_offload_error',
		'rollback_error' => 'optimole_rollback_error',
	];
	const OM_OFFLOADED_FLAG = 'om_image_offloaded';
	const POST_OFFLOADED_FLAG = 'optimole_offload_post';
	const POST_ROLLBACK_FLAG = 'optimole_rollback_post';
	const RETRYABLE_META_COUNTER = '_optimole_retryable_errors';
	/**
	 * Flag used inside wp_get_attachment url filter.
	 *
	 * @var bool Whether or not to return the original url of the image.
	 */
	private static $return_original_url = false;
	/**
	 * Flag used inside wp_get_attachment url filter.
	 *
	 * @var bool Whether or not to return the original url of the image.
	 */
	private static $offload_update_post = false;

	/**
	 * Flag used inside wp_unique_filename filter.
	 *
	 * @var bool|string Whether to skip our custom deduplication.
	 */
	private static $current_file_deduplication = false;
	/**
	 * Keeps the last deduplicated lower case value.
	 *
	 * @var bool|string Used to check if the current processed image was deduplicated.
	 */
	private static $last_deduplicated = false;
	/**
	 * Checks if the plugin was installed before adding POST_OFFLOADED_FLAG.
	 *
	 * @var bool Used when applying the flags for the page query.
	 */
	private static $is_legacy_install = null;

	/**
	 * Adds page meta query args
	 *
	 * @param string $action The action for which the args are needed.
	 * @param array  $args The initial args without the added meta_query args.
	 * @return array The args with the added meta_query args.
	 */
	public static function add_page_meta_query_args( $action, $args ) {
		if ( $action === 'offload_images' ) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key' => self::POST_OFFLOADED_FLAG,
					'compare' => 'NOT EXISTS',
				],
			];
		}
		if ( $action === 'rollback_images' ) {
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key' => self::POST_ROLLBACK_FLAG,
					'compare' => 'NOT EXISTS',
				],
			];
			if ( self::$is_legacy_install ) {
				$args['meta_query'][] = [
					'key' => self::POST_OFFLOADED_FLAG,
					'value' => 'true',
					'compare' => '=',
				];
			}
		}
		return $args;
	}

	/**
	 * Get count of all images from db.
	 *
	 * @return int Number of all images.
	 */
	public static function number_of_all_images() {
		$total_images_by_mime = wp_count_attachments( 'image' );
		return  array_sum( (array) $total_images_by_mime );
	}

	/**
	 * Optml_Media_Offload constructor.
	 */
	public static function instance() {
		if ( null === self::$instance || self::is_phpunit_test() ) {
			self::$instance = new self();
			self::$instance->settings = new Optml_Settings();
			self::$instance->logger = Optml_Logger::instance();

			if ( self::$instance->settings->is_connected() ) {
				self::$instance->init();
			}
			if ( self::$instance->settings->is_offload_enabled() ) {
				add_filter( 'image_downsize', [self::$instance, 'generate_filter_downsize_urls'], 10, 3 );
				add_filter( 'wp_generate_attachment_metadata', [self::$instance, 'generate_image_meta'], 10, 2 );
				add_filter( 'wp_get_attachment_url', [self::$instance, 'get_image_attachment_url'], -999, 2 );
				add_filter( 'wp_insert_post_data', [self::$instance, 'filter_uploaded_images'] );

				self::$instance->add_new_actions();

				add_action( 'delete_attachment', [self::$instance, 'delete_attachment_hook'], 10 );
				add_filter( 'handle_bulk_actions-upload', [self::$instance, 'bulk_action_handler'], 10, 3 );
				add_filter( 'bulk_actions-upload', [self::$instance, 'register_bulk_media_actions'] );
				add_filter( 'media_row_actions', [self::$instance, 'add_inline_media_action'], 10, 2 );
				add_filter( 'wp_calculate_image_srcset', [self::$instance, 'calculate_image_srcset'], 1, 5 );
				add_action( 'post_updated', [self::$instance, 'update_offload_meta'], 10, 3 );

				// Backwards compatibility for older versions of WordPress < 6.0.0 requiring 3 parameters for this specific filter.
				$below_6_0_0 = version_compare( get_bloginfo( 'version' ), '6.0.0', '<' );
				if ( $below_6_0_0 ) {
					add_filter( 'wp_insert_attachment_data', [self::$instance, 'insert_legacy'], 10, 3 );
				} else {
					add_filter( 'wp_insert_attachment_data', [self::$instance, 'insert'], 10, 4 );
				}

				add_action( 'optml_start_processing_images', [self::$instance, 'start_processing_images'], 10, 5 );
				add_action( 'optml_start_processing_images_by_id', [self::$instance, 'start_processing_images_by_id'], 10, 4 );

				if ( self::$is_legacy_install === null ) {
					self::$is_legacy_install = get_option( 'optimole_wp_install', 0 ) > 1677171600;
				}
			}
		}
		return self::$instance;
	}

	/**
	 * Function for `update_attached_file` filter-hook.
	 *
	 * @param string $file          Path to the attached file to update.
	 * @param int    $attachment_id Attachment ID.
	 *
	 * @return string
	 */
	function wp_update_attached_file_filter( $file, $attachment_id ) {

		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'called updated attached' );
		}
		$info = pathinfo( $file );
		$file_name = basename( $file );
		$no_ext_file_name = basename( $file, '.' . $info['extension'] );
		// if we have current deduplication set and it contains the filename that is updated
		// we replace the updated filename with the deduplicated filename
		if ( ! empty( self::$current_file_deduplication ) && stripos( self::$current_file_deduplication, $no_ext_file_name ) !== false ) {
			$file = str_replace( $file_name, self::$current_file_deduplication, $file );
			// we need to store the filename we replaced to check when uploading the image if it was deduplicated
			self::$last_deduplicated = $file_name;

			self::$current_file_deduplication = false;
		}
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', self::$last_deduplicated );
		}
		remove_filter( 'update_attached_file', [self::$instance, 'wp_update_attached_file_filter'], 10 );
		return $file;
	}

	/**
	 * Function for `wp_insert_attachment_data` filter-hook.
	 * Because we remove the images when new images are added the wp deduplication using the files will not work
	 * To overcome this we hook the attachment data when it's added to the database and we use the post name (slug) which is unique against the database
	 * For creating a unique quid by replacing the filename with the slug inside the existing guid
	 * This will ensure the guid is unique and the next step will be to make sure the attached_file meta for the image is also unique
	 * For this we will hook `update_attached_file` filter which is called after the data is inserted and there we will make sure we replace the filename
	 * with the deduplicated one which we stored into `$current_file_deduplication` variable
	 *
	 * @param array $data                An array of slashed, sanitized, and processed attachment post data.
	 * @param array $postarr             An array of slashed and sanitized attachment post data, but not processed.
	 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed attachment post data as originally passed to wp_insert_post().
	 * @param bool  $update              Whether this is an existing attachment post being updated.
	 *
	 * @see self::insert_legacy() for backwards compatibility with older versions of WordPress < 6.0.0.
	 *
	 * @return array
	 */
	function insert( $data, $postarr, $unsanitized_postarr, $update ) {

		// the post name is unique against the database so not affected by removing the files
		// https://developer.wordpress.org/reference/functions/wp_unique_post_slug/
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'data before' );
			do_action( 'optml_log', $data );
		}
		if ( empty( $data['guid'] ) ) {
			return $data;
		}

		$filename = wp_basename( $data['guid'] );
		$ext = $this->get_ext( $filename );
		// skip if the file is not an image
		if ( ! isset( Optml_Config::$all_extensions[ $ext ] ) && ! in_array( $ext, ['jpg', 'jpeg', 'jpe'], true ) ) {
			return $data;
		}

		// on some instances (just unit tests) the post name has the extension appended like this : `image-1-jpg`
		// we remove that as it is redundant for the file name deduplication we are using it
		$sanitized_post_name = str_replace( '-' . $ext, '', $data['post_name'] );

		// with the wp deduplication working the post_title is identical to the post_name
		// so when they are different it means we need to deduplicate using the post_name
		if ( ! empty( $data['post_name'] ) && $data['post_title'] !== $sanitized_post_name ) {
			// we append the extension to the post_name to create a filename
			// and use it to replace the filename in the guid
			$no_ext_filename = str_replace( '.' . $ext, '', $filename );

			$no_ext_filename_sanitized = sanitize_title( $no_ext_filename );

			// get the deduplication addition from the database post_name
			$diff = str_replace( strtolower( $no_ext_filename_sanitized ), '', $sanitized_post_name );

			// create the deduplicated filename
			$to_replace_with = $no_ext_filename . $diff . '.' . $ext;

			$data['guid'] = str_replace( $filename, $to_replace_with, $data['guid'] );
			// we store the deduplication to be used and add the filter for updating the attached_file meta
			self::$current_file_deduplication = $to_replace_with;
			add_filter( 'update_attached_file', [self::$instance, 'wp_update_attached_file_filter'], 10, 2 );
		}
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'data after' );
			do_action( 'optml_log', $data );
		}

		return $data;
	}

	/**
	 * Wrapper for the `insert` method for WP versions < 6.0.0.
	 *
	 * @param array $data                An array of slashed, sanitized, and processed attachment post data.
	 * @param array $postarr             An array of slashed and sanitized attachment post data, but not processed.
	 * @param array $unsanitized_postarr An array of slashed yet *unsanitized* and unprocessed attachment post data as originally passed to wp_insert_post().
	 *
	 * @return array
	 */
	function insert_legacy( $data, $postarr, $unsanitized_postarr ) {
		return $this->insert( $data, $postarr, $unsanitized_postarr, false );
	}

	/**
	 * Update offload meta when the page is updated.
	 *
	 * @param int     $post_ID Updated post id.
	 * @param WP_Post $post_after Post before the update.
	 * @param WP_Post $post_before Post after the update.
	 * @uses action:post_updated
	 *
	 * @return void
	 */
	public function update_offload_meta( $post_ID, $post_after, $post_before ) {
		if ( self::$offload_update_post === true ) {
			return;
		}
		if ( get_post_type( $post_ID ) === 'attachment' ) {
			return;
		}

		// revisions are skipped inside the function no need to check them before
		delete_post_meta( $post_ID, self::POST_ROLLBACK_FLAG );
	}

	/**
	 * Get image size name from width and meta.
	 *
	 * @param array   $sizes Image sizes .
	 * @param integer $width Size width.
	 * @param string  $filename Image filename.
	 *
	 * @return null|string|array
	 */
	public static function get_image_size_from_width( $sizes, $width, $filename, $just_name = true ) {
		foreach ( $sizes as $name => $size ) {
			if ( $width === absint( $size['width'] ) && $size['file'] === $filename ) {
				return $just_name ? $name : array_merge( $size, [ 'name' => $name ] );
			}
		}

		return null;
	}

	/**
	 * Replace image URLs in the srcset attributes.
	 *
	 * @param array  $sources Array of image sources.
	 * @param array  $size_array Array of width and height values in pixels (in that order).
	 * @param string $image_src The 'src' of the image.
	 * @param array  $image_meta The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int    $attachment_id Image attachment ID.
	 *
	 * @return array
	 */
	public function calculate_image_srcset( $sources, $size_array, $image_src, $image_meta, $attachment_id ) {
		if ( ! is_array( $sources ) ) {
			return $sources;
		}

		if ( $this->is_legacy_offloaded_attachment( $attachment_id ) ) {
			if ( ! Optml_Media_Offload::is_uploaded_image( $image_src ) || ! isset( $image_meta['file'] ) || ! Optml_Media_Offload::is_uploaded_image( $image_meta['file'] ) ) {
				return $sources;
			}
			foreach ( $sources as $width => $source ) {
				$filename      = wp_basename( $image_meta['file'] );
				$size          = $this->get_image_size_from_width( $image_meta['sizes'], $width, $filename );
				$optimized_url = wp_get_attachment_image_src( $attachment_id, $size );

				if ( false === $optimized_url ) {
					continue;
				}

				$sources[ $width ]['url'] = $optimized_url[0];
			}
			return $sources;
		}

		if ( ! $this->is_new_offloaded_attachment( $attachment_id ) ) {
			return $sources;
		}

		$requested_width  = $size_array[0];
		$requested_height = $size_array[1];

		if ( $requested_height < 1 || $requested_width < 1 ) {
			return $sources;
		}

		$requested_ratio     = $requested_width / $requested_height;

		$image_sizes = $this->get_all_image_sizes();
		$crop = false;

		// Loop through image sizes to make sure we're using the right cropping.
		foreach ( $image_sizes as $size_name => $args ) {
			if ( $args['width'] !== $requested_width && $args['height'] !== $requested_height ) {
				continue;
			}

			if ( isset( $args['crop'] ) ) {
				$crop = (bool) $args['crop'];
			}
		}

		foreach ( $sources as $width => $source ) {
			$filename = ( $image_meta['file'] );
			$size     = $this->get_image_size_from_width( $image_meta['sizes'], $width, $filename, false );

			if ( $size === null || ! isset( $size['name'] ) ) {
				unset( $sources[ $width ] );

				continue;
			}

			if ( ! isset( $image_sizes[ $size['name'] ] ) || (bool) $image_sizes[ $size['name'] ]['crop'] !== $crop ) {
				unset( $sources[ $width ] );

				continue;
			}

			// Some image sizes might have 0 values for width or height.
			if ( $size['width'] < 1 || $size['height'] < 1 ) {
				unset( $sources[ $width ] );

				continue;
			}

			$size_ratio = $size['width'] / $size['height'];

			// We need a srcset with the same aspect ratio.
			// Otherwise, we'll display different images on different devices.
			if ( $requested_ratio !== $size_ratio ) {
				unset( $sources[ $width ] );

				continue;
			}

			$optimized_url = wp_get_attachment_image_src( $attachment_id, $size['name'] );

			if ( false === $optimized_url ) {
				unset( $sources[ $width ] );

				continue;
			}

			$sources[ $width ]['url'] = $optimized_url[0];
		}

		// Add the requested size to the srcset.
		$sources[ $requested_width ] = [
			'url' => $image_src,
			'descriptor' => 'w',
			'value' => $requested_width,
		];

		return $sources;
	}

	/**
	 * Check if the image is stored on our servers or not.
	 *
	 * @param string $src Image src or url.
	 * @return bool Whether image is upload or not.
	 */
	public static function is_not_processed_image( $src ) {
		return strpos( $src, self::KEYS['not_processed_flag'] ) !== false;
	}

	/**
	 * Check if the image is stored on our servers or not.
	 *
	 * @param string $src Image src or url.
	 * @return bool Whether image is upload or not.
	 */
	public static function is_uploaded_image( $src ) {
		return strpos( $src, '/' . self::KEYS['uploaded_flag'] ) !== false;
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
	 * Get attachment id from url
	 *
	 * @param string $url  The optimized url .
	 * @return false|mixed The attachment id .
	 */
	public static function get_attachment_id_from_url( $url ) {
		preg_match( '/\/' . Optml_Media_Offload::KEYS['not_processed_flag'] . '([^\/]*)\//', $url, $attachment_id );
		return isset( $attachment_id[1] ) ? $attachment_id[1] : false;
	}

	/**
	 * Get attachment id from local url
	 *
	 * @param string $url The url to look for.
	 * @return array The attachment id and the size from the url.
	 */
	public function get_local_attachement_id_from_url( $url ) {

		$size = 'full';
		$found_size = $this->parse_dimensions_from_filename( $url );
		$strip_url = $url;
		$scaled_url = $url;
		if ( $found_size[0] !== false && $found_size[1] !== false ) {
			$size = $found_size;
			$strip_url = str_replace( '-' . $found_size[0] . 'x' . $found_size[1], '', $url );
			$scaled_url = str_replace( '-' . $found_size[0] . 'x' . $found_size[1], '-scaled', $url );
		}
		$strip_url = $this->add_schema( $strip_url );

		$attachment_id = attachment_url_to_postid( $strip_url );
		if ( $attachment_id === 0 ) {
			$scaled_url = $this->add_schema( $scaled_url );
			$attachment_id = attachment_url_to_postid( $scaled_url );
		}

		return [ 'attachment_id' => $attachment_id, 'size' => $size ];
	}

	/**
	 * Filter out the urls that are saved to our servers when saving to the DB.
	 *
	 * @param array $data The post data array to save.
	 *
	 * @return array
	 * @uses filter:wp_insert_post_data
	 */
	public function filter_uploaded_images( $data ) {

		$content = trim( wp_unslash( $data['post_content'] ) );
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'content to update' );
			do_action( 'optml_log', $content );
		}
		$images  = Optml_Manager::instance()->extract_urls_from_content( $content );
		if ( ! isset( $images[0] ) ) {
			return $data;
		}
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'images to update' );
			do_action( 'optml_log', $images );
		}
		foreach ( $images as $url ) {
			$is_original_uploaded = self::is_uploaded_image( $url );
			$attachment_id = false;
			$size = 'thumbnail';
			if ( $is_original_uploaded ) {
				$found_size = $this->parse_dimension_from_optimized_url( $url );
				if ( $found_size[0] !== 'auto' && $found_size[1] !== 'auto' ) {
					$size = $found_size;
				}
				$attachment_id = self::get_attachment_id_from_url( $url );
			} else {
				$id_and_size = $this->get_local_attachement_id_from_url( $url );
				$attachment_id = $id_and_size['attachment_id'];
				$size = $id_and_size['size'];
			}

			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', 'image id and found size' );
				do_action( 'optml_log', $attachment_id );
				do_action( 'optml_log', $size );
			}
			if ( false === $attachment_id || ! $this->is_legacy_offloaded_attachment( $attachment_id ) || ! wp_attachment_is_image( $attachment_id ) ) {
				continue;
			}
			$optimized_url = wp_get_attachment_image_src( $attachment_id, $size );
			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' image url to replace with ' );
				do_action( 'optml_log', $optimized_url );
			}

			if ( ! isset( $optimized_url[0] ) ) {
				continue;
			}
			if ( $is_original_uploaded === self::is_uploaded_image( $optimized_url[0] ) ) {
				continue;
			}
			$content = str_replace( $url, $optimized_url[0], $content );
		}
		$data['post_content'] = wp_slash( $content );
		return $data;
	}

	/**
	 * Get all images that need to be updated from a post.
	 *
	 * @param string $post_content The content of the post.
	 * @param string $job The job name.
	 * @return array An array containing the image ids.
	 */
	public function get_image_id_from_content( $post_content, $job ) {
		$content = trim( wp_unslash( $post_content ) );
		$images  = Optml_Manager::instance()->extract_urls_from_content( $content );
		$found_images = [];
		if ( isset( $images[0] ) ) {
			foreach ( $images as $url ) {
				$is_original_uploaded = self::is_uploaded_image( $url );
				$attachment_id = false;
				if ( $is_original_uploaded ) {
					if ( $job === 'rollback_images' ) {
						$attachment_id = self::get_attachment_id_from_url( $url );
					}
				} else {
					if ( $job === 'offload_images' ) {
						$id_and_size = $this->get_local_attachement_id_from_url( $url );
						$attachment_id = $id_and_size['attachment_id'];
					}
				}
				if ( false === $attachment_id || $attachment_id === 0 || ! wp_attachment_is_image( $attachment_id ) ) {
					continue;
				}
				$found_images[] = intval( $attachment_id );
			}
		}
		return apply_filters( 'optml_content_images_to_update', $found_images, $content );
	}

	/**
	 * Get the posts ids and the images from them that need sync/rollback.
	 *
	 * @param int    $page The current page from the query.
	 * @param string $job The job name rollback_images/offload_images.
	 * @param int    $batch How many posts to query on a page.
	 * @param array  $page_in The pages that need to be updated.
	 *
	 * @return array An array containing the page of the query and an array containing the images for every post that need to be updated.
	 */
	public function update_content( $page, $job, $batch = 1, $page_in = [] ) {
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', ' updating_content ' );
		}
		$post_types = array_values(
			array_filter(
				get_post_types(),
				function( $post_type ) {
					if ( $post_type === 'attachment' || $post_type === 'revision' ) {
						return false;
					}
					return true;
				}
			)
		);
		$query_args = apply_filters(
			'optml_replacement_wp_query_args',
			[
				'post_type'              => $post_types,
				'post_status'            => 'any',
				'fields'                 => 'ids',
				'posts_per_page'         => $batch,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			]
		);

		$query_args = self::add_page_meta_query_args( $job, $query_args );

		if ( ! empty( $page_in ) ) {
			$query_args['post__in'] = $page_in;
		}

		$content = new \WP_Query( $query_args );
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', $page );
		}
		$images_to_update = [];
		if ( $content->have_posts() ) {
			while ( $content->have_posts() ) {
				$content->the_post();
				$content_id = get_the_ID();
				if ( get_post_type() !== 'attachment' ) {
					$ids = $this->get_image_id_from_content( get_post_field( 'post_content', $content_id ), $job );
					if ( count( $ids ) > 0 ) {
						$images_to_update[ $content_id ] = $ids;
						$duplicated_pages = apply_filters( 'optml_offload_duplicated_images', [], $content_id );
						if ( is_array( $duplicated_pages ) && ! empty( $duplicated_pages ) ) {
							foreach ( $duplicated_pages as $duplicated_id ) {
								$duplicated_ids = $this->get_image_id_from_content( get_post_field( 'post_content', $duplicated_id ), $job );
								$images_to_update[ $duplicated_id ] = $duplicated_ids;
							}
						}
					}
					if ( $job === 'offload_images' ) {
						update_post_meta( $content_id, self::POST_OFFLOADED_FLAG, 'true' );
						delete_post_meta( $content_id, self::POST_ROLLBACK_FLAG );
					}
					if ( $job === 'rollback_images' ) {
						update_post_meta( $content_id, self::POST_ROLLBACK_FLAG, 'true' );
						delete_post_meta( $content_id, self::POST_OFFLOADED_FLAG );
					}
				}
			}
			$page ++;
		}
		$result['page'] = $page;
		$result['imagesToUpdate'] = $images_to_update;
		return $result;
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
		$meta = wp_get_attachment_metadata( $post->ID );
		if ( ! isset( $meta['file'] ) ) {
			return $actions;
		}
		$file = $meta['file'];
		if ( wp_check_filetype( $file, Optml_Config::$all_extensions )['ext'] === false || ! current_user_can( 'delete_post', $post->ID ) ) {
			return $actions;
		}
		if ( ! self::is_uploaded_image( $file ) ) {
			$upload_action_url = add_query_arg(
				[
					'page' => 'optimole',
					'optimole_action' => 'offload_images',
					'0' => $post->ID,
				],
				'admin.php'
			);

			$actions['offload_images'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				$upload_action_url,
				esc_attr__( 'Offload to Optimole', 'optimole-wp' ),
				esc_html__( 'Offload to Optimole', 'optimole-wp' )
			);
		}
		if ( self::is_uploaded_image( $file ) ) {
			$rollback_action_url = add_query_arg(
				[
					'page' => 'optimole',
					'optimole_action' => 'rollback_images',
					'0' => $post->ID,
				],
				'admin.php'
			);
			$actions['rollback_images'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				$rollback_action_url,
				esc_attr__( 'Restore image to media library', 'optimole-wp' ),
				esc_html__( 'Restore image to media library', 'optimole-wp' )
			);
		}
		return $actions;
	}

	/**
	 * Upload images to our servers and update inside pages.
	 *
	 * @param array $image_ids The id of the attachments for the selected images.
	 * @return int The number of successfully processed images.
	 */
	public function upload_and_update_existing_images( $image_ids ) {
		$success_up = 0;
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', ' images to upload ' );
			do_action( 'optml_log', $image_ids );
		}
		foreach ( $image_ids as $id ) {
			if ( self::is_uploaded_image( wp_get_attachment_metadata( $id )['file'] ) ) {
				// if this meta flag below failed at the initial update but the file meta above is updated it will cause an infinite query loop
				update_post_meta( $id, 'optimole_offload', 'true' );
				$success_up ++;
				continue;
			}

			$meta = $this->generate_image_meta( wp_get_attachment_metadata( $id ), $id );
			if ( isset( $meta['file'] ) && self::is_uploaded_image( $meta['file'] ) ) {
				$success_up ++;
				wp_update_attachment_metadata( $id, $meta );
			}
		}
		if ( $success_up > 0 ) {
			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' call post update, succesful images: ' );
				do_action( 'optml_log', $success_up );
			}
		}
		return $success_up;
	}

	/**
	 * Return the original url of an image attachment.
	 *
	 * @param integer $post_id Image attachment id.
	 * @return string|bool The original url of the image.
	 */
	public static function get_original_url( $post_id ) {
		self::$return_original_url = true;
		$original_url = wp_get_attachment_url( $post_id );
		self::$return_original_url = false;
		return $original_url;
	}

	/**
	 * Bring images back to media library and update inside pages.
	 *
	 * @param array $image_ids The id of the attachments for the selected images.
	 * @return int The number of successfully processed images.
	 */
	public function rollback_and_update_images( $image_ids ) {
		$success_back = 0;
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', ' images to rollback ' );
			do_action( 'optml_log', $image_ids );
		}

		foreach ( $image_ids as $id ) {
			// Skip DAM attachment filtering.
			if ( $this->is_dam_imported_image( $id ) ) {
				continue;
			}
			$current_meta = wp_get_attachment_metadata( $id );
			if ( ! isset( $current_meta['file'] ) || ! self::is_uploaded_image( $current_meta['file'] ) ) {
				delete_post_meta( $id, self::META_KEYS['offloaded'] );
				delete_post_meta( $id, self::OM_OFFLOADED_FLAG );
				$success_back++;
				continue;
			}
			$table_id = [];
			// Account for scaled images.
			$source_file = isset( $current_meta['original_image'] ) ? $current_meta['original_image'] : $current_meta['file']; // @phpstan-ignore-line - this exists for scaled images.
			$filename = pathinfo( $source_file, PATHINFO_BASENAME );
			preg_match( '/\/' . self::KEYS['uploaded_flag'] . '([^\/]*)\//', $current_meta['file'], $table_id );
			if ( ! isset( $table_id[1] ) ) {
				continue;
			}
			$table_id = $table_id[1];
			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' image cloud id ' );
				do_action( 'optml_log', $table_id );
			}
			$request = new Optml_Api();
			$get_response = $request->call_upload_api( '', 'false', $table_id, 'false', 'true' );

			if ( is_wp_error( $get_response ) || wp_remote_retrieve_response_code( $get_response ) !== 200 ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', ' error get url' );
				}

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Image ID: ' . $id . ' has error getting URL.' );
				continue;
			}

			$get_url = json_decode( $get_response['body'], true )['getUrl'];

			if ( ! function_exists( 'download_url' ) ) {
				include_once ABSPATH . 'wp-admin/includes/file.php';
			}
			if ( ! function_exists( 'download_url' ) ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				continue;
			}
			$timeout_seconds = 60;
			$temp_file = download_url( $get_url, $timeout_seconds );

			if ( is_wp_error( $temp_file ) ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', ' download_url error ' );
				}

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Image ID: ' . $id . ' has error getting URL.' );
				continue;
			}

			$extension = $this->get_ext( $filename );

			if ( ! isset( Optml_Config::$image_extensions [ $extension ] ) ) {
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', ' image has invalid extension' );
					do_action( 'optml_log', $extension );
				}
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Image ID: ' . $id . ' has invalid extension.' );
				continue;
			}

			$type = Optml_Config::$image_extensions [ $extension ];
			$file = [
				'name'     => $filename,
				'type'     => $type,
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			];

			$overrides = [
				// do not expect the default form data from normal uploads
				'test_form' => false,

				// Setting this to false lets WordPress allow empty files, not recommended.
				'test_size' => true,

				// A properly uploaded file will pass this test. There should be no reason to override this one.
				'test_upload' => true,
			];

			if ( ! function_exists( 'wp_handle_sideload' ) ) {
				include_once ABSPATH . '/wp-admin/includes/file.php';
			}
			if ( ! function_exists( 'wp_handle_sideload' ) ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				continue;
			}

			// Move the temporary file into the uploads directory.
			$results = wp_handle_sideload( $file, $overrides, get_the_date( 'Y/m', $id ) );
			if ( ! empty( $results['error'] ) ) {
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', ' wp_handle_sideload error' );
				}
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Image ID: ' . $id . ' faced wp_handle_sideload error.' );
				continue;
			}

			if ( ! function_exists( 'wp_create_image_subsizes' ) ) {
				include_once ABSPATH . '/wp-admin/includes/image.php';
			}
			if ( ! function_exists( 'wp_create_image_subsizes' ) ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				continue;
			}
			$original_meta = wp_create_image_subsizes( $results['file'], $id );
			if ( $type === 'image/svg+xml' ) {
				if ( ! function_exists( 'wp_get_attachment_metadata' ) || ! function_exists( 'wp_update_attachment_metadata' ) ) {
					include_once ABSPATH . '/wp-admin/includes/post.php';
				}
				if ( ! function_exists( 'wp_get_attachment_metadata' ) ) {
					update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
					continue;
				}
				$meta = wp_get_attachment_metadata( $id );
				if ( ! isset( $meta['file'] ) ) {
					update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
					continue;
				}
				$meta['file'] = $results['file'];
				wp_update_attachment_metadata( $id, $meta );
			}

			if ( ! function_exists( 'update_attached_file' ) ) {
				include_once ABSPATH . '/wp-admin/includes/post.php';
			}
			if ( ! function_exists( 'update_attached_file' ) ) {
				update_post_meta( $id, self::META_KEYS['rollback_error'], 'true' );
				continue;
			}
			update_attached_file( $id, $results['file'] );

			$duplicated_images = apply_filters( 'optml_offload_duplicated_images', [], $id );
			if ( is_array( $duplicated_images ) && ! empty( $duplicated_images ) ) {
				foreach ( $duplicated_images as $duplicated_id ) {
					$duplicated_meta = wp_get_attachment_metadata( $duplicated_id );
					if ( isset( $duplicated_meta['file'] ) && self::is_uploaded_image( $duplicated_meta['file'] ) ) {
						$duplicated_meta['file'] = $results['file'];
						if ( isset( $meta ) ) {
							foreach ( $meta['sizes'] as $key => $value ) {
								if ( isset( $original_meta['sizes'][ $key ]['file'] ) ) {
									$duplicated_meta['sizes'][ $key ]['file'] = $original_meta['sizes'][ $key ]['file'];
								}
							}
						}
						wp_update_attachment_metadata( $duplicated_id, $duplicated_meta );
						delete_post_meta( $duplicated_id, self::META_KEYS['offloaded'] );
						delete_post_meta( $duplicated_id, self::OM_OFFLOADED_FLAG );
					}
				}
			}
			$success_back++;

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_ROLLBACK, 'Image ID: ' . $id . ' has been rolled back.' );

			$original_url  = self::get_original_url( $id );
			if ( $original_url === false ) {
				continue;
			}
			$this->delete_attachment_from_server( $original_url, $id, $table_id );
		}

		if ( $success_back > 0 ) {
			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' call update post, success rollback' );
				do_action( 'optml_log', $success_back );
			}
		}
		return $success_back;
	}

	/**
	 * Handle the bulk actions.
	 *
	 * @param string $redirect  The current url from the media library.
	 * @param string $doaction  The current action selected.
	 * @param array  $image_ids The id of the attachments for the selected images.
	 * @return string The url with the correspondent query args for the executed actions.
	 */
	public function bulk_action_handler( $redirect, $doaction, $image_ids ) {

		if ( empty( $image_ids ) ) {
			return $redirect;
		}

		$image_ids = array_slice( $image_ids, 0, 20, true );
		$redirect = 'admin.php';
		$redirect = add_query_arg( 'optimole_action', $doaction, $redirect );
		$redirect = add_query_arg( 'page', 'optimole', $redirect );
		$redirect = add_query_arg( $image_ids, $redirect );
		return $redirect;

	}

	/**
	 *  Register the bulk media actions.
	 *
	 *  @param array $bulk_array The existing actions array.
	 *  @return array The array with the appended actions.
	 */
	public function register_bulk_media_actions( $bulk_array ) {

		$bulk_array['offload_images'] = __( 'Push Image to Optimole', 'optimole-wp' );
		$bulk_array['rollback_images'] = __( 'Restore image to media library', 'optimole-wp' );
		return $bulk_array;

	}

	/**
	 * Send delete request to our servers and update the meta.
	 *
	 * @param string  $original_url Original url of the image.
	 * @param integer $post_id Image id inside db.
	 * @param string  $table_id Our cloud id for the image.
	 */
	public function delete_attachment_from_server( $original_url, $post_id, $table_id ) {
		$request = new Optml_Api();
		$delete_response = $request->call_upload_api( $original_url, 'true', $table_id );

		delete_post_meta( $post_id, self::META_KEYS['offloaded'] );
		delete_post_meta( $post_id, self::OM_OFFLOADED_FLAG );
		if ( is_wp_error( $delete_response ) || wp_remote_retrieve_response_code( $delete_response ) !== 200 ) {
			// should add some routine to retry delete once if delete fails
		}
	}

	/**
	 * Delete an image from our servers after it is removed from media.
	 *
	 * @param int $post_id The deleted post id.
	 */
	public function delete_attachment_hook( $post_id ) {
		$file = wp_get_attachment_metadata( $post_id );
		if ( $file === false ) {
			return;
		}

		// Skip if the image was imported from cloud library.
		if ( $this->is_dam_imported_image( $post_id ) ) {
			return;
		}

		if ( ! $this->is_new_offloaded_attachment( $post_id ) && ! $this->is_legacy_offloaded_attachment( $post_id ) ) {
			return;
		}

		$file = $file['file'];
		if ( self::is_uploaded_image( $file ) || $this->is_new_offloaded_attachment( $post_id ) ) {
			$original_url  = self::get_original_url( $post_id );
			if ( $original_url === false ) {
				return;
			}
			$table_id = [];

			preg_match( '/\/' . self::KEYS['uploaded_flag'] . '([^\/]*)\//', $file, $table_id );

			if ( ! isset( $table_id[1] ) ) {
				return;
			}
			$this->delete_attachment_from_server( $original_url, $post_id, $table_id[1] );
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
		if ( self::$return_original_url === true ) {
			return $url;
		}

		if ( $this->is_legacy_offloaded_attachment( $attachment_id ) ) {
			$meta = wp_get_attachment_metadata( $attachment_id );
			if ( ! isset( $meta['file'] ) ) {
				return $url;
			}

			// Skip DAM attachment filtering.
			if ( $this->is_dam_imported_image( $attachment_id ) ) {
				return $url;
			}

			$file = $meta['file'];
			if ( self::is_uploaded_image( $file ) ) {
				$optimized_url = ( new Optml_Image( $url, ['width' => 'auto', 'height' => 'auto', 'quality' => $this->settings->get_numeric_quality()], $this->settings->get( 'cache_buster' ) ) )->get_url();
				return str_replace( '/' . $url, '/' . self::KEYS['not_processed_flag'] . $attachment_id . $file, $optimized_url );
			} else {
				// this is for the users that already offloaded the images before the other fixes
				$local_file = get_attached_file( $attachment_id );
				if ( ! file_exists( $local_file ) ) {
					$duplicated_images = apply_filters( 'optml_offload_duplicated_images', [], $attachment_id );
					if ( is_array( $duplicated_images ) && ! empty( $duplicated_images ) ) {
						foreach ( $duplicated_images as $id ) {
							if ( ! empty( $id ) ) {
								$duplicated_meta = wp_get_attachment_metadata( $id );
								if ( isset( $duplicated_meta['file'] ) && self::is_uploaded_image( $duplicated_meta['file'] ) ) {
									$optimized_url = ( new Optml_Image( $url, ['width' => 'auto', 'height' => 'auto', 'quality' => $this->settings->get_numeric_quality()], $this->settings->get( 'cache_buster' ) ) )->get_url();
									return str_replace( '/' . $url, '/' . self::KEYS['not_processed_flag'] . $id . $duplicated_meta['file'], $optimized_url );
								}
							}
						}
					}
				}
			}
			return $url;
		}

		if ( ! $this->is_new_offloaded_attachment( $attachment_id ) ) {
			return $url;
		}

		return $this->get_new_offloaded_attachment_url( $url, $attachment_id );
	}

	/**
	 * Filter the requested image url.
	 *
	 * @param bool|array   $image         The previous image value (null).
	 * @param int          $attachment_id The ID of the attachment.
	 * @param string|array $size          Requested size of image. Image size name, or array of width and height values (in that order).
	 *
	 * @return bool|array The image sizes and optimized url.
	 * @uses filter:image_downsize
	 */
	public function generate_filter_downsize_urls( $image, $attachment_id, $size ) {
		if ( $this->is_dam_imported_image( $attachment_id ) ) {
			return $image;
		}

		if ( $this->is_legacy_offloaded_attachment( $attachment_id ) ) {
			if ( self::$return_original_url === true ) {
				return $image;
			}

			$sizes2crop = self::size_to_crop();
			if ( wp_attachment_is( 'video', $attachment_id ) && doing_action( 'wp_insert_post_data' ) ) {
				return $image;
			}
			$data = image_get_intermediate_size( $attachment_id, $size );
			if ( false === $data || ! self::is_uploaded_image( $data['url'] ) ) {
				return $image;
			}
			$resize = apply_filters( 'optml_default_crop', [] );
			if ( isset( $sizes2crop[ $data['width'] . $data['height'] ] ) ) {
				$resize = $this->to_optml_crop( $sizes2crop[ $data['width'] . $data['height'] ] );
			}
			$id_filename = [];

			preg_match( '/\/(' . self::KEYS['not_processed_flag'] . '.*)/', $data['url'], $id_filename );
			if ( ! isset( $id_filename[1] ) ) {
				return $image;
			}
			$url           = self::get_original_url( $attachment_id );
			$optimized_url = ( new Optml_Image(
				$url,
				[
					'width'   => $data['width'],
					'height' => $data['height'],
					'resize' => $resize,
					'quality' => $this->settings->get_numeric_quality(),
				],
				$this->settings->get( 'cache_buster' )
			) )->get_url();
			$optimized_url = str_replace( $url, $id_filename[1], $optimized_url );
			$image         = [
				$optimized_url,
				$data['width'],
				$data['height'],
				true,
			];

			return $image;
		}

		if ( ! $this->is_new_offloaded_attachment( $attachment_id ) ) {
			return $image;
		}

		return $this->alter_attachment_image_src( $image, $attachment_id, $size, false );
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
	 * Mark an image as having a retryable error.
	 *
	 * @param int    $attachment_id The attachment ID.
	 * @param string $reason The reason for the error.
	 */
	public static function mark_retryable_error( $attachment_id, $reason ) {
		static $allowed_retries = 5;

		$retries = get_post_meta( $attachment_id, self::RETRYABLE_META_COUNTER, true );
		$retries = empty( $retries ) ? 0 : (int) $retries;
		if ( $retries >= $allowed_retries ) {
			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' ' . $reason . '. Reached the maximum number of retries.' );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			return;
		}

		self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' ' . $reason . '. Marked for retry, retries done: ' . $retries );

		update_post_meta( $attachment_id, self::RETRYABLE_META_COUNTER, ( $retries + 1 ) );
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

		if ( $this->is_dam_imported_image( $attachment_id ) ) {
			return $meta;
		}

		if ( self::$instance->settings->is_offload_limit_reached() ) {
			return $meta;
		}

		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'called generate meta' );
		}
		// No meta, or image was already uploaded.
		if ( ! isset( $meta['file'] ) || ! isset( $meta['width'] ) || ! isset( $meta['height'] ) || self::is_uploaded_image( $meta['file'] ) ) {
			do_action( 'optml_log', 'invalid meta' );
			do_action( 'optml_log', $meta );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has invalid meta.' );
			return $meta;
		}
		// Skip images based on filters.
		if ( false === Optml_Filters::should_do_image( $meta['file'], self::$filters[ Optml_Settings::FILTER_TYPE_OPTIMIZE ][ Optml_Settings::FILTER_FILENAME ] ) ) {
			do_action( 'optml_log', 'optimization filter' );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );
			return $meta;
		}
		$original_url  = self::get_original_url( $attachment_id );

		// Could not find original URL.
		if ( $original_url === false ) {
			do_action( 'optml_log', 'error getting original url' );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has invalid original url.' );
			return $meta;
		}

		// We should strip the `-scaled` from the URL to not generate inconsistencies with automatically scaled images.
		$original_url = $this->maybe_strip_scaled( $original_url );
		$local_file   = $this->maybe_strip_scaled( get_attached_file( $attachment_id ) );

		$extension = $this->get_ext( $local_file );
		$content_type = Optml_Config::$image_extensions [ $extension ];
		$temp = explode( '/', $local_file );
		$file_name = end( $temp );
		$no_ext_filename = str_replace( '.' . $extension, '', $file_name );
		$original_name = $file_name;
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'file before replace' );
			do_action( 'optml_log', $local_file );
		}

		// check if the current filename is the last deduplicated filename
		if ( ! empty( self::$last_deduplicated ) && strpos( $no_ext_filename, str_replace( '.' . $extension, '', self::$last_deduplicated ) ) !== false ) {
			// replace the file with the original before deduplication to get the path where the image is uploaded
			$local_file = str_replace( $file_name, self::$last_deduplicated, $local_file );
			$original_name = self::$last_deduplicated;
			self::$last_deduplicated = false;
		}
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'file after replace' );
			do_action( 'optml_log', $local_file );
		}
		if ( ! file_exists( $local_file ) ) {
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );
			do_action( 'optml_log', 'missing file' );
			do_action( 'optml_log', $local_file );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has missing file.' );
			return $meta;
		}

		if ( ! isset( Optml_Config::$image_extensions [ $extension ] ) ) {
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );
			do_action( 'optml_log', 'invalid extension' );
			do_action( 'optml_log', $extension );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has invalid extension.' );
			return $meta;
		}
		if ( false === Optml_Filters::should_do_extension( self::$filters[ Optml_Settings::FILTER_TYPE_OPTIMIZE ][ Optml_Settings::FILTER_EXT ], $extension ) ) {
			do_action( 'optml_log', 'extension filter' );
			do_action( 'optml_log', $extension );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );
			return $meta;
		}

		$request = new Optml_Api();
		$generate_url_response = $request->call_upload_api( $original_url );

		if ( is_wp_error( $generate_url_response ) || wp_remote_retrieve_response_code( $generate_url_response ) !== 200 ) {

			$decoded_response = json_decode( $generate_url_response['body'], true );

			// Handle limit exceeded
			if ( isset( $decoded_response['error'] ) && $decoded_response['error'] === 'limit_exceeded' ) {
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', 'limit exceeded error' );
					do_action( 'optml_log', $decoded_response );
				}

				self::$instance->settings->update( 'offload_limit', absint( $decoded_response['limit'] ) );
				self::$instance->settings->update( 'offload_limit_reached', 'enabled' );
				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Offload stopped: reached asset offload limit.' );

				return $meta;
			}

			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' call to signed url error' );
				do_action( 'optml_log', $generate_url_response );
			}
			self::mark_retryable_error( $attachment_id, 'Image ID: ' . $attachment_id . ' has invalid signed url.' );
			return $meta;
		}
		// We clear the retry counter if we reach this step. In case we plan to use the retry on other context, we should clear it at after the last retryable step.
		delete_post_meta( $attachment_id, self::RETRYABLE_META_COUNTER );
		$decoded_response = json_decode( $generate_url_response['body'], true );

		// Update the offload limit if it has changed.
		if ( isset( $decoded_response['limit'] ) && isset( $decoded_response['count'] ) ) {
			$remote_limit = absint( $decoded_response['limit'] );

			self::$instance->settings->update( 'offload_limit', $remote_limit );

			$current_run = self::get_process_meta();
			$remaining = isset( $current_run['remaining'] ) ? absint( $current_run['remaining'] ) : 0;

			if ( $remaining + $decoded_response['count'] >= $remote_limit ) {
				if ( OPTML_DEBUG_MEDIA ) {
					do_action( 'optml_log', 'limit exceeded' );
					do_action( 'optml_log', $decoded_response );
				}

				self::$instance->settings->update( 'offload_limit_reached', 'enabled' );
				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Offload stopped: offloading images would exceed limit.' );

				return $meta;
			}
		}

		if ( ! isset( $decoded_response['tableId'] ) || ! isset( $decoded_response['uploadUrl'] ) ) {
			if ( OPTML_DEBUG_MEDIA ) {
				do_action( 'optml_log', ' missing table id or upload url' );
				do_action( 'optml_log', $decoded_response );
			}
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has invalid table id or upload url.' );
			return $meta;
		}
		$table_id = $decoded_response['tableId'];
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', ' table id' );
			do_action( 'optml_log', $table_id );
		}
		$upload_signed_url = $decoded_response['uploadUrl'];
		$image = file_get_contents( $local_file );
		if ( $image === false ) {
			do_action( 'optml_log', 'can not find file' );
			do_action( 'optml_log', $local_file );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has missing file.' );
			return $meta;
		}
		if ( $upload_signed_url !== 'found_resource' ) {

			$request = new Optml_Api();
			$result = $request->upload_image( $upload_signed_url, $content_type, $image );

			if ( is_wp_error( $result ) || wp_remote_retrieve_response_code( $result ) !== 200 ) {
				do_action( 'optml_log', 'upload error' );
				do_action( 'optml_log', $result );
				update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has upload error.' );
				return $meta;
			}
			$file_size = filesize( $local_file );
			if ( $file_size === false ) {
				$file_size = 0;
			}
			$request = new Optml_Api();
			$result_update = $request->call_upload_api(
				$original_url,
				'false',
				$table_id,
				'success',
				'false',
				$meta['width'],
				$meta['height'],
				$file_size
			);
			if ( is_wp_error( $result_update ) || wp_remote_retrieve_response_code( $result_update ) !== 200 ) {
				do_action( 'optml_log', 'dynamo update error' );
				do_action( 'optml_log', $result_update );
				update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

				self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has dynamo update error.' );
				return $meta;
			}
		}
		$url_to_append = $original_url;
		$url_parts     = parse_url( $original_url );

		if ( isset( $url_parts['scheme'] ) && isset( $url_parts['host'] ) ) {
			$url_to_append = $url_parts['scheme'] . '://' . $url_parts['host'] . '/' . $file_name;
		}
		$optimized_url = $this->get_media_optimized_url( $url_to_append, $table_id );
		$request = new Optml_Api();
		if ( $request->check_optimized_url( $optimized_url ) === false ) {
			do_action( 'optml_log', 'optimization error' );
			do_action( 'optml_log', $optimized_url );
			$request->call_upload_api( $original_url, 'true', $table_id );
			update_post_meta( $attachment_id, self::META_KEYS['offload_error'], 'true' );

			self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has optimization error.' );
			return $meta;
		}
		unlink( $local_file );
		update_post_meta( $attachment_id, self::META_KEYS['offloaded'], 'true' );
		update_post_meta( $attachment_id, self::OM_OFFLOADED_FLAG, true );
		$meta['file'] = '/' . self::KEYS['uploaded_flag'] . $table_id . '/' . $url_to_append;

		if ( isset( $meta['sizes'] ) ) {
			foreach ( $meta['sizes'] as $key => $value ) {
				$generated_image_size_path = str_replace( $original_name, $meta['sizes'][ $key ]['file'], $local_file );
				file_exists( $generated_image_size_path ) && unlink( $generated_image_size_path );
				$meta['sizes'][ $key ]['file'] = $file_name;
			}
		}

		// This is needed for scaled images.
		// Otherwise, `-scaled` images will be left behind.
		if ( isset( $meta['original_image'] ) ) {
			$ext         = $this->get_ext( $local_file );
			$scaled_path = str_replace( '.' . $ext, '-scaled.' . $ext, $local_file );
			file_exists( $scaled_path ) && unlink( $scaled_path );
		}

		$duplicated_images = apply_filters( 'optml_offload_duplicated_images', [], $attachment_id );

		if ( is_array( $duplicated_images ) && ! empty( $duplicated_images ) ) {
			foreach ( $duplicated_images as $duplicated_id ) {
				$duplicated_meta = wp_get_attachment_metadata( $duplicated_id );
				if ( isset( $duplicated_meta['file'] ) && ! self::is_uploaded_image( $duplicated_meta['file'] ) ) {
					$duplicated_meta['file'] = $meta['file'];
					if ( $duplicated_meta['sizes'] ) {
						foreach ( $meta['sizes'] as $key => $value ) {
							$duplicated_meta['sizes'][ $key ]['file'] = $file_name;
						}
					}
					wp_update_attachment_metadata( $duplicated_id, $duplicated_meta );
					update_post_meta( $duplicated_id, self::META_KEYS['offloaded'], 'true' );
					update_post_meta( $attachment_id, self::OM_OFFLOADED_FLAG, true );
				}
			}
		}
		if ( OPTML_DEBUG_MEDIA ) {
			do_action( 'optml_log', 'success offload' );
		}

		self::decrement_process_meta_remaining();
		self::$instance->logger->add_log( Optml_Logger::LOG_TYPE_OFFLOAD, 'Image ID: ' . $attachment_id . ' has been offloaded.' );
		$attachment_page_id = wp_get_post_parent_id( $attachment_id );

		if ( $attachment_page_id !== false && $attachment_page_id !== 0 ) {
			self::$offload_update_post = true;
			update_post_meta( $attachment_page_id, self::POST_OFFLOADED_FLAG, 'true' );
			self::$offload_update_post = false;
		}
		return $meta;
	}

	/**
	 * Get the args for wp query according to the scope.
	 *
	 * @param int    $batch Number of images to get.
	 * @param string $action The action for which to get the images.
	 * @return array|false The query options array or false if not passed a valid action.
	 */
	public static function get_images_or_pages_query_args( $batch, $action, $get_images = false ) {

		$args = [
			'posts_per_page'      => $batch,
			'fields'              => 'ids',
			'ignore_sticky_posts' => false,
			'no_found_rows'       => true,
		];

		if ( $get_images === true ) {
			$args['post_type'] = 'attachment';
			$args['post_mime_type'] = 'image';
			$args['post_status'] = 'inherit';

			// Offload args.
			if ( $action === 'offload_images' ) {
				$args['meta_query'] = [
					'relation' => 'AND',
					[
						'key'     => self::META_KEYS['offloaded'],
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => self::META_KEYS['offload_error'],
						'compare' => 'NOT EXISTS',
					],
				];
				return $args;
			}

			// Rollback args.
			$args['meta_query'] = [
				'relation' => 'AND',
				[
					'key'     => self::META_KEYS['offloaded'],
					'value'   => 'true',
					'compare' => '=',
				],
				[
					'key'     => self::META_KEYS['rollback_error'],
					'compare' => 'NOT EXISTS',
				],
				[
					'key'     => Optml_Dam::OM_DAM_IMPORTED_FLAG,
					'compare' => 'NOT EXISTS',
				],
			];

			return $args;
		}

		$args       = self::add_page_meta_query_args( $action, $args );
		$post_types = array_filter(
			get_post_types(),
			function ( $post_type ) {
				if ( $post_type === 'attachment' || $post_type === 'revision' ) {
					return false;
				}

				return true;
			}
		);

		$args ['post_type'] = array_values( $post_types );

		return $args;
	}

	/**
	 *  Query the database and upload images to our servers.
	 *
	 * @param int $batch Number of images to process in a batch.
	 * @return array Number of found images and number of successfully processed images.
	 */
	public function upload_images( $batch, $images = [] ) {
		self::$instance->settings->update( 'offload_limit_reached', 'disabled' );

		if ( empty( $images ) || $images === 'none' ) {
			$args = self::get_images_or_pages_query_args( $batch, 'offload_images', true );
			$attachments = new \WP_Query( $args );
			$ids = $attachments->get_posts();
		} else {
			$ids = array_slice( $images, 0, $batch );
		}
		$result = [ 'found_images' => count( $ids ) ];
		$result['success_offload'] = $this->upload_and_update_existing_images( $ids );
		return $result;
	}

	/**
	 *  Query the database and bring back image to media library.
	 *
	 * @param int $batch Number of images to process in a batch.
	 * @return array Number of found images and number of successfully processed images.
	 */
	public function rollback_images( $batch, $images = [] ) {
		if ( empty( $images ) || $images === 'none' ) {
			$args = self::get_images_or_pages_query_args( $batch, 'rollback_images', true );
			$attachments = new \WP_Query( $args );
			$ids = $attachments->get_posts();
		} else {
			$ids = array_slice( $images, 0, $batch );
		}
		$result = [ 'found_images' => count( $ids ) ];
		$result['success_rollback'] = $this->rollback_and_update_images( $ids );
		return $result;
	}

	/**
	 * Update the post with the given id, the images will be updated by the filters we use.
	 *
	 * @param int $post_id The post id to update.
	 * @return bool Whether the update was succesful or not.
	 */
	public function update_page( $post_id ) {
		self::$offload_update_post = true;
		$post_update = wp_update_post( ['ID' => $post_id] );
		self::$offload_update_post = false;
		if ( $post_update === 0 ) {
			return false;
		}
		do_action( 'optml_updated_post', $post_id );
		return true;
	}

	/**
	 *  Calculate the number of images in media library and the number of posts/pages.
	 *
	 * @param string $action The actions for which to get the number of images.
	 * @return int Number of images.
	 */
	public static function number_of_images_and_pages( $action ) {
		$images_args = self::get_images_or_pages_query_args( -1, $action, true );

		$images = new \WP_Query( $images_args );

		// With the new mechanism, when offloading images, we don't need to address pages anymore.
		// Bail early with the number of images.
		if ( $action === 'offload_images' ) {
			return $images->post_count;
		}

		$pages_args = self::get_images_or_pages_query_args( -1, $action );
		$pages = new \WP_Query( $pages_args );

		return $pages->post_count + $images->post_count;
	}

	/**
	 *  Calculate the number of images in media library and the number of posts/pages by IDs.
	 *
	 * @param string $action The actions for which to get the number of images.
	 * @return int Number of images.
	 */
	public static function number_of_images_by_ids( $action, $ids ) {
		$args             = self::get_images_or_pages_query_args( - 1, $action, true );
		$args['post__in'] = $ids;
		$images           = new \WP_Query( $args );

		return $images->post_count;
	}

	/**
	 * Get pages that contain images by IDs.
	 *
	 * @param string $action The actions for which to get the number of images.
	 * @param array  $images Image IDs.
	 * @param int    $batch Batch count.
	 * @param int    $page Page number.
	 */
	public static function get_posts_by_image_ids( $action, $images = [], $batch = 10, $page = 1 ) {
		if ( empty( $images ) ) {
			return [];
		}

		$transient_key = 'optml_images_' . md5( serialize( $images ) );
		$transient = get_transient( $transient_key );

		if ( false !== $transient ) {
			return array_slice( $transient, ( $page - 1 ) * $batch, $batch );
		}

		global $wpdb;

		$image_urls = array_map(
			function( $image_id ) {
				$meta = wp_get_attachment_metadata( $image_id );
				$extension = Optml_Media_Offload::instance()->get_ext( $meta['file'] );

				return str_replace( '.' . $extension, '', $meta['file'] );
			},
			$images
		);

		// Sanitize the image URLs for use in the SQL query.
		$urls = array_map( 'esc_url_raw', $image_urls );

		// Initialize an empty string to hold the query.
		$query = '';

		// Iterate through the array and add each URL to the query.
		foreach ( $urls as $index => $url ) {
			// If it's the first item, we don't need to add OR to the beginning.
			if ( $index === 0 ) {
				$query .= $wpdb->prepare( 'post_content LIKE %s', '%' . $wpdb->esc_like( $url ) . '%' );
			} else {
				$query .= $wpdb->prepare( ' OR post_content LIKE %s', '%' . $wpdb->esc_like( $url ) . '%' );
			}
		}

		// Get all the posts IDs by using LIMIT and offset in a loop.
		$ids = [];
		$offset = 0;
		$limit = $batch;

		while ( true ) {
			$posts = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT ID FROM $wpdb->posts WHERE $query LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$limit,
					$offset
				)
			);

			if ( empty( $posts ) ) {
				break;
			}

			$ids = array_merge( $ids, $posts );
			$offset += $limit;
		}

		set_transient( $transient_key, $ids, HOUR_IN_SECONDS );

		return array_slice( $ids, ( $page - 1 ) * $batch, $batch );
	}

	/**
	 * Record process meta,
	 *
	 * @param int $count The number of images to process.
	 *
	 * @return void
	 */
	public static function record_process_meta( $count ) {
		$meta = get_option( 'optml_process_meta', [] );
		$meta['count'] = $count;
		$meta['remaining'] = $count;
		$meta['start_time'] = time();
		update_option( 'optml_process_meta', $meta );
	}

	/**
	 * Update the process meta count.
	 *
	 * @return void
	 */
	public static function decrement_process_meta_remaining() {
		$meta = get_option( 'optml_process_meta', [] );

		if ( ! isset( $meta['remaining'] ) ) {
			return;
		}

		$meta['remaining'] = $meta['remaining'] - 1;
		update_option( 'optml_process_meta', $meta );
	}

	/**
	 * Get process meta,
	 *
	 * @return array
	 */
	public static function get_process_meta() {
		$res = [];
		$meta = get_option( 'optml_process_meta', [] );
		$res['time_passed'] = isset( $meta['start_time'] ) ? ( time() - $meta['start_time'] ) / 60 : 0;
		$res['count'] = isset( $meta['count'] ) ? $meta['count'] : 0;
		$res['remaining'] = isset( $meta['remaining'] ) ? $meta['remaining'] : $res['count'];
		return $res;
	}

	/**
	 * Calculate the number of images in media library and the number of posts/pages.
	 *
	 * @param string $action The actions for which to get the number of images.
	 * @param bool   $refresh Whether to refresh the cron or not.
	 * @param array  $images The images to process.
	 *
	 * @return array Image count and Cron status.
	 */
	public static function get_image_count( $action, $refresh, $images = [] ) {
		$option = 'offload_images' === $action ? 'offloading_status' : 'rollback_status';
		$count = 0;
		$step  = 0;
		$batch = apply_filters( 'optimole_offload_batch', 20 ); // Reduce this to smaller if we have memory issues during testing.

		if ( empty( $images ) ) {
			$count = Optml_Media_Offload::number_of_images_and_pages( $action );
		} else {
			$count = Optml_Media_Offload::number_of_images_by_ids( $action, $images );
		}

		$possible_batch = ceil( $count / 10 );

		if ( $possible_batch < $batch ) {
			$batch = $possible_batch;
		}

		// If batch is less than 10, set it to 10.
		if ( $batch < 10 ) {
			$batch = 10;
		}

		$in_progress = self::$instance->settings->get( $option ) !== 'disabled';

		if ( $count === 0 ) {
			$in_progress = false;
		}

		$type = 'offload_images' === $action ? 'offload' : 'rollback';

		if ( false === $refresh ) {
			self::$instance->settings->update( 'offload_limit_reached', 'disabled' );
			self::record_process_meta( $count );

			self::$instance->settings->update( $option, $in_progress ? 'enabled' : 'disabled' );
			self::$instance->logger->add_log( $type, Optml_Logger::LOG_SEPARATOR );
			self::$instance->logger->add_log( $type, 'Started with a total count of ' . intval( $count ) . '.' );

			if ( $in_progress !== true ) {
				return [
					'count'  => $count,
					'status' => $in_progress,
					'action' => $type,
				];
			}

			if ( empty( $images ) ) {
				$total = ceil( $count / $batch );
				self::schedule_action(
					time(),
					'optml_start_processing_images',
					[
						$action,
						$batch,
						1,
						$total,
						$step,
					]
				);
			} else {
				self::schedule_action(
					time(),
					'optml_start_processing_images_by_id',
					[
						$action,
						$batch,
						1,
						$images,
					]
				);
			}
		}

		$response = [
			'count'  => $count,
			'action' => $type,
		];

		if ( $type === 'offload' ) {
			$offload_limit_reached = self::$instance->settings->is_offload_limit_reached();
			if ( $offload_limit_reached ) {
				$in_progress = false;
				self::$instance->settings->update( $option, 'disabled' );
			}

			$response['reached_limit'] = self::$instance->settings->is_offload_limit_reached();
			$response['offload_limit'] = self::$instance->settings->get( 'offload_limit' );
		}

		$response['status'] = $in_progress;

		return $response;
	}
	/**
	 * Schedule an action.
	 *
	 * @param int    $time The time to schedule the action.
	 * @param string $hook The hook to schedule.
	 * @param array  $args The arguments to pass to the hook.
	 *
	 * @return mixed
	 */
	public static function schedule_action( $time, $hook, $args ) {
		if ( self::$instance->settings->is_offload_limit_reached() ) {
			return null;
		}

		// We use AS if available to avoid issues with WP Cron.
		if ( function_exists( 'as_schedule_single_action' ) ) {
			return as_schedule_single_action( $time, $hook, $args );
		} else {
			return wp_schedule_single_event( $time, $hook, $args );
		}
	}
	/**
	 * Start Processing Images by IDs
	 *
	 * @param string $action The action for which to get the number of images.
	 * @param int    $batch  The batch of images to process.
	 * @param int    $page   The page of images to process.
	 * @param array  $image_ids The images to process.
	 *
	 * @return void
	 */
	public function start_processing_images_by_id( $action, $batch, $page, $image_ids = [] ) {
		$option = 'offload_images' === $action ? 'offloading_status' : 'rollback_status';
		$type   = 'offload_images' === $action ? Optml_Logger::LOG_TYPE_OFFLOAD : Optml_Logger::LOG_TYPE_ROLLBACK;

		if ( self::$instance->settings->get( $option ) === 'disabled' ) {
			return;
		}

		set_time_limit( 0 );

		// Only use the legacy offloaded attachments to query the pages that need to be updated.
		// We can be confident that these IDs are already marked as offloaded.
		$legacy_offloaded = array_filter(
			$image_ids,
			function( $id ) {
				return ! $this->is_new_offloaded_attachment( $id );
			}
		);

		// On the new mechanism, we don't update posts anymore when offloading.
		$page_in = $action === 'offload_images' ? [] : Optml_Media_Offload::get_posts_by_image_ids( $action, $legacy_offloaded, $batch, $page );

		if ( empty( $image_ids ) && empty( $page_in ) && empty( $legacy_offloaded ) ) {
			$meta = self::get_process_meta();
			self::$instance->logger->add_log( $type, 'Process finished with ' . $meta['count'] . ' items in ' . $meta['time_passed'] . ' minutes.' );

			self::$instance->settings->update( $option, 'disabled' );
			return;
		}

		try {
			// This will be 0 in the case of offloading now.
			if ( $action === 'rollback_images' && 0 !== count( $page_in ) ) {
				$to_update = Optml_Media_Offload::instance()->update_content( $page, $action, $batch, $page_in );

				if ( isset( $to_update['page'] ) ) {
					if ( isset( $to_update['imagesToUpdate'] ) && count( $to_update['imagesToUpdate'] ) ) {
						foreach ( $to_update['imagesToUpdate'] as $post_id => $images ) {
							if ( ! empty( $image_ids ) ) {
								$images = array_intersect( $images, $image_ids );
							}

							if ( empty( $images ) ) {
								continue;
							}

							Optml_Media_Offload::instance()->rollback_and_update_images( $images );
							Optml_Media_Offload::instance()->update_page( $post_id );
						}
					}
				}

				$page = $page + 1;
			} else {
				// From $image_ids get the number as per $batch and save it in $page_in and update $images with the remaining images.
				$images    = array_slice( $image_ids, 0, $batch );
				$image_ids = array_slice( $image_ids, $batch );
				$action === 'rollback_images' ?
					Optml_Media_Offload::instance()->rollback_images( $batch, $images ) :
					Optml_Media_Offload::instance()->upload_images( $batch, $images );
			}

			self::schedule_action(
				time(),
				'optml_start_processing_images_by_id',
				[
					$action,
					$batch,
					$page,
					$image_ids,
				]
			);
		} catch ( Exception $e ) {
			// Reschedule the cron to run again after a delay. Sometimes memory limit is exhausted.
			$delay_in_seconds = 10;
			self::$instance->logger->add_log( $type, $e->getMessage() );

			self::schedule_action(
				time() + $delay_in_seconds,
				'optml_start_processing_images_by_id',
				[
					$action,
					$batch,
					$page,
					$image_ids,
				]
			);
		}
	}

	/**
	 * Start Processing Images
	 *
	 * @param string $action The action for which to get the number of images.
	 * @param int    $batch  The batch of images to process.
	 * @param int    $page   The page of images to process.
	 * @param int    $total  The total number of pages.
	 * @param int    $step   The current step.
	 *
	 * @return void
	 */
	public function start_processing_images( $action, $batch, $page, $total, $step ) {
		$option = 'offload_images' === $action ? 'offloading_status' : 'rollback_status';
		$type   = 'offload_images' === $action ? 'offload' : 'rollback';

		if ( self::$instance->settings->get( $option ) === 'disabled' ) {
			return;
		}

		if ( $step > $total || 0 === $total ) {
			$meta = self::get_process_meta();
			self::$instance->logger->add_log( $type, 'Process finished with ' . $meta['count'] . ' items in ' . $meta['time_passed'] . ' minutes.' );

			self::$instance->settings->update( $option, 'disabled' );

			self::$instance->settings->update( 'show_offload_finish_notice', $type );

			return;
		}

		set_time_limit( 0 );

		try {
			$posts_to_update = $action === 'offload_images' ? [] : Optml_Media_Offload::instance()->update_content( $page, $action, $batch );

			// Kept for backward compatibility with old offloading mechanism where pages were modified.
			if ( $action === 'rollback_images' && isset( $posts_to_update['page'] ) && $posts_to_update['page'] > $page ) {
				$page = $posts_to_update['page'];
				if ( isset( $posts_to_update['imagesToUpdate'] ) && count( $posts_to_update['imagesToUpdate'] ) ) {
					foreach ( $posts_to_update['imagesToUpdate'] as $post_id => $images ) {
						Optml_Media_Offload::instance()->rollback_and_update_images( $images );
						Optml_Media_Offload::instance()->update_page( $post_id );
					}
				}
			} else {
				$action === 'rollback_images' ? Optml_Media_Offload::instance()->rollback_images( $batch ) : Optml_Media_Offload::instance()->upload_images( $batch );
			}

			$step = $step + 1;

			self::schedule_action(
				time(),
				'optml_start_processing_images',
				[
					$action,
					$batch,
					$page,
					$total,
					$step,
				]
			);
		} catch ( Exception $e ) {
			// Reschedule the cron to run again after a delay. Sometimes memory limit is exausted.
			$delay_in_seconds = 10;
			self::$instance->logger->add_log( $type, $e->getMessage() );

			self::schedule_action(
				time() + $delay_in_seconds,
				'optml_start_processing_images',
				[
					$action,
					$batch,
					$page,
					$total,
					$step,
				]
			);
		}
	}

	/**
	 * Alter attachment image src for offloaded images.
	 *
	 * @param array|false  $image {
	 *      Array of image data.
	 *
	 * @type string $0 Image source URL.
	 * @type int    $1 Image width in pixels.
	 * @type int    $2 Image height in pixels.
	 * @type bool   $3 Whether the image is a resized image.
	 * }
	 *
	 * @param int          $attachment_id attachment id.
	 * @param string|int[] $size image size.
	 * @param bool         $icon Whether the image should be treated as an icon.
	 *
	 * @return array $image.
	 */
	public function alter_attachment_image_src( $image, $attachment_id, $size, $icon ) {
		if ( ! $this->is_new_offloaded_attachment( $attachment_id ) ) {
			return $image;
		}

		$url       = get_post( $attachment_id );
		$url       = $url->guid;
		$image_url = $this->get_new_offloaded_attachment_url( $url, $attachment_id );
		$metadata  = wp_get_attachment_metadata( $attachment_id );

		// Use the original size if the requested size is full.
		if ( $size === 'full' || $this->is_attachment_edit_page( $attachment_id ) ) {
			$image_url = $this->get_new_offloaded_attachment_url(
				$url,
				$attachment_id,
				[
					'width' => $metadata['width'],
					'height' => $metadata['height'],
					'attachment_id' => $attachment_id,
				]
			);

			return [
				$image_url,
				$metadata['width'],
				$metadata['height'],
				false,
			];
		}

		$crop = false;

		// Size can be int [] containing width and height.
		if ( is_array( $size ) ) {
			$width  = $size[0];
			$height = $size[1];
			$crop   = true;
		} else {
			$sizes = $this->get_all_image_sizes();

			if ( ! isset( $sizes[ $size ] ) ) {
				return [
					$image_url,
					$metadata['width'],
					$metadata['height'],
					false,
				];
			}

			$width  = $sizes[ $size ]['width'];
			$height = $sizes[ $size ]['height'];
			$crop   = is_array( $sizes[ $size ]['crop'] ) ? $sizes[ $size ]['crop'] : (bool) $sizes[ $size ]['crop'];
		}

		$sizes2crop = self::size_to_crop();

		if ( wp_attachment_is( 'video', $attachment_id ) && doing_action( 'wp_insert_post_data' ) ) {
			return $image;
		}

		$resize = apply_filters( 'optml_default_crop', [] );
		$data = image_get_intermediate_size( $attachment_id, $size );

		if ( is_array( $data ) && isset( $data['width'] ) && isset( $data['height'] ) ) { // @phpstan-ignore-line - these both exist.
			if ( isset( $sizes2crop[ $data['width'] . $data['height'] ] ) ) {
				$resize = $this->to_optml_crop( $sizes2crop[ $data['width'] . $data['height'] ] );
			}
		}

		if ( $crop !== false ) {
			$resize = $this->to_optml_crop( $crop );
		}

		$image_url = $this->get_new_offloaded_attachment_url( $url, $attachment_id, ['width' => $width, 'height' => $height, 'resize' => $resize, 'attachment_id' => $attachment_id] );

		return [
			$image_url,
			$width,
			$height,
			$crop,
		];
	}

	/**
	 * Needed for image sizes inside the editor.
	 *
	 * @param array       $response Array of prepared attachment data. @see wp_prepare_attachment_for_js().
	 * @param WP_Post     $attachment Attachment object.
	 * @param array|false $meta Array of attachment meta data, or false if there is none.
	 *
	 * @return array
	 */
	public function alter_attachment_for_js( $response, $attachment, $meta ) {
		if ( ! $this->is_new_offloaded_attachment( $attachment->ID ) ) {
			return $response;
		}

		$sizes = $this->get_all_image_sizes();

		foreach ( $sizes as $size => $args ) {
			if ( isset( $response['sizes'][ $size ] ) ) {
				continue;
			}

			$args = [
				'height' => $args['height'],
				'width'  => $args['width'],
				'crop'   => true,
			];

			$response['sizes'][ $size ] = array_merge(
				$args,
				[
					'url'         => $this->get_new_offloaded_attachment_url( $response['url'], $attachment->ID, $args ),
					'orientation' => ( $args['height'] > $args['width'] ) ? 'portrait' : 'landscape',
				]
			);
		}

		$url_args = [
			'height' => $response['height'],
			'width'  => $response['width'],
			'crop'   => false,
		];

		$response['url'] = $this->get_new_offloaded_attachment_url( $response['url'], $attachment->ID, $url_args );

		return $response;
	}

	/**
	 * Alter attachment metadata.
	 *
	 * @param array $metadata The attachment metadata.
	 * @param int   $id The attachment ID.
	 *
	 * @return array
	 */
	public function alter_attachment_metadata( $metadata, $id ) {
		if ( ! $this->is_new_offloaded_attachment( $id ) ) {
			return $metadata;
		}

		return $this->get_altered_metadata_for_remote_images( $metadata, $id );
	}

	/**
	 * Get offloaded image attachment URL for new offloads.
	 *
	 * @param string $url The initial attachment URL.
	 * @param int    $attachment_id The attachment ID.
	 * @param array  $args The additional arguments.
	 *                     - width: The width of the image.
	 *                     - height: The height of the image.
	 *                     - crop: Whether to crop the image.
	 *
	 * @return string
	 */
	private function get_new_offloaded_attachment_url( $url, $attachment_id, $args = [] ) {
		$process_flag = self::KEYS['not_processed_flag'] . $attachment_id;

		// Image might have already passed through this filter.
		if ( strpos( $url, $process_flag ) !== false ) {
			return $url;
		}

		$meta = wp_get_attachment_metadata( $attachment_id );
		if ( ! isset( $meta['file'] ) ) {
			return $url;
		}

		$default_args = [
			'width'  => 'auto',
			'height' => 'auto',
			'resize' => apply_filters( 'optml_default_crop', [] ),
		];

		$args = wp_parse_args( $args, $default_args );
		// If this is not cropped, we constrain the dimensions to the original image.
		if ( empty( $args['resize'] ) && ! in_array( 'auto', [ $args['width'], $args['height'] ], true ) ) {
			$dimensions     = wp_constrain_dimensions( $meta['width'], $meta['height'], $args['width'], $args['height'] );
			$args['width']  = $dimensions[0];
			$args['height'] = $dimensions[1];
		}

		$file = $meta['file'];
		if ( self::is_uploaded_image( $file ) ) {
			$optimized_url = ( new Optml_Image(
				$url,
				[
					'width'         => $args['width'],
					'height'        => $args['height'],
					'quality'       => $this->settings->get_numeric_quality(),
					'resize'        => $args['resize'],
					'attachment_id' => $attachment_id,
				],
				$this->settings->get( 'cache_buster' )
			) )->get_url();

			if ( strpos( $optimized_url, $process_flag ) !== false ) {
				return $optimized_url;
			}

			$process_flag = $process_flag . $file;

			return str_replace( '/' . $url, '/' . $process_flag, $optimized_url );
		} else {
			// this is for the users that already offloaded the images before the other fixes
			$local_file = get_attached_file( $attachment_id );
			if ( ! file_exists( $local_file ) ) {
				$duplicated_images = apply_filters( 'optml_offload_duplicated_images', [], $attachment_id );
				if ( is_array( $duplicated_images ) && ! empty( $duplicated_images ) ) {
					foreach ( $duplicated_images as $id ) {
						if ( ! empty( $id ) ) {
							$duplicated_meta = wp_get_attachment_metadata( $id );
							if ( isset( $duplicated_meta['file'] ) && self::is_uploaded_image( $duplicated_meta['file'] ) ) {
								$optimized_url = ( new Optml_Image(
									$url,
									[
										'width'   => $args['width'],
										'height'  => $args['height'],
										'quality' => $this->settings->get_numeric_quality(),
										'attachment_id' => $attachment_id,
									],
									$this->settings->get( 'cache_buster' )
								) )->get_url();
								return $optimized_url;
							}
						}
					}
				}
			}
		}
		return $url;
	}

	/**
	 * Replace the URLs in the editor content with the offloaded ones.
	 *
	 * @param string $content The incoming content.
	 *
	 * @return string
	 */
	public function replace_urls_in_editor_content( $content ) {
		$raw_extracted = Optml_Main::instance()->manager->extract_urls_from_content( $content );

		if ( empty( $raw_extracted ) ) {
			return $content;
		}

		$to_replace = [];
		foreach ( $raw_extracted as $url ) {
			$attachment = $this->get_local_attachement_id_from_url( $url );

			// No local attachment.
			if ( $attachment['attachment_id'] === 0 ) {
				continue;
			}

			$attachment_id = $attachment['attachment_id'];

			// Not offloaded.
			if ( ! $this->is_new_offloaded_attachment( $attachment_id ) ) {
				continue;
			}

			// Get W/H from url.
			$size   = $this->parse_dimensions_from_filename( $url );
			$width  = $size[0] !== false ? $size[0] : 'auto';
			$height = $size[1] !== false ? $size[1] : 'auto';

			// Handle resize.
			$sizes2crop = self::size_to_crop();
			$resize     = apply_filters( 'optml_default_crop', [] );
			$sizes      = image_get_intermediate_size( $attachment_id, $size );
			if ( false !== $sizes ) {
				if ( isset( $sizes2crop[ $width . $height ] ) ) {
					$resize = $this->to_optml_crop( $sizes2crop[ $width . $height ] );
				}
			}

			// Build the optimized URL.
			$optimized_url = ( new Optml_Image(
				$url,
				[
					'width'         => $width,
					'height'        => $height,
					'quality'       => $this->settings->get_numeric_quality(),
					'resize'        => $resize,
					'attachment_id' => $attachment_id,
				],
				$this->settings->get( 'cache_buster' )
			) )->get_url();

			// Drop any image size from the URL.
			$optimized_url = str_replace( '-' . $width . 'x' . $height, '', $optimized_url );

			$to_replace[ $url ] = $optimized_url;
		}

		return str_replace( array_keys( $to_replace ), array_values( $to_replace ), $content );
	}

	/**
	 * Replaces the post content URLs to use Offloaded ones on editor fetch.
	 *
	 * @param \WP_REST_Response $response The response object.
	 * @param \WP_Post          $post The post object.
	 * @param \WP_REST_Request  $request The request object.
	 *
	 * @return \WP_REST_Response
	 */
	public function pre_filter_rest_content( \WP_REST_Response $response, \WP_Post $post, \WP_REST_Request $request ) {
		$context = $request->get_param( 'context' );

		if ( $context !== 'edit' ) {
			return $response;
		}

		$data = $response->get_data();

		// Actually replace all URLs.
		$data['content']['raw'] = $this->replace_urls_in_editor_content( $data['content']['raw'] );

		$response->set_data( $data );

		return $response;
	}

	/**
	 * Legacy function to be used for WordPress versions under 6.0.0.
	 *
	 * @param array $post_data Slashed, sanitized, processed post data.
	 * @param array $postarr Slashed sanitized post data.
	 * @param array $unsanitized_postarr Un-sanitized post data.
	 *
	 * @return array
	 */
	public function legacy_filter_saved_data( $post_data, $postarr, $unsanitized_postarr ) {
		return $this->filter_saved_data( $post_data, $postarr, $unsanitized_postarr, true );
	}

	/**
	 * Filter post content to use local attachments when saving offloaded images.
	 *
	 * @param array $post_data Slashed, sanitized, processed post data.
	 * @param array $postarr Slashed sanitized post data.
	 * @param array $unsanitized_postarr Un-sanitized post data.
	 * @param bool  $update Whether this is an existing post being updated or not.
	 *
	 * @return array
	 */
	public function filter_saved_data( $post_data, $postarr, $unsanitized_postarr, $update ) {
		if ( $postarr['post_status'] === 'trash' ) {
			return $post_data;
		}

		$content = $post_data['post_content'];

		$extracted = Optml_Main::instance()->manager->extract_urls_from_content( $content );
		$replace   = [];

		foreach ( $extracted as $idx => $url ) {
			$id = self::get_attachment_id_from_url( $url );

			if ( $id === false ) {
				continue;
			}

			$id = (int) $id;

			if ( $this->is_legacy_offloaded_attachment( $id ) ) {
				continue;
			}

			$replace[ $url ] = self::get_original_url( $id );

			$size = $this->parse_dimension_from_optimized_url( $url );

			if ( $size[0] === 'auto' || $size[1] === 'auto' ) {
				continue;
			}

			$extension = $this->get_ext( $url );
			$metadata  = wp_get_attachment_metadata( $id );

			// Is this the full URL.
			if ( $metadata['width'] === (int) $size[0] && $metadata['height'] === (int) $size[1] ) {
				continue;
			}

			$size_crop_map = self::size_to_crop();

			$crop = false;

			if ( isset( $size_crop_map[ $size[0] . $size[1] ] ) ) {
				$crop = $size_crop_map[ $size[0] . $size[1] ];
			}

			if ( $crop ) {
				$width  = $size[0];
				$height = $size[1];
			} else {
				// In case of an image size, we need to calculate the new dimensions for the proper file path.
				$constrained = wp_constrain_dimensions( $metadata['width'], $metadata['height'], $size[0], $size[1] );

				$width  = $constrained[0];
				$height = $constrained[1];
			}
			$replace[ $url ] = $this->maybe_strip_scaled( $replace[ $url ] );

			$suffix = sprintf( '-%sx%s.%s', $width, $height, $extension );

			$replace[ $url ] = str_replace( '.' . $extension, $suffix, $replace[ $url ] );
		}

		$post_data['post_content'] = str_replace( array_keys( $replace ), array_values( $replace ), $content );

		return $post_data;
	}

	/**
	 * Alter the image size for the image widget.
	 *
	 * @param string $html the attachment image HTML string.
	 * @param array  $settings       Control settings.
	 * @param string $image_size_key Optional. Settings key for image size.
	 *                               Default is `image`.
	 * @param string $image_key      Optional. Settings key for image. Default
	 *                               is null. If not defined uses image size key
	 *                               as the image key.
	 *
	 * @return string
	 */
	public function alter_elementor_image_size( $html, $settings, $image_size_key, $image_key ) {
		if ( ! isset( $settings['image'] ) ) {
			return $html;
		}

		$image = $settings['image'];

		if ( ! isset( $image['id'] ) ) {
			return $html;
		}

		if ( ! $this->is_new_offloaded_attachment( $image['id'] ) ) {
			return $html;
		}

		if ( ! isset( $settings['image_size'] ) ) {
			return $html;
		}

		if ( $settings['image_size'] === 'custom' ) {
			if ( ! isset( $settings['image_custom_dimension'] ) ) {
				return $html;
			}

			$custom_dimensions = $settings['image_custom_dimension'];

			if ( ! isset( $custom_dimensions['width'] ) || ! isset( $custom_dimensions['height'] ) ) {
				return $html;
			}

			$new_args = [
				'width'  => $custom_dimensions['width'],
				'height' => $custom_dimensions['height'],
				'resize' => $this->to_optml_crop( true ),
			];
			$new_url  = $this->get_new_offloaded_attachment_url( $image['url'], $image['id'], $new_args );

			return str_replace( $image['url'], $new_url, $html );
		}

		return $html;
	}

	/**
	 * Adds new actions for new offloads.
	 *
	 * @return void
	 */
	public function add_new_actions() {
		add_filter( 'wp_prepare_attachment_for_js', [ self::$instance, 'alter_attachment_for_js' ], 999, 3 );
		add_filter( 'wp_get_attachment_metadata', [ self::$instance, 'alter_attachment_metadata' ], 10, 2 );
		add_filter( 'wp_get_attachment_image_src', [ self::$instance, 'alter_attachment_image_src' ], 10, 4 );

		// Needed for rendering beaver builder css properly.
		add_filter( 'fl_builder_render_css', [self::$instance, 'replace_urls_in_editor_content'], 10, 1 );

		// Filter saved data on insert to use local attachments.
		// Backwards compatibility for older versions of WordPress < 6.0.0 requiring 3 parameters for this specific filter.
		$below_6_0_0 = version_compare( get_bloginfo( 'version' ), '6.0.0', '<' );
		if ( $below_6_0_0 ) {
			add_filter( 'wp_insert_post_data', [ self::$instance, 'legacy_filter_saved_data' ], 10, 3 );
		} else {
			add_filter( 'wp_insert_post_data', [ self::$instance, 'filter_saved_data' ], 10, 4 );
		}

		// Filter loaded data in the editors to use local attachments.
		add_filter( 'content_edit_pre', [self::$instance, 'replace_urls_in_editor_content'], 10, 1 );
		$types = get_post_types_by_support( 'editor' );
		foreach ( $types as $type ) {
			$post_type = get_post_type_object( $type );
			if ( property_exists( $post_type, 'show_in_rest' ) && true === $post_type->show_in_rest ) {
				add_filter(
					'rest_prepare_' . $type,
					[
						self::$instance,
						'pre_filter_rest_content',
					],
					10,
					3
				);
			}
		}

		add_filter( 'get_attached_file', [$this, 'alter_attached_file_response'], 10, 2 );
		add_filter(
			'elementor/image_size/get_attachment_image_html',
			[
				$this,
				'alter_elementor_image_size',
			],
			10,
			4
		);

	}

	/**
	 * Elementor checks if the file exists before requesting a specific image size.
	 *
	 * Needed because otherwise there won't be any width/height on the `img` tags, breaking lazyload.
	 *
	 * Also needed because some
	 *
	 * @param string $file The file path.
	 * @param int    $id The attachment ID.
	 *
	 * @return bool|string
	 */
	public function alter_attached_file_response( $file, $id ) {
		if ( ! $this->is_new_offloaded_attachment( $id ) ) {
			return $file;
		}

		$metadata = wp_get_attachment_metadata( $id );

		if ( isset( $metadata['file'] ) ) {
			$uploads = wp_get_upload_dir();

			return $uploads['basedir'] . '/' . $metadata['file'];
		}

		return true;
	}

	/**
	 * Maybe strip the `-scaled` from the URL.
	 *
	 * @param string $url The url.
	 *
	 * @return string
	 */
	public function maybe_strip_scaled( $url ) {
		$ext = $this->get_ext( $url );

		return str_replace( '-scaled.' . $ext, '.' . $ext, $url );
	}

	/**
	 * Is it a PHPUnit test run.
	 *
	 * @return bool
	 */
	public static function is_phpunit_test() {
		return defined( 'OPTML_PHPUNIT_TESTING' ) && OPTML_PHPUNIT_TESTING === true;
	}
}
