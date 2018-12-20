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
	const SIGNATURE_SIZE = 10;

	/**
	 * Resize the image while keeping aspect ratio to fit given size.
	 */
	const RESIZE_FILL = 'fill';
	/**
	 * Resize the image while keeping aspect ratio
	 * to fill given size and cropping projecting parts.
	 */
	const RESIZE_FIT = 'fit';
	/**
	 * Crops the image to a given size.
	 */
	const RESIZE_CROP = 'crop';


	/**
	 * Top edge.
	 */
	const GRAVITY_NORTH = 'no';
	/**
	 * Bottom Edge.
	 */
	const GRAVITY_SOUTH = 'so';
	/**
	 * Right Edge.
	 */
	const GRAVITY_EAST = 'ea';
	/**
	 * Left edge.
	 */
	const GRAVITY_WEST = 'we';

	/**
	 * Top right corner.
	 */
	const GRAVITY_NORTH_WEST = 'noea';
	/**
	 * Top left corner.
	 */
	const GRAVITY_NORTH_EAST = 'nowe';
	/**
	 * Bottom right corner.
	 */
	const GRAVITY_SOUTH_EAST = 'soea';
	/**
	 * Bottom left corner.
	 */
	const GRAVITY_SOUTH_WEST = 'sowe';


	/**
	 * Center
	 */
	const GRAVITY_CENTER = 'ce';
	/**
	 * Detects the most "interesting" section of the image and
	 * considers it as the center of the resulting image
	 */
	const GRAVITY_SMART = 'sm';
	/**
	 * Detects the most "interesting" section of the image and
	 * considers it as the center of the resulting image
	 */
	const GRAVITY_FOCUS_POINT = 'fp';

	/**
	 * Floating point numbers between 0 and 1 that define the coordinates of the resulting image for X axis.
	 *
	 * @var int Focus point X.
	 */
	private $focus_point_x = 0;
	/**
	 * Floating point numbers between 0 and 1 that define the coordinates of the resulting image for X axis.
	 *
	 * @var int Focus point Y.
	 */
	private $focus_point_y = 0;

	/**
	 * Quality of the resulting image.
	 *
	 * @var int Quality;
	 */
	private $quality = '';
	/**
	 * Width of the resulting image.
	 *
	 * @var mixed Width.
	 */
	private $width = '';
	/**
	 * Height of the resulting image.
	 *
	 * @var mixed Height.
	 */
	private $height = '';
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
		if ( $this->is_valid_numeric( $args['width'] ) ) {
			$this->width = $this->to_positive_integer( $args['width'] );
		}

		if ( $this->is_valid_numeric( $args['height'] ) ) {
			$this->height = $this->to_positive_integer( $args['height'] );
		}

		if ( $this->is_valid_numeric( $args['quality'] ) ) {
			$this->quality = $this->to_bound_integer( $args['quality'], 0, 100 );
		}
		if ( $args['quality'] === 'eco' ) {
			$this->quality = 'eco';
		}

		$this->source_url = $url;

	}

	/**
	 * Set defaults for image transformations.
	 */
	private function set_defaults() {
		$this->width         = 'auto';
		$this->height        = 'auto';
		$this->quality       = 'auto';
		$this->focus_point_x = 0;
		$this->focus_point_y = 0;
	}

	/**
	 * Return transformed url.
	 *
	 * @param bool $signed Either will be signed or not.
	 *
	 * @return string Transformed image url.
	 */
	public function get_url( $signed = false ) {
		$path_parts = array();
		if ( $this->width > 0 ) {
			$path_parts[] = sprintf( 'w:%s', $this->width );
		}
		if ( $this->height > 0 ) {
			$path_parts[] = sprintf( 'h:%s', $this->height );
		}
		if ( $this->quality > 0 || $this->quality === 'eco' ) {
			$path_parts[] = sprintf( 'q:%s', $this->quality );
		}
		$path = '/' . $this->source_url;

		if ( ! empty( $path_parts ) ) {
			$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );
		}
		if ( $signed ) {
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

		$full_signature = hash_hmac( 'sha256', Optml_Config::$key . $path, Optml_Config::$secret, true );

		return substr( $full_signature, 0, self::SIGNATURE_SIZE );
	}

}
