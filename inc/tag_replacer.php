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
	 * The initialize method.
	 */
	public function init() {

		if ( ! parent::init() ) {
			return;
		}

		add_filter( 'optml_content_images_tags', array( $this, 'process_image_tags' ), 1, 2 );

		if ( ! $this->settings->use_lazyload() ) {
			add_filter( 'optml_tag_replace', array( $this, 'regular_tag_replace' ), 1, 4 );
			add_filter( 'image_downsize', array( $this, 'filter_image_downsize' ), PHP_INT_MAX, 3 );
			add_filter( 'wp_calculate_image_srcset', array( $this, 'filter_srcset_attr' ), PHP_INT_MAX, 5 );
			add_filter( 'wp_calculate_image_sizes', array( $this, 'filter_sizes_attr' ), 1, 2 );
		}
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
	private function parse_dimensions_from_tag( $tag, $image_sizes, $args = array() ) {
		if ( preg_match( '#width=["|\']?([\d%]+)["|\']?#i', $tag, $width_string ) ) {
			$args['width'] = $width_string[1];
		}
		if ( preg_match( '#height=["|\']?([\d%]+)["|\']?#i', $tag, $height_string ) ) {
			$args['height'] = $height_string[1];
		}
		if ( preg_match( '#class=["|\']?[^"\']*size-([^"\'\s]+)[^"\']*["|\']?#i', $tag, $size ) ) {
			$size = array_pop( $size );
			if ( false === $args['width'] && false === $args['height'] && 'full' != $size && array_key_exists( $size, $image_sizes ) ) {
				$args['width']  = (int) $image_sizes[ $size ]['width'];
				$args['height'] = (int) $image_sizes[ $size ]['height'];
				$args['resize'] = $this->to_optml_crop( $image_sizes[ $size ]['crop'] );
			}
		}

		return array( $args['width'], $args['height'], $args['resize'] );
	}

	/**
	 * Called by hook to replace image tags in content.
	 *
	 * @param string $content The content to process.
	 * @param array  $images List of image tags.
	 *
	 * @return mixed
	 */
	public function process_image_tags( $content, $images = array() ) {
		$image_sizes = self::image_sizes();
		foreach ( $images[0] as $index => $tag ) {
			$width   = $height = false;
			$resize  = array(   );
			$new_tag = $tag;
			$src     = $tmp = wp_unslash( $images['img_url'][ $index ] );
			if ( apply_filters( 'optml_ignore_image_link', false, $src ) ||
				 false !== strpos( $src, Optml_Config::$service_url ) ||
				 ! $this->can_replace_url( $src )
			) {
				continue; // @codeCoverageIgnore
			}

			list( $width, $height, $resize ) = self::parse_dimensions_from_tag( $images['img_tag'][ $index ], $image_sizes, array( 'width' => $width, 'height' => $height, 'resize' => $resize ) );
			if ( false === $width && false === $height ) {
				list( $width, $height ) = $this->parse_dimensions_from_filename( $tmp );
			}
			$optml_args = $this->to_optml_dimensions_bound( $width, $height, $this->max_width, $this->max_height );
			$tmp        = $this->strip_image_size_from_url( $tmp );
			$optml_args = array_merge( $optml_args, $resize );

			$new_url = apply_filters( 'optml_content_url', $tmp, $optml_args );

			if ( $new_url === $tmp ) {
				continue; // @codeCoverageIgnore
			}
			// replace the url in hrefs or links
			if ( ! empty( $images['link_url'][ $index ] ) && $this->is_valid_mimetype_from_url( $images['link_url'][ $index ] ) ) {
				$new_tag = preg_replace( '#(href=["|\'])' . $images['link_url'][ $index ] . '(["|\'])#i', '\1' . apply_filters( 'optml_content_url', $tmp, $optml_args ) . '\2', $tag, 1 );
			}

			$new_tag = str_replace( 'width="' . $width . '"', 'width="' . $optml_args['width'] . '"', $new_tag );
			$new_tag = str_replace( 'height="' . $height . '"', 'height="' . $optml_args['height'] . '"', $new_tag );
			$new_tag = apply_filters( 'optml_tag_replace', $new_tag, $images['img_url'][ $index ], $new_url, $optml_args );

			$content = str_replace( $tag, $new_tag, $content );
		}
		return $content;
	}

	/**
	 * Replaces the tags by default.
	 *
	 * @param string $new_tag The new tag.
	 * @param string $original_url The original URL.
	 * @param string $new_url The optimized URL.
	 * @param array  $optml_args Options passed for URL optimization.
	 *
	 * @return string
	 */
	public function regular_tag_replace( $new_tag, $original_url, $new_url, $optml_args ) {
		return str_replace( 'src="' . $original_url . '"', 'src="' . $new_url . '"', $new_tag );
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
	public function filter_srcset_attr( $sources = array(), $size_array = array(), $image_src = '', $image_meta = array(), $attachment_id = 0 ) {
		if ( ! is_array( $sources ) ) {
			return $sources;
		}

		var_dump( $sources );
		foreach ( $sources as $i => $source ) {
			$url = $source['url'];
			list( $width, $height ) = $this->parse_dimensions_from_filename( $url );

			if ( empty( $width ) ) {
				$width = $image_meta['width'];
			}

			if ( empty( $height ) ) {
				$height = $image_meta['height'];
			}

			if ( ! empty( $attachment_id ) ) {
				$url = wp_get_attachment_url( $attachment_id );
			} else {
				$url = $this->strip_image_size_from_url( $source['url'] );
			}

			$args = array();
			if ( 'w' === $source['descriptor'] ) {
				if ( $height && ( $source['value'] == $width ) ) {
					$args['width']  = $width;
					$args['height'] = $height;
				} else {
					$args['width'] = $source['value'];
				}
			}
			$sources[ $i ]['url'] = apply_filters( 'optml_content_url', $url, $args );
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

		if ( $image_url === false ) {
			return $image;
		}

		$image_meta = wp_get_attachment_metadata( $attachment_id );
		$image_args = self::image_sizes();

		// default size
		$sizes = array(
			'width'  => isset( $image_meta['width'] ) ? intval( $image_meta['width'] ) : 'auto',
			'height' => isset( $image_meta['height'] ) ? intval( $image_meta['height'] ) : 'auto',
		);

		// in case there is a custom image size $size will be an array.
		if ( is_array( $size ) ) {

			$sizes['width']  = ( $size[0] < $sizes['width'] ? $size[0] : $sizes['width'] );
			$sizes['height'] = ( $size[1] < $sizes['height'] ? $size[1] : $sizes['height'] );

		} elseif ( 'full' !== $size && isset( $image_args[ $size ] ) ) { // overwrite if there a size

			$sizes['width']  = $image_args[ $size ]['width'] < $sizes['width'] ? $image_args[ $size ]['width'] : $sizes['width'];
			$sizes['height'] = $image_args[ $size ]['height'] < $sizes['height'] ? $image_args[ $size ]['height'] : $sizes['height'];
			$sizes['resize'] = $this->to_optml_crop( $image_args[ $size ]['crop'] );
		}
		$new_sizes = $this->to_optml_dimensions_bound( $sizes['width'], $sizes['height'], $this->max_width, $this->max_height );

		$new_sizes = array_merge( $sizes, $new_sizes );
		$image_url = $this->strip_image_size_from_url( $image_url );

		$new_url   = apply_filters( 'optml_content_url', $image_url, $new_sizes );

		if ( $new_url === $image_url ) {
			return $image;
		}

		return array(
			$new_url,
			$sizes['width'],
			$sizes['height'],
			true,
		);
	}

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
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			add_action( 'after_setup_theme', array( self::$instance, 'init' ) );
		}

		return self::$instance;
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
