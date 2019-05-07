<?php
class Optml_Test_Conflict extends Optml_abstract_conflict {

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
