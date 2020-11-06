<?php

/**
 * Class Optml_Resize
 */
class Optml_Resize extends Optml_Property_Type {


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
	 * Resize type.
	 *
	 * @var string Resize type string.
	 */
	private $resize_type = '';
	/**
	 * Should enlarge.
	 *
	 * @var string Enlarge flag
	 */
	private $enlarge = false;
	/**
	 * Global default enlarge.
	 *
	 * @var string Enlarge flag
	 */
	public static $default_enlarge = false;

	/**
	 * Gravity type.
	 *
	 * @var string Gravity type string.
	 */
	private $gravity = '';

	/**
	 * Optml_Resize constructor.
	 */
	public function __construct( $value = [] ) {
		$this->set( $value );
	}

	/**
	 * Set property value.
	 *
	 * @param mixed $value Value to set.
	 */
	public function set( $value ) {
		$this->resize_type = isset( $value['type'] ) ? $value['type'] : '';
		$this->enlarge     = isset( $value['enlarge'] ) ? $value['enlarge'] : self::$default_enlarge;
		$this->gravity     = isset( $value['gravity'] ) ? is_array( $value['gravity'] ) ? self::GRAVITY_FOCUS_POINT : $value['gravity'] : '';
		if ( $this->gravity === self::GRAVITY_FOCUS_POINT ) {
			$this->focus_point_x = $value['gravity'][0];
			$this->focus_point_y = $value['gravity'][1];
		}

	}

	/**
	 * Return property value.
	 *
	 * @return mixed
	 */
	public function get() {
		if ( empty( $this->resize_type ) ) {
			return [];
		}
		if ( empty( $this->gravity ) ) {
			return [ 'type' => $this->resize_type ];
		}
		if ( $this->gravity === self::GRAVITY_FOCUS_POINT ) {
			return [ 'type' => $this->gravity, 'gravity' => [ $this->focus_point_x, $this->focus_point_y ] ];
		}

		return [
			'type'    => $this->resize_type,
			'gravity' => $this->gravity,
			'enlarge' => $this->enlarge,
		];
	}

	/**
	 * Return ImageProxy URL formatted string property.
	 *
	 * @return string
	 */
	public function toString() {

		$resize = sprintf( 'rt:%s', $this->resize_type );

		$resize .= sprintf( '/g:%s', $this->gravity );

		if ( $this->enlarge ) {
			$resize .= '/el:1';
		}

		if ( $this->gravity === self::GRAVITY_FOCUS_POINT ) {
			$resize .= sprintf( ':%s:%s', $this->focus_point_x, $this->focus_point_y );
		}

		return $resize;
	}
}
