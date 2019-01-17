<?php

/**
 * The class handles the img tag replacements for lazyload.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Lazyload_Replacer extends Optml_App_Replacer {
	use Optml_Normalizer;
	use Optml_Validator;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Tag_Replacer
	 */
	protected static $instance = null;

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
	 * The initialize method.
	 */
	public function init() {

		if ( ! parent::init() ) {
			return; // @codeCoverageIgnore
		}

		if ( $this->settings->use_lazyload() ) {
			add_filter(
				'max_srcset_image_width',
				function () {
					return 1;
				}
			);
			add_filter( 'optml_tag_replace', array( $this, 'lazyload_tag_replace' ), 2, 5 );
		}
	}

	/**
	 * Replaces the tags with lazyload tags.
	 *
	 * @param string $new_tag The new tag.
	 * @param string $original_url The original URL.
	 * @param string $new_url The optimized URL.
	 * @param array  $optml_args Options passed for URL optimization.
	 * @param bool   $is_slashed If the url needs slashes.
	 *
	 * @return string
	 */
	public function lazyload_tag_replace( $new_tag, $original_url, $new_url, $optml_args, $is_slashed = false ) {

		if ( ! $this->can_lazyload_for( $original_url ) ) {
			return Optml_Tag_Replacer::instance()->regular_tag_replace( $new_tag, $original_url, $new_url, $optml_args, $is_slashed );
		}
		$optml_args['quality'] = 'eco';
		$low_url               = apply_filters( 'optml_content_url', $is_slashed ? stripslashes( $original_url ) : $original_url, $optml_args );
		$low_url               = $is_slashed ? addcslashes( $low_url, '/' ) : $low_url;

		$opt_format = ' data-opt-src="%s" ';

		$opt_format = $is_slashed ? addslashes( $opt_format ) : $opt_format;
		$new_url    = $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;

		$opt_src = sprintf( $opt_format, $new_url );

		$no_script_tag = str_replace(
			$original_url,
			$new_url,
			$new_tag
		);
		$new_tag       = str_replace(
			[
				$original_url,
				'src=',
			],
			array(
				$low_url,
				$opt_src . ' src=',
			),
			$new_tag
		);

		return '<noscript>' . $no_script_tag . '</noscript>' . $new_tag;
	}

	/**
	 * Check if the lazyload is allowed for this url.
	 *
	 * @param string $url Url.
	 *
	 * @return bool We can lazyload?
	 */
	public function can_lazyload_for( $url ) {
		if ( ! defined( 'OPTML_DISABLE_PNG_LAZYLOAD' ) ) {
			return true;
		}
		if ( ! OPTML_DISABLE_PNG_LAZYLOAD ) {
			return true; // @codeCoverageIgnore
		}
		$type = wp_check_filetype(
			basename( $url ),
			array(
				'png' => 'image/png',
			)
		);
		if ( ! isset( $type['ext'] ) || empty( $type['ext'] ) ) {
			return true;
		}

		return false;
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
