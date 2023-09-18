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
	 * @param array $params Image transforms parameters.
	 *
	 * @return string Transformed image url.
	 */
	public function get_url( $params = [] ) {
		$path_params = $this->get_path_params( $params );

		if ( $this->is_dam_url() ) {
			return $this->get_dam_url( $path_params );
		}

		$path = $path_params . '/' . $this->source_url;

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}

	/**
	 * Check if this contains the DAM flag.
	 *
	 * @return bool
	 */
	public function is_dam_url() {
		return strpos( $this->source_url, Optml_Dam::URL_DAM_FLAG ) !== false;
	}

	/**
	 * Get the DAM image URL.
	 *
	 * @param string $path_params Path parameters.
	 *
	 * @return string
	 */
	private function get_dam_url( $path_params ) {
		$url = $this->source_url;

		// Remove DAM flag.
		$url = str_replace( Optml_Dam::URL_DAM_FLAG, '', $url );

		// Split the path params.
		$path_params_parts = explode( '/', $path_params );

		foreach ( $path_params_parts as $param ) {
			if ( empty( $param ) ) {
				continue;
			}

			// Split into parts.
			$param_parts = explode( ':', $param );

			// Check if the param is width or height and if it's auto.
			// Rely on the initial URL as it generates the correct image size.
			if ( in_array( $param_parts[0], ['w', 'h'], true ) ) {
				if ( $param_parts[1] === 'auto' ) {
					continue;
				}
			}

			// if the param exists we do a non-greedy replace for /$param:$value/ with the updated value.
			if ( strpos( $url, '/' . $param_parts[0] . ':' ) !== false ) {
				$url = preg_replace( '/\/' . $param_parts[0] . ':.*?\//', '/' . $param . '/', $url );

				continue;
			}

			// Otherwise, we add it before the /q: param.
			$url = str_replace( '/q:', '/' . $param . '/q:', $url );
		}

		return $url;
	}

	/**
	 * Get transform params as string.
	 *
	 * @param array $params Image transforms parameters.
	 *
	 * @return string Transform parameters string to use in the URL.
	 */
	private function get_path_params( $params = [] ) {
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

		$path = '';

		if ( isset( $params['format'] ) && ! empty( $params['format'] ) ) {
			$path = '/f:' . $params['format'] . $path;
		}

		if ( isset( $params['strip_metadata'] ) && '0' === $params['strip_metadata'] ) {
			$path = '/sm:' . $params['strip_metadata'] . $path;
		}

		if ( isset( $params['ignore_avif'] ) && $params['ignore_avif'] === true ) {
			$path = '/ig:avif' . $path;
		}

		if ( apply_filters( 'optml_keep_copyright', false ) === true ) {
			$path = '/keep_copyright:true' . $path;
		}

		$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );

		$cache_buster = $this->get_cache_buster();
		if ( $cache_buster !== false ) {
			$path = sprintf( '/%s%s', 'cb:' . $cache_buster, $path );
		}

		return $path;
	}
}
