<?php
class Optm_Test_conflict extends Optml_Abstract_Conflict {

	public function __construct() {
		$this->priority = 2;
		$this->severity = self::SEVERITY_HIGH;
		parent::__construct();
	}

	public function define_message() {
		$this->message = __( 'This is a conflict', 'optimole-wp' );
	}

	public function is_conflict_valid() {
		return true;
	}
}
