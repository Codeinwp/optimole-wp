<?php
final class Optml_Url_Replacer {

	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Url_Replacer
	 */
	protected static $instance = null;

	/**
	 * Holds an array of image sizes.
	 *
	 * @var array
	 */
	protected static $image_sizes = array();

	/**
	 * Defines which is the maximum width accepted in the optimization process.
	 *
	 * @var int
	 */
	protected $max_width = 3000;

	/**
	 * Defines which is the maximum width accepted in the optimization process.
	 *
	 * @var int
	 */
	protected $max_height = 3000;

	/**
	 * A cached version of `wp_upload_dir`
	 *
	 * @var null
	 */
	protected $upload_resource = null;

	/**
	 * Settings handler.
	 *
	 * @var Optml_Settings $settings
	 */
	protected $settings = null;

	/**
	 * Possible domain sources to optimize.
	 *
	 * @var array Domains.
	 */
	protected $possible_sources = array();

	/**
	 * Whitelisted domains sources to optimize from, according to optimole service.
	 *
	 * @var array Domains.
	 */
	protected $allowed_sources = array();

	/**
	 * Holds site mapping array,
	 * if there is already a cdn and we want to fetch the images from there
	 * and not from he original site.
	 *
	 * @var array Site mappings.
	 */
	protected $site_mappings = array();
	/**
	 * Whether the site is whitelisted or not. Used when signing the urls..
	 *
	 * @var bool Domains.
	 */
	protected $is_allowed_site = array();

	public function init() {

		add_filter( 'optml_replace_image', array( $this, 'build_image_url' ), 10, 2 );

		$this->settings = new Optml_Settings();

		if ( ! $this->should_replace() ) {
			return;
		}
		$this->set_properties();

		add_filter( 'optml_content_url', array( $this, 'build_image_url' ), 1, 2 );
	}

	/**
	 * Check if we should rewrite the urls.
	 *
	 * @return bool If we can replace the image.
	 */
	public function should_replace() {

		if ( is_admin() || ! $this->settings->is_connected() || ! $this->settings->is_enabled() || is_customize_preview() ) {
			return false;
		}

		if ( array_key_exists( 'preview', $_GET ) && 'true' == $_GET['preview'] ) {
			return false;
		}

		if ( array_key_exists( 'optml_off', $_GET ) && 'true' == $_GET['optml_off'] ) {
			return false;
		}
		if ( array_key_exists( 'elementor-preview', $_GET ) && ! empty( $_GET['elementor-preview'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Set the cdn url based on the current connected user.
	 */
	public function set_properties() {
		$upload_data                         = wp_upload_dir();
		$this->upload_resource               = array(
			'url'       => str_replace( array( 'https://', 'http://' ), '', $upload_data['baseurl'] ),
			'directory' => $upload_data['basedir'],
		);
		$this->upload_resource['url_length'] = strlen( $this->upload_resource['url'] );

		$service_data = $this->settings->get( 'service_data' );

		Optml_Config::init(
			array(
				'key'    => $service_data['cdn_key'],
				'secret' => $service_data['cdn_secret'],
			)
		);

		if ( defined( 'OPTML_SITE_MIRROR' ) && ! empty( constant('OPTML_SITE_MIRROR' ) ) ) {
			$this->site_mappings = array(
				rtrim( get_site_url(), '/' ) => rtrim( constant('OPTML_SITE_MIRROR' ), '/' ),
			);
		}

		$this->possible_sources = $this->extract_domain_from_urls(
			array_merge(
				array( get_site_url() ),
				array_values( $this->site_mappings )
			)
		);

		$this->allowed_sources = $this->extract_domain_from_urls( $service_data['whitelist'] );

		$this->is_allowed_site = count( array_diff_key( $this->possible_sources, $this->allowed_sources ) ) > 0;

		$this->max_height = $this->settings->get( 'max_height' );
		$this->max_width  = $this->settings->get( 'max_width' );
	}

	/**
	 * Extract domains and use them as keys for fast processing.
	 *
	 * @param array $urls Input urls.
	 *
	 * @return array Array of domains as keys.
	 */
	private function extract_domain_from_urls( $urls = array() ) {
		if ( ! is_array( $urls ) ) {
			return $urls;
		}

		$urls = array_map(
			function ( $value ) {
				$parts = parse_url( $value );

				return isset( $parts['host'] ) ? $parts['host'] : '';
			},
			$urls
		);
		$urls = array_filter( $urls );
		$urls = array_unique( $urls );
		$urls = array_fill_keys( array_keys( $urls ), true );

		return $urls;
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