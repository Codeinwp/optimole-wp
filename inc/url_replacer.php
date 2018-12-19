<?php

/**
 * The class handles the url replacements.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Url_Replacer extends Optml_App_Replacer {

	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Url_Replacer
	 */
	protected static $instance = null;

	/**
	 * The initialize method.
	 */
	public function init() {

		add_filter( 'optml_replace_image', array( $this, 'build_image_url' ), 10, 2 );

		if ( ! parent::init() ) {
			return;
		}

		add_filter( 'optml_content_url', array( $this, 'build_image_url' ), 1, 2 );
	}

	/**
	 * Returns a signed image url authorized to be used in our CDN.
	 *
	 * @param string $url The url which should be signed.
	 * @param array  $args Dimension params; Supports `width` and `height`.
	 *
	 * @return string
	 */
	public function build_image_url(
		$url, $args = array(
			'width'   => 'auto',
			'height'  => 'auto',
			'quality' => '',
		)
	) {
		if ( apply_filters( 'optml_dont_replace_url', false, $url ) ) {
			return $url;
		}
		if ( strpos( $url, Optml_Config::$service_url ) !== false ) {
			return $url;
		}
		if ( ! $this->is_valid_mimetype_from_url( $url ) ) {
			return $url;
		}

		$compress_level = apply_filters( 'optml_image_quality', $this->settings->get_quality() );
		if ( isset( $args['quality'] ) && ! empty( $args['quality'] ) ) {
			$compress_level = $args['quality'];
		}

		$args['quality'] = $this->to_accepted_quality( $compress_level );

		// this will authorize the image
		if ( ! empty( $this->site_mappings ) ) {
			$url = str_replace( array_keys( $this->site_mappings ), array_values( $this->site_mappings ), $url );
		}

		return ( new Optml_Image( $url, $args ) )->get_url( $this->is_allowed_site );
	}

	/**
	 * Class instance method.
	 *
	 * @static
	 * @since  1.0.0
	 * @access public
	 * @return Optml_Url_Replacer
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
