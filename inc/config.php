<?php

class Optml_Config {

	/**
	 * Service api key.
	 *
	 * @var string Service key.
	 */
	public static $key = '';
	/**
	 * Service secret key used for signing the requests.
	 *
	 * @var string Secret key.
	 */
	public static $secret = '';
	/**
	 * Service url.
	 *
	 * @var string Service url.
	 */
	public static $service_url = '';

	/**
	 * Service settings.
	 *
	 * @param array $service_settings Service settings.
	 *
	 * @throws InvalidArgumentException in case that key or secret is not provided.
	 */
	public static function init( $service_settings = array() ) {

		if ( empty( $service_settings['key'] ) && ! defined( 'OPTML_KEY' ) ) {
			throw new \InvalidArgumentException( "Optimole SDK requires service api key." );
		}
		if ( empty( $service_settings['secret'] ) && ! defined( 'OPTML_SECRET' ) ) {
			throw new \InvalidArgumentException( "Optimole SDK requires service secret key." );
		}

		if ( ! empty ( $service_settings['key'] ) ) {
			self::$key = trim( $service_settings['key'] );
		} else {
			self::$key = OPTML_KEY;
		}
		if ( ! empty ( $service_settings['secret'] ) ) {
			self::$secret = trim( $service_settings['secret'] );
		} else {
			self::$secret = OPTML_SECRET;
		}

		if ( isset( $service_settings['domain'] ) && ! empty( $service_settings['domain'] ) ) {
			self::$service_url = $service_settings['domain'];
		} else if ( defined( 'OPTML_CUSTOM_DOMAIN' ) && ! empty( OPTML_CUSTOM_DOMAIN ) ) {
			self::$service_url = OPTML_CUSTOM_DOMAIN;
		} else {
			self::$service_url = sprintf( '%s.i.optimole.com', self::$key );
		}



	}
}