<?php
/**
 * Optimole Hero Preloader.
 *
 * @package     Optimole/Inc
 * @copyright   Copyright (c) 2023, Hardeep Asrani
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Optml_Hero_Preloader
 */
class Optml_Hero_Preloader {

	/**
	 * Hold the settings object.
	 *
	 * @var Optml_Settings Settings object.
	 */
	public $settings;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Hero_Preloader
	 */
	protected static $instance = null;

	/**
	 * Has flagged preloading image.
	 *
	 * @var bool
	 */
	protected static $has_flagged_preloading_image = false;

	/**
	 * Has flagged preloading logo to prevent footer logos from being targetted.
	 *
	 * @var bool
	 */
	protected static $has_flagged_preloading_logo = false;

	/**
	 * Class instance method.
	 *
	 * @static
	 * @return Optml_Hero_Preloader
	 * @since  3.9.0
	 * @access public
	 */
	public static function instance() {
		if ( null === self::$instance || ( self::$instance->settings !== null && ( ! self::$instance->settings->is_connected() ) ) ) {
			self::$instance = new self();
			self::$instance->settings = new Optml_Settings();
			if ( self::$instance->settings->is_connected() && ! function_exists( 'wp_get_loading_optimization_attributes' ) ) {
				self::$instance->init();
			}
		}

		return self::$instance;
	}

	/**
	 * The initialize method.
	 *
	 * @since  3.9.0
	 * @access public
	 */
	public function init() {
		add_filter( 'get_header_image_tag_attributes', [ $this, 'add_preload' ] );
		add_filter( 'post_thumbnail_html', [ $this, 'add_preload_to_thumbnail' ] );
		add_filter( 'wp_get_attachment_image_attributes', [ $this, 'add_preload_to_image_attributes' ], 10, 2 );
		add_filter( 'get_custom_logo_image_attributes', [ $this, 'add_preload_to_logo' ] );
		add_filter( 'wp_content_img_tag', [ $this, 'add_preload_to_thumbnail' ] );
	}

	/**
	 * Add preload attribute to image.
	 *
	 * @since  3.9.0
	 * @access public
	 *
	 * @param array $attr Image attributes.
	 *
	 * @return array
	 */
	public function add_preload( $attr ) {
		if ( self::$has_flagged_preloading_image ) {
			return $attr;
		}

		self::$has_flagged_preloading_image = true;

		$attr['fetchpriority'] = 'high';
		return $attr;
	}

	/**
	 * Add preload attribute to thumbnail.
	 *
	 * @since  3.9.0
	 * @access public
	 *
	 * @param string $html The post thumbnail HTML.
	 *
	 * @return string
	 */
	public function add_preload_to_thumbnail( $html ) {
		if ( self::$has_flagged_preloading_image ) {
			return $html;
		}

		if ( ! empty( $html ) && strpos( $html, 'loading="lazy"' ) === false && strpos( $html, 'fetchpriority=' ) === false ) {
			self::$has_flagged_preloading_image = true;
			$html = str_replace( '<img', '<img fetchpriority="high"', $html );
		}
		return $html;
	}

	/**
	 * Filter attachment image attributes.
	 *
	 * @since  3.9.0
	 * @access public
	 *
	 * @param array  $attr       Image attributes.
	 * @param object $attachment Image attachment.
	 *
	 * @return array
	 */
	public function add_preload_to_image_attributes( $attr, $attachment ) {
		if ( self::$has_flagged_preloading_image ) {
			return $attr;
		}

		global $wp_query;

		$post         = null;
		$queried_post = get_queried_object();

		if ( is_singular() && $queried_post instanceof WP_Post ) {
			$post = $queried_post;
		} elseif ( $wp_query->is_main_query() && $wp_query->post_count > 0 && isset( $wp_query->posts[0] ) ) {
			$post = $wp_query->posts[0];
		}

		if ( $post instanceof WP_Post && $attachment instanceof WP_Post && (int) get_post_thumbnail_id( $post ) === $attachment->ID ) {
			$attr = $this->add_preload( $attr );
		}

		return $attr;
	}

	/**
	 * Add preload attribute to logo.
	 *
	 * @since  3.9.0
	 * @access public
	 *
	 * @param array $attr Image attributes.
	 *
	 * @return array
	 */
	public function add_preload_to_logo( $attr ) {
		if ( self::$has_flagged_preloading_logo ) {
			return $attr;
		}

		self::$has_flagged_preloading_logo = true;

		$attr['fetchpriority'] = 'high';
		return $attr;
	}
}
