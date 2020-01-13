<?php

/**
 * CLI class.
 *
 * Author:          Bogdan Preda <bogdan.preda@themeisle.com>
 * Created on:      19/07/2018
 *
 * @package    \Optimole\Inc
 * @author     Optimole <friends@optimole.com>
 */

/**
 * Class Optml_Cli
 */
class Optml_Cli {

	/**
	 * Api version.
	 *
	 * @var string Version string.
	 */
	const CLI_NAMESPACE = 'optimole';

	/**
	 * CLI controllers
	 *
	 * @var array List of CLI controllers.
	 */
	private $commands = array(
		'setting',
	);

	/**
	 * Optml_Cli constructor.
	 */
	public function __construct() {
		foreach ( $this->commands as $command ) {
			$class_name = 'Optml_Cli_' . ucfirst( $command );
			$controller = new $class_name();
			try {
				\WP_CLI::add_command( self::CLI_NAMESPACE . ' ' . $command, $controller );
			} catch ( \Exception $e ) {
				// TODO Log this exception.
			}
		}
	}
}
