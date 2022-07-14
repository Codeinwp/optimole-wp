<?php

/**
 * Class Optml_App_Replacer
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
abstract class Optml_App_Replacer {

	/**
	 * Filters used for lazyload.
	 *
	 * @var null Lazyload filters.
	 */
	protected static $filters = null;
	/**
	 * Holds an array of image sizes.
	 *
	 * @var array
	 */
	protected static $image_sizes = [];
	/**
	 * Holds width/height to crop array based on possible image sizes.
	 *
	 * @var array
	 */
	protected static $size_to_crop = [];
	/**
	 * Holds possible src attributes.
	 *
	 * @var array
	 */
	protected static $possible_src_attributes = null;
	/**
	 * Holds possible tag replace flags where we should ignore our optimization.
	 *
	 * @var array
	 */
	protected static $ignore_tag_strings = null;
	/**
	 * Holds possible lazyload flags where we should ignore our lazyload.
	 *
	 * @var array
	 */
	protected static $ignore_lazyload_strings = null;
	/**
	 * Holds flags that should ignore the data-opt-tag format.
	 *
	 * @var array
	 */
	protected static $ignore_data_opt_attribute = null;
	/**
	 * Settings handler.
	 *
	 * @var Optml_Settings $settings
	 */
	public $settings = null;
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
	 * Defines if css minification should be used.
	 *
	 * @var int
	 */
	protected $is_css_minify_on = 1;
	/**
	 * Defines if js minification should be used.
	 *
	 * @var int
	 */
	protected $is_js_minify_on = 0;
	/**
	 * A cached version of `wp_upload_dir`
	 *
	 * @var null
	 */
	protected $upload_resource = null;

	/**
	 * Possible domain sources to optimize.
	 *
	 * @var array Domains.
	 */
	protected $possible_sources = [];
	/**
	 * Possible custom sizes definitions.
	 *
	 * @var array Custom sizes definitions.
	 */
	private static $custom_size_buffer = [];
	/**
	 * Whitelisted domains sources to optimize from, according to optimole service.
	 *
	 * @var array Domains.
	 */
	protected $allowed_sources = [];

	/**
	 * Holds site mapping array,
	 * if there is already a cdn and we want to fetch the images from there
	 * and not from he original site.
	 *
	 * @var array Site mappings.
	 */
	protected $site_mappings = [];
	/**
	 * Whether the site is whitelisted or not. Used when signing the urls.
	 *
	 * @var bool Domains.
	 */
	protected $is_allowed_site = [];

	/**
	 * Holds the most recent value for the cache buster, updated to hold only for images if active_cache_buster_assets is defined.
	 *
	 * @var string Cache Buster global value if active_cache_buster_assets is undefined else the value for images.
	 */
	protected $active_cache_buster = '';

	/**
	 * Holds possible ignored by class urls.
	 *
	 * @var array
	 */
	protected static $ignored_url_map = [];

	/**
	 * Holds the most recent value for the cache buster for assets.
	 *
	 * @var string Cache Buster value.
	 */
	protected $active_cache_buster_assets = '';

	/**
	 * Returns possible src attributes.
	 *
	 * @return array
	 */
	public static function possible_src_attributes() {

		if ( ! empty( self::$possible_src_attributes ) && is_array( self::$possible_src_attributes ) ) {
			return self::$possible_src_attributes;
		}

		self::$possible_src_attributes = apply_filters( 'optml_possible_src_attributes', [] );

		return self::$possible_src_attributes;
	}

	/**
	 * Returns possible src attributes.
	 *
	 * @return array
	 */
	public static function possible_lazyload_flags() {

		if ( ! empty( self::$ignore_lazyload_strings ) && is_array( self::$ignore_lazyload_strings ) ) {
			return self::$ignore_lazyload_strings;
		}

		self::$possible_src_attributes = apply_filters( 'optml_possible_lazyload_flags', [ 'skip-lazy', 'data-skip-lazy' ] );

		return array_merge( self::$possible_src_attributes, [ '<noscript' ] );
	}
	/**
	 * Returns possible tag replacement flags.
	 *
	 * @return array
	 */
	public static function possible_tag_flags() {

		if ( ! empty( self::$ignore_tag_strings ) && is_array( self::$ignore_tag_strings ) ) {
			return self::$ignore_tag_strings;
		}

		self::$ignore_tag_strings = apply_filters( 'optml_skip_optimizations_css_classes', [ 'skip-optimization' ] );

		return  self::$ignore_tag_strings;
	}
	/**
	 * Returns possible data-opt-src ignore flags attributes.
	 *
	 * @return array
	 */
	public static function possible_data_ignore_flags() {

		if ( ! empty( self::$ignore_data_opt_attribute ) && is_array( self::$ignore_data_opt_attribute ) ) {
			return self::$ignore_data_opt_attribute;
		}

		self::$ignore_data_opt_attribute = apply_filters( 'optml_ignore_data_opt_flag', [] );

		return self::$ignore_data_opt_attribute;
	}

	/**
	 * Size to crop maping.
	 *
	 * @return array Size mapping.
	 */
	protected static function size_to_crop() {
		if ( ! empty( self::$size_to_crop ) && is_array( self::$size_to_crop ) ) {
			return self::$size_to_crop;
		}

		foreach ( self::image_sizes() as $size_data ) {
			if ( isset( self::$size_to_crop[ $size_data['width'] . $size_data['height'] ] ) && isset( $size_data['enlarge'] ) ) {
				continue;
			}
			self::$size_to_crop[ $size_data['width'] . $size_data['height'] ] =
				isset( $size_data['enlarge'] ) ? [
					'crop'    => $size_data['crop'],
					'enlarge' => true,
				] : $size_data['crop'];
		}

		return self::$size_to_crop;
	}

	/**
	 * Set possible custom size.
	 *
	 * @param null $width Width value.
	 * @param null $height Height Value.
	 * @param null $crop Croping.
	 */
	public static function add_size( $width = null, $height = null, $crop = null ) {
		if ( empty( $width ) || empty( $height ) ) {
			return;
		}
		self::$custom_size_buffer[ 'cmole' . $width . $height ] = [
			'width'   => (int) $width,
			'height'  => (int) $height,
			'enlarge' => true,
			'crop'    => $crop,
		];

	}

	/**
	 * Returns the array of image sizes since `get_intermediate_image_sizes` and image metadata  doesn't include the
	 * custom image sizes in a reliable way.
	 *
	 * @return array
	 * @global $wp_additional_image_sizes
	 */
	protected static function image_sizes() {

		if ( ! empty( self::$image_sizes ) && is_array( self::$image_sizes ) ) {
			return self::$image_sizes;
		}

		global $_wp_additional_image_sizes;

		// Populate an array matching the data structure of $_wp_additional_image_sizes so we have a consistent structure for image sizes
		$images = [
			'thumb'  => [
				'width'  => intval( get_option( 'thumbnail_size_w' ) ),
				'height' => intval( get_option( 'thumbnail_size_h' ) ),
				'crop'   => (bool) get_option( 'thumbnail_crop', false ),
			],
			'medium' => [
				'width'  => intval( get_option( 'medium_size_w' ) ),
				'height' => intval( get_option( 'medium_size_h' ) ),
				'crop'   => false,
			],
			'large'  => [
				'width'  => intval( get_option( 'large_size_w' ) ),
				'height' => intval( get_option( 'large_size_h' ) ),
				'crop'   => false,
			],
			'full'   => [
				'width'  => null,
				'height' => null,
				'crop'   => false,
			],
		];

		// Compatibility mapping as found in wp-includes/media.php
		$images['thumbnail'] = $images['thumb'];

		// Update class variable, merging in $_wp_additional_image_sizes if any are set
		if ( is_array( $_wp_additional_image_sizes ) && ! empty( $_wp_additional_image_sizes ) ) {
			self::$image_sizes = array_merge( $images, $_wp_additional_image_sizes );
		} else {
			self::$image_sizes = $images;
		}

		self::$image_sizes = array_merge( self::$image_sizes, self::$custom_size_buffer );
		self::$image_sizes = array_map(
			function ( $value ) {
				$value['crop'] = isset( $value['crop'] ) ?
					( is_array( $value['crop'] )
						? $value['crop'] :
						(bool) $value['crop'] )
					: false;

				return $value;
			},
			self::$image_sizes
		);

		return is_array( self::$image_sizes ) ? self::$image_sizes : [];
	}

	/**
	 * The initialize method.
	 */
	public function init() {
		$this->settings = new Optml_Settings();
		$this->set_properties();

		self::$filters = $this->settings->get_filters();
		add_filter(
			'optml_should_avif_ext',
			function( $should_avif, $ext ) {
				return $ext !== 'svg';
			},
			10,
			2
		);
		add_filter(
			'optml_possible_lazyload_flags',
			function ( $strings = [] ) {
				foreach ( self::$filters[ Optml_Settings::FILTER_TYPE_LAZYLOAD ][ Optml_Settings::FILTER_CLASS ] as $rule_flag => $status ) {
					$strings[] = $rule_flag;
				}

				return $strings;
			},
			10,
			2
		);
		add_filter(
			'optml_skip_optimizations_css_classes',
			function ( $strings = [] ) {
				foreach ( self::$filters[ Optml_Settings::FILTER_TYPE_OPTIMIZE ][ Optml_Settings::FILTER_CLASS ] as $rule_flag => $status ) {
					$strings[] = $rule_flag;
				}
				return $strings;
			},
			10,
			2
		);
	}


	/**
	 * Set the cdn url based on the current connected user.
	 */
	public function set_properties() {

		$upload_data                         = wp_upload_dir();
		$this->upload_resource               = [
			'url'       => str_replace( [ 'https://', 'http://' ], '', $upload_data['baseurl'] ),
			'directory' => $upload_data['basedir'],
		];
		$this->upload_resource['url_length'] = strlen( $this->upload_resource['url'] );

		$content_url = content_url();

		if ( strpos( $content_url, '/' ) === 0 ) {
				$content_url = get_site_url() . $content_url;
		}
		$content_parts = parse_url( $content_url );
		$this->upload_resource['content_path'] = '/';
		$this->upload_resource['content_folder'] = '/';
		if ( isset( $content_parts['path'] ) ) {
			$this->upload_resource['content_path'] = $content_parts['path'];
			$this->upload_resource['content_folder'] = ltrim( $content_parts['path'], '/' );
		}

		$this->upload_resource['content_folder_length'] = strlen( $this->upload_resource['content_folder'] );
		$this->upload_resource['content_host']          = $content_parts['scheme'] . '://' . $content_parts['host'];

		$service_data = $this->settings->get( 'service_data' );

		$domain = '';
		if ( isset( $service_data['is_cname_assigned'] ) && $service_data['is_cname_assigned'] === 'yes' && ! empty( $service_data['domain'] ) ) {
			$domain = $service_data['domain'];
		}
		Optml_Config::init(
			[
				'key'               => $service_data['cdn_key'],
				'secret'            => $service_data['cdn_secret'],
				'domain'            => $domain,
			]
		);

		if ( defined( 'OPTML_SITE_MIRROR' ) && constant( 'OPTML_SITE_MIRROR' ) ) {
			$this->site_mappings[ rtrim( get_home_url(), '/' ) ] = rtrim( constant( 'OPTML_SITE_MIRROR' ), '/' );
		}

		$this->possible_sources = $this->extract_domain_from_urls(
			array_merge(
				[ get_home_url() ],
				array_values( $this->site_mappings ),
				array_keys( $this->site_mappings )
			)
		);

		$this->allowed_sources              = $this->extract_domain_from_urls( $service_data['whitelist'] );
		$this->active_cache_buster          = $this->settings->get( 'cache_buster_images' );
		$this->active_cache_buster_assets   = $this->settings->get( 'cache_buster_assets' );

		if ( empty( $this->active_cache_buster ) ) {
			$this->active_cache_buster  = $this->settings->get( 'cache_buster' );
		}
		if ( empty( $this->active_cache_buster_assets ) ) {
			$this->active_cache_buster_assets  = $this->settings->get( 'cache_buster' );
		}

		// Allways allow Photon urls.
		$this->allowed_sources['i0.wp.com'] = true;
		$this->allowed_sources['i1.wp.com'] = true;
		$this->allowed_sources['i2.wp.com'] = true;
		$this->is_allowed_site              = count( array_diff_key( $this->possible_sources, $this->allowed_sources ) ) > 0;

		$this->max_height = $this->settings->get( 'max_height' );
		$this->max_width  = $this->settings->get( 'max_width' );

		$this->is_css_minify_on = ( $this->settings->get( 'css_minify' ) === 'enabled' ) ? 1 : 0;
		$this->is_js_minify_on  = ( $this->settings->get( 'js_minify' ) === 'enabled' ) ? 1 : 0;

		add_filter( 'optml_strip_image_size_from_url', [ $this, 'strip_image_size_from_url' ], 10, 1 );
		add_filter(
			'image_resize_dimensions',
			[ __CLASS__, 'listen_to_sizes' ],
			999999,
			6
		);
	}

	/**
	 * Method to expose upload resource property.
	 *
	 * @return null
	 */
	public function get_upload_resource() {
		return $this->upload_resource;
	}

	/**
	 * List to resizes and save the crop for later re-use.
	 *
	 * @param null  $value Original resize.
	 * @param int   $orig_w Original W.
	 * @param int   $orig_h Original H.
	 * @param int   $dest_w Output W.
	 * @param int   $dest_h Output H.
	 * @param mixed $crop Cropping behaviour.
	 *
	 * @return mixed Original value.
	 */
	static function listen_to_sizes( $value, $orig_w, $orig_h, $dest_w, $dest_h, $crop ) {
		self::add_size( $dest_w, $dest_h, $crop );

		return $value;
	}

	/**
	 * Extract domains and use them as keys for fast processing.
	 *
	 * @param array $urls Input urls.
	 *
	 * @return array Array of domains as keys.
	 */
	protected function extract_domain_from_urls( $urls = [] ) {
		if ( ! is_array( $urls ) ) {
			return [];
		}

		$urls = array_map(
			function ( $value ) {
				$parts = parse_url( $value );
				$domain = '';
				if ( isset( $parts['host'] ) ) {
					$domain = $parts['host'];
				} elseif ( isset( $parts['path'] ) ) {
					$domain = $parts['path'];
				}
				return $domain;
			},
			$urls
		);
		$urls = array_filter( $urls );
		$urls = array_unique( $urls );

		$urls = array_fill_keys( $urls, true );
		// build www versions of urls, just in case we need them for validation.
		foreach ( $urls as $domain => $status ) {
			if ( ! ( substr( $domain, 0, 4 ) === 'www.' ) ) {
				$urls[ 'www.' . $domain ] = true;
			}
		}

		return $urls;
	}

	/**
	 * Check if we can replace the url.
	 *
	 * @param string $url Url to change.
	 *
	 * @return bool Either we can replace this url or not.
	 */
	public function can_replace_url( $url ) {

		if ( ! is_string( $url ) ) {
			return false; // @codeCoverageIgnore
		}

		$url_parts = parse_url( $url );

		if ( ! isset( $url_parts['host'] ) ) {
			return false;
		}
		if ( false === ( isset( $this->possible_sources[ $url_parts['host'] ] ) || isset( $this->allowed_sources[ $url_parts['host'] ] ) ) ) {
			return false;
		}

		if ( false === Optml_Filters::should_do_image( $url, self::$filters[ Optml_Settings::FILTER_TYPE_OPTIMIZE ][ Optml_Settings::FILTER_FILENAME ] ) ) {
			return false;
		}

		if ( ! empty( self::$ignored_url_map ) && isset( self::$ignored_url_map[ crc32( $url ) ] ) ) {
			return false;
		}

		return true;
	}
	/**
	 * Checks if the file is a image size and return the full url.
	 *
	 * @param string $url The image URL.
	 *
	 * @return string
	 **/
	public function strip_image_size_from_url( $url ) {

		if ( apply_filters( 'optml_should_skip_strip_image_size', false, $url ) === true ) {
			return $url;
		}
		if ( preg_match( '#(-\d+x\d+(?:_c)?|(@2x))\.(' . implode( '|', array_keys( Optml_Config::$image_extensions ) ) . '){1}$#i', $url, $src_parts ) ) {
			$stripped_url = str_replace( $src_parts[1], '', $url );
			// Extracts the file path to the image minus the base url
			$file_path = substr( $stripped_url, strpos( $stripped_url, $this->upload_resource['url'] ) + $this->upload_resource['url_length'] );
			if ( file_exists( $this->upload_resource['directory'] . $file_path ) ) {
				$url = $stripped_url;
			}
		}

		return $url;
	}

	/**
	 * Try to determine height and width from strings WP appends to resized image filenames.
	 *
	 * @param string $src The image URL.
	 *
	 * @return array An array consisting of width and height.
	 */
	protected function parse_dimensions_from_filename( $src ) {
		$width_height_string = [];
		$extensions          = array_keys( Optml_Config::$image_extensions );
		if ( preg_match( '#-(\d+)x(\d+)(:?_c)?\.(?:' . implode( '|', $extensions ) . '){1}$#i', $src, $width_height_string ) ) {
			$width  = (int) $width_height_string[1];
			$height = (int) $width_height_string[2];
			$crop   = ( isset( $width_height_string[3] ) && $width_height_string[3] === '_c' );
			if ( $width && $height ) {
				return [ $width, $height, $crop ];
			}
		}

		return [ false, false, false ];
	}

	/**
	 * Get the optimized urls for the wp media modal.
	 *
	 * @param string $url Original url.
	 * @param string $table_id The cloud id of the image.
	 * @param string $width Image width.
	 * @param string $height Image height.
	 * @param array  $resize Optml crop array.
	 * @return string The optimized url.
	 */
	public function get_media_optimized_url( $url, $table_id, $width = 'auto', $height = 'auto', $resize = [] ) {
		$optimized_url = ( new Optml_Image( $url, ['width' => $width, 'height' => $height, 'resize' => $resize, 'quality' => $this->settings->get_numeric_quality()], $this->settings->get( 'cache_buster' ) ) )->get_url();
		$optimized_url = str_replace( $url, Optml_Media_Offload::KEYS['not_processed_flag'] . 'media_cloud' . '/' . Optml_Media_Offload::KEYS['uploaded_flag'] . $table_id . '/' . $url, $optimized_url );
		return $optimized_url;
	}
}
