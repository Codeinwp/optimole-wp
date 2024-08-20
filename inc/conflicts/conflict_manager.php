<?php
/**
 * The Conflict Manager class, orchestrates conflicts.
 *
 * @package    \Optimole\Inc\Conflicts
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Conflict_Manager
 *
 * @since   2.0.6
 */
class Optml_Conflict_Manager {
	/**
	 * List of conflicts to watch.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var array $watched_conflicts
	 */
	protected $watched_conflicts = [];
	/**
	 * List of conflicts dismissed by user.
	 *
	 * @since   2.0.6
	 * @access  protected
	 * @var array $dismissed_conflicts
	 */
	protected $dismissed_conflicts = [];



	/**
	 * Optml_Conflict_Manager constructor.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @param array $register_conflicts A list of conflicts to be registered.
	 */
	public function __construct( $register_conflicts = [] ) {
		$this->dismissed_conflicts = get_option( 'optml_dismissed_conflicts', [] );
		if ( ! empty( $register_conflicts ) ) {
			foreach ( $register_conflicts as $conflict_to_watch ) {
				$this->watch( $conflict_to_watch );
			}
		}
	}

	/**
	 * Add a conflict to the watched conflicts.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @param string $conflict A conflict class name.
	 */
	public function watch( $conflict ) {
		if ( is_subclass_of( new $conflict(), 'Optml_Abstract_Conflict' ) ) {
			array_push( $this->watched_conflicts, new $conflict() );
		}
	}

	/**
	 * Dismiss conflict.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @param string $id The conflict ID.
	 *
	 * @return bool
	 */
	public function dismiss_conflict( $id ) {
		$this->dismissed_conflicts[ $id ] = 'true';
		return update_option( 'optml_dismissed_conflicts', $this->dismissed_conflicts );
	}

	/**
	 * Get the conflict list.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @return array
	 */
	public function get_conflict_list() {
		$conflict_list = [];
		if ( empty( $this->watched_conflicts ) ) {
			return $conflict_list;
		}

		/**
		 * An instance of Optml_Abstract_Conflict
		 *
		 * @var Optml_Abstract_Conflict $conflict
		 */
		foreach ( $this->watched_conflicts as $conflict ) {
			if ( $conflict->is_active( $this->dismissed_conflicts ) ) {
				array_push( $conflict_list, $conflict->get_conflict() );
			}
		}

		// Sort conflicts by severity and priority.
		usort(
			$conflict_list,
			function ( $item1, $item2 ) {
				if ( ! isset( $item1['severity'] ) ) {
					return -1;
				}
				if ( ! isset( $item2['severity'] ) ) {
					return -1;
				}
				$severity_map = [
					'high' => 0,
					'medium' => 1,
					'low' => 1,
				];

				if ( $severity_map[ $item1['severity'] ] === $severity_map[ $item2['severity'] ] ) {
					if ( ! isset( $item1['priority'] ) ) {
						return 0;
					}
					if ( ! isset( $item2['priority'] ) ) {
						return 0;
					}
					if ( $item1['priority'] === $item2['priority'] ) {
						return 0;
					}
					return $item1['priority'] < $item2['priority'] ? -1 : +1;
				}
				return $severity_map[ $item1['severity'] ] < $severity_map[ $item2['severity'] ] ? -1 : 1;
			}
		);

		return $conflict_list;
	}

	/**
	 * Get the total count for active conflicts.
	 *
	 * @since   2.0.6
	 * @access  public
	 * @return int
	 */
	public function get_conflict_count() {
		if ( empty( $this->watched_conflicts ) ) {
			return 0;
		}

		$count = 0;
		/**
		 * An instance of Optml_Abstract_Conflict
		 *
		 * @var Optml_Abstract_Conflict $conflict
		 */
		foreach ( $this->watched_conflicts as $conflict ) {
			if ( $conflict->is_active( $this->dismissed_conflicts ) ) {
				++$count;
			}
		}
		return $count;
	}
}
