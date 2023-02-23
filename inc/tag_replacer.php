<?php

/**
 * The class handles the img tag replacements.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Tag_Replacer extends Optml_App_Replacer {
	use Optml_Normalizer;
	use Optml_Validator;

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

		if ( $this->settings->use_lazyload() ) {
			return;
		}

		add_filter( 'optml_tag_replace', [ $this, 'regular_tag_replace' ], 1, 6 );
		add_filter( 'image_downsize', [ $this, 'filter_image_downsize' ], PHP_INT_MAX, 3 );
		add_filter( 'wp_calculate_image_srcset', [ $this, 'filter_srcset_attr' ], PHP_INT_MAX, 5 );
		add_filter( 'wp_calculate_image_sizes', [ $this, 'filter_sizes_attr' ], 1, 2 );

	}
	/**
	 * Called by hook to replace image tags in content.
	 *
	 * @param string $content The content to process.
	 * @param array  $images List of image tags.
	 *
	 * @return mixed
	 */

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
			['width' => 'auto',
				'height' => 'auto',
				'format' => 'mp4',
			]
		);

		$link_png = apply_filters(
			'optml_content_url',
			$image_url,
			['width' => 'auto',
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
				 false !== strpos( $src, Optml_Config::$service_url ) ||
				 ! $this->can_replace_url( $src ) ||
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

			if ( $new_url === $tmp && ! Optml_Media_Offload::is_not_processed_image( $src ) ) {
				continue; // @codeCoverageIgnore
			}

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
			if ( $images['in_header'][ $index ] || $should_lazy_gif === false ) {
				$image_tag = $this->regular_tag_replace( $image_tag, $images['img_url'][ $index ], $new_url, $optml_args, $is_slashed, $tag );
			} else {
				$image_tag = apply_filters( 'optml_tag_replace', $image_tag, $images['img_url'][ $index ], $new_url, $optml_args, $is_slashed, $tag );
			}
			if ( strpos( $image_tag, 'decoding=' ) === false ) {
				$pattern = '/<img(.*?)/is';
				$replace = '<img decoding=async $1';
				$image_tag = preg_replace( $pattern, $replace, $image_tag );
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
			if ( ctype_digit( $width_string[1] ) === true ) {
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
		$pattern = '/(?<!\/)' . preg_quote( $original_url, '/' ) . '/i';
		$replace = $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;
		self::$lazyload_skipped_images ++;
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
			$sources[ $i ]['url'] = apply_filters( 'optml_content_url', $original_url, $args );
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

		$image_url = wp_get_attachment_url( $attachment_id );
		if ( Optml_Media_Offload::is_uploaded_image( $image_url ) ) {
			return $image;
		}
		if ( $image_url === false ) {
			return $image;
		}

		$image_meta = wp_get_attachment_metadata( $attachment_id );
		$image_args = self::image_sizes();
		// default size
		$sizes = [
			'width'  => isset( $image_meta['width'] ) ? intval( $image_meta['width'] ) : false,
			'height' => isset( $image_meta['height'] ) ? intval( $image_meta['height'] ) : false,
		];

		switch ( $size ) {
			case is_array( $size ):
				$width  = isset( $size[0] ) ? (int) $size[0] : false;
				$height = isset( $size[1] ) ? (int) $size[1] : false;
				if ( ! $width || ! $height ) {
					break;
				}
				$image_resized = image_resize_dimensions( $sizes['width'], $sizes['height'], $width, $height );
				if ( $image_resized ) {
					$width  = $image_resized[6];
					$height = $image_resized[7];
				} else {
					$width  = $image_meta['width'];
					$height = $image_meta['height'];
				}
				list( $sizes['width'], $sizes['height'] ) = image_constrain_size_for_editor( $width, $height, $size );

				break;
			case 'full' !== $size && isset( $image_args[ $size ] ):
				$image_resized = image_resize_dimensions( $sizes['width'], $sizes['height'], $image_args[ $size ]['width'], $image_args[ $size ]['height'], $image_args[ $size ]['crop'] );

				if ( $image_resized ) { // This could be false when the requested image size is larger than the full-size image.
					$sizes['width']  = $image_resized[6];
					$sizes['height'] = $image_resized[7];
				}

				list( $sizes['width'], $sizes['height'] ) = image_constrain_size_for_editor( $sizes['width'], $sizes['height'], $size, 'display' );

				$sizes['resize'] = $this->to_optml_crop( $image_args[ $size ]['crop'] );

				break;
		}
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
