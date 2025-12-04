<?php

use Optimole\Sdk\Optimole;
use Optimole\Sdk\Resource\Image;
use Optimole\Sdk\Resource\ImageProperty\GravityProperty;
use Optimole\Sdk\ValueObject\Position;

/**
 * The class handles the url replacements.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
final class Optml_Url_Replacer extends Optml_App_Replacer {

	use Optml_Dam_Offload_Utils;
	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Cached object instance.
	 *
	 * @var Optml_Url_Replacer
	 */
	protected static $instance = null;

	/**
	 * Class instance method.
	 *
	 * @codeCoverageIgnore
	 * @static
	 * @return Optml_Url_Replacer
	 * @since  1.0.0
	 * @access public
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			add_action( 'optml_replacer_setup', [ self::$instance, 'init' ] );
		}

		return self::$instance;
	}


	/**
	 * The initialize method.
	 */
	public function init() {

		add_filter( 'optml_replace_image', [ $this, 'build_url' ], 10, 2 );
		parent::init();

		add_filter( 'optml_content_url', [ $this, 'build_url' ], 1, 2 );
	}

	/**
	 * Returns a signed image url authorized to be used in our CDN.
	 *
	 * @param string $url The url which should be signed.
	 * @param array  $args Dimension params; Supports `width` and `height`.
	 * @param bool   $retried Whether the url has been retried.
	 * @return string
	 */
	public function build_url(
		$url,
		$args = [
			'width'  => 'auto',
			'height' => 'auto',
		],
		$retried = false
	) {
		if ( apply_filters( 'optml_dont_replace_url', false, $url ) ) {
			return $url;
		}

		if ( OPTML_DEBUG ) {
			do_action( 'optml_log', 'building url: ' . $url . ' args: ' . print_r( $args, true ) );
		}
		$original_url = $url;

		$is_slashed = strpos( $url, '\/' ) !== false;

		// We do a little hack here, for json unicode chars we first replace them with html special chars,
		// we then strip slashes to normalize the URL and last we convert html special chars back to get a clean URL
		$url = $is_slashed ? html_entity_decode( stripslashes( preg_replace( '/\\\u([\da-fA-F]{4})/', '&#x\1;', $url ) ) ) : ( $url );
		$is_uploaded = Optml_Media_Offload::is_not_processed_image( $url );
		if ( $is_uploaded === true ) {
			$attachment_id = [];
			preg_match( '/\/' . Optml_Media_Offload::KEYS['not_processed_flag'] . '([^\/]*)\//', $url, $attachment_id );
			if ( ! isset( $attachment_id[1] ) ) {
				return $url;
			}
			$id_and_filename = [];
			preg_match( '/\/(' . Optml_Media_Offload::KEYS['uploaded_flag'] . '.*)/', $url, $id_and_filename );
			if ( isset( $id_and_filename[0] ) ) {
				$id_and_filename = $id_and_filename[0];
			}
			$sizes = $this->parse_dimension_from_optimized_url( $url );
			if ( isset( $sizes[0] ) && $sizes[0] !== false ) {
				$args['width'] = $sizes[0];
			}
			if ( isset( $sizes[1] ) && $sizes[1] !== false ) {
				$args['height'] = $sizes[1];
			}
			$unoptimized_url = false;
			if ( $attachment_id[1] === 'media_cloud' ) {
				$tmp_url = explode( '/', $id_and_filename, 3 );
				if ( isset( $tmp_url[2] ) ) {
					$unoptimized_url = $tmp_url[2];
				}
			} else {
				$unoptimized_url = Optml_Media_Offload::get_original_url( (int) $attachment_id[1] );
			}
			if ( $unoptimized_url !== false ) {
				$url = $unoptimized_url;
			}
		}
		if ( isset( $args['force'] ) && $args['force'] === true && strpos( $url, Optml_Config::$service_url ) !== false && ! $this->url_has_dam_flag( $url ) ) {
			if ( OPTML_DEBUG ) {
				do_action( 'optml_log', 'url is already using optimole: ' . $url );
			}
			if ( $retried === true ) {
				return $original_url;
			}
			// We retry because the url is already using optimole, but we need to rebuild it because the args might be different.
			$pos = strpos( $url, 'http', 8 ); // skip 'https://' or 'http://'
			if ( $pos !== false ) {
				return $this->build_url( substr( $url, $pos ), $args, true );
			}
			return $original_url;
		}

		if ( ! $this->can_replace_url( $url ) ) {
			return $original_url;
		}
		// Remove any query strings that might affect conversion.
		$url = strtok( $url, '?' );
		$ext = $this->is_valid_mimetype_from_url( $url, self::$filters[ Optml_Settings::FILTER_TYPE_OPTIMIZE ][ Optml_Settings::FILTER_EXT ] );
		if ( false === $ext ) {
			return $original_url;
		}

		if ( isset( $args['quality'] ) && ! empty( $args['quality'] ) ) {
			$args['quality'] = $this->to_accepted_quality( $args['quality'] );
		}

		if ( isset( $args['minify'] ) && ! empty( $args['minify'] ) ) {
			$args['minify'] = $this->to_accepted_minify( $args['minify'] );
		}

		// this will authorize the image
		if ( ! empty( $this->site_mappings ) ) {
			$url = str_replace( array_keys( $this->site_mappings ), array_values( $this->site_mappings ), $url );
		}
		if ( substr( $url, 0, 2 ) === '//' ) {
			$url = sprintf( '%s:%s', is_ssl() ? 'https' : 'http', $url );
		}
		$normalized_ext = strtolower( $ext );
		if ( isset( Optml_Config::$image_extensions[ $normalized_ext ] ) ) {
			$new_url = $this->normalize_image( $url, $original_url, $args, $is_uploaded, $normalized_ext );
			if ( $is_uploaded ) {
				$new_url = str_replace( '/' . $url, $id_and_filename, $new_url );
			}
		} else {
			$asset = Optimole::asset( $url, $this->active_cache_buster_assets );

			if ( stripos( $url, '.css' ) || stripos( $url, '.js' ) ) {
				$asset->quality();
			}

			$asset->minify( ( $this->is_css_minify_on && str_ends_with( strtolower( $url ), '.css' ) ) || ( $this->is_js_minify_on && str_ends_with( strtolower( $url ), '.js' ) ) );

			return $asset->getUrl();
		}
		return $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;
	}

	/**
	 * Apply extra normalization to image.
	 *
	 * @param string $url Image url.
	 * @param string $original_url Original image url.
	 * @param array  $args Args.
	 *
	 * @return string
	 */
	private function normalize_image( $url, $original_url, $args, $is_uploaded = false, $ext = '' ) {

		$new_url = $this->strip_image_size_from_url( $url );

		if ( $new_url !== $url || $is_uploaded === true ) {
			if ( ! isset( $args['quality'] ) || $args['quality'] !== 'eco' ) {
				if ( $is_uploaded === true ) {
					$crop = false;
				}
				if ( $is_uploaded !== true ) {
					list($args['width'], $args['height'], $crop) = $this->parse_dimensions_from_filename( $url );
				}
				if ( ! $crop ) {
					$sizes2crop = self::size_to_crop();
					$crop       = isset( $sizes2crop[ $args['width'] . $args['height'] ] ) ? $sizes2crop[ $args['width'] . $args['height'] ] : false;
				}
				if ( $crop ) {
					$args['resize'] = $this->to_optml_crop( $crop );
				}
			}
			$url = $new_url;
		}

		if ( ! isset( $args['width'] ) ) {
			$args['width'] = $this->limit_dimensions_enabled ? $this->limit_width : 'auto';
		}
		if ( ! isset( $args['height'] ) ) {
			$args['height'] = $this->limit_dimensions_enabled ? $this->limit_height : 'auto';
		}

		$args['width']  = (int) $args['width'];
		$args['height'] = (int) $args['height'];

		if ( $this->limit_dimensions_enabled && $args['height'] !== 0 && $args['width'] !== 0 ) {
			if ( $args['width'] > $this->limit_width || $args['height'] > $this->limit_height ) {
				$scale = min( $this->limit_width / $args['width'], $this->limit_height / $args['height'] );

				if ( $scale < 1 ) {
					$args['width']  = (int) floor( $scale * $args['width'] );
					$args['height'] = (int) floor( $scale * $args['height'] );
				}
			}

			if ( isset( $args['resize'], $args['resize']['enlarge'] ) ) {
				$args['resize']['enlarge'] = false;
			}
		}

		if ( empty( $args['quality'] ) ) {
			$args['quality'] = $this->to_accepted_quality( $this->settings->get_quality() );
		}

		if ( isset( $args['resize'], $args['resize']['gravity'] ) && $this->settings->is_smart_cropping() ) {
			$args['resize']['gravity'] = GravityProperty::SMART;
		}

		$is_retina_enabled = $this->settings->get( 'retina_images' ) !== 'disabled';
		if ( ! $is_retina_enabled ) {
			$max_dimension = max( $args['width'], $args['height'] );
			$should_apply_dpr = false;

			if ( $max_dimension > 0 && $max_dimension < 150 ) {
				$should_apply_dpr = true;
			}

			$should_apply_dpr = apply_filters( 'optml_should_apply_dpr', $should_apply_dpr, $args, $url );

			if ( $should_apply_dpr && ! isset( $args['dpr'] ) ) {
				$args['dpr'] = 2;
			}
		}

		$args = apply_filters( 'optml_image_args', $args, $original_url );
		$image = Optimole::image( apply_filters( 'optml_processed_url', $url ), self::get_active_cache_booster( $url, $this->active_cache_buster ) );

		$image->width( ! empty( $args['width'] ) && is_int( $args['width'] ) ? $args['width'] : 'auto' );
		$image->height( ! empty( $args['height'] ) && is_int( $args['height'] ) ? $args['height'] : 'auto' );

		$image->quality( $args['quality'] );

		if ( ! empty( $args['resize'] ) ) {
			$this->apply_resize( $image, $args['resize'] );
		} elseif ( $this->settings->is_smart_cropping() ) {
			// If smart cropping is enabled and no resize is set, we apply smart focus since the resize can be triggered from the JS library.
			$image->smartFocus();
		}

		if ( apply_filters( 'optml_apply_watermark_for', true, $url ) ) {
			$this->apply_watermark( $image );
		}

		if ( ! empty( $args['format'] ) ) {
			$image->format( (string) $args['format'] );
		} elseif ( $this->settings->is_best_format() ) {
			// If format is not already set, we use best format if it's enabled.
			$image->format( 'best' );
		}

		if ( $this->settings->get( 'strip_metadata' ) === 'disabled' ) {
			$image->stripMetadata( false );
		}

		if ( ! apply_filters( 'optml_should_avif_ext', true, $ext, $original_url ) || $this->settings->get( 'avif' ) === 'disabled' ) {
			$image->ignoreAvif();
		}

		if ( apply_filters( 'optml_keep_copyright', false ) === true ) {
			$image->keepCopyright();
		}

		if ( $this->is_dam_url( $image->getSource() ) ) {
			return $this->get_dam_url( $image );
		}

		$offloaded_id = $this->is_offloaded_url( $image->getSource() );

		if ( isset( $args['dpr'] ) && $args['dpr'] > 1 ) {
			$image->dpr( $args['dpr'] );
		}
		if ( $offloaded_id !== 0 ) {
			return $this->get_offloaded_url( $offloaded_id, $image->getUrl(), $image->getSource() );
		}

		return $image->getUrl();
	}

	/**
	 * Get the active cache booster.
	 *
	 * @param string $url The URL.
	 * @param string $main_cache_buster The default value.
	 *
	 * @return string
	 */
	public static function get_active_cache_booster( $url, $main_cache_buster ) {
		return ( get_transient( Optml_Settings::INDIVIDUAL_CACHE_TOKENS_KEY ) ?: [] )[ crc32( wp_basename( $url ) ) ] ?? $main_cache_buster;
	}
	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @codeCoverageIgnore
	 * @access public
	 * @return void
	 * @since  1.0.0
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
	 * @return void
	 * @since  1.0.0
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Cheatin&#8217; huh?', 'optimole-wp' ), '1.0.0' );
	}

	/**
	 * Apply resize to image.
	 *
	 * @param Image $image Image object.
	 * @param mixed $resize Resize arguments.
	 */
	private function apply_resize( Image $image, $resize ) {
		if ( ! is_array( $resize ) || empty( $resize['type'] ) ) {
			return;
		}

		$image->resize( $resize['type'], $resize['gravity'] ?? Position::CENTER, $resize['enlarge'] ?? false );
	}

	/**
	 * Apply watermark to image.
	 *
	 * @param Image $image Image object.
	 */
	private function apply_watermark( Image $image ) {
		$settings = $this->settings->get_site_settings();

		if ( empty( $settings['watermark'] ) ) {
			return;
		}

		$watermark = $settings['watermark'];

		if ( ! isset( $watermark['id'], $watermark['opacity'], $watermark['position'] ) || $watermark['id'] <= 0 ) {
			return;
		}

		$image->watermark( $watermark['id'], $watermark['opacity'], $watermark['position'], $watermark['x_offset'] ?? 0, $watermark['y_offset'] ?? 0, $watermark['scale'] ?? 0 );
	}

	/**
	 * Get the DAM image URL.
	 *
	 * @param Image $image Image object.
	 *
	 * @return string
	 */
	private function get_dam_url( Image $image ) {
		// Remove DAM flag.
		$url = str_replace( Optml_Dam::URL_DAM_FLAG, '', $image->getSource() );

		foreach ( $image->getProperties() as $property ) {
			[ $name, $value ] = explode( ':', $property );

			// Check if the property exists in the URL, if so, replace it with the current property value. Otherwise, add it before the /q: param.
			$url = strpos( $url, '/' . $name . ':' ) !== false
				? preg_replace( '/\/' . $name . ':.*?\//', '/' . $name . ':' . $value . '/', $url )
				: str_replace( '/q:', '/' . $name . ':' . $value . '/q:', $url );
		}

		return $url;
	}


	/**
	 * Check if the URL is offloaded.
	 *
	 * @param string $source_url The source image URL.
	 *
	 * @return int
	 */
	private function is_offloaded_url( $source_url ) {
		$attachment_id = 0;

		if ( ! self::$offload_enabled ) {
			return 0;
		}
		if ( strpos( $source_url, Optml_Media_Offload::KEYS['not_processed_flag'] ) !== false ) {
			$attachment_id = (int) Optml_Media_Offload::get_attachment_id_from_url( $source_url );
		} else {
			$attachment_id = $this->attachment_url_to_post_id( $source_url );
		}

		if ( $attachment_id === 0 ) {
			return 0;
		}

		if ( ! $this->is_completed_offload( $attachment_id ) ) {
			return 0;
		}

		return (int) $attachment_id;
	}

	/**
	 * Get the offloaded URL for an image.
	 *
	 * @param int    $id The attachment ID.
	 * @param string $optimized_url The optimized image URL.
	 * @param string $source_url The source image URL.
	 *
	 * @return string
	 */
	private function get_offloaded_url( $id, $optimized_url, $source_url ) {
		$suffix   = wp_get_attachment_metadata( $id )['file'];

		return str_replace( $source_url, ltrim( $suffix, '/' ), $optimized_url );
	}
}
