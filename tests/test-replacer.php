<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Generic.
 */
class Test_Generic extends WP_UnitTestCase {
	const IMG_TAGS = '<div id="wp-custom-header" class="wp-custom-header"><img src="http://test.com/wp-content/themes/twentyseventeen/assets/images/header.jpg" width="2000" height="1200" alt="Test" /></div></div>';


	/**
	 * TODO * Test if image tag replacement is done.
	 * TODO * Test if replacement on optimole optimized image is not doing it twice.
	 * TODO * Test if replacement on background url is working ok, i.e css style.
	 * TODO * Test if optimization on non allowed extensions is not working.
	 * TODO * Test replacement on elementor_data post meta
	 * TODO * TEST lazy load replacement.
	 * TODO * TEst max width/height normalization.
	 * TODO * Test replacement on post content.
	 * TODO * TEst maybe strip image size with source image does not exists.
	 * TODO * Test maybe strip image size with source image exists.
	 * TODO * Test replacement hooks of when replacement option is off.
	 * TODO * Test compression quality change, url change.
	 * TODO * Test duplicate srcset with same url signature.
	 * TODO * Test custom domain replacement.
	 * TODO * TEST site mirror replacement.
	 */

}