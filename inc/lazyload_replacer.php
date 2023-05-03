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

	const IFRAME_PLACEHOLDER_CLASS = '
			iframe[data-opt-src]:not([data-opt-lazy-loaded]) {
				background-color: #ffffff;
				background-image: url("data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20style%3D%22-webkit-transform-origin%3A50%25%2050%25%3B-webkit-animation%3Aspin%201.5s%20linear%20infinite%3B-webkit-backface-visibility%3Ahidden%3Banimation%3Aspin%201.5s%20linear%20infinite%22%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20stroke-linejoin%3D%22round%22%20stroke-miterlimit%3D%221.414%22%3E%3Cdefs%3E%3Cstyle%3E%3C%21%5BCDATA%5B%40-webkit-keyframes%20spin%7Bfrom%7B-webkit-transform%3Arotate%280deg%29%7Dto%7B-webkit-transform%3Arotate%28-359deg%29%7D%7D%40keyframes%20spin%7Bfrom%7Btransform%3Arotate%280deg%29%7Dto%7Btransform%3Arotate%28-359deg%29%7D%7D%5D%5D%3E%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22outer%22%3E%3Cpath%20d%3D%22M20%200a3.994%203.994%200%20110%207.988A3.994%203.994%200%200120%200z%22%2F%3E%3Cpath%20d%3D%22M5.858%205.858a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23d2d2d2%22%2F%3E%3Cpath%20d%3D%22M20%2032.012A3.994%203.994%200%201120%2040a3.994%203.994%200%20010-7.988z%22%20fill%3D%22%23828282%22%2F%3E%3Cpath%20d%3D%22M28.494%2028.494a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23656565%22%2F%3E%3Cpath%20d%3D%22M3.994%2016.006a3.994%203.994%200%20110%207.988%203.994%203.994%200%20010-7.988z%22%20fill%3D%22%23bbb%22%2F%3E%3Cpath%20d%3D%22M5.858%2028.494a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23a4a4a4%22%2F%3E%3Cpath%20d%3D%22M36.006%2016.006a3.994%203.994%200%20110%207.988%203.994%203.994%200%20010-7.988z%22%20fill%3D%22%234a4a4a%22%2F%3E%3Cpath%20d%3D%22M28.494%205.858a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23323232%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E");
				background-repeat: no-repeat;
				background-position: 50% 50%;
			}
			video[data-opt-src]:not([data-opt-lazy-loaded]) {
				background-color: #ffffff;
				background-image: url("data:image/svg+xml,%3Csvg%20width%3D%2240%22%20height%3D%2240%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20style%3D%22-webkit-transform-origin%3A50%25%2050%25%3B-webkit-animation%3Aspin%201.5s%20linear%20infinite%3B-webkit-backface-visibility%3Ahidden%3Banimation%3Aspin%201.5s%20linear%20infinite%22%20fill-rule%3D%22evenodd%22%20clip-rule%3D%22evenodd%22%20stroke-linejoin%3D%22round%22%20stroke-miterlimit%3D%221.414%22%3E%3Cdefs%3E%3Cstyle%3E%3C%21%5BCDATA%5B%40-webkit-keyframes%20spin%7Bfrom%7B-webkit-transform%3Arotate%280deg%29%7Dto%7B-webkit-transform%3Arotate%28-359deg%29%7D%7D%40keyframes%20spin%7Bfrom%7Btransform%3Arotate%280deg%29%7Dto%7Btransform%3Arotate%28-359deg%29%7D%7D%5D%5D%3E%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22outer%22%3E%3Cpath%20d%3D%22M20%200a3.994%203.994%200%20110%207.988A3.994%203.994%200%200120%200z%22%2F%3E%3Cpath%20d%3D%22M5.858%205.858a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23d2d2d2%22%2F%3E%3Cpath%20d%3D%22M20%2032.012A3.994%203.994%200%201120%2040a3.994%203.994%200%20010-7.988z%22%20fill%3D%22%23828282%22%2F%3E%3Cpath%20d%3D%22M28.494%2028.494a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23656565%22%2F%3E%3Cpath%20d%3D%22M3.994%2016.006a3.994%203.994%200%20110%207.988%203.994%203.994%200%20010-7.988z%22%20fill%3D%22%23bbb%22%2F%3E%3Cpath%20d%3D%22M5.858%2028.494a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23a4a4a4%22%2F%3E%3Cpath%20d%3D%22M36.006%2016.006a3.994%203.994%200%20110%207.988%203.994%203.994%200%20010-7.988z%22%20fill%3D%22%234a4a4a%22%2F%3E%3Cpath%20d%3D%22M28.494%205.858a3.994%203.994%200%20115.648%205.648%203.994%203.994%200%2001-5.648-5.648z%22%20fill%3D%22%23323232%22%2F%3E%3C%2Fg%3E%3C%2Fsvg%3E");
				background-repeat: no-repeat;
				background-position: 50% 50%;
			}';
	const IFRAME_PLACEHOLDER_STYLE = '<style type="text/css">' . self::IFRAME_PLACEHOLDER_CLASS . '</style>';

	const IFRAME_TEMP_COMMENT = '/** optmliframelazyloadplaceholder */';

	const SVG_PLACEHOLDER = 'data:image/svg+xml,%3Csvg%20viewBox%3D%220%200%20#width#%20#height#%22%20width%3D%22#width#%22%20height%3D%22#height#%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3C%2Fsvg%3E';
	/**
	 * If frame lazyload is present on page.
	 *
	 * @var bool Whether or not at least one iframe has been lazyloaded.
	 */
	private static $found_iframe = false;
	/**
	 * Cached object instance.
	 *
	 * @var Optml_Tag_Replacer
	 */
	protected static $instance = null;
	/**
	 * Holds the number of images to skip from lazyload.
	 *
	 * @var integer The number image tags to skip.
	 */
	private static $skip_lazyload_images = null;
	/**
	 * Holds classes for listening to lazyload on background.
	 *
	 * @var array Lazyload background classes.
	 */
	private static $lazyload_background_classes = null;
	/**
	 * Selectors used for background lazyload.
	 *
	 * @var array Lazyload background CSS selectors.
	 */
	private static $background_lazyload_selectors = null;
	/**
	 * Holds flags which remove noscript tag bundle causing issues on render, i.e slider plugins.
	 *
	 * @var array Noscript flags.
	 */
	private static $ignore_no_script_flags = null;
	/**
	 * Holds possible iframe lazyload flags where we should ignore our lazyload.
	 *
	 * @var array
	 */
	protected static $iframe_lazyload_flags = null;
	/**
	 * Holds classes responsabile for watching lazyload behaviour.
	 *
	 * @var array Lazyload classes.
	 */
	private static $lazyload_watcher_classes = null;
	/**
	 * Should we use the generic placeholder?
	 *
	 * @var bool Lazyload placeholder flag.
	 */
	private static $is_lazyload_placeholder = false;
	/**
	 * Holds flags which remove noscript tag bundle causing issues on render, i.e slider plugins.
	 *
	 * @var array Noscript flags.
	 */

	/**
	 * Class instance method.
	 *
	 * @codeCoverageIgnore
	 * @static
	 * @return Optml_Tag_Replacer
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
	 * Return lazyload selectors for background images.
	 *
	 * @return array Lazyload selectors.
	 */
	public static function get_background_lazyload_selectors() {

		if ( ! empty( self::$background_lazyload_selectors ) && is_array( self::$background_lazyload_selectors ) ) {
			return self::$background_lazyload_selectors;
		}
		if ( self::instance()->settings->get( 'bg_replacer' ) === 'disabled' ) {
			self::$background_lazyload_selectors = [];

			return self::$background_lazyload_selectors;
		}
		$default_watchers = [
			'.elementor-section[data-settings*="background_background"]',
			'.elementor-section > .elementor-background-overlay',
			'[class*="wp-block-cover"][style*="background-image"]',
		];

		$saved_watchers = self::instance()->settings->get_watchers();

		$saved_watchers = str_replace( [ "\n", "\r" ], ',', $saved_watchers );
		$saved_watchers = explode( ',', $saved_watchers );
		$all_watchers   = array_merge( $default_watchers, $saved_watchers );
		$all_watchers   = apply_filters( 'optml_lazyload_bg_selectors', $all_watchers );
		$all_watchers   = array_filter(
			$all_watchers,
			function ( $value ) {
				return ! empty( $value ) && strlen( $value ) >= 2;
			}
		);

		self::$background_lazyload_selectors = $all_watchers;

		return self::$background_lazyload_selectors;
	}

	/**
	 * Returns background classes for lazyload.
	 *
	 * @return array
	 */
	public static function get_lazyload_bg_classes() {

		if ( ! empty( self::$lazyload_background_classes ) && is_array( self::$lazyload_background_classes ) ) {
			return self::$lazyload_background_classes;
		}

		self::$lazyload_background_classes = apply_filters( 'optml_lazyload_bg_classes', [] );

		return self::$lazyload_background_classes;
	}
	/**
	 * Returns the number of images to skip from lazyload.
	 *
	 * @return integer
	 */
	public static function get_skip_lazyload_limit() {

		if ( self::$skip_lazyload_images !== null ) {
			return self::$skip_lazyload_images;
		}
		self::$skip_lazyload_images = apply_filters( 'optml_lazyload_images_skip', self::instance()->settings->get( 'skip_lazyload_images' ) );
		return self::$skip_lazyload_images;
	}
	/**
	 * Returns classes for lazyload additional watch.
	 *
	 * @return array
	 */
	public static function get_watcher_lz_classes() {

		if ( ! empty( self::$lazyload_watcher_classes ) && is_array( self::$lazyload_watcher_classes ) ) {
			return self::$lazyload_watcher_classes;
		}

		self::$lazyload_watcher_classes = apply_filters( 'optml_watcher_lz_classes', [] );

		return self::$lazyload_watcher_classes;
	}

	/**
	 * The initialize method.
	 */
	public function init() {
		parent::init();

		if ( ! $this->settings->use_lazyload() ) {
			return;
		}
		$filters = $this->settings->get_filters();
		if ( ! Optml_Filters::should_do_page( $filters[ Optml_Settings::FILTER_TYPE_LAZYLOAD ][ Optml_Settings::FILTER_URL ], $filters[ Optml_Settings::FILTER_TYPE_LAZYLOAD ][ Optml_Settings::FILTER_URL_MATCH ] ) ) {
			return;
		}

		add_filter(
			'max_srcset_image_width',
			function () {
				return 1;
			}
		);
		self::$is_lazyload_placeholder = self::$instance->settings->get( 'lazyload_placeholder' ) === 'enabled';

		add_filter( 'optml_tag_replace', [ $this, 'lazyload_tag_replace' ], 2, 6 );

		add_filter( 'optml_video_replace', [$this, 'lazyload_video_replace'], 2, 1 );

	}
	/**
	 * Check if there are lazyloaded iframes.
	 *
	 * @return bool Whether an iframe was lazyloaded on the page or not.
	 */
	public static function found_iframe() {
		return self::$found_iframe;
	}

	/**
	 * Replaces the tags with lazyload tags.
	 *
	 * @param string $new_tag The new tag.
	 * @param string $original_url The original URL.
	 * @param string $new_url The optimized URL.
	 * @param array  $optml_args Options passed for URL optimization.
	 * @param bool   $is_slashed If the url needs slashes.
	 * @param string $full_tag Full tag, wrapper included.
	 *
	 * @return string
	 */
	public function lazyload_tag_replace( $new_tag, $original_url, $new_url, $optml_args, $is_slashed = false, $full_tag = '' ) {

		if ( ! $this->can_lazyload_for( $original_url, $full_tag ) ) {
			return Optml_Tag_Replacer::instance()->regular_tag_replace( $new_tag, $original_url, $new_url, $optml_args, $is_slashed );
		}
		$should_ignore_rescale = ! $this->is_valid_mimetype_from_url( $original_url, [ 'gif' => true, 'svg' => true ] );

		if ( ! self::$is_lazyload_placeholder && ! $should_ignore_rescale ) {
			$optml_args['quality'] = 'eco';
			$optml_args['resize']  = [];
			$low_url               = apply_filters( 'optml_content_url', $original_url, $optml_args );
			$low_url               = $is_slashed ? addcslashes( $low_url, '/' ) : $low_url;
		} else {
			$low_url = $this->get_svg_for(
				isset( $optml_args['width'] ) ? $optml_args['width'] : '100%',
				isset( $optml_args['height'] ) ? $optml_args['height'] : '100%',
				( $should_ignore_rescale ? null : $original_url )
			);
		}

		$opt_format = '';

		if ( $this->should_add_data_tag( $full_tag ) ) {
			$opt_format = ' data-opt-src="%s" ';
			if ( $should_ignore_rescale ) {
				if ( strpos( $new_tag, 'class=' ) === false ) {
					$opt_format .= ' class="optimole-lazy-only" ';
				} else {
					$new_tag = str_replace(
						( $is_slashed ? 'class=\"' : 'class="' ),
						( $is_slashed ? 'class=\"optimole-lazy-only ' : 'class="optimole-lazy-only ' ),
						$new_tag
					);
				}
			}
			$opt_format = $is_slashed ? addslashes( $opt_format ) : $opt_format;
		}
		$new_url = $is_slashed ? addcslashes( $new_url, '/' ) : $new_url;

		$opt_src = sprintf( $opt_format, $new_url );

		$no_script_tag = str_replace(
			$original_url,
			$new_url,
			$new_tag
		);
		$new_tag       = preg_replace(
			[
				'/((?:\s|\'|"){1,}src(?>=|"|\'|\s|\\\\)*)' . preg_quote( $original_url, '/' ) . '/m',
				'/<img/im',
			],
			[
				"$1$low_url",
				'<img' . $opt_src,
			],
			$new_tag,
			1
		);
		$new_tag = str_replace( 'srcset=', 'old-srcset=', $new_tag );
		if ( strpos( $new_tag, 'loading=' ) === false && self::instance()->settings->get( 'native_lazyload' ) === 'enabled' ) {
			$new_tag = preg_replace( '/<img/im', $is_slashed ? '<img loading=\"lazy\"' : '<img loading="lazy"', $new_tag );
		}
		if ( ! $this->should_add_noscript( $new_tag ) ) {
			return $new_tag;
		}

		return $new_tag . '<noscript>' . $no_script_tag . '</noscript>';
	}
	/**
	 * Replaces video embeds with lazyload embeds.
	 *
	 * @param string $content Html page content.
	 *
	 * @return string
	 */
	public function lazyload_video_replace( $content ) {
		$video_tags = [];
		$iframes = [];
		$videos = [];
		preg_match_all( '#(?:<noscript\s*>\s*)?<iframe(.*?)></iframe>(?:\s*</noscript\s*>)?#is', $content, $iframes );
		preg_match_all( '#(?:<noscript\s*>\s*)?<video(.*?)>.*?</video>(?:\s*</noscript\s*>)?#is', $content, $videos );
		$video_tags = array_merge( $iframes[0], $videos[0] );

		$search = [];
		$replace = [];

		foreach ( $video_tags as $video_tag ) {

			if ( ! $this->should_lazyload_iframe( $video_tag ) ) {
				continue;
			}
			if ( preg_match( "/ data-opt-src=['\"]/is", $video_tag ) ) {
				continue;
			}
			$should_add_noscript = true;
			$original_tag = $video_tag;
			// replace the src and add the data-opt-src attribute
			if ( strpos( $video_tag, 'iframe' ) !== false ) {
				$video_tag = preg_replace( '/iframe(.*?)src=/is', 'iframe$1 src="about:blank" data-opt-src=', $video_tag );
			} elseif ( strpos( $video_tag, 'video' ) !== false ) {
				if ( strpos( $video_tag, 'source' ) !== false ) {
					if ( strpos( $video_tag, 'preload' ) === false ) {
						$video_tag = preg_replace( '/video(.*?)>/is', 'video$1 preload="metadata">', $video_tag, 1 );
						$should_add_noscript = false;
					} else {
						continue;
					}
				} else {
					$video_tag = preg_replace( '/video(.*?)src=/is', 'video$1 data-opt-src=', $video_tag );
				}
			}
			if ( $this->should_add_noscript( $video_tag ) && $should_add_noscript ) {
				$video_tag .= '<noscript>' . $original_tag . '</noscript>';
			}
			array_push( $search, $original_tag );
			array_push( $replace, $video_tag );
			self::$found_iframe = true;
		}
		$search = array_unique( $search );
		$replace = array_unique( $replace );
		$content = str_replace( $search, $replace, $content );
		return $content;
	}

	/**
	 * Check if the lazyload is allowed for this url.
	 *
	 * @param string $url Url.
	 * @param string $tag Html tag.
	 *
	 * @return bool We can lazyload?
	 */
	public function can_lazyload_for( $url, $tag = '' ) {
		foreach ( self::possible_lazyload_flags() as $banned_string ) {
			if ( strpos( $tag, $banned_string ) !== false ) {
				return false;
			}
		}
		if ( false === Optml_Filters::should_do_image( $url, self::$filters[ Optml_Settings::FILTER_TYPE_LAZYLOAD ][ Optml_Settings::FILTER_FILENAME ] ) ) {
			return false;
		}
		$url = strtok( $url, '?' );

		$type = wp_check_filetype(
			basename( $url ),
			Optml_Config::$image_extensions
		);

		if ( ! isset( $type['ext'] ) || empty( $type['ext'] ) ) {
			return false;
		}

		if ( false === Optml_Filters::should_do_extension( self::$filters[ Optml_Settings::FILTER_TYPE_LAZYLOAD ][ Optml_Settings::FILTER_EXT ], $type['ext'] ) ) {
			return false;
		}

		if ( defined( 'OPTML_DISABLE_PNG_LAZYLOAD' ) && OPTML_DISABLE_PNG_LAZYLOAD ) {
			return $type['ext'] !== 'png';
		}
		if ( Optml_Tag_Replacer::$lazyload_skipped_images < self::get_skip_lazyload_limit() ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if we should add the data-opt-tag.
	 *
	 * @param string $tag Html tag.
	 *
	 * @return bool Should add?
	 */
	public function should_add_data_tag( $tag ) {
		foreach ( self::possible_data_ignore_flags() as $banned_string ) {
			if ( strpos( $tag, $banned_string ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get SVG markup with specific width/height.
	 *
	 * @param int         $width Markup Width.
	 * @param int         $height Markup Height.
	 * @param string|null $url Original URL.
	 *
	 * @return string SVG code.
	 */
	public function get_svg_for( $width, $height, $url = null ) {

		if ( $url !== null && ! is_numeric( $width ) ) {
			$url   = strtok( $url, '?' );
			$key   = crc32( $url );
			$sizes = wp_cache_get( $key, 'optml_sources' );
			if ( $sizes === false ) {
				$filepath = substr( $url, strpos( $url, $this->upload_resource['content_folder'] ) + $this->upload_resource['content_folder_length'] );
				$filepath = WP_CONTENT_DIR . $filepath;
				if ( is_file( $filepath ) ) {
					$sizes = getimagesize( $filepath );
					wp_cache_add( $key, [ $sizes[0], $sizes[1] ], 'optml_sources', DAY_IN_SECONDS );
				}
			}
			list( $width, $height ) = $sizes;
		}

		$width  = ! is_numeric( $width ) ? '100%' : $width;
		$height = ! is_numeric( $height ) ? '100%' : $height;

		return
			str_replace(
				[ '#width#', '#height#' ],
				[
					$width,
					$height,
				],
				self::SVG_PLACEHOLDER
			);
	}

	/**
	 * Check if we should add the noscript tag.
	 *
	 * @param string $tag Html tag.
	 *
	 * @return bool Should add?
	 */
	public function should_add_noscript( $tag ) {
		if ( $this->settings->get( 'no_script' ) === 'disabled' ) {
			return false;
		}
		foreach ( self::get_ignore_noscript_flags() as $banned_string ) {
			if ( strpos( $tag, $banned_string ) !== false ) {
				return false;
			}
		}

		return true;
	}
	/**
	 * Check if we should lazyload iframe.
	 *
	 * @param string $tag Html tag.
	 *
	 * @return bool Should add?
	 */
	public function should_lazyload_iframe( $tag ) {

		foreach ( self::get_iframe_lazyload_flags() as $banned_string ) {
			if ( strpos( $tag, $banned_string ) !== false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns flags for ignoring noscript tag additional watch.
	 *
	 * @return array
	 */
	public static function get_ignore_noscript_flags() {

		if ( ! empty( self::$ignore_no_script_flags ) && is_array( self::$ignore_no_script_flags ) ) {
			return self::$ignore_no_script_flags;
		}

		self::$ignore_no_script_flags = apply_filters( 'optml_ignore_noscript_on', [] );

		return self::$ignore_no_script_flags;
	}
	/**
	 * Returns possible lazyload flags for iframes.
	 *
	 * @return array
	 */
	public static function get_iframe_lazyload_flags() {

		if ( ! empty( self::$iframe_lazyload_flags ) && is_array( self::$iframe_lazyload_flags ) ) {
			return self::$iframe_lazyload_flags;
		}

		self::$iframe_lazyload_flags = self::possible_lazyload_flags();
		self::$iframe_lazyload_flags = array_merge( self::$iframe_lazyload_flags, apply_filters( 'optml_iframe_lazyload_flags', [ 'gform_ajax_frame', '<noscript', 'recaptcha', '-src' ] ) );

		return self::$iframe_lazyload_flags;
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

}
