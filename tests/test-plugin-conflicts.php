<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2023, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Plugin_Conflicts.
 */
class Test_Plugin_Conflicts extends WP_UnitTestCase {
	public static $conflicts;

	public function setUp(): void {
		parent::setUp();
        self::$conflicts = new Optml_Conflicting_Plugins();

        add_filter( 'optml_conflicting_active_plugins', function ( $plugins ) {
            return [
                'wp-smush'     => 'wp-smushit/wp-smush.php',
                'wp-smush-pro' => 'wp-smush-pro/wp-smush.php'
            ];
        } );
	}

	public function test_has_conflicting_plugins() {
        $this->assertTrue( self::$conflicts->has_conflicting_plugins() );
        $this->assertCount( 2, self::$conflicts->get_conflicting_plugins() );
	}

	public function test_dismissed_conflicts() {
        self::$conflicts->dismiss_conflicting_plugins();
        $this->assertCount( 0, self::$conflicts->get_conflicting_plugins() );
	}

	public function test_new_dismissed_conflicts() {
        self::$conflicts->dismiss_conflicting_plugins();

        add_filter( 'optml_conflicting_active_plugins', function ( $plugins ) {
            $plugins['kraken'] = 'kraken-image-optimizer/kraken.php';
            return $plugins;
        } );

        $this->assertCount( 1, self::$conflicts->get_conflicting_plugins() );
	}
}