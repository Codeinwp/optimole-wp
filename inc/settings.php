<?php

/**
 * Class Optml_Settings.
 */
class Optml_Settings {

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
		$this->namespace = OPTML_NAMESPACE . '_settings';
		$this->options   = wp_parse_args( get_option( $this->namespace, $this->default_schema ), $this->default_schema );
		if ( defined( 'OPTIML_ENABLED_MU' ) && OPTIML_ENABLED_MU && defined( 'OPTIML_MU_SITE_ID' ) && ! empty( OPTIML_MU_SITE_ID ) ) {
			switch_to_blog( OPTIML_MU_SITE_ID );
			$this->options = wp_parse_args( get_option( $this->namespace, $this->default_schema ), $this->default_schema );
			restore_current_blog();
		}
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
	 * Return site settings.
	 *
	 * @return array Site settings.
	 */
	public function get_site_settings() {

		return array(
			'quality'        => $this->get( 'quality' ),
			'admin_bar_item' => $this->get( 'admin_bar_item' ),
			'lazyload'       => $this->get( 'lazyload' ),
			'image_replacer' => $this->get( 'image_replacer' ),
			'max_width'      => $this->get( 'max_width' ),
			'max_height'     => $this->get( 'max_height' ),
		);
	}

	/**
	 * Check if replacer is enabled.
	 *
	 * @return bool Replacer enabled
	 */
	public function is_enabled() {
		$status = $this->get( 'image_replacer' );
		if ( $status === 'disabled' ) {
			return false;
		}
		if ( empty( $status ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if lazyload is enabled.
	 *
	 * @return bool Lazyload enabled
	 */
	public function use_lazyload() {
		$status = $this->get( 'lazyload' );
		if ( $status === 'disabled' ) {
			return false;
		}
		if ( empty( $status ) ) {
			return false;
		}

		return true;
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
		if ( defined( 'OPTIML_ENABLED_MU' ) && OPTIML_ENABLED_MU && defined( 'OPTIML_MU_SITE_ID' ) && ! empty( OPTIML_MU_SITE_ID ) ) {
			if ( intval( OPTIML_MU_SITE_ID ) !== get_current_blog_id() ) {
				return false;
			}
		}
		$options         = $this->options;
		$options[ $key ] = $value;
		$update          = update_option( $this->namespace, $options );
		if ( $update ) {
			$this->options = $options;
		}

		return $update;
	}

}
