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
	 * Try to determine height and width from strings WP appends to resized image filenames.
	 *
	 * @param string $src The image URL.
	 *
	 * @return array An array consisting of width and height.
	 */
	public static function parse_dimensions_from_filename( $src ) {
		$width_height_string = array();
		$extensions          = array_keys( Optml_Config::$extensions );
		if ( preg_match( '#-(\d+)x(\d+)\.(?:' . implode( '|', $extensions ) . '){1}$#i', $src, $width_height_string ) ) {
			$width  = (int) $width_height_string[1];
			$height = (int) $width_height_string[2];

			if ( $width && $height ) {
				return array( $width, $height );
			}
		}

		return array( false, false );
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
			$resize  = array( 'resize' => Optml_Image::RESIZE_FIT );
			$new_tag = $tag;
			$src     = $tmp = wp_unslash( $images['img_url'][ $index ] );
			if ( apply_filters( 'optml_ignore_image_link', false, $src ) ) {
				continue;
			}

			if ( false !== strpos( $src, Optml_Config::$service_url ) ) {
				continue; // we already have this
			}

			if ( ! $this->can_replace_url( $src ) ) {
				continue;
			}

			list( $width, $height, $resize ) = self::parse_dimensions_from_tag( $images['img_tag'][ $index ], $image_sizes, array( 'width' => $width, 'height' => $height, 'resize' => $resize ) );
			if ( false === $width && false === $height ) {
				list( $width, $height ) = self::parse_dimensions_from_filename( $tmp );
			}
			$optml_args = $this->to_optml_dimensions_bound( $width, $height, $this->max_width, $this->max_height );
			$tmp        = $this->strip_image_size_from_url( $tmp );
			$optml_args = array_merge( $optml_args, $resize );

			$new_url = apply_filters( 'optml_content_url', $tmp, $optml_args );

			if ( $new_url === $tmp ) {
				continue;
			}
			// replace the url in hrefs or links
			if ( ! empty( $images['link_url'][ $index ] ) ) {
				if ( $this->is_valid_mimetype_from_url( $images['link_url'][ $index ] ) ) {
					$new_tag = preg_replace( '#(href=["|\'])' . $images['link_url'][ $index ] . '(["|\'])#i', '\1' . apply_filters( 'optml_content_url', $tmp, $optml_args ) . '\2', $tag, 1 );
				}
			}

			$new_tag = str_replace( 'width="' . $width . '"', 'width="' . $optml_args['width'] . '"', $new_tag );
			$new_tag = str_replace( 'height="' . $height . '"', 'height="' . $optml_args['height'] . '"', $new_tag );
			$new_tag = str_replace( 'src="' . $images['img_url'][ $index ] . '"', 'src="' . $new_url . '"', $new_tag );

			// if ( $this->lazyload && $this->can_lazyload_for( $tmp ) ) {
			// $new_sizes['quality'] = 'eco';
			// $low_url              = $this->get_image_url( $tmp, $new_sizes );
			//
			// $noscript_tag = str_replace(
			// array(
			// 'src="' . $images['img_url'][ $index ] . '"',
			// 'src=\"' . $images['img_url'][ $index ] . '"',
			// ),
			// array(
			// 'src="' . $new_url . '"',
			// wp_slash( 'src="' . $new_url . '"' ),
			// ),
			// $new_tag
			// );
			// $new_tag      = str_replace(
			// array(
			// 'src="' . $images['img_url'][ $index ] . '"',
			// 'src=\"' . $images['img_url'][ $index ] . '"',
			// ),
			// array(
			// 'src="' . $low_url . '" data-opt-src="' . $new_url . '"',
			// wp_slash( 'src="' . $low_url . '" data-opt-src="' . $new_url . '"' ),
			// ),
			// $new_tag
			// );
			//
			// $new_tag = '<noscript>' . $noscript_tag . '</noscript>' . $new_tag;
			// } else {
			// $new_tag = str_replace( 'src="' . $images['img_url'][ $index ] . '"', 'src="' . $new_url . '"', $new_tag );
			// }
			$content = str_replace( $tag, $new_tag, $content );
		}
		return $content;
	}

	/**
	 * Class instance method.
	 *
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
	 * @access public
	 * @since  1.0.0
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

}
