<?php
abstract  class Optml_Abstract_Conflict {

	const SEVERITY_LOW = 'low';
	const SEVERITY_MEDIUM = 'medium';
	const SEVERITY_HIGH = 'high';

	protected $type = 'base_conflict';

	protected $severity = self::SEVERITY_LOW;

	protected $message = '';

	protected $priority = 1;

	public function __construct() {
		$this->define_message();
	}

	abstract public function define_message();

	abstract public function is_conflict_valid();

	public function get_conflict() {
		return array(
			'type' => $this->type,
			'priority' => $this->priority,
			'severity' => $this->severity,
			'message' => $this->message,
		);
	}
}
