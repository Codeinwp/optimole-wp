<?php

/**
 * Class Optml_Settings.
 */
class Optml_Settings {
	use Optml_Normalizer;

	/**
	 * Default settings schema.
	 *
	 * @var array Settings schema.
	 */
	private $default_schema = array(
		'api_key'        => '',
		'service_data'   => '',
		'max_height'     => 1500,
		'max_width'      => 2000,
		'admin_bar_item' => 'enabled',
		'lazyload'       => 'disabled',
		'quality'        => 'auto',
		'wm_id'          => - 1,
		'wm_opacity'     => 1,
		'wm_position'    => Optml_Resize::GRAVITY_SOUTH_EAST,
		'wm_x'           => 0,
		'wm_y'           => 0,
		'wm_scale'       => 0,
		'image_replacer' => 'enabled',
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
				case 'image_replacer':
					$sanitized_value = $this->to_map_values( $value, array( 'enabled', 'disabled' ), 'enabled' );
					break;
				case 'max_width':
				case 'max_height':
					$sanitized_value = $this->to_bound_integer( $value, 100, 5000 );
					break;
				case 'quality':
					$sanitized_value = $this->to_map_values(
						$value,
						array(
							'low_c',
							'medium_c',
							'high_c',
							'auto',
						),
						'auto'
					);
					break;
				case 'wm_id':
					$sanitized_value = intval( $value );
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
	 * @param  string $key Is key allowed or not.
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
			'quality'        => $this->get_quality(),
			'admin_bar_item' => $this->get( 'admin_bar_item' ),
			'lazyload'       => $this->get( 'lazyload' ),
			'image_replacer' => $this->get( 'image_replacer' ),
			'max_width'      => $this->get( 'max_width' ),
			'max_height'     => $this->get( 'max_height' ),
			'watermark'      => $this->get_watermark(),
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
		$status = $this->get( 'image_replacer' );

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
	 * Return cdn url.
	 *
	 * @return string CDN url.
	 */
	public function get_cdn_url() {
		$service_data = $this->get( 'service_data' );
		if ( ! isset( $service_data['cdn_key'] ) ) {
			return '';
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
		$update = update_option( $this->namespace, $this->default_schema );
		if ( $update ) {
			$this->options = $this->default_schema;
		}

		return $update;
	}

}
