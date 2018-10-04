<?php

/**
 * The class handles the image replacements and optimizations.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Replacer {
	/**
	 * Cached object instance.
	 *
	 * @var Optml_Replacer
	 */
	protected static $instance = null;

	/**
	 * A list of allowd extensions.
	 *
	 * @var array
	 */
	protected static $extensions = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
		'svg'          => 'image/svg+xml',
	);

	/**
	 * Holds an array of image sizes.
	 *
	 * @var array
	 */
	protected static $image_sizes;

	/**
	 * Te cdn url, it will be build on the run.
	 *
	 * @var null
	 */
	protected $cdn_url = null;

	/**
	 * A secret key to encode payload.
	 *
	 * @var null
	 */
	protected $cdn_secret = null;

	/**
	 * Defines which is the maximum width accepted in the optimization process.
	 *
	 * @var int
	 */
	protected $max_width = 3000;

	/**
	 * Defines the quality parameter.
	 *
	 * @var int
	 */
	protected $quality = 'auto';

	/**
	 * Defines which is the maximum width accepted in the optimization process.
	 *
	 * @var int
	 */
	protected $max_height = 3000;

	/**
	 * Holds the real images sizes as an array.
	 *
	 * @var null
	 */
	protected $img_real_sizes = null;

	/**
	 * A cached version of `wp_upload_dir`
	 *
	 * @var null
	 */
	protected $upload_dir = null;
	/**
	 * Setings handler.
	 *
	 * @var Optml_Settings $settings
	 */
	protected $settings = null;

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
			self::$instance->init();
		}

		return self::$instance;
	}

	/**
	 * The initialize method.
	 */
	function init() {

		add_filter( 'optml_replace_image', array( $this, 'get_imgcdn_url' ), 10, 2 );

		$this->settings = new Optml_Settings();

		$this->set_properties();

		if ( ! $this->should_replace() ) {
			return;
		}

		if ( empty( $this->cdn_url ) ) {
			return;
		}
		add_filter( 'image_downsize', array( $this, 'filter_image_downsize' ), PHP_INT_MAX, 3 );
		add_filter( 'the_content', array( $this, 'filter_the_content' ), PHP_INT_MAX );
		add_filter( 'wp_calculate_image_srcset', array( $this, 'filter_srcset_attr' ), PHP_INT_MAX, 5 );
		add_filter( 'init', array( $this, 'filter_options_and_mods' ) );
		add_action( 'template_redirect', array( $this, 'init_html_replacer' ), PHP_INT_MAX );
		add_action( 'get_post_metadata', array( $this, 'replace_meta' ), PHP_INT_MAX, 4 );
	}

	/**
	 * Set the cdn url based on the current connected user.
	 */
	protected function set_properties() {
		$this->upload_dir = wp_upload_dir();
		$this->upload_dir = $this->upload_dir['baseurl'];

		$service_data = $this->settings->get( 'service_data' );
		if ( ! isset( $service_data['cdn_key'] ) ) {
			return;
		}
		$cdn_key       = $service_data ['cdn_key'];
		$cdn_secret    = $service_data['cdn_secret'];
		$this->quality = $this->settings->get( 'quality' );
		if ( empty( $cdn_key ) || empty( $cdn_secret ) ) {
			return;
		}
		$this->cdn_secret = $cdn_secret;
		$this->cdn_url    = sprintf(
			'https://%s.%s',
			strtolower( $cdn_key ),
			'i.optimole.com'
		);
	}

	/**
	 * Check if we should rewrite the urls.
	 *
	 * @return bool If we can replace the image.
	 *
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

		return true;
	}

	/**
	 * Init html replacer handler.
	 */
	public function init_html_replacer() {
		if ( is_admin() ) {
			return;
		}
		ob_start(
			array( &$this, 'replace_urls' )
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
		// we don't run optimizations on dashboard side
		if ( is_admin() ) {
			return $image;
		}

		$image_url = wp_get_attachment_url( $attachment_id );

		if ( $image_url ) {
			// $image_meta = image_get_intermediate_size( $attachment_id, $size );
			$image_meta = wp_get_attachment_metadata( $attachment_id );
			$image_args = self::image_sizes();

			// default size
			$sizes = array(
				'width'  => isset( $image_meta['width'] ) ? intval( $image_meta['width'] ) : 'auto',
				'height' => isset( $image_meta['height'] ) ? intval( $image_meta['height'] ) : 'auto',
			);
			// in case there is a custom image size $size will be an array.
			if ( is_array( $size ) ) {
				$sizes = array(
					'width'  => ( $size[0] < $sizes['width'] ? $size[0] : $sizes['width'] ),
					'height' => ( $size[1] < $sizes['height'] ? $size[0] : $sizes['height'] ),
				);
			} elseif ( 'full' !== $size && isset( $image_args[ $size ] ) ) { // overwrite if there a size
				$sizes = array(
					'width'  => $image_args[ $size ]['width'] < $sizes['width'] ? $image_args[ $size ]['width'] : $sizes['width'],
					'height' => $image_args[ $size ]['height'] < $sizes['height'] ? $image_args[ $size ]['height'] : $sizes['height'],
				);
			}

			$new_sizes = $this->validate_image_sizes( $sizes['width'], $sizes['height'] );

			// resized thumbnails will have their own filenames. we should get those instead of the full image one
			if ( is_string( $size ) && ! empty( $image_meta['sizes'] ) && ! empty( $image_meta['sizes'][ $size ] ) ) {
				$image_url = str_replace( basename( $image_url ), $image_meta['sizes'][ $size ]['file'], $image_url );
			}

			// try to get an optimized image url.
			$new_url = $this->get_imgcdn_url( $image_url, $new_sizes );
			if ( $new_url === $image_url ) {
				return $image;
			}
			$return = array(
				$new_url,
				$sizes['width'],
				$sizes['height'],
				false,
			);

			return $return;
		}

		// in case something wrong comes, well return the default.
		return $image;
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
		if ( null == self::$image_sizes ) {
			global $_wp_additional_image_sizes;

			// Populate an array matching the data structure of $_wp_additional_image_sizes so we have a consistent structure for image sizes
			$images = array(
				'thumb'  => array(
					'width'  => intval( get_option( 'thumbnail_size_w' ) ),
					'height' => intval( get_option( 'thumbnail_size_h' ) ),
					'crop'   => (bool) get_option( 'thumbnail_crop' ),
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
		}

		return is_array( self::$image_sizes ) ? self::$image_sizes : array();
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

		$percentWidth = $percentHeight = null;

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
	 * Returns a signed image url authorized to be used in our CDN.
	 *
	 * @param string $url The url which should be signed.
	 * @param array  $args Dimension params; Supports `width` and `height`.
	 *
	 * @return string
	 */
	public function get_imgcdn_url( $url, $args = array( 'width' => 'auto', 'height' => 'auto' ) ) {
		if ( apply_filters( 'optml_dont_replace_url', false, $url ) ) {
			return $url;
		}
		if ( ! $this->check_mimetype( $url ) ) {

			return $url;
		}
		// not used yet.
		$compress_level = apply_filters( 'optml_image_quality', $this->quality );
		if ( isset( $args['quality'] ) || ! empty( $args['quality'] ) ) {
			$compress_level = $args['quality'];
		}
		$compress_level = $this->normalize_quality( $compress_level );
		// this will authorize the image
		$url_parts = explode( '://', $url );
		$scheme    = $url_parts[0];
		$path      = $url_parts[1];
		if ( $args['width'] !== 'auto' ) {
			$args['width'] = round( $args['width'], 0 );
		}
		if ( $args['height'] !== 'auto' ) {
			$args['height'] = round( $args['height'], 0 );
		}
		$payload = array(
			'path'    => $this->urlception_encode( $path ),
			'scheme'  => $scheme,
			'width'   => (string) $args['width'],
			'height'  => (string) $args['height'],
			'quality' => (string) $compress_level,
		);
		ksort( $payload );

		$values  = array_values( $payload );
		$payload = implode( '', $values );
		$hash    = hash_hmac( 'md5', $payload, $this->cdn_secret );

		$new_url = sprintf(
			'%s/%s/%s/%s/%s/%s/%s',
			$this->cdn_url,
			$hash,
			(string) $args['width'],
			(string) $args['height'],
			(string) $compress_level,
			$scheme,
			$path
		);

		return $new_url;
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
	 * @param string|int $quality Normalize quality
	 *
	 * @return int Numeric quality.
	 */
	private function normalize_quality( $quality ) {

		if ( is_numeric( $quality ) ) {
			return intval( $quality );
		}
		$quality = trim( $quality );
		if ( $quality === 'auto' ) {
			return 'auto';
		}
		if ( $quality === 'high' ) {
			return 85;
		}
		if ( $quality === 'medium' ) {
			return 55;
		}
		if ( $quality === 'low' ) {
			return 25;
		}

		return 60;
	}

	/**
	 * Ensures that an url parameter can stand inside an url.
	 *
	 * @param string $url The required url.
	 *
	 * @return string
	 */
	protected function urlception_encode( $url ) {
		$new_url = rtrim( $url, '/' );

		return urlencode( $new_url );
	}

	/**
	 * Identify images in post content.
	 *
	 * @param string $content The post content which will be filtered.
	 *
	 * @return string
	 */
	public function filter_the_content( $content ) {
		$images = self::parse_images_from_html( $content );

		if ( empty( $images ) ) {
			return $content; // simple. no images
		}

		$image_sizes = self::image_sizes();
		foreach ( $images[0] as $index => $tag ) {
			$width   = $height = false;
			$new_tag = $tag;
			$src     = $images['img_url'][ $index ];

			if ( apply_filters( 'optml_imgcdn_disable_optimization_for_link', false, $src ) ) {
				continue;
			}

			if ( false !== strpos( $src, 'i.optimole.com' ) ) {
				continue; // we already have this
			}

			// we handle only images uploaded to this site.
			if ( false === strpos( $src, $this->upload_dir ) ) {
				continue;
			}

			// try to get the declared sizes from the img tag
			if ( preg_match( '#width=["|\']?([\d%]+)["|\']?#i', $images['img_tag'][ $index ], $width_string ) ) {
				$width = $width_string[1];
			}

			if ( preg_match( '#height=["|\']?([\d%]+)["|\']?#i', $images['img_tag'][ $index ], $height_string ) ) {
				$height = $height_string[1];
			}

			// Detect WP registered image size from HTML class
			if ( preg_match( '#class=["|\']?[^"\']*size-([^"\'\s]+)[^"\']*["|\']?#i', $images['img_tag'][ $index ], $size ) ) {
				$size = array_pop( $size );

				if ( false === $width && false === $height && 'full' != $size && array_key_exists( $size, $image_sizes ) ) {
					$width  = (int) $image_sizes[ $size ]['width'];
					$height = (int) $image_sizes[ $size ]['height'];
				}
			} else {
				unset( $size );
			}

			$new_sizes = $this->validate_image_sizes( $width, $height );

			$new_url = $this->get_imgcdn_url( $src, $new_sizes );
			if ( $new_url === $src ) {
				continue;
			}
			// replace the url in hrefs or links
			if ( ! empty( $images['link_url'][ $index ] ) ) {
				if ( $this->check_mimetype( $images['link_url'][ $index ] ) ) {
					$new_tag = preg_replace( '#(href=["|\'])' . $images['link_url'][ $index ] . '(["|\'])#i', '\1' . $new_url . '\2', $tag, 1 );
				}
			}

			// replace the new sizes
			$new_tag = str_replace( 'width="' . $width . '"', 'width="' . $new_sizes['width'] . '"', $new_tag );
			$new_tag = str_replace( 'height="' . $height . '"', 'height="' . $new_sizes['height'] . '"', $new_tag );
			// replace the new url
			$new_tag = str_replace( 'src="' . $src . '"', 'src="' . $new_url . '"', $new_tag );

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
	protected static function parse_images_from_html( $content ) {
		$images = array();

		if ( preg_match_all( '#(?:<a[^>]+?href=["|\'](?P<link_url>[^\s]+?)["|\'][^>]*?>\s*)?(?P<img_tag><img[^>]*?\s+?src=["|\'](?P<img_url>[^\s]+?)["|\'].*?>){1}(?:\s*</a>)?#is', $content, $images ) ) {
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
	 * Replace image URLs in the srcset attributes and in case there is a resize in action, also replace the sizes.
	 *
	 * @param array $sources Array of image sources.
	 * @param array $size_array Array of width and height values in pixels (in that order).
	 * @param array $image_src The 'src' of the image.
	 * @param array $image_meta The image meta data as returned by 'wp_get_attachment_metadata()'.
	 * @param int   $attachment_id Image attachment ID.
	 *
	 * @return array
	 */
	public function filter_srcset_attr( $sources = array(), $size_array = array(), $image_src = array(), $image_meta = array(), $attachment_id = 0 ) {
		if ( ! is_array( $sources ) ) {
			return $sources;
		}
		$used        = array();
		$new_sources = array();
		foreach ( $sources as $i => $source ) {
			list( $width, $height ) = self::parse_dimensions_from_filename( $source['url'] );

			if ( empty( $width ) ) {
				$width = $image_meta['width'];
			}

			if ( empty( $height ) ) {
				$height = $image_meta['height'];
			}

			$new_sizes = $this->validate_image_sizes( $width, $height );
			$new_url   = $this->get_imgcdn_url( $source['url'], $new_sizes );
			if ( isset( $used[ md5( $new_url ) ] ) ) {
				continue;
			}

			$used[ md5( $new_url ) ]  = true;
			$new_sources[ $i ]        = $sources[ $i ];
			$new_sources[ $i ]['url'] = $new_url;

			if ( $new_sources[ $i ]['descriptor'] ) {
				$new_sources[ $i ]['value'] = $new_sizes['width'];
			} else {
				$new_sources[ $i ]['value'] = $new_sizes['height'];
			}
		}

		return $new_sources;
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
		$theme_slug = get_option( 'stylesheet' );

		$options_list = apply_filters(
			'optml_imgcdn_options_with_url', array(
				"theme_mods_$theme_slug",
			)
		);

		foreach ( $options_list as $option ) {
			add_filter( "option_$option", array( $this, 'replace_option_url' ) );

			// this one will not work for theme mods, since get_theme_mod('header_image', $default) has its own default.
			// add_filter( "default_option_$option", array( $this, 'replace_option_url' ) );
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

		// we handle only images uploaded to this site./
		// @TODO this is still wrong, not all the images are coming from the uploads folder.
		// if ( false === strpos( $url, $this->upload_dir ) ) {
		// return $url;
		// }
		// get the optimized url.
		$new_url = $this->get_imgcdn_url( $url );

		return $new_url;
	}

	/**
	 * Replace urls in post meta values.
	 *
	 * @param mixed  $metadata Metadata.
	 * @param int    $object_id Post id.
	 * @param string $meta_key Meta key.
	 * @param bool   $single Is single?
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

			return $this->replace_urls( $current_meta, 'elementor' );
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
	public function replace_urls( $html, $context = 'raw' ) {

		switch ( $context ) {
			case 'elementor':
				$old_urls = $this->extract_slashed_urls( $html );
				$urls     = array_map( 'wp_unslash', $old_urls );
				$urls     = array_combine( $old_urls, $urls );
				//return $html;
				break;
			case 'raw':
			default:
				$urls = wp_extract_urls( $html );

				$urls = array_combine( $urls, $urls );
				break;

		}

		$cdn_url  = $this->cdn_url;
		$site_url = get_site_url();
		$urls     = array_filter(
			$urls, function ( $url ) use ( $cdn_url, $site_url ) {
			if ( strpos( $url, $cdn_url ) !== false ) {
				return false;
			}
			if ( strpos( $url, $site_url ) === false ) {
				return false;
			}

			return $this->check_mimetype( $url );
		}
		);

		$urls = array_map( array( $this, 'get_imgcdn_url' ), $urls );

		return str_replace( array_keys( $urls ), array_values( $urls ), $html );
	}

	/**
	 * Extract slashed urls from content.
	 *
	 * @param string $content Content to parse.
	 *
	 * @return array Urls found.
	 */
	private function extract_slashed_urls( $content ) {
		/**
		 * Regex rule to match slashed urls, i.e -> http:\/\/optimole.com\/wp-content\/uploads\/2018\/09\/picture.jpg
		 * Based on the extract_url patter.
		 *
		 * @var string Regex rule string.
		 *
		 */
		$regex = "/(http(s)*.+?)\"/";

		preg_match_all(
			$regex,
			$content,
			$urls
		);

		$urls = array_map( function ( $value ) {
			return rtrim( html_entity_decode( $value ), '\\' );
		}, $urls[1] );

		$urls = array_unique( $urls );

		return array_values( $urls );
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
