<?php

use OptimoleWP\Integrations\JetpackStatus;

/**
 * Jetpack conflict tests.
 *
 * @package Optimole-WP
 */

/**
 * Class Test_Jetpack_Conflicts.
 */
class Test_Jetpack_Conflicts extends WP_UnitTestCase {
	/**
	 * Active plugins before each test.
	 *
	 * @var array
	 */
	private $active_plugins;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		$this->active_plugins = get_option( 'active_plugins', [] );
		update_option(
			'active_plugins',
			array_merge( $this->active_plugins, [ 'jetpack/jetpack.php' ] )
		);
	}

	/**
	 * Restore active plugins after each test.
	 */
	public function tear_down() {
		Jetpack::$photon_active = false;
		update_option( 'active_plugins', $this->active_plugins );

		parent::tear_down();
	}

	/**
	 * Jetpack without Photon should not be a generic conflict.
	 */
	public function test_jetpack_without_photon_is_not_a_generic_conflict() {
		$conflicts = new Optml_Conflicting_Plugins();

		$this->assertFalse( JetpackStatus::is_photon_active() );
		$this->assertNotContains( 'jetpack/jetpack.php', $conflicts->get_conflicting_plugins( true ) );
	}

	/**
	 * Jetpack with Photon should remain a generic conflict.
	 */
	public function test_jetpack_with_photon_is_a_generic_conflict() {
		Jetpack::$photon_active = true;
		$conflicts              = new Optml_Conflicting_Plugins();

		$this->assertTrue( JetpackStatus::is_photon_active() );
		$this->assertContains( 'jetpack/jetpack.php', $conflicts->get_conflicting_plugins( true ) );
	}
}

if ( ! class_exists( 'Jetpack', false ) ) {
	/**
	 * Minimal Jetpack test double.
	 */
	class Jetpack {
		/**
		 * Whether Photon is active.
		 *
		 * @var bool
		 */
		public static $photon_active = false;

		/**
		 * Check whether a module is active.
		 *
		 * @param string $module Module slug.
		 * @return bool
		 */
		public static function is_module_active( $module ) {
			return 'photon' === $module && self::$photon_active;
		}
	}
}
