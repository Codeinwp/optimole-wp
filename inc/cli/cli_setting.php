<?php
/**
 * CLI commands responsible for the Optimole settings.
 */

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * Class Optml_Cli_Setting
 */
class Optml_Cli_Setting extends WP_CLI_Command {
	/**
	 * Connect to service
	 * <apikey>
	 * : The api key to use.
	 */
	public function connect( $args ) {
		if ( empty( $args ) || ! isset( $args[0] ) || $args[0] === '' ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( api key )' );
		}

		if ( sizeof( $args ) > 1 ) {
			return \WP_CLI::error( 'To many arguments passed' );
		}

		$api_key = $args[0];

		$request = new Optml_Api();
		$data    = $request->get_user_data( $api_key );
		if ( $data === false || is_wp_error( $data ) ) {
			$extra = '';
			if ( is_wp_error( $data ) ) {
				/**
				 * Error from api.
				 *
				 * @var WP_Error $data Error object.
				 */
				$extra = sprintf( __( '. ERROR details: %s', 'optimole-wp' ), $data->get_error_message() );
			}

			return \WP_CLI::error( __( 'Can not connect to Optimole service', 'optimole-wp' ) . $extra );
		}
		$settings = new Optml_Settings();
		$settings->update( 'service_data', $data );
		$settings->update( 'api_key', $api_key );

		\WP_CLI::success( sprintf( 'Connected API key %s to Optimole Service', $args[0] ) );
	}

	/**
	 * Disconnect from service.
	 */
	public function disconnect() {
		$settings = new Optml_Settings();
		$settings->reset();
		\WP_CLI::success( 'Disconnected from Optimole Service' );
	}

	/**
	 * Update settings.
	 *
	 * <setting_name>
	 * : The setting name to update.
	 *
	 * <setting_value>
	 * : The setting value to update.
	 */
	public function update( $args ) {
		if ( empty( $args ) || ! isset( Optml_Settings::$whitelisted_settings[ $args[0] ] ) ) {
			\WP_CLI::error( sprintf( 'Setting must be one of: %s', implode( ',', array_keys( Optml_Settings::$whitelisted_settings ) ) ) );

			return false;
		}

		if ( Optml_Settings::$whitelisted_settings[ $args[0] ] === 'bool' && ( empty( $args ) || ! isset( $args[1] ) || $args[1] === '' || ! in_array(
			$args[1],
			[
				'on',
				'off',
			],
			true
		) ) ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( on/off )' );
		}

		if ( Optml_Settings::$whitelisted_settings[ $args[0] ] === 'int' && ( empty( $args ) || ! isset( $args[1] ) || $args[1] === '' || (int) $args[1] > 100 || (int) $args[1] < 0 ) ) {
			return \WP_CLI::error( 'Invalid argument, must be between 0 and 100.' );
		}

		$value = ( Optml_Settings::$whitelisted_settings[ $args[0] ] === 'bool' ) ? ( $args[1] === 'on' ? 'enabled' : 'disabled' ) : (int) $args[1];

		$new_value = $this->update_setting( [ $args[0] => $value ] );
		\WP_CLI::success( sprintf( 'Setting %s updated to: %s', $args[0], $new_value[ $args[0] ] ) );
	}

	/**
	 * Check settings.
	 *
	 * <setting_name>
	 * : The setting name to check.
	 */
	public function get( $args ) {
		if ( empty( $args ) || ! isset( Optml_Settings::$whitelisted_settings[ $args[0] ] ) ) {
			\WP_CLI::error( sprintf( 'Setting must be one of: %s', implode( ',', array_keys( Optml_Settings::$whitelisted_settings ) ) ) );

			return false;
		}

		$value = ( new Optml_Settings() )->get( $args[0] );

		\WP_CLI::success( sprintf( 'Setting %s is set to: %s', $args[0], $value ) );
	}


	/**
	 * Utility method to update setting
	 *
	 * @param mixed $new_setting The setting to parse.
	 *
	 * @return array
	 */
	private function update_setting( $new_setting ) {
		if ( empty( $new_setting ) ) {
			\WP_CLI::error( __( 'No setting to update', 'optimole-wp' ) );
		}
		$settings = new Optml_Settings();

		return $settings->parse_settings( $new_setting );
	}

}
