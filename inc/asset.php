<?php

/**
 * The class maps settings and options for assets.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Asset extends Optml_Resource {

	/**
	 * Quality of the resulting image.
	 *
	 * @var Optml_Quality Quality;
	 */
	public $quality = null;

	/**
	 * Type of asset
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * Optml_Asset constructor.
	 *
	 * @param string $url Source image url.
	 * @param array  $args Transformation arguments.
	 *
	 * @throws \InvalidArgumentException In case that the url is not provided.
	 */
	public function __construct( $url = '', $args = array() ) {
		parent::__construct( $url );

		if ( strpos( $url, '.css' ) ) {
			$this->type = 'css';
		}

		if ( strpos( $url, '.js' ) ) {
			$this->type = 'js';
		}

		if ( isset( $args['quality'] ) ) {
			$this->quality->set( $args['quality'] );
		}
	}

	/**
	 * Set defaults for asset transformations.
	 */
	protected function set_defaults() {
		$this->quality = new Optml_Quality();
	}

	/**
	 * Return transformed url.
	 *
	 * @param array $params Either will be signed or not.
	 *
	 * @return string Transformed asset url.
	 */
	public function get_url( $params = [] ) {
		$path_parts = array();

		$path_parts[] = $this->quality->toString();

		$path = '/' . $this->source_url;

		if ( ! empty( $this->type ) ) {
			$path = '/f:' . $this->type . '/' . $this->source_url;
		}

		$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );

		$path = sprintf( '/%s%s', $this->get_domain_token() . '-' . $this->get_url_token(), $path );

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}

}
