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
		$this->width  = new Optml_Width( 'auto' );
		$this->height = new Optml_Height( 'auto' );
		$this->resize = new Optml_Resize();
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

		$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );

		if ( isset( $params['signed'] ) && $params['signed'] ) {
			$path = sprintf( '/%s%s', $this->get_signature( $path ), $path );
		}

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}

	/**
	 * Return the url signature.
	 *
	 * @param string $path The path from url.
	 *
	 * @return bool|string
	 */
	public function get_signature( $path = '' ) {

		$binary    = hash_hmac( 'sha256', Optml_Config::$secret . $path, Optml_Config::$key, true );
		$binary    = pack( 'A' . self::SIGNATURE_SIZE, $binary );
		$signature = rtrim( strtr( base64_encode( $binary ), '+/', '-_' ), '=' );

		return $signature;
	}

}
