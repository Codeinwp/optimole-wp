<?php

/**
 * Class Optml_Manager. Adds hooks for processing tags and urls.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Manager {

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Manager
	 */
	protected static $instance = null;

	/**
	 * Class instance method.
	 *
	 * @codeCoverageIgnore
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
	 * The initialize method.
	 */
	public function init() {
		add_filter( 'init', array( $this, 'filter_options_and_mods' ) );
		add_filter( 'the_content', array( $this, 'process_images_from_content' ), PHP_INT_MAX );
		/**
		 * When we have to process cdn images, i.e MIRROR is defined,
		 * we need this as late as possible for other replacers to occur.
		 * Otherwise, we can hook first to avoid any other plugins to take care of replacement.
		 */
		add_action(
			'template_redirect',
			array(
				$this,
				'process_template_redirect_content',
			),
			defined( 'OPTML_SITE_MIRROR' ) ? PHP_INT_MAX : PHP_INT_MIN
		);
		add_action( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX, 4 );
	}

	/**
	 * Handles the url replacement in options and theme mods.
	 */
	public function filter_options_and_mods() {
		/**
		 * `optml_imgcdn_options_with_url` is a filter that allows themes or plugins to select which option
		 * holds an url and needs an optimization.
		 */
		$options_list = apply_filters(
			'optml_imgcdn_options_with_url',
			array(
				'theme_mods_' . get_option( 'stylesheet' ),
				'theme_mods_' . get_option( 'template' ),
			)
		);

		foreach ( $options_list as $option ) {
			add_filter( "option_$option", array( $this, 'replace_option_url' ) );
		}

	}

	/**
	 * A filter which turns a local url into an optimized CDN image url or an array of image urls.
	 *
	 * @param string $url The url which should be replaced.
	 *
	 * @return string Replaced url.
	 */
	public function replace_option_url( $url ) {
		if ( empty( $url ) ) {
			return $url;
		}
		// $url might be an array or an json encoded array with urls.
		if ( is_array( $url ) || filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
			$array   = $url;
			$encoded = false;

			// it might a json encoded array
			if ( is_string( $url ) ) {
				$array   = json_decode( $url, true );
				$encoded = true;
			}

			// in case there is an array, apply it recursively.
			if ( is_array( $array ) ) {
				foreach ( $array as $index => $value ) {
					$array[ $index ] = $this->replace_option_url( $value );
				}

				if ( $encoded ) {
					return json_encode( $array );
				}

				return $array;
			}

			if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
				return $url;
			}
		}

		return apply_filters( 'optml_content_url', $url );
	}

	/**
	 * Replace urls in post meta values.
	 *
	 * @param mixed  $metadata Metadata.
	 * @param int    $object_id Post id.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Is single.
	 *
	 * @return mixed Altered meta.
	 */
	public function replace_meta( $metadata, $object_id, $meta_key, $single ) {

		$meta_needed = '_elementor_data';

		if ( isset( $meta_key ) && $meta_needed == $meta_key ) {
			remove_filter( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX );

			$current_meta = get_post_meta( $object_id, $meta_needed, $single );
			add_filter( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX, 4 );

			if ( ! is_string( $current_meta ) ) {
				return $metadata;
			}

			return $this->replace_content( $current_meta, 'elementor' );
		}

		// Return original if the check does not pass
		return $metadata;
	}

	/**
	 * Filter raw content for urls.
	 *
	 * @param string $html HTML to filter.
	 * @param string $context Context for $html.
	 *
	 * @return mixed Filtered content.
	 */
	public function replace_content( $html, $context = 'raw' ) {
		$html = $this->process_images_from_content( $html );

		$extracted_urls = $this->extract_image_urls_from_content( $html );
		$extracted_urls = apply_filters( 'optml_extracted_urls', $extracted_urls );

		if ( empty( $extracted_urls ) ) {
			return $html;
		}

		$urls = array_combine( $extracted_urls, $extracted_urls );
		$urls = array_map(
			function ( $url ) {
				$is_slashed = strpos( $url, '\/' ) !== false;
				$new_url    = apply_filters( 'optml_content_url', $url );

				return $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;
			},
			$urls
		);

		foreach ( $urls as $origin => $replace ) {
			$html = preg_replace( '/(?<!\/)' . preg_quote( $origin, '/' ) . '/m', $replace, $html );
		}

		return $html;
	}

	/**
	 * Adds a filter with detected images tags and the content.
	 *
	 * @param string $content The HTML content.
	 *
	 * @return mixed
	 */
	public function process_images_from_content( $content ) {
		if ( $this->should_ignore_image_tags() ) {
			return $content;
		}
		$images = self::parse_images_from_html( $content );

		if ( empty( $images ) ) {
			return $content;
		}

		return apply_filters( 'optml_content_images_tags', $content, $images );
	}

	/**
	 * Check if we are on a amp endpoint.
	 *
	 * IMPORTANT: This needs to be  used after parse_query hook, otherwise will return false positives.
	 *
	 * @return bool
	 */
	protected function should_ignore_image_tags() {
		// Ignore image tags replacement in amp context as they are not available.
		if ( function_exists( 'is_amp_endpoint' ) ) {
			return is_amp_endpoint();
		}
		if ( function_exists( 'ampforwp_is_amp_endpoint' ) ) {
			return ampforwp_is_amp_endpoint();
		}

		// Ignore image tag replacement in feed context as we don't need it.
		if ( is_feed() ) {
			return true;
		}

		return false;
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
					continue;
				}
				if ( $key !== 'img_url' ) {
					continue;
				}
				foreach ( $unused as $url_key => $url_value ) {
					$images[ $key ][ $url_key ] = rtrim( $url_value, '\\' );
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
	 * Method to extract images from content.
	 *
	 * @param string $content The HTML content.
	 *
	 * @return array
	 */
	public function extract_image_urls_from_content( $content ) {
		$regex = '/(?:http(?:s?):)(?:[\/\\\\|.|\w|\s|-])*\.(?:' . implode( '|', array_keys( Optml_Config::$extensions ) ) . ')/';
		preg_match_all(
			$regex,
			$content,
			$urls
		);

		$urls = array_map(
			function ( $value ) {
				return rtrim( html_entity_decode( $value ), '\\' );
			},
			$urls[0]
		);

		$urls = array_unique( $urls );

		return array_values( $urls );
	}

	/**
	 * Init html replacer handler.
	 */
	public function process_template_redirect_content() {
		// We no longer need this if the handler was started.
		remove_filter( 'the_content', array( $this, 'process_images_from_content' ), PHP_INT_MAX );

		ob_start(
			array( &$this, 'replace_content' )
		);
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
