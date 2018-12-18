<?php
final class Optml_Tag_Replacer extends Optml_App_Replacer {
	use Optml_Normalizer;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Tag_Replacer
	 */
	protected static $instance = null;

	public function init() {

		if ( ! parent::init() ) {
			return;
		}

		add_filter( 'optml_content_images_tags', array( $this, 'process_image_tags' ), 1, 2 );
	}

	private function parse_size_from_tag( $tag, $image_sizes, $args = array() ) {
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

			list( $width, $height, $resize ) = $this->parse_size_from_tag( $images['img_tag'][ $index ], $image_sizes, array( 'width' => $width, 'height' => $height, 'resize' => $resize ) );

			if ( false === $width && false === $height ) {
				list( $width, $height ) = self::parse_dimensions_from_filename( $tmp );
			}
//			$optml_args = $this->validate_image_sizes( $width, $height );
//			$tmp        = $this->strip_image_size_maybe( $tmp );
//			$optml_args = array_merge( $optml_args, $resize );
//			if ( $new_url === $tmp ) {
//				continue;
//			}
//			// replace the url in hrefs or links
//			if ( ! empty( $images['link_url'][ $index ] ) ) {
//				if ( $this->check_mimetype( $images['link_url'][ $index ] ) ) {
//					$new_tag = preg_replace( '#(href=["|\'])' . $images['link_url'][ $index ] . '(["|\'])#i', '\1' . $this->get_image_url( $tmp ) . '\2', $tag, 1 );
//				}
//			}
//			$new_tag = str_replace( 'width="' . $width . '"', 'width="' . $optml_args['width'] . '"', $new_tag );
//			$new_tag = str_replace( 'height="' . $height . '"', 'height="' . $optml_args['height'] . '"', $new_tag );
//			$new_tag = apply_filters( 'optml_image_tag_replacement', $new_tag, $original_url, str_replace( 'src="' . $images['img_url'][ $index ] . '"', 'src="' . $new_url . '"', $new_tag ) );
//			if ( $this->lazyload && $this->can_lazyload_for( $tmp ) ) {
//				$new_sizes['quality'] = 'eco';
//				$low_url              = $this->get_image_url( $tmp, $new_sizes );
//
//				$noscript_tag = str_replace(
//					array(
//						'src="' . $images['img_url'][ $index ] . '"',
//						'src=\"' . $images['img_url'][ $index ] . '"',
//					),
//					array(
//						'src="' . $new_url . '"',
//						wp_slash( 'src="' . $new_url . '"' ),
//					),
//					$new_tag
//				);
//				$new_tag      = str_replace(
//					array(
//						'src="' . $images['img_url'][ $index ] . '"',
//						'src=\"' . $images['img_url'][ $index ] . '"',
//					),
//					array(
//						'src="' . $low_url . '" data-opt-src="' . $new_url . '"',
//						wp_slash( 'src="' . $low_url . '" data-opt-src="' . $new_url . '"' ),
//					),
//					$new_tag
//				);
//
//				$new_tag = '<noscript>' . $noscript_tag . '</noscript>' . $new_tag;
//			} else {
//				$new_tag = str_replace( 'src="' . $images['img_url'][ $index ] . '"', 'src="' . $new_url . '"', $new_tag );
//			}
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