<?php

/**
 * The class maps settings and options from imageproxy.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
abstract class Optml_Resource {

	use Optml_Validator;
	use Optml_Normalizer;

	/**
	 * Signature size.
	 */
	const SIGNATURE_SIZE = 8;

	/**
	 * Source image url.
	 *
	 * @var string Source image.
	 */
	protected $source_url = '';

	/**
	 * Cache Buster passed from  Optimole Service.
	 *
	 * @var string Active Cache Buster
	 */
	protected $cache_buster = '';



	/**
	 * Optml_Resource constructor.
	 *
	 * @param string $url Source image url.
	 * @param string $cache_buster The cache buster.
	 *
	 * @throws \InvalidArgumentException In case that the url is not provided.
	 */
	public function __construct( $url = '', $cache_buster = '' ) {
		if ( empty( $url ) ) {
			throw new \InvalidArgumentException( 'Optimole resource builder requires the source url to optimize.' ); // @codeCoverageIgnore
		}
		$this->set_defaults();
		$this->source_url = apply_filters( 'optml_processed_url', $url );

		$this->cache_buster = $cache_buster;
	}

	/**
	 * Set defaults for resource transformations.
	 */
	abstract protected function set_defaults();

	/**
	 * Return transformed url.
	 *
	 * @param array $params Either will be signed or not.
	 *
	 * @return string Transformed image url.
	 */
	abstract public function get_url( $params = [] );

	/**
	 * Method to generate tokens and cache them for further requests.
	 *
	 * @param string $source The source string to tokenize.
	 * @param int    $size The size of bites for the generated token.
	 *
	 * @return string
	 */
	private function get_token_from_cache( $source, $size = 8 ) {
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
		return $this->get_token_from_cache( $domain, 5 );
	}

	/**
	 * Get token for the url.
	 *
	 * @return string
	 */
	private function get_url_token() {
		$url = strtok( $this->source_url, '?' );
		return $this->get_token_from_cache( $url, 6 );
	}

	/**
	 * Return a the cache buster, defaults to the url token used previously.
	 *
	 * @return string
	 */
	public function get_cache_buster() {
		if ( $this->cache_buster !== '' ) {
			return '.' . $this->cache_buster;
		}
		return '-' . $this->get_url_token();
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
