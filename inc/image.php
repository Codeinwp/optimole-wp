<?php

/**
 * The class maps settings and options from imageproxy.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Image {

	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Signature size.
	 */
	const SIGNATURE_SIZE = 8;
	/**
	 * Watermark for the image.
	 *
	 * @var Optml_Watermark Watermark.
	 */
	public static $watermark = null;
	/**
	 * Quality of the resulting image.
	 *
	 * @var Optml_Quality Quality;
	 */
	public $quality = null;
	/**
	 * Width of the resulting image.
	 *
	 * @var Optml_Width Width.
	 */
	private $width = null;
	/**
	 * Height of the resulting image.
	 *
	 * @var Optml_Height Height.
	 */
	private $height = null;
	/**
	 * Resize type for the image.
	 *
	 * @var Optml_Resize Resize details.
	 */
	private $resize = null;
	/**
	 * Source image url.
	 *
	 * @var string Source image.
	 */
	private $source_url = '';



	/**
	 * Optml_Image constructor.
	 *
	 * @param string $url Source image url.
	 * @param array  $args Transformation arguments.
	 *
	 * @throws \InvalidArgumentException In case that the url is not provided.
	 */
	public function __construct( $url = '', $args = array() ) {
		if ( empty( $url ) ) {
			throw new \InvalidArgumentException( 'Optimole image builder requires the source url to optimize.' ); // @codeCoverageIgnore
		}
		$this->set_defaults();

		$this->width->set( $args['width'] );
		$this->height->set( $args['height'] );

		if ( isset( $args['quality'] ) ) {
			$this->quality->set( $args['quality'] );
		}

		if ( isset( $args['resize'] ) ) {
			$this->resize->set( $args['resize'] );
		}
		$this->source_url = $url;

	}

	/**
	 * Set defaults for image transformations.
	 */
	private function set_defaults() {
		$this->width   = new Optml_Width( 'auto' );
		$this->height  = new Optml_Height( 'auto' );
		$this->resize  = new Optml_Resize();
		$this->quality = new Optml_Quality();
	}

	/**
	 * Return transformed url.
	 *
	 * @param array $params Either will be signed or not.
	 *
	 * @return string Transformed image url.
	 */
	public function get_url( $params = [] ) {
		$path_parts = array();

		$path_parts[] = $this->width->toString();
		$path_parts[] = $this->height->toString();
		$path_parts[] = $this->quality->toString();

		if ( isset( $this->resize->get()['type'] ) ) {
			$path_parts[] = $this->resize->toString();
		}
		if ( isset( $params['apply_watermark'] ) && $params['apply_watermark'] && is_array( self::$watermark->get() ) && isset( self::$watermark->get()['id'] ) && self::$watermark->get()['id'] > 0 ) {
			$path_parts[] = self::$watermark->toString();
		}

		$path = '/' . $this->source_url;

		if ( isset( $params['format'] ) && ! empty( $params['format'] ) ) {
			$path = '/f:' . $params['format'] . '/' . $this->source_url;
		}

		$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );

		$path = sprintf( '/%s%s', $this->get_domain_token() . '-' . $this->get_url_token(), $path );

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}

	/**
	 * Method to generate tokens and cache them for further requests.
	 *
	 * @param string $source The source string to tokenize.
	 * @param int    $size The size of bites for the generated token.
	 *
	 * @return string
	 */
	private function get_cache_token( $source, $size = 8 ) {
		$key         = crc32( $source );
		$cache_token = wp_cache_get( $key, 'optml_cache_tokens' );
		if ( $cache_token === false ) {
			$cache_token = $this->get_signature( $source, $size );
			wp_cache_add( $key, $cache_token, 'optml_cache_tokens', DAY_IN_SECONDS );
		}

		return $cache_token;
	}

	/**
	 * Get the token for the domain.
	 *
	 * @return string
	 */
	public function get_domain_token() {
		$parts  = parse_url( $this->source_url );
		$domain = isset( $parts['host'] ) ? str_replace( 'www.', '', $parts['host'] ) : '';

		return $this->get_cache_token( $domain, 4 );
	}

	/**
	 * Get the token for the url.
	 *
	 * @return string
	 */
	public function get_url_token() {
		$url = strtok( $this->source_url, '?' );

		return $this->get_cache_token( $url, 6 );
	}

	/**
	 * Return the url signature.
	 *
	 * @param string $string The path from url.
	 *
	 * @return bool|string
	 */
	public function get_signature( $string = '', $size = self::SIGNATURE_SIZE ) {

		$binary    = hash_hmac( 'sha256', Optml_Config::$secret . $string, Optml_Config::$key, true );
		$binary    = pack( 'A' . $size, $binary );
		$signature = rtrim( strtr( base64_encode( $binary ), '+/', '-_' ), '=' );

		return $signature;
	}

}
