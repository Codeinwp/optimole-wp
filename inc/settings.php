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
		'admin_bar_item' => 'enabled',
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
		$this->options   = get_option( $this->namespace, $this->default_schema );
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

		return $this->options[ $key ];
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
	 * @param string $key   Settings key.
	 * @param mixed  $value Settings value.
	 *
	 * @return bool Update result.
	 */
	public function update( $key, $value ) {
		if ( ! $this->is_allowed( $key ) ) {
			return false;
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
