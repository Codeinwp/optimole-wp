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
	 * Holds the url replacer class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Url_Replacer Replacer instance.
	 */
	public $url_replacer;

	/**
	 * Holds the tag replacer class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Tag_Replacer Replacer instance.
	 */
	public $tag_replacer;

	/**
	 * Holds the lazyload replacer class.
	 *
	 * @access  public
	 * @since   1.0.0
	 * @var Optml_Lazyload_Replacer Replacer instance.
	 */
	public $lazyload_replacer;
	/**
	 * Holds plugin settings.
	 *
	 * @var Optml_Settings WordPress settings.
	 */
	protected $settings;

	/**
	 * Possible integrations with different plugins.
	 *
	 * @var array Integrations classes.
	 */
	private $compatibilities = array(
		'shortcode_ultimate',
		'foogallery',
		'envira',
		'beaver_builder',
		'jet_elements',
		'revslider',
		'metaslider',
		'woocommerce',
	);

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
		if ( null === self::$instance ) {
			self::$instance                    = new self();
			self::$instance->url_replacer      = Optml_Url_Replacer::instance();
			self::$instance->tag_replacer      = Optml_Tag_Replacer::instance();
			self::$instance->lazyload_replacer = Optml_Lazyload_Replacer::instance();
			add_action( 'after_setup_theme', array( self::$instance, 'init' ) );
		}

		return self::$instance;
	}

	/**
	 * The initialize method.
	 */
	public function init() {

		$this->settings = new Optml_Settings();

		if ( ! $this->should_replace() ) {
			return;
		}
		$this->register_hooks();
	}

	/**
	 * Check if we should rewrite the urls.
	 *
	 * @return bool If we can replace the image.
	 */
	public function should_replace() {

		if ( apply_filters( 'optml_should_replace_page', false ) ) {
			return false;
		}

		if ( ( is_admin() && ! self::is_ajax_request() ) || ! $this->settings->is_connected() || ! $this->settings->is_enabled() || is_customize_preview() ) {
			return false; // @codeCoverageIgnore
		}

		if ( array_key_exists( 'preview', $_GET ) && 'true' == $_GET['preview'] ) {
			return false; // @codeCoverageIgnore
		}

		if ( array_key_exists( 'optml_off', $_GET ) && 'true' == $_GET['optml_off'] ) {
			return false; // @codeCoverageIgnore
		}
		if ( array_key_exists( 'elementor-preview', $_GET ) && ! empty( $_GET['elementor-preview'] ) ) {
			return false; // @codeCoverageIgnore
		}

		if ( class_exists( 'FLBuilderModel', false ) ) {
			$post_data = FLBuilderModel::get_post_data();
			if ( isset( $_GET['fl_builder'] ) || isset( $post_data['fl_builder'] ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Check if we are in a ajax contex where we should enable replacement.
	 *
	 * @return bool Is ajax request?
	 */
	public static function is_ajax_request() {

		if ( ! function_exists( 'is_user_logged_in' ) ) {
			return false;
		}
		// Disable for logged in users to avoid unexpected results.
		if ( is_user_logged_in() ) {
			return false;
		}

		if ( ! function_exists( 'wp_doing_ajax' ) ) {
			return false;
		}
		if ( ! wp_doing_ajax() ) {
			return false;
		}

		return true;
	}

	/**
	 * Register frontend replacer hooks.
	 */
	public function register_hooks() {

		do_action( 'optml_replacer_setup' );

		add_filter( 'the_content', array( $this, 'process_images_from_content' ), PHP_INT_MAX );
		/**
		 * When we have to process cdn images, i.e MIRROR is defined,
		 * we need this as late as possible for other replacers to occur.
		 * Otherwise, we can hook first to avoid any other plugins to take care of replacement.
		 */
		add_action(
			self::is_ajax_request() ? 'init' : 'template_redirect',
			array(
				$this,
				'process_template_redirect_content',
			),
			defined( 'OPTML_SITE_MIRROR' ) ? PHP_INT_MAX : PHP_INT_MIN
		);

		add_action( 'rest_api_init', array( $this, 'process_template_redirect_content' ), PHP_INT_MIN );

		add_action( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX, 4 );

		foreach ( $this->compatibilities as $compatibility_class ) {
			$compatibility_class = 'Optml_' . $compatibility_class;
			$compatibility       = new $compatibility_class;

			/**
			 * Check if we should load compatibility.
			 *
			 * @var Optml_compatibility $compatibility Class to register.
			 */
			if ( $compatibility->should_load() ) {
				$compatibility->register();
			}
		}
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

			return $this->process_urls_from_json( $current_meta );
		}

		// Return original if the check does not pass
		return $metadata;
	}

	/**
	 * Process json string.
	 *
	 * @param string $json Json string.
	 *
	 * @return string Processed string.
	 */
	public function process_urls_from_json( $json ) {

		$extracted_urls = $this->extract_urls_from_json( $json );

		return $this->do_url_replacement( $json, $extracted_urls );
	}

	/**
	 * Extract urls used as values in json string, i.e not prefixed by =("|') char.
	 *
	 * @param string $content Raw json string.
	 *
	 * @return array array of urls.
	 */
	public function extract_urls_from_json( $content ) {
		$regex = '/(?<!(=|\\\\)(?:"|\'|"))(?:http(?:s?):)(?:[\/\\\\|.|\w|\s|-])*\.(?:' . implode( '|', array_keys( Optml_Config::$extensions ) ) . ')(?:\??[\w|=|&|\-|\.|:]*)/';
		preg_match_all(
			$regex,
			$content,
			$urls
		);

		return $this->normalize_urls( $urls[0] );
	}

	/**
	 * Normalize extracted urls.
	 *
	 * @param array $urls Raw urls extracted.
	 *
	 * @return array Normalized array.
	 */
	private function normalize_urls( $urls ) {

		$urls = array_map(
			function ( $value ) {
				return rtrim( html_entity_decode( $value ), '\\";\'' );
			},
			$urls
		);
		$urls = array_unique( $urls );

		return array_values( $urls );
	}

	/**
	 * Process string content and replace possible urls.
	 *
	 * @param string $html String content.
	 * @param array  $extracted_urls Urls to check.
	 *
	 * @return string Processed html.
	 */
	private function do_url_replacement( $html, $extracted_urls ) {
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
	 * Filter raw HTML content for urls.
	 *
	 * @param string $html HTML to filter.
	 *
	 * @return mixed Filtered content.
	 */
	public function replace_content( $html ) {
		$html = $this->process_images_from_content( $html );

		$html = $this->process_urls_from_content( $html );

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

		$header_start = null;
		$header_end   = null;

		if ( preg_match( '/<header.*<\/header>/ismU', $content, $matches, PREG_OFFSET_CAPTURE ) === 1 ) {
			$header_start = $matches[0][1];
			$header_end   = $header_start + strlen( $matches[0][0] );
		}

		if ( preg_match_all( '/(?:<a[^>]+?href=["|\'](?P<link_url>[^\s]+?)["|\'][^>]*?>\s*)?(?P<img_tag>(?:<\s*noscript\s*>\s*)?<img[^>]*?\s+?(?:' . implode( '|', array_merge( [ 'src' ], Optml_Tag_Replacer::possible_src_attributes() ) ) . ')=\\\\?["|\'](?P<img_url>[^\s]+?)["|\'].*?>){1}(?:\s*<\/a>)?/ism', $content, $images, PREG_OFFSET_CAPTURE ) ) {

			foreach ( $images as $key => $unused ) {
				// Simplify the output as much as possible, mostly for confirming test results.
				if ( is_numeric( $key ) && $key > 0 ) {
					unset( $images[ $key ] );
					continue;
				}
				foreach ( $unused as $url_key => $url_value ) {
					if ( $key === 'img_url' ) {
						$images[ $key ][ $url_key ] = rtrim( $url_value[0], '\\' );
						continue;
					}
					$images[ $key ][ $url_key ] = $url_value[0];
					if ( $key === 0 ) {
						$images['in_header'][ $url_key ] = $header_start !== null ? ( $url_value[1] > $header_start && $url_value[1] < $header_end ) : false;
					}
				}
			}

			return $images;
		}

		return array();
	}


	/**
	 * Process url replacement from raw html strings.
	 *
	 * @param string $html Raw html.
	 *
	 * @return string Processed string.
	 */
	public function process_urls_from_content( $html ) {
		$extracted_urls = $this->extract_image_urls_from_content( $html );

		return $this->do_url_replacement( $html, $extracted_urls );

	}

	/**
	 * Method to extract images from content.
	 *
	 * @param string $content The HTML content.
	 *
	 * @return array
	 */
	public function extract_image_urls_from_content( $content ) {
		$regex = '/(?:http(?:s?):)(?:[\/\\\\|.|\w|\s|-])*\.(?:' . implode( '|', array_keys( Optml_Config::$extensions ) ) . ')(?:\?{1}[\w|=|&|\-|\.|:|;]*)?/';
		preg_match_all(
			$regex,
			$content,
			$urls
		);

		return $this->normalize_urls( $urls[0] );
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
