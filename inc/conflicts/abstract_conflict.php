<?php
/**
 * The Abstract class inherited by all conflicts.
 *
 * @package    \Optimole\Inc\Conflicts
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Abstract_Conflict
 *
 * @since   2.0.6
 */
abstract class Optml_Abstract_Conflict {
	/**
	 * Constant for low severity.
	 *
	 * @since   2.0.6
	 * @const string SEVERITY_LOW
	 */
	const SEVERITY_LOW = 'low';
	/**
	 * Constant for medium severity.
	 *
	 * @since   2.0.6
	 * @const string SEVERITY_MEDIUM
	 */
	const SEVERITY_MEDIUM = 'medium';
	/**
	 * Constant for high severity.
	 *
	 * @since   2.0.6
	 * @const string SEVERITY_HIGH
	 */
	const SEVERITY_HIGH = 'high';
	/**
	 * The type of the conflict if required.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var string $type
	 */
	protected $type = 'base_conflict';
	/**
	 * Level of conflict severity.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var string $severity
	 */
	protected $severity = self::SEVERITY_LOW;
	/**
	 * Message of the conflict.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var string
	 */
	protected $message = '';
	/**
	 * Priority of conflict for same level of severity conflicts.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var int
	 */
	protected $priority = 1;

	/**
	 * Optml_Abstract_Conflict constructor.
	 */
	public function __construct() {
		$this->define_message();
	}

	/**
	 * Set the message property
	 *
	 * @since   2.0.6
	 * @access  public
	 */
	abstract public function define_message();

	/**
	 * Checks if conflict is active.
	 *
	 * @param array $dismissed_conflicts A list of dismissed conflicts. Passed by the manager.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	public function is_active( $dismissed_conflicts = [] ) {
		$conflict_id = $this->get_id();
		if ( isset( $dismissed_conflicts[ $conflict_id ] ) && $dismissed_conflicts[ $conflict_id ] === 'true' ) {
			return false;
		}

		return $this->is_conflict_valid();
	}

	/**
	 * Get the id for the conflict.
	 *
	 * @param int $length Optional. A length for the generated ID.
	 *
	 * @return bool|string
	 * @since   2.0.6
	 * @access  public
	 */
	public function get_id( $length = 8 ) {
		$hash = sha1( strtolower( get_called_class() ) . $this->type . $this->severity . $this->priority );

		return substr( $hash, 0, $length );
	}

	/**
	 * Determine if conflict is applicable.
	 *
	 * @return bool
	 * @since   2.0.6
	 * @access  public
	 */
	abstract public function is_conflict_valid();

	/**
	 * Get the conflict information.
	 *
	 * @return array
	 * @since   2.0.6
	 * @access  public
	 */
	public function get_conflict() {
		return [
			'id'       => $this->get_id(),
			'type'     => $this->type,
			'priority' => $this->priority,
			'severity' => $this->severity,
			'message'  => $this->message,
		];
	}
}
