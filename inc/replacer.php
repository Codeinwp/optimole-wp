<?php

/**
 * The class handles the image replacements and optimizations.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Replacer {
	/**
	 * A list of allowed extensions.
	 *
	 * @var array
	 */
	public static $extensions = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
		'svg'          => 'image/svg+xml',
	);
	/**
	 * Cached object instance.
	 *
	 * @var Optml_Replacer
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

	/**
	 * Class instance method.
	 *
	 * @static
	 * @since  1.0.0
	 * @access public
	 * @return Optml_Replacer
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
	function init() {

		add_filter( 'optml_replace_image', array( $this, 'get_image_url' ), 10, 2 );

		$this->settings = new Optml_Settings();

		if ( ! $this->should_replace() ) {
			return;
		}
		$this->set_properties();

		add_filter( 'the_content', array( $this, 'filter_image_tags' ), PHP_INT_MAX );
		add_filter( 'init', array( $this, 'filter_options_and_mods' ) );
		add_action( 'template_redirect', array( $this, 'init_html_replacer' ), PHP_INT_MAX );
		add_action( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX, 4 );

		add_filter( 'image_downsize', array( $this, 'filter_image_downsize' ), PHP_INT_MAX, 3 );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'filter_srcset_attr' ), PHP_INT_MAX, 5 );
		add_filter( 'wp_calculate_image_sizes', array( $this, 'filter_sizes_attr' ), 1, 2 );

	}

	/**
	 * Check if we should rewrite the urls.
	 *
	 * @return bool If we can replace the image.
	 */
	public function should_replace() {

		if ( is_admin() ) {
			return false;
		}

		if ( ! $this->settings->is_connected() ) {
			return false;
		}
		if ( ! $this->settings->is_enabled() ) {
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

		if ( is_customize_preview() ) {
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

		if ( defined( 'OPTML_SITE_MIRROR' ) && ! empty( OPTML_SITE_MIRROR ) ) {
			$this->site_mappings = array(
				rtrim( get_site_url(), '/' ) => rtrim( OPTML_SITE_MIRROR, '/' ),
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
	 * Init html replacer handler.
	 */
	public function init_html_replacer() {

		// We no longer need this if the handler was started.
		remove_filter( 'the_content', array( $this, 'filter_image_tags' ), PHP_INT_MAX );

		ob_start(
			array( &$this, 'replace_content' )
		);
	}

	/**
	 * This filter will replace all the images retrieved via "wp_get_image" type of functions.
	 *
	 * @param array        $image The filtered value.
	 * @param int          $attachment_id The related attachment id.
	 * @param array|string $size This could be the name of the thumbnail size or an array of custom dimensions.
	 *
	 * @return array
	 */
	public function filter_image_downsize( $image, $attachment_id, $size ) {

		if ( $this->settings->use_lazyload() ) {
			return $image;
		}

		$image_url = wp_get_attachment_url( $attachment_id );

		if ( $image_url === false ) {
			return $image;
		}

		// $image_meta = image_get_intermediate_size( $attachment_id, $size );
		$image_meta = wp_get_attachment_metadata( $attachment_id );
		$image_args = self::image_sizes();

		// default size
		$sizes = array(
			'width'  => isset( $image_meta['width'] ) ? intval( $image_meta['width'] ) : 'auto',
			'height' => isset( $image_meta['height'] ) ? intval( $image_meta['height'] ) : 'auto',
			'resize' => Optml_Image::RESIZE_FIT,
		);

		// in case there is a custom image size $size will be an array.
		if ( is_array( $size ) ) {

			$sizes['width']  = ( $size[0] < $sizes['width'] ? $size[0] : $sizes['width'] );
			$sizes['height'] = ( $size[1] < $sizes['height'] ? $size[1] : $sizes['height'] );

		} elseif ( 'full' !== $size && isset( $image_args[ $size ] ) ) { // overwrite if there a size
			$sizes['width']  = $image_args[ $size ]['width'] < $sizes['width'] ? $image_args[ $size ]['width'] : $sizes['width'];
			$sizes['height'] = $image_args[ $size ]['height'] < $sizes['height'] ? $image_args[ $size ]['height'] : $sizes['height'];
			$sizes           = array_merge( $sizes, $this->wp_crop_to_optml( $image_args[ $size ]['crop'] ) );
		}

		$new_sizes = $this->validate_image_sizes( $sizes['width'], $sizes['height'] );
		$image_url = $this->strip_image_size_maybe( $image_url );
		$new_url   = $this->get_image_url( $image_url, $new_sizes );

		if ( $new_url === $image_url ) {
			return $image;
		}

		return array(
			$new_url,
			$sizes['width'],
			$sizes['height'],
			false,
		);
	}

	/**
	 * Returns the array of image sizes since `get_intermediate_image_sizes` and image metadata  doesn't include the
	 * custom image sizes in a reliable way.
	 *
	 * Inspired from jetpack/photon.
	 *
	 * @global $wp_additional_image_sizes
	 *
	 * @return array
	 */
	protected static function image_sizes() {

		if ( null != self::$image_sizes && is_array( self::$image_sizes ) ) {
			return self::$image_sizes;
		}

		global $_wp_additional_image_sizes;

		// Populate an array matching the data structure of $_wp_additional_image_sizes so we have a consistent structure for image sizes
		$images = array(
			'thumb'  => array(
				'width'  => intval( get_option( 'thumbnail_size_w' ) ),
				'height' => intval( get_option( 'thumbnail_size_h' ) ),
				'crop'   => get_option( 'thumbnail_crop', false ),
			),
			'medium' => array(
				'width'  => intval( get_option( 'medium_size_w' ) ),
				'height' => intval( get_option( 'medium_size_h' ) ),
				'crop'   => false,
			),
			'large'  => array(
				'width'  => intval( get_option( 'large_size_w' ) ),
				'height' => intval( get_option( 'large_size_h' ) ),
				'crop'   => false,
			),
			'full'   => array(
				'width'  => null,
				'height' => null,
				'crop'   => false,
			),
		);

		// Compatibility mapping as found in wp-includes/media.php
		$images['thumbnail'] = $images['thumb'];

		// Update class variable, merging in $_wp_additional_image_sizes if any are set
		if ( is_array( $_wp_additional_image_sizes ) && ! empty( $_wp_additional_image_sizes ) ) {
			self::$image_sizes = array_merge( $images, $_wp_additional_image_sizes );
		} else {
			self::$image_sizes = $images;
		}

		return is_array( self::$image_sizes ) ? self::$image_sizes : array();
	}

	/**
	 * Convert WordPress cropping strategy to optimole schema.
	 *
	 * @param array $crop_args WordPress args.
	 *
	 * @return array|string Gravity position for optimole.
	 */
	private function wp_crop_to_optml( $crop_args = array() ) {
		if ( $crop_args === false ) {
			return array();
		}
		if ( $crop_args === true ) {
			return array(
				'resize'  => Optml_Image::RESIZE_FILL,
				'gravity' => Optml_Image::GRAVITY_CENTER,
			);
		}
		if ( ! is_array( $crop_args ) ) {
			return array();
		}

		if ( empty( $crop_args ) || count( $crop_args ) != 2 ) {
			return array(
				'resize'  => Optml_Image::RESIZE_FILL,
				'gravity' => Optml_Image::GRAVITY_CENTER,
			);
		}
		switch ( strval( $crop_args[0] ) . strval( $crop_args[1] ) ) {
			case 'left':
				$gravity = Optml_Image::GRAVITY_WEST;
				break;
			case 'right':
				$gravity = Optml_Image::GRAVITY_EAST;
				break;
			case 'top':
				$gravity = Optml_Image::GRAVITY_NORTH;
				break;
			case 'bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH;
				break;
			case 'center':
				$gravity = Optml_Image::GRAVITY_CENTER;
				break;

			case 'left_top':
				$gravity = Optml_Image::GRAVITY_NORTH_WEST;
				break;
			case 'left_bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH_WEST;
				break;

			case 'right_top':
				$gravity = Optml_Image::GRAVITY_NORTH_EAST;
				break;
			case 'right_bottom':
				$gravity = Optml_Image::GRAVITY_SOUTH_EAST;
				break;
			case 'center_top':
				$gravity = array( 0.5, 0 );
				break;
			case 'center_bottom':
				$gravity = array( 0.5, 1 );
				break;
			case 'left_center':
				$gravity = array( 0, 0.5 );
				break;
			case 'right_center':
				$gravity = array( 1, 0.5 );
				break;
			case 'center_center':
			default:
				$gravity = Optml_Image::GRAVITY_CENTER;
				break;
		}

		return array(
			'resize'  => Optml_Image::RESIZE_FILL,
			'gravity' => $gravity,
		);
	}

	/**
	 * Keep the image sizes under a sane limit.
	 *
	 * @param string $width The width value which should be sanitized.
	 * @param string $height The height value which should be sanitized.
	 *
	 * @return array
	 */
	protected function validate_image_sizes( $width, $height ) {
		global $content_width;
		/**
		 * While we are inside a content filter we need to keep our max_width under the content_width global
		 * There is no reason the have a image wider than the content width.
		 */
		if (
			doing_filter( 'the_content' )
			&& isset( $GLOBALS['content_width'] )
			&& apply_filters( 'optml_imgcdn_allow_resize_images_from_content_width', false )
		) {
			$content_width = (int) $GLOBALS['content_width'];

			if ( $this->max_width > $content_width ) {
				$this->max_width = $content_width;
			}
		}

		if ( $width > $this->max_width ) {
			// we need to remember how much in percentage the width was resized and apply the same treatment to the height.
			$percentWidth = ( 1 - $this->max_width / $width ) * 100;
			$width        = $this->max_width;
			$height       = round( $height * ( ( 100 - $percentWidth ) / 100 ), 0 );
		}

		// now for the height
		if ( $height > $this->max_height ) {
			$percentHeight = ( 1 - $this->max_height / $height ) * 100;
			// if we reduce the height to max_height by $x percentage than we'll also reduce the width for the same amount.
			$height = $this->max_height;
			$width  = round( $width * ( ( 100 - $percentHeight ) / 100 ), 0 );
		}

		return array(
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Checks if the file is a image size and return the full url.
	 *
	 * @param string $src The image URL.
	 *
	 * @return string
	 **/
	protected function strip_image_size_maybe( $src ) {

		if ( preg_match( '#(-\d+x\d+)\.(' . implode( '|', array_keys( self::$extensions ) ) . '){1}$#i', $src, $src_parts ) ) {
			$stripped_src = str_replace( $src_parts[1], '', $src );
			// Extracts the file path to the image minus the base url
			$file_path = substr( $stripped_src, strpos( $stripped_src, $this->upload_resource['url'] ) + $this->upload_resource['url_length'] );
			if ( file_exists( $this->upload_resource['directory'] . $file_path ) ) {
				$src = $stripped_src;
			}
		}

		return $src;
	}

	/**
	 * Returns a signed image url authorized to be used in our CDN.
	 *
	 * @param string $url The url which should be signed.
	 * @param array  $args Dimension params; Supports `width` and `height`.
	 *
	 * @return string
	 */
	public function get_image_url(
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
		if ( ! $this->check_mimetype( $url ) ) {
			return $url;
		}

		$compress_level = apply_filters( 'optml_image_quality', $this->settings->get_quality() );
		if ( isset( $args['quality'] ) && ! empty( $args['quality'] ) ) {
			$compress_level = $args['quality'];
		}

		$args['quality'] = $this->normalize_quality( $compress_level );

		// this will authorize the image
		if ( ! empty( $this->site_mappings ) ) {
			$url = str_replace( array_keys( $this->site_mappings ), array_values( $this->site_mappings ), $url );
		}

		return ( new Optml_Image( $url, $args ) )->get_url( $this->is_allowed_site );
	}

	/**
	 * Check url mimetype.
	 *
	 * @param string $url Url to check.
	 *
	 * @return bool Is a valid image url or not.
	 */
	private function check_mimetype( $url ) {

		$mimes = self::$extensions;
		$type  = wp_check_filetype( $url, $mimes );

		if ( ! isset( $type['ext'] ) || empty( $type['ext'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Sanitize quality.
	 *
	 * @param string|int $quality Normalize quality.
	 *
	 * @return int Numeric quality.
	 */
	private function normalize_quality( $quality ) {

		if ( is_numeric( $quality ) ) {
			return intval( $quality );
		}
		$quality = trim( $quality );
		if ( $quality === 'eco' ) {
			return 'eco';
		}
		if ( $quality === 'auto' ) {
			return 'auto';
		}
		if ( $quality === 'high_c' ) {
			return 55;
		}
		if ( $quality === 'medium_c' ) {
			return 75;
		}
		if ( $quality === 'low_c' ) {
			return 90;
		}

		// Legacy values.
		return 60;
	}

	/**
	 * Filters sizes attribute of the images.
	 *
	 * @param array $sizes An array of media query breakpoints.
	 * @param array $size Width and height of the image
	 *
	 * @return mixed An array of media query breakpoints.
	 */
	public function filter_sizes( $sizes, $size ) {
		if ( ! doing_filter( 'the_content' ) ) {
			return $sizes;
		}

		$content_width = isset( $GLOBALS['content_width'] ) ? $GLOBALS['content_width'] : false;
		if ( ! $content_width ) {
			$content_width = 1000;
		}

		if ( ( is_array( $size ) && $size[0] < $content_width ) ) {
			return $sizes;
		}

		return sprintf( '(max-width: %1$dpx) 100vw, %1$dpx', $content_width );
	}

	/**
	 * Replace image URLs in the srcset attributes and in case there is a resize in action, also replace the sizes.
	 *
	 * @param array  $sources Array of image sources.
	 * @param array  $size_array Array of width and height values in pixels (in that order).
	 * @param string $image_src The 'src' of the image.
	 * @param array  $image_meta The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int    $attachment_id Image attachment ID.
	 *
	 * @return array
	 */
	public function filter_srcset_attr( $sources = array(), $size_array = array(), $image_src = '', $image_meta = array(), $attachment_id = 0 ) {
		if ( ! is_array( $sources ) ) {
			return $sources;
		}
		// If lazyload is enabled and we are not in AMP/feed context we should drop this.
		if ( $this->settings->use_lazyload() && ! $this->should_ignore_image_tags() ) {
			return array();
		}
		foreach ( $sources as $i => $source ) {
			$url = $source['url'];
			list( $width, $height ) = self::parse_dimensions_from_filename( $url );

			if ( empty( $width ) ) {
				$width = $image_meta['width'];
			}

			if ( empty( $height ) ) {
				$height = $image_meta['height'];
			}

			if ( ! empty( $attachment_id ) ) {
				$url = wp_get_attachment_url( $attachment_id );
			} else {
				$url = $this->strip_image_size_maybe( $source['url'] );
			}
			$args = array();
			if ( 'w' === $source['descriptor'] ) {
				if ( $height && ( $source['value'] == $width ) ) {
					$args['width']  = $width;
					$args['height'] = $height;
				} else {
					$args['width'] = $source['value'];
				}
			}
			$sources[ $i ]['url'] = $this->get_image_url( $url, $args );
		}

		return $sources;
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
	 * Try to determine height and width from strings WP appends to resized image filenames.
	 *
	 * @param string $src The image URL.
	 *
	 * @return array An array consisting of width and height.
	 */
	public static function parse_dimensions_from_filename( $src ) {
		$width_height_string = array();
		$extensions          = array_keys( self::$extensions );
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
				} else {
					return $array;
				}
			}

			if ( filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
				return $url;
			}
		}

		$new_url = $this->get_image_url( $url );

		return $new_url;
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
	function replace_meta( $metadata, $object_id, $meta_key, $single ) {

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
	 *
	 * @return mixed Filtered content.
	 */
	public function replace_content( $html, $context = 'raw' ) {
		//return $html;
		$html     = $this->filter_image_tags( $html );
		$old_urls = $this->extract_non_replaced_urls( $html );
		$urls     = array_combine( $old_urls, $old_urls );
		switch ( $context ) {
			case 'elementor':
				$urls = array_map( 'wp_unslash', $urls );
				break;
		}
		$urls = array_map(
			function ( $url ) {

				$tmp_new_url = $this->strip_image_size_maybe( $url );
				$new_url     = $this->get_image_url( $tmp_new_url );
				if ( $tmp_new_url == $new_url ) {
					return $url;
				}

				return $new_url;
			},
			$urls
		);

		return str_replace( array_keys( $urls ), array_values( $urls ), $html );
	}

	/**
	 * Identify image tags in html.
	 *
	 * @param string $content The html which will be filtered.
	 *
	 * @return string
	 */
	public function filter_image_tags( $content ) {
		if ( $this->should_ignore_image_tags() ) {
			return $content;
		}
		$images = self::parse_images_from_html( $content );

		if ( empty( $images ) ) {
			return $content;
		}
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

			// try to get the declared sizes from the img tag
			if ( preg_match( '#width=["|\']?([\d%]+)["|\']?#i', $images['img_tag'][ $index ], $width_string ) ) {
				$width = $width_string[1];
			}

			if ( preg_match( '#height=["|\']?([\d%]+)["|\']?#i', $images['img_tag'][ $index ], $height_string ) ) {
				$height = $height_string[1];
			}

			if ( preg_match( '#class=["|\']?[^"\']*size-([^"\'\s]+)[^"\']*["|\']?#i', $images['img_tag'][ $index ], $size ) ) {
				$size = array_pop( $size );

				if ( false === $width && false === $height && 'full' != $size && array_key_exists( $size, $image_sizes ) ) {
					$width  = (int) $image_sizes[ $size ]['width'];
					$height = (int) $image_sizes[ $size ]['height'];
					$resize = $this->wp_crop_to_optml( $image_sizes[ $size ]['crop'] );
				}
			} else {
				unset( $size );
			}

			if ( false === $width && false === $height ) {
				list( $width, $height ) = self::parse_dimensions_from_filename( $tmp );
			}

			$optml_args = $this->validate_image_sizes( $width, $height );

			$tmp        = $this->strip_image_size_maybe( $tmp );
			$optml_args = array_merge( $optml_args, $resize );

			if ( $new_url === $tmp ) {
				continue;
			}

			// replace the url in hrefs or links
			if ( ! empty( $images['link_url'][ $index ] ) ) {
				if ( $this->check_mimetype( $images['link_url'][ $index ] ) ) {
					$new_tag = preg_replace( '#(href=["|\'])' . $images['link_url'][ $index ] . '(["|\'])#i', '\1' . $this->get_image_url( $tmp ) . '\2', $tag, 1 );
				}
			}

			$new_tag = str_replace( 'width="' . $width . '"', 'width="' . $optml_args['width'] . '"', $new_tag );
			$new_tag = str_replace( 'height="' . $height . '"', 'height="' . $optml_args['height'] . '"', $new_tag );

			$new_tag = apply_filters( 'optml_image_tag_replacement', $new_tag, $original_url, str_replace( 'src="' . $images['img_url'][ $index ] . '"', 'src="' . $new_url . '"', $new_tag ) );

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
	 * Check if we can replace the url.
	 *
	 * @param string $url Url to change.
	 *
	 * @return bool Either we can replace this url or not.
	 */
	public function can_replace_url( $url ) {
		if ( ! is_string( $url ) ) {
			return false;
		}
		$url = @parse_url( $url );

		return isset( $this->possible_sources[ $url['host'] ] );
	}

	/**
	 * Extract slashed urls from content.
	 *
	 * @param string $content Content to parse.
	 *
	 * @return array Urls found.
	 */
	private function extract_non_replaced_urls( $content ) {
		/**
		 * Based on the extract_url patter.
		 *
		 * @var string Regex rule string.
		 */
		$regex = '/(?:http(?:s?):)(?:[\/\\\\|.|\w|\s|-](?!i.optimole.com))*\.(?:' . implode( '|', array_keys( self::$extensions ) ) . ')/';
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
			return true;
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
