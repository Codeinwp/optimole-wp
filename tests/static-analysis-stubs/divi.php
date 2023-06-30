<?php
// File: ET_Core_PageResource.php

class ET_Core_PageResource {

	/**
	 * @var \WP_Filesystem_Base|null
	 */
	public static $wpfs;

	/**
	 * The absolute path to the static resource on the server.
	 *
	 * @var string
	 */
	public $PATH;

	/**
	 * Get the data associated with the provided path.
	 *
	 * @param string $context
	 * @return mixed
	 */
	public function get_data( $context ) {}
}
