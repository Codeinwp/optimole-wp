<?php

class Optml_Manager {

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Manager
	 */
	protected static $instance = null;


	public function init() {
		add_filter( 'the_content', array( $this, 'process_images_from_content' ), PHP_INT_MAX );
	}

	public function process_images_from_content( $content ) {
		return $content;
	}

	/**
	 * Match all images and any relevant <a> tags in a block of HTML.
	 *
	 * @param string $content Some HTML.
	 *
	 * @return array An array of $images matches, where $images[0] is
	 *         an array of full matches, and the link_url, img_tag,
	 *         and img_url keys are arrays of those matches.
	 */
	public static function parse_images_from_html( $content ) {
		$images = array();

		$content = self::strip_header_from_content( $content );

		if ( preg_match_all( '/(?:<a[^>]+?href=["|\'](?P<link_url>[^\s]+?)["|\'][^>]*?>\s*)?(?P<img_tag><img[^>]*?\s+?src=\\\\?["|\'](?P<img_url>[^\s]+?)["|\'].*?>){1}(?:\s*<\/a>)?/ism', $content, $images ) ) {
			foreach ( $images as $key => $unused ) {
				// Simplify the output as much as possible, mostly for confirming test results.
				if ( is_numeric( $key ) && $key > 0 ) {
					unset( $images[ $key ] );
				}
			}

			return $images;
		}

		return array();
	}

	/**
	 * Matches the header tag and removes it.
	 *
	 * @param string $content Some HTML.
	 *
	 * @return string The HTML without the <header/> tag
	 */
	public static function strip_header_from_content( $content ) {
		if ( preg_match( '/<header.*<\/header>/ismU', $content, $matches ) !== 1 ) {
			return $content;
		}

		return str_replace( $matches[0], '', $content );
	}

	/**
	 * Class instance method.
	 *
	 * @static
	 * @since  1.0.0
	 * @access public
	 * @return Optml_Manager
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