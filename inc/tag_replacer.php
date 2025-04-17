<?php

use OptimoleWP\Preload\Links;

/**
 * The class handles the img tag replacements.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Tag_Replacer extends Optml_App_Replacer {
	use Optml_Normalizer;
	use Optml_Validator;
	use Optml_Dam_Offload_Utils;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Tag_Replacer
	 */
	protected static $instance = null;

	/**
	 * The number of images skipped from lazyload.
	 *
	 * @var integer
	 */
	public static $lazyload_skipped_images = 0;

	/**
	 * Class instance method.
	 *
	 * @codeCoverageIgnore
	 * @static
	 * @since  1.0.0
	 * @access public
	 * @return Optml_Tag_Replacer
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			add_action( 'optml_replacer_setup', [ self::$instance, 'init' ] );
		}

		return self::$instance;
	}

	/**
	 * The initialize method.
	 */
	public function init() {
		parent::init();
		add_filter( 'optml_content_images_tags', [ $this, 'process_image_tags' ], 1, 2 );

		if ( ! $this->settings->use_lazyload() ) {
			add_filter( 'optml_tag_replace', [ $this, 'regular_tag_replace' ], 1, 6 );
		}
		add_filter( 'image_downsize', [ $this, 'filter_image_downsize' ], PHP_INT_MAX, 3 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'filter_srcset_attr' ], PHP_INT_MAX - 1, 5 );
		add_filter( 'wp_calculate_image_sizes', [ $this, 'filter_sizes_attr' ], 1, 2 );
		add_filter( 'wp_image_src_get_dimensions', [ $this, 'filter_image_src_get_dimensions' ], 99, 4 );
		if ( $this->settings->get( 'retina_images' ) === 'enabled' ) {
			add_filter( 'wp_get_attachment_image_attributes', [ $this, 'filter_attachment_image_attributes' ], 99, 3 );
		}
	}

	/**
	 * We have to short-circuit the logic that adds width and height to the img tag.
	 * It compares the URL basename, and the `file` param for each image.
	 * This happens for any image that gets its size set non-explicitly
	 * e.g. an image block with its size set from the sidebar to `thumbnail`).
	 *
	 * Optimole has a single basename for all image resizes in its URL.
	 *
	 * @param mixed $dimensions The dimensions of the image.
	 * @param mixed $image_src The source of the image.
	 * @param mixed $image_meta The meta of the image.
	 * @param mixed $attachment_id The ID of the attachment.
	 */
	public function filter_image_src_get_dimensions( $dimensions, $image_src, $image_meta, $attachment_id ) {

		list($width, $height) = $this->parse_dimension_from_optimized_url( $image_src );

		if ( false === $width || false === $height ) {
			return $dimensions;
		}
		$sizes = Optml_App_Replacer::image_sizes();

		// If this is an image size. Return its dimensions.
		foreach ( $sizes as $size => $args ) {
			if ( (int) $args['width'] !== (int) $width ) {
				continue;
			}

			if ( (int) $args['height'] !== (int) $height ) {
				continue;
			}

			return [
				$args['width'],
				$args['height'],
			];
		}

		// Fall-through with the original dimensions.
		return $dimensions;
	}
	/**
	 * Filter the attachment image attributes to add the srcset attribute with retina support.
	 *
	 * This is covering the case where the image has no sizes.
	 *
	 * @param array   $attr The attributes.
	 * @param WP_Post $attachment The attachment.
	 * @param string  $size The size.
	 */
	public function filter_attachment_image_attributes( $attr, $attachment, $size ) {
		if ( ! isset( $attr['srcset'] ) && isset( $attr['src'] ) && strpos( $attr['src'], '/w:' ) !== false ) {
			$attr['srcset'] = str_replace( '/w:', '/dpr:2/w:', $attr['src'] ) . ' 2x';
		}
		return $attr;
	}


	/**
	 *  Replace in given content the given image tags with video tags is image is gif
	 *
	 * @param string $image_url Image url to process.
	 * @param string $image_tag The tag to replace.
	 * @param string $content The content to process.
	 *
	 * @return bool
	 */
	public function img_to_video( $image_url, $image_tag, &$content ) {
		if ( $this->settings->get( 'img_to_video' ) === 'disabled' ) {
			return false;
		}

		if ( false === Optml_Filters::should_do_image( $image_tag, apply_filters( 'optml_gif_to_video_flags', [ 'lazyload' => true, 'placeholder' => true, 'original-src' => true ] ) ) ) {
			return false;
		}
		$link_mp4 = apply_filters(
			'optml_content_url',
			$image_url,
			[
				'width' => 'auto',
				'height' => 'auto',
				'format' => 'mp4',
			]
		);

		$link_png = apply_filters(
			'optml_content_url',
			$image_url,
			[
				'width' => 'auto',
				'height' => 'auto',
				'quality' => 'eco',
			]
		);

		$video_tag = $image_tag;

		$video_tag = str_replace(
			[
				'src=',
				'<img',
				'/>',
			],
			[
				'original-src=',
				'<video autoplay muted loop playsinline poster="' . $link_png . '"',
				'><source src="' . $link_mp4 . '" type="video/mp4"></video>',
			],
			$video_tag
		);
		$content = str_replace( $image_tag, $video_tag, $content );
		return true;
	}

	/**
	 * Method invoked by `optml_content_images_tags` filter.
	 *
	 * @param string $content The content to be processed.
	 * @param array  $images A list of images.
	 *
	 * @return mixed
	 */
	public function process_image_tags( $content, $images = [] ) {

		$image_sizes = self::image_sizes();
		$sizes2crop  = self::size_to_crop();
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'process_image_tags: ' . print_r( $images, true ) );
		}
		foreach ( $images[0] as $index => $tag ) {
			$width     = $height = false;
			$crop = null;
			$image_tag = $images['img_tag'][ $index ];

			$is_slashed = strpos( $images['img_url'][ $index ], '\/' ) !== false;

			$src = $tmp = $is_slashed ? $this->strip_slashes( $images['img_url'][ $index ] ) : $images['img_url'][ $index ];

			if ( strpos( $src, $this->upload_resource['content_path'] ) === 0 ) {
				$src = $tmp = untrailingslashit( $this->upload_resource['content_host'] ) . $src;

				$new_src                     = $is_slashed ? addcslashes( $src, '/' ) : $src;
				$image_tag                   = str_replace(
					[
						'"' . $images['img_url'][ $index ],
						"'" . $images['img_url'][ $index ],
					],
					[
						'"' . $new_src,
						"'" . $new_src,
					],
					$image_tag
				);
				$images['img_url'][ $index ] = $new_src;
			}
			if ( ( apply_filters( 'optml_ignore_image_link', false, $src ) ||
				! $this->can_replace_tag( $images['img_url'][ $index ], $tag ) ) && ! Optml_Media_Offload::is_not_processed_image( $src )
			) {
				continue; // @codeCoverageIgnore
			}
			$resize = apply_filters( 'optml_default_crop', [] );

			list( $width, $height, $resize ) = self::parse_dimensions_from_tag(
				$images['img_tag'][ $index ],
				$image_sizes,
				[
					'width'  => $width,
					'height' => $height,
					'resize' => $resize,
				]
			);
			if ( false === $width && false === $height ) {
				list( $width, $height, $crop ) = $this->parse_dimensions_from_filename( $tmp );
			}
			if ( empty( $resize ) && isset( $sizes2crop[ $width . $height ] ) ) {
				$resize = $this->to_optml_crop( $sizes2crop[ $width . $height ] );
			} elseif ( $crop === true ) {
				$resize = $this->to_optml_crop( $crop );
			}

			$optml_args = [ 'width' => $width, 'height' => $height, 'resize' => $resize ];

			$is_gif = $this->is_valid_gif( $images['img_url'][ $index ] );
			$should_lazy_gif = $is_gif ? $this->should_lazy_gif( $images['img_url'][ $index ], $optml_args ) : null;

			if ( $should_lazy_gif === true && $this->img_to_video( $images['img_url'][ $index ], $images['img_tag'][ $index ], $content ) ) {
				continue;
			}

			$tmp        = $this->strip_image_size_from_url( $tmp );
			$new_url    = apply_filters( 'optml_content_url', $tmp, $optml_args );

			$image_tag = str_replace(
				[
					'width="' . $width . '"',
					'width=\"' . $width . '\"',
					'height="' . $height . '"',
					'height=\"' . $height . '\"',
				],
				[
					'width="' . $optml_args['width'] . '"',
					'width=\"' . $optml_args['width'] . '\"',
					'height="' . $optml_args['height'] . '"',
					'height=\"' . $optml_args['height'] . '\"',
				],
				$image_tag
			);

			// If the image is in header or has a class excluded from lazyload or is an excluded gif, we need to do the regular replace.
			if ( $images['in_no_script'][ $index ] || $should_lazy_gif === false ) {

				$image_tag = $this->regular_tag_replace( $image_tag, $images['img_url'][ $index ], $new_url, $optml_args, $is_slashed, $tag );
			} else {
				$image_tag = apply_filters( 'optml_tag_replace', $image_tag, $images['img_url'][ $index ], $new_url, $optml_args, $is_slashed, $tag );
			}
			if ( strpos( $image_tag, 'data-opt-id=' ) === false ) {
				$image_tag = preg_replace( '/<img/im', '<img data-opt-id=' . $this->get_id_by_url( $images['img_url'][ $index ] ) . ' ', $image_tag );
			}

			if ( $priority = Links::is_preloaded( $this->get_id_by_url( $images['img_url'][ $index ] ) ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.Found
				Links::preload_tag( $image_tag, $priority );
			}

			if ( strpos( $image_tag, 'decoding=' ) === false ) {
				$image_tag = str_replace( 'data-opt-id=', 'decoding=async data-opt-id=', $image_tag );
			}
			$content = str_replace( $images['img_tag'][ $index ], $image_tag, $content );
		}
		return $content;
	}
	/**
	 * Check if we should lazyload a gif.
	 *
	 * @param string $url URL to check.
	 * @param array  $optml_args The width/height that we find for the image.
	 * @return bool Should we lazyload the gif ?
	 */
	public function should_lazy_gif( $url, $optml_args = [] ) {

		if ( strpos( $url, '/plugins/' ) !== false ) {
			return false;
		}
		if ( $this->is_valid_numeric( $optml_args['width'] ) && $this->is_valid_numeric( $optml_args['height'] ) && min( $optml_args['height'], $optml_args['width'] ) <= 20 ) {
			return false;
		}
		return true;
	}
	/**
	 * Check replacement is allowed for this tag.
	 *
	 * @param string $url Url.
	 * @param string $tag Html tag.
	 *
	 * @return bool We can replace?
	 */
	public function can_replace_tag( $url, $tag = '' ) {
		foreach ( self::possible_tag_flags() as $banned_string ) {
			if ( strpos( $tag, $banned_string ) !== false ) {
				self::$ignored_url_map[ crc32( $url ) ] = true;
				return false;
			}
		}
		return true;
	}

	/**
	 * Extract image dimensions from img tag.
	 *
	 * @param string $tag The HTML img tag.
	 * @param array  $image_sizes WordPress supported image sizes.
	 * @param array  $args Default args to use.
	 *
	 * @return array
	 */
	private function parse_dimensions_from_tag( $tag, $image_sizes, $args = [] ) {
		if ( preg_match( '#width=["|\']?([\d%]+)["|\']?#i', $tag, $width_string ) ) {
			if ( ctype_digit( $width_string[1] ) === true ) {
				$args['width'] = $width_string[1];
			}
		}
		if ( preg_match( '#height=["|\']?([\d%]+)["|\']?#i', $tag, $height_string ) ) {
			if ( ctype_digit( $height_string[1] ) === true ) {
				$args['height'] = $height_string[1];
			}
		}
		if ( preg_match( '#class=["|\']?[^"\']*size-([^"\'\s]+)[^"\']*["|\']?#i', $tag, $size ) ) {
			$size = array_pop( $size );

			if ( false === $args['width'] && false === $args['height'] && 'full' !== $size && array_key_exists( $size, $image_sizes ) ) {
				$args['width']  = (int) $image_sizes[ $size ]['width'];
				$args['height'] = (int) $image_sizes[ $size ]['height'];
			}
			if ( 'full' !== $size && array_key_exists( $size, $image_sizes ) ) {
				$args['resize'] = $this->to_optml_crop( $image_sizes[ $size ]['crop'] );
			}
		} else {
			$args['resize'] = apply_filters( 'optml_parse_resize_from_tag', [], $tag );
		}

		return [ $args['width'], $args['height'], $args['resize'] ];
	}

	/**
	 * Replaces the tags by default.
	 *
	 * @param string $new_tag The new tag.
	 * @param string $original_url The original URL.
	 * @param string $new_url The optimized URL.
	 * @param array  $optml_args Options passed for URL optimization.
	 * @param bool   $is_slashed Url needs to slashed.
	 * @param string $full_tag Full tag, wrapper included.
	 *
	 * @return string
	 */
	public function regular_tag_replace( $new_tag, $original_url, $new_url, $optml_args, $is_slashed = false, $full_tag = '' ) {
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'regular_tag_replace: ' . $original_url . ' ' . $new_url . $new_tag );
		}
		$pattern = '/(?<!\/)' . preg_quote( $original_url, '/' ) . '/i';
		$replace = $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;
		if ( $this->settings->get( 'lazyload' ) === 'enabled' && $this->settings->get( 'native_lazyload' ) === 'enabled'
			&& apply_filters( 'optml_should_load_eager', '__return_true' ) && ! $this->is_valid_gif( $original_url ) ) {
			if ( strpos( $new_tag, 'loading=' ) === false ) {
				$new_tag = preg_replace( '/<img/im', $is_slashed ? '<img loading=\"eager\"' : '<img loading="eager"', $new_tag );
			} else {
				$new_tag = $is_slashed ? str_replace( 'loading=\"lazy\"', 'loading=\"eager\"', $new_tag ) : str_replace( 'loading="lazy"', 'loading="eager"', $new_tag );
			}
		}
		if ( $this->settings->is_lazyload_type_viewport() && Optml_Manager::instance()->page_profiler->is_in_all_viewports( $this->get_id_by_url( $original_url ) ) ) {
			if ( OPTML_DEBUG ) {
				do_action( 'optml_log', 'Adding preload priority for image ' . $original_url . '|' . $this->get_id_by_url( $original_url ) );
			}
			$new_tag = preg_replace( '/<img/im', $is_slashed ? '<img fetchpriority=\"high\"' : '<img fetchpriority="high"', $new_tag );
			// collect ID for preload.
			Links::add_id( $this->get_id_by_url( $original_url ), 'high' );
		}
		if ( $this->settings->is_lazyload_type_viewport() && Optml_Manager::instance()->page_profiler->is_lcp_image_in_all_viewports( $this->get_id_by_url( $original_url ) ) ) {
			if ( OPTML_DEBUG ) {
				do_action( 'optml_log', 'Adding preload image is LCP ' . $original_url . '|' . $this->get_id_by_url( $original_url ) );
			}

			$new_tag = preg_replace( '/<img/im', $is_slashed ? '<img fetchpriority=\"high\"' : '<img fetchpriority="high"', $new_tag );
			Links::add_id( $this->get_id_by_url( $original_url ), 'high' );
		}
		// // If the image is between the first images we add the fetchpriority attribute to improve the LCP.
		if ( $this->settings->is_lazyload_type_fixed() && self::$lazyload_skipped_images < Optml_Lazyload_Replacer::get_skip_lazyload_limit() ) {
			if ( strpos( $new_tag, 'fetchpriority=' ) === false ) {
				$new_tag = preg_replace( '/<img/im', $is_slashed ? '<img fetchpriority=\"high\"' : '<img fetchpriority="high"', $new_tag );
			}
		}

		++self::$lazyload_skipped_images;
		return preg_replace( $pattern, $replace, $new_tag );
	}

	/**
	 * Replace image URLs in the srcset attributes and in case there is a resize in action, also replace the sizes.
	 *
	 * @param array  $sources Array of image sources.
	 * @param array  $size_array Array of width and height values in pixels (in that order).
	 * @param string $image_src The 'src' of the image.
	 * @param array  $image_meta The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int    $attachment_id Image attachment ID.
	 *
	 * @return array
	 */
	public function filter_srcset_attr( $sources = [], $size_array = [], $image_src = '', $image_meta = [], $attachment_id = 0 ) {

		if ( ! is_array( $sources ) ) {
			return $sources;
		}

		if ( Optml_Media_Offload::is_uploaded_image( $image_src ) ) {
			return $sources;
		}
		$original_url = null;
		$cropping     = null;
		if ( count( $size_array ) === 2 ) {
			$sizes    = self::size_to_crop();
			$cropping = isset( $sizes[ $size_array[0] . $size_array[1] ] ) ? $this->to_optml_crop( $sizes[ $size_array[0] . $size_array[1] ] ) : null;
		}
		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'sources: ' . print_r( $sources, true ) . ' size_array: ' . print_r( $size_array, true ) );
		}
		$biggest_size = [];
		$max_width = 0;
		foreach ( $sources as $i => $source ) {
			$url = $source['url'];
			if ( Optml_Media_Offload::is_uploaded_image( $url ) ) {
				continue;
			}
			list( $width, $height, $file_crop ) = $this->parse_dimensions_from_filename( $url );

			if ( empty( $width ) ) {
				$width = $image_meta['width'];
			}

			if ( empty( $height ) ) {
				$height = $image_meta['height'];
			}

			if ( $original_url === null ) {
				if ( ! empty( $attachment_id ) ) {
					$original_url = wp_get_attachment_url( $attachment_id );
				} else {
					$original_url = $this->strip_image_size_from_url( $source['url'] );
				}
			}
			$args = [];
			if ( 'w' === $source['descriptor'] ) {
				if ( $height && ( $source['value'] === $width ) ) {
					$args['width']  = $width;
					$args['height'] = $height;
				} else {
					$args['width'] = $source['value'];
				}
			}
			if ( $cropping !== null ) {
				$args['resize'] = $cropping;
			} else {
				$args['resize'] = $this->to_optml_crop( $file_crop );
			}
			if ( $i > $max_width ) {
				$max_width = $i;
				$biggest_size = [ $args, $original_url ];
			}
			$sources[ $i ]['url'] = apply_filters( 'optml_content_url', $original_url, $args );

		}

		if ( $this->settings->get( 'retina_images' ) === 'enabled' && ! empty( $biggest_size ) ) {
			$sources[ $max_width * 2 ]['url'] = apply_filters( 'optml_content_url', $biggest_size[1], array_merge( $biggest_size[0], [ 'dpr' => 2 ] ) );
			$sources[ $max_width * 2 ]['value'] = 2;
			$sources[ $max_width * 2 ]['descriptor'] = 'x';
		}
		return $sources;
	}

	/**
	 * Filters sizes attribute of the images.
	 *
	 * @param array $sizes An array of media query breakpoints.
	 * @param array $size Width and height of the image.
	 *
	 * @return mixed An array of media query breakpoints.
	 */
	public function filter_sizes_attr( $sizes, $size ) {

		if ( ! doing_filter( 'the_content' ) ) {
			return $sizes;
		}

		$content_width = false;
		if ( isset( $GLOBALS['content_width'] ) ) {
			$content_width = $GLOBALS['content_width'];
		}

		if ( ! $content_width ) {
			$content_width = 1000;
		}
		if ( is_array( $size ) && $size[0] < $content_width ) {
			return $sizes;
		}

		return sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $content_width );
	}

	/**
	 * This filter will replace all the images retrieved via "wp_get_image" type of functions.
	 *
	 * @param array        $image The filtered value.
	 * @param int          $attachment_id The related attachment id.
	 * @param array|string $size This could be the name of the thumbnail size or an array of custom dimensions.
	 *
	 * @return array
	 */
	public function filter_image_downsize( $image, $attachment_id, $size ) {

		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return $image;
		}
		$image_url = wp_get_attachment_url( $attachment_id );
		if ( Optml_Media_Offload::is_uploaded_image( $image_url ) ) {
			return $image;
		}
		if ( $image_url === false ) {
			return $image;
		}

		$image_meta = wp_get_attachment_metadata( $attachment_id );
		$sizes = $this->size_to_dimension( $size, $image_meta, $attachment_id );

		$image_url = $this->strip_image_size_from_url( $image_url );

		$new_url = apply_filters( 'optml_content_url', $image_url, $sizes );

		if ( $new_url === $image_url ) {
			return $image;
		}

		return [
			$new_url,
			$sizes['width'],
			$sizes['height'],
			$size === 'full',
		];
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @codeCoverageIgnore
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @codeCoverageIgnore
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}
}
