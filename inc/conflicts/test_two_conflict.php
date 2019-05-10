<?php
/**
 * Class Optml_Test_Two_Conflict
 *
 * An example of a conflict.
 */
class Optml_Test_Two_Conflict extends Optml_abstract_conflict {

	/**
	 * Optml_Test_Two_Conflict constructor.
	 */
	public function __construct() {
		$this->priority = 2;
		$this->severity = self::SEVERITY_HIGH;
		parent::__construct();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.0.6
	 * @access  public
	 */
	public function define_message() {
		$this->message = __( 'This is the second conflict', 'optimole-wp' );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @return bool
	 */
	public function is_conflict_valid() {
		// Do additional checking here.
		return true;
	}
}
