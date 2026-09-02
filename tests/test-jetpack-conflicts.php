<?php

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
	 * Jetpack Photon compatibility.
	 *
	 * @var Optml_jetpack_photon_compatibility
	 */
	private $compatibility;

	/**
	 * Set up the test.
	 */
	public function set_up() {
		parent::set_up();

		$this->active_plugins = get_option( 'active_plugins', [] );
		$this->compatibility  = new Optml_jetpack_photon_compatibility();
		update_option(
			'active_plugins',
			array_merge( $this->active_plugins, [ 'jetpack/jetpack.php' ] )
		);
	}

	/**
	 * Restore active plugins after each test.
	 */
	public function tear_down() {
		remove_filter( 'optml_conflicting_defined_plugins', [ $this->compatibility, 'add_conflicting_plugin' ] );
		Jetpack::$photon_active = false;
		update_option( 'active_plugins', $this->active_plugins );

		parent::tear_down();
	}

	/**
	 * Jetpack without Photon should not be a generic conflict.
	 */
	public function test_jetpack_without_photon_is_not_a_generic_conflict() {
		$conflicts = new Optml_Conflicting_Plugins();
		$conflict  = new Optml_Jetpack_Photon();

		$this->assertFalse( $this->compatibility->should_load() );
		$this->assertFalse( $conflict->is_conflict_valid() );
		$this->assertNotContains( 'jetpack/jetpack.php', $conflicts->get_conflicting_plugins( true ) );
	}

	/**
	 * Jetpack with Photon should remain a generic conflict.
	 */
	public function test_jetpack_with_photon_is_a_generic_conflict() {
		Jetpack::$photon_active = true;
		$this->compatibility->register();
		$conflicts              = new Optml_Conflicting_Plugins();
		$conflict               = new Optml_Jetpack_Photon();

		$this->assertTrue( $this->compatibility->should_load() );
		$this->assertTrue( $conflict->is_conflict_valid() );
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
