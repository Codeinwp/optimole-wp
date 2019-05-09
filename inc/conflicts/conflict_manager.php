<?php
class Optml_Conflict_Manager {

	protected $watched_conflicts = array();

	public function __construct( $register_conflicts = array() ) {

		if ( ! empty( $register_conflicts ) ) {
			foreach ( $register_conflicts as $conflict_to_watch ) {
				$this->watch( $conflict_to_watch );
			}
		}

	}

	public function watch( $conflict ) {
		if ( is_subclass_of( new $conflict,"Optml_Abstract_Conflict" ) ) {
			array_push( $this->watched_conflicts, new $conflict );
		}
	}

	public function get_conflict_list() {
		$conflict_list = array();
		if ( empty( $this->watched_conflicts ) ) {
			return $conflict_list;
		}

		/**
		 * @var Optml_Abstract_Conflict $conflict
		 */
		foreach ( $this->watched_conflicts as $conflict ) {
			if ( $conflict->is_conflict_valid() ) {
				array_push( $conflict_list, $conflict->get_conflict() );
			}
		}

		usort(
			$conflict_list,
			function ( $item1, $item2 ) {
				if ( ! isset( $item1['severity'] ) ) {
					return -1;
				}
				if ( ! isset( $item2['severity'] ) ) {
					return -1;
				}
				$severity_map = array(
					'high' => 0,
					'medium' => 1,
					'low' => 1,
				);

				if ( $severity_map[$item1['severity']] === $severity_map[$item2['severity']] ) {
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
				return $severity_map[$item1['severity']] < $severity_map[$item2['severity']] ? -1 : 1;
			}
		);

		return $conflict_list;
	}

	public function get_conflict_count() {
		if ( empty( $this->watched_conflicts ) || sizeof( $this->watched_conflicts ) === 0 ) {
			return 0;
		}

		$count = 0;
		/**
		 * @var Optml_Abstract_Conflict $conflict
		 */
		foreach ( $this->watched_conflicts as $conflict ) {
			if ( $conflict->is_conflict_valid() ) {
				$count++;
			}
		}
		return $count;
	}


}
