<?php

/**
 * Class Optml_Config holds configuration for the service.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Config {

	/**
	 * A list of allowed extensions.
	 *
	 * @var array
	 */
	public static $extensions = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
		'svg'          => 'image/svg+xml',
	);

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
	 * @throws \InvalidArgumentException In case that key or secret is not provided.
	 */
	public static function init( $service_settings = array() ) {

		if ( empty( $service_settings['key'] ) && ! defined( 'OPTML_KEY' ) ) {
			throw new \InvalidArgumentException( 'Optimole SDK requires service api key.' ); // @codeCoverageIgnore
		}
		if ( empty( $service_settings['secret'] ) && ! defined( 'OPTML_SECRET' ) ) {
			throw new \InvalidArgumentException( 'Optimole SDK requires service secret key.' ); // @codeCoverageIgnore
		}

		if ( defined( 'OPTML_KEY' ) && constant( 'OPTML_KEY' ) ) {
			self::$key = constant( 'OPTML_KEY' );
		}

		if ( defined( 'OPTML_SECRET' ) && constant( 'OPTML_SECRET' ) ) {
			self::$secret = constant( 'OPTML_SECRET' );
		}

		if ( ! empty( $service_settings['key'] ) ) {
			self::$key = trim( strtolower( $service_settings['key'] ) );
		}

		if ( ! empty( $service_settings['secret'] ) ) {
			self::$secret = trim( $service_settings['secret'] );
		}

		self::$service_url = sprintf( 'https://%s.i.optimole.com', self::$key );
		if ( isset( $service_settings['domain'] ) && ! empty( $service_settings['domain'] ) ) {
			self::$service_url = $service_settings['domain'];
		} elseif ( defined( 'OPTML_CUSTOM_DOMAIN' ) && constant( 'OPTML_CUSTOM_DOMAIN' ) ) {
			self::$service_url = constant( 'OPTML_CUSTOM_DOMAIN' );
		}
	}
}
