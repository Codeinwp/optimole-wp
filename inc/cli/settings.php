<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/**
 * CLI commands responsible for the Optimole settings.
 */
class Optml_Cli_Settings extends WP_CLI_Command {
	/**
	 * Connect to service
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
	 * Disconnect service
	 */
	public function disconnect() {
		$settings = new Optml_Settings();
		$settings->reset();
		\WP_CLI::success( 'Disconnected from Optimole Service' );
	}

	/**
	 * Replacement toggle
	 */
	public function replacement( $args ) {
		if ( empty( $args ) || ! isset( $args[0] ) || $args[0] === '' || ! in_array( $args[0], array( 'on', 'off' ) ) ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( on/off )' );
		}

		if ( sizeof( $args ) > 1 ) {
			return \WP_CLI::error( 'To many arguments passed' );
		}

		$value = ( $args[0] === 'on' ) ? 'enabled' : 'disabled';

		$new_value = $this->update_setting( array( 'image_replacer' => $value ) );

		\WP_CLI::success( sprintf( 'Optimole replacement is: %s', $new_value['image_replacer'] ) );
	}

	/**
	 * Lazy-load toggle
	 */
	public function lazy( $args ) {
		if ( empty( $args ) || ! isset( $args[0] ) || $args[0] === '' || ! in_array( $args[0], array( 'on', 'off' ) ) ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( on/off )' );
		}

		if ( sizeof( $args ) > 1 ) {
			return \WP_CLI::error( 'To many arguments passed' );
		}

		$value = ( $args[0] === 'on' ) ? 'enabled' : 'disabled';

		$new_value = $this->update_setting( array( 'lazyload' => $value ) );

		\WP_CLI::success( sprintf( 'Optimole lazyload is: %s', $new_value['lazyload'] ) );
	}

	/**
	 * Placeholder toggle
	 */
	public function placeholder( $args ) {
		if ( empty( $args ) || ! isset( $args[0] ) || $args[0] === '' || ! in_array( $args[0], array( 'on', 'off' ) ) ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( on/off )' );
		}

		if ( sizeof( $args ) > 1 ) {
			return \WP_CLI::error( 'To many arguments passed' );
		}

		$value = ( $args[0] === 'on' ) ? 'enabled' : 'disabled';

		$new_value = $this->update_setting( array( 'lazyload_placeholder' => $value ) );

		\WP_CLI::success( sprintf( 'Optimole generic placeholder is: %s', $new_value['lazyload_placeholder'] ) );
	}

	/**
	 * Quality
	 */
	public function quality( $args ) {
		if ( empty( $args ) || ! isset( $args[0] ) || $args[0] === '' ) {
			return \WP_CLI::error( 'No argument passed. Required one argument ( auto or 1-100 )' );
		}

		if ( sizeof( $args ) > 1 ) {
			return \WP_CLI::error( 'To many arguments passed' );
		}

		if ( $args[0] !== 'auto' && ( absint( $args[0] ) < 1 || absint( $args[0] ) > 100 ) ) {
			return \WP_CLI::error( 'Accepted values are: ( auto or 1-100 )' );
		}

		$value = $args[0];

		$new_value = $this->update_setting( array( 'quality' => $value ) );

		\WP_CLI::success( sprintf( 'Optimole quality is: %s', $new_value['quality'] ) );
	}

	/**
	 * Utility method to update setting
	 *
	 * @param mixed $new_setting The setting to parse.
	 *
	 * @return array
	 */
	protected function update_setting( $new_setting ) {
		if ( empty( $new_setting ) ) {
			\WP_CLI::error( __( 'No setting to update', 'optimole-wp' ) );
		}
		$settings  = new Optml_Settings();
		return $settings->parse_settings( $new_setting );
	}
}
