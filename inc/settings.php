<?php

/**
 * Class Optml_Settings.
 */
class Optml_Settings {
	use Optml_Normalizer;
	const FILTER_EXT = 'extension';
	const FILTER_URL = 'page_url';
	const FILTER_FILENAME = 'filename';
	const FILTER_CLASS = 'class';
	const FILTER_TYPE_LAZYLOAD = 'lazyload';
	const FILTER_TYPE_OPTIMIZE = 'optimize';
	/**
	 * Default settings schema.
	 *
	 * @var array Settings schema.
	 */
	private $default_schema = array(
		'api_key'              => '',
		'service_data'         => '',
		'cache_buster'         => '',
		'cdn'                  => 'disabled',
		'max_height'           => 1500,
		'max_width'            => 2000,
		'admin_bar_item'       => 'enabled',
		'lazyload'             => 'disabled',
		'scale'                => 'disabled',
		'network_optimization' => 'disabled',
		'lazyload_placeholder' => 'disabled',
		'bg_replacer'          => 'enabled',
		'retina_images'        => 'disabled',
		'resize_smart'         => 'disabled',
		'filters'              => [],
		'watchers'             => '',
		'quality'              => 'auto',
		'wm_id'                => - 1,
		'wm_opacity'           => 1,
		'wm_position'          => Optml_Resize::GRAVITY_SOUTH_EAST,
		'wm_x'                 => 0,
		'wm_y'                 => 0,
		'wm_scale'             => 0,
		'image_replacer'       => 'enabled',
		'img_to_video'         => 'disabled',
		'css_minify'           => 'enabled',
		'js_minify'            => 'disabled',

	);
	/**
	 * Option key.
	 *
	 * @var string Option name.
	 */
	private $namespace;
	/**
	 * Holds all options from db.
	 *
	 * @var array All options.
	 */
	private $options;

	/**
	 * Optml_Settings constructor.
	 */
	public function __construct() {
		$this->namespace      = OPTML_NAMESPACE . '_settings';
		$this->default_schema = apply_filters( 'optml_default_settings', $this->default_schema );
		$this->options        = wp_parse_args( get_option( $this->namespace, $this->default_schema ), $this->default_schema );

		if ( defined( 'OPTIML_ENABLED_MU' ) && defined( 'OPTIML_MU_SITE_ID' ) && $this->to_boolean( constant( 'OPTIML_ENABLED_MU' ) ) && constant( 'OPTIML_MU_SITE_ID' ) ) {
			switch_to_blog( constant( 'OPTIML_MU_SITE_ID' ) );
			$this->options = wp_parse_args( get_option( $this->namespace, $this->default_schema ), $this->default_schema );
			restore_current_blog();
		}
	}

	/**
	 * Return filter definitions.
	 *
	 * @return mixed|null Filter values.
	 */
	public function get_watchers() {

		return $this->get( 'watchers' );

	}

	/**
	 * Return filter definitions.
	 *
	 * @return mixed|null Filter values.
	 */
	public function get_filters() {

		$filters = $this->get( 'filters' );
		if ( ! isset( $filters[ self::FILTER_TYPE_LAZYLOAD ] ) ) {
			$filters[ self::FILTER_TYPE_LAZYLOAD ] = [];
		}
		if ( ! isset( $filters[ self::FILTER_TYPE_OPTIMIZE ] ) ) {
			$filters[ self::FILTER_TYPE_OPTIMIZE ] = [];
		}
		foreach ( $filters as $filter_key => $filter_rules ) {
			if ( ! isset( $filter_rules[ self::FILTER_EXT ] ) ) {
				$filters[ $filter_key ][ self::FILTER_EXT ] = [];
			}
			if ( ! isset( $filter_rules[ self::FILTER_FILENAME ] ) ) {
				$filters[ $filter_key ][ self::FILTER_FILENAME ] = [];
			}
			if ( ! isset( $filter_rules[ self::FILTER_URL ] ) ) {
				$filters[ $filter_key ][ self::FILTER_URL ] = [];
			}
			if ( ! isset( $filter_rules[ self::FILTER_CLASS ] ) ) {
				$filters[ $filter_key ][ self::FILTER_CLASS ] = [];
			}
		}

		return $filters;
	}

	/**
	 * Process settings.
	 *
	 * @param array $new_settings List of settings.
	 *
	 * @return array
	 */
	public function parse_settings( $new_settings ) {
		$sanitized = array();
		foreach ( $new_settings as $key => $value ) {
			switch ( $key ) {
				case 'admin_bar_item':
				case 'lazyload':
				case 'scale':
				case 'image_replacer':
				case 'cdn':
				case 'network_optimization':
				case 'lazyload_placeholder':
				case 'retina_images':
				case 'resize_smart':
				case 'bg_replacer':
				case 'img_to_video':
				case 'css_minify':
				case 'js_minify':
					$sanitized_value = $this->to_map_values( $value, array( 'enabled', 'disabled' ), 'enabled' );
					break;
				case 'max_width':
				case 'max_height':
					$sanitized_value = $this->to_bound_integer( $value, 100, 5000 );
					break;
				case 'quality':
					$sanitized_value = $this->to_bound_integer( $value, 1, 100 );
					break;
				case 'wm_id':
					$sanitized_value = intval( $value );
					break;
				case 'cache_buster':
					$sanitized_value = is_string( $value ) ? $value : '';
					break;
				case 'filters':
					$current_filters = $this->get_filters();
					$sanitized_value = array_replace_recursive( $current_filters, $value );
					// Remove falsy vars.
					foreach ( $sanitized_value as $filter_type => $filter_values ) {
						foreach ( $filter_values as $filter_rule_type => $filter_rules_value ) {
							$sanitized_value[ $filter_type ][ $filter_rule_type ] = array_filter(
								$filter_rules_value,
								function ( $value ) {
									return ( $value !== 'false' && $value !== false );
								}
							);
						}
					}
					break;
				case 'watchers':
					$sanitized_value = $value;
					break;
				case 'wm_opacity':
				case 'wm_scale':
				case 'wm_x':
				case 'wm_y':
					$sanitized_value = floatval( $value );
					break;
				case 'wm_position':
					$sanitized_value = $this->to_map_values(
						$value,
						array(
							Optml_Resize::GRAVITY_NORTH,
							Optml_Resize::GRAVITY_NORTH_EAST,
							Optml_Resize::GRAVITY_NORTH_WEST,
							Optml_Resize::GRAVITY_CENTER,
							Optml_Resize::GRAVITY_EAST,
							Optml_Resize::GRAVITY_WEST,
							Optml_Resize::GRAVITY_SOUTH_EAST,
							Optml_Resize::GRAVITY_SOUTH,
							Optml_Resize::GRAVITY_SOUTH_WEST,
						),
						Optml_Resize::GRAVITY_SOUTH_EAST
					);
					break;
				default:
					$sanitized_value = '';
					break;

			}

			$sanitized[ $key ] = $sanitized_value;
			$this->update( $key, $sanitized_value );
		}

		return $sanitized;
	}

	/**
	 * Update settings.
	 *
	 * @param string $key Settings key.
	 * @param mixed  $value Settings value.
	 *
	 * @return bool Update result.
	 */
	public function update( $key, $value ) {
		if ( ! $this->is_allowed( $key ) ) {
			return false;
		}
		// If we try to update from a website which is not the main OPTML blog, bail.
		if ( defined( 'OPTIML_ENABLED_MU' ) && constant( 'OPTIML_ENABLED_MU' ) && defined( 'OPTIML_MU_SITE_ID' ) && constant( 'OPTIML_MU_SITE_ID' ) &&
			 intval( constant( 'OPTIML_MU_SITE_ID' ) ) !== get_current_blog_id()
		) {
			return false;
		}
		$opt         = $this->options;
		$opt[ $key ] = $value;
		$update      = update_option( $this->namespace, $opt, false );
		if ( $update ) {
			$this->options = $opt;
		}

		return $update;
	}

	/**
	 * Check if key is allowed.
	 *
	 * @param string $key Is key allowed or not.
	 *
	 * @return bool Is key allowed or not.
	 */
	private function is_allowed( $key ) {
		return isset( $this->default_schema[ $key ] );
	}

	/**
	 * Check if the user is connected to Optimole.
	 *
	 * @return bool Connection status.
	 */
	public function is_connected() {
		$service_data = $this->get( 'service_data' );
		if ( ! isset( $service_data['cdn_key'] ) ) {
			return false;
		}
		if ( empty( $service_data ['cdn_key'] ) || empty( $service_data['cdn_secret'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get setting value by key.
	 *
	 * @param string $key Key to search against.
	 *
	 * @return mixed|null Setting value.
	 */
	public function get( $key ) {
		if ( ! $this->is_allowed( $key ) ) {
			return null;
		}

		return isset( $this->options[ $key ] ) ? $this->options[ $key ] : '';
	}

	/**
	 * Return site settings.
	 *
	 * @return array Site settings.
	 */
	public function get_site_settings() {

		return array(
			'quality'              => $this->get_quality(),
			'admin_bar_item'       => $this->get( 'admin_bar_item' ),
			'lazyload'             => $this->get( 'lazyload' ),
			'network_optimization' => $this->get( 'network_optimization' ),
			'retina_images'        => $this->get( 'retina_images' ),
			'lazyload_placeholder' => $this->get( 'lazyload_placeholder' ),
			'bg_replacer'          => $this->get( 'bg_replacer' ),
			'resize_smart'         => $this->get( 'resize_smart' ),
			'image_replacer'       => $this->get( 'image_replacer' ),
			'cdn'                  => $this->get( 'cdn' ),
			'max_width'            => $this->get( 'max_width' ),
			'max_height'           => $this->get( 'max_height' ),
			'filters'              => $this->get_filters(),
			'watchers'             => $this->get_watchers(),
			'watermark'            => $this->get_watermark(),
			'img_to_video'         => $this->get( 'img_to_video' ),
			'scale'                => $this->get( 'scale' ),
			'css_minify'           => $this->get( 'css_minify' ),
			'js_minify'            => $this->get( 'js_minify' ),
		);
	}

	/**
	 * Return quality factor.
	 *
	 * @return string Quality factor.
	 */
	public function get_quality() {
		$quality = $this->get( 'quality' );
		// Legacy compat.
		if ( $quality === 'low' ) {
			return 'high_c';
		}
		if ( $quality === 'medium' ) {
			return 'medium_c';
		}

		if ( $quality === 'high' ) {
			return 'low_c';
		}

		return $quality;
	}

	/**
	 * Return an watermark array.
	 *
	 * @return array
	 */
	public function get_watermark() {
		return array(
			'id'       => $this->get( 'wm_id' ),
			'opacity'  => $this->get( 'wm_opacity' ),
			'position' => $this->get( 'wm_position' ),
			'x_offset' => $this->get( 'wm_x' ),
			'y_offset' => $this->get( 'wm_y' ),
			'scale'    => $this->get( 'wm_scale' ),
		);
	}

	/**
	 * Check if smart cropping is enabled.
	 *
	 * @return bool Is smart cropping enabled.
	 */
	public function is_smart_cropping() {
		return $this->get( 'resize_smart' ) === 'enabled';
	}

	/**
	 * Get numeric quality used by the service.
	 *
	 * @return int Numeric quality.
	 */
	public function get_numeric_quality() {
		$value = $this->get_quality();

		return (int) $this->to_accepted_quality( $value );
	}

	/**
	 * Check if replacer is enabled.
	 *
	 * @return bool Replacer enabled
	 */
	public function is_enabled() {
		$status       = $this->get( 'image_replacer' );
		$service_data = $this->get( 'service_data' );
		if ( empty( $service_data ) ) {
			return false;
		}
		if ( isset( $service_data['status'] ) && $service_data['status'] === 'inactive' ) {
			return false;
		}
		return $this->to_boolean( $status );
	}

	/**
	 * Check if lazyload is enabled.
	 *
	 * @return bool Lazyload enabled
	 */
	public function use_lazyload() {
		$status = $this->get( 'lazyload' );

		return $this->to_boolean( $status );
	}

	/**
	 * Check if replacer is enabled.
	 *
	 * @return bool Replacer enabled
	 */
	public function use_cdn() {
		$status = $this->get( 'cdn' );

		return $this->to_boolean( $status );
	}

	/**
	 * Return cdn url.
	 *
	 * @return string CDN url.
	 */
	public function get_cdn_url() {
		$service_data = $this->get( 'service_data' );
		if ( ! isset( $service_data['cdn_key'] ) ) {
			return '';
		}
		if ( defined( 'OPTML_CUSTOM_DOMAIN' ) && constant( 'OPTML_CUSTOM_DOMAIN' ) ) {
			return parse_url( strtolower( OPTML_CUSTOM_DOMAIN ), PHP_URL_HOST );
		}

		return sprintf(
			'%s.%s',
			strtolower( $service_data['cdn_key'] ),
			'i.optimole.com'
		);
	}

	/**
	 * Reset options to defaults.
	 *
	 * @return bool Reset action status.
	 */
	public function reset() {
		$reset_schema = $this->default_schema;
		$reset_schema['filters'] = $this->options['filters'];

		$update = update_option( $this->namespace, $reset_schema );
		if ( $update ) {
			$this->options = $reset_schema;
		}

		return $update;
	}

}
