<?php

/**
 * The class maps settings and options for assets.
 *
 * @package    \Optml\Inc
 * @author     Optimole <friends@optimole.com>
 */
class Optml_Asset extends Optml_Resource {

	/**
	 * Quality of the images urls inside the assets.
	 *
	 * @var Optml_Quality Quality;
	 */
	public $quality = null;

	/**
	 * Minify of the resulting asset.
	 *
	 * @var Optml_Minify Minify;
	 */
	public $minify = null;

	/**
	 * Type of asset
	 *
	 * @var string
	 */
	protected $type = '';

	/**
	 * CSS minify
	 *
	 * @var string
	 */
	protected $minify_css = 1;

	/**
	 * JS minify
	 *
	 * @var string
	 */
	protected $minify_js = 0;

	/**
	 * Optml_Asset constructor.
	 *
	 * @param string $url Source image url.
	 * @param array  $args Transformation arguments.
	 *
	 * @throws \InvalidArgumentException In case that the url is not provided.
	 */
	public function __construct( $url = '', $args = [], $cache_buster = '', $minify_css = 1, $minify_js = 0 ) {
		parent::__construct( $url, $cache_buster );

		$this->minify_css = $minify_css;
		$this->minify_js = $minify_js;

		if ( strpos( $url, '.css' ) ) {
			$this->type = 'css';
		}

		if ( strpos( $url, '.js' ) ) {
			$this->type = 'js';
		}

		$this->minify->set( $this->{'minify_' . $this->type} );

		if ( isset( $args['quality'] ) ) {
			$this->quality->set( $args['quality'] );
		}
	}

	/**
	 * Set defaults for asset transformations.
	 */
	protected function set_defaults() {
		$this->quality = new Optml_Quality();
		$this->minify = new Optml_Minify();
	}

	/**
	 * Return transformed url.
	 *
	 * @param array $params Either will be signed or not.
	 *
	 * @return string Transformed asset url.
	 */
	public function get_url( $params = [] ) {
		$path_parts = [];
		if ( ! empty( $this->type ) ) {
			$path_parts[] = 'f:' . $this->type;
		}

		$path_parts[] = $this->quality->toString();
		$path_parts[] = $this->minify->toString();

		$path = '/' . $this->source_url;

		$path = sprintf( '/%s%s', implode( '/', $path_parts ), $path );

		$path = sprintf( '/%s%s', $this->get_domain_token() . $this->get_cache_buster(), $path );

		return sprintf( '%s%s', Optml_Config::$service_url, $path );

	}

}
