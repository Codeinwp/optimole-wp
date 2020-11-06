<?php

/**
 * The class maps settings and options from imageproxy.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Image extends Optml_Resource {

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
	 * Optml_Image constructor.
	 *
	 * @param string $url Source image url.
	 * @param array  $args Transformation arguments.
	 *
	 * @throws \InvalidArgumentException In case that the url is not provided.
	 */
	public function __construct( $url = '', $args = [], $cache_buster = '' ) {
		parent::__construct( $url, $cache_buster );

		$this->width->set( $args['width'] );
		$this->height->set( $args['height'] );

		if ( isset( $args['quality'] ) ) {
			$this->quality->set( $args['quality'] );
		}

		if ( isset( $args['resize'] ) ) {
			$this->resize->set( $args['resize'] );
		}
	}

	/**
	 * Set defaults for image transformations.
	 */
	protected function set_defaults() {
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
		$path_parts = [];

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

		$path = sprintf( '/%s%s', $this->get_domain_token() . $this->get_cache_buster(), $path );

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}
}
