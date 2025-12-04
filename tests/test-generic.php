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
	use Optml_Normalizer;
	/**
	 * Check if the version is defined
	 */
	function test_version_exists() {
		$this->assertTrue( defined( 'OPTML_VERSION' ) );
		$this->assertTrue( ( version_compare( OPTML_VERSION, '0.0.1', '>' ) ) );
	}

	/**
	 * We should remove DEBUG mode in production.
	 */
	function test_debug_mode() {

		$this->assertFalse( OPTML_DEBUG );
	}
	/**
	 * Test domain hash.
	 */
	function test_domain_hash() {
		$value = $this->to_domain_hash( "https://domain.com");

		$this->assertEquals( $this->to_domain_hash("https://domain.com/fr/"), $value );
		$this->assertEquals( $this->to_domain_hash("https://domain.com/fr/dada"), $value );
		$this->assertEquals( $this->to_domain_hash("https://domain.com/fr/dada/dada"), $value );
		$this->assertEquals( $this->to_domain_hash("https://domain.com/fr"), $value );
		$this->assertEquals( $this->to_domain_hash("https://domain.com/"), $value );
		$this->assertEquals( $this->to_domain_hash("https://www.domain.com/"), $value );
		$this->assertEquals( $this->to_domain_hash("http://www.domain.com/"), $value );
		$this->assertEquals( $this->to_domain_hash("//www.domain.com/"), $value );
		$this->assertNotEquals( $this->to_domain_hash("https://something.com/"), $value );
	}

	/**
	 * Test get_unoptimized_url with non-Optimole URLs.
	 */
	function test_get_unoptimized_url_non_optimole() {
		// Initialize config for testing
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Config::init( [
			'key'    => 'test123',
			'secret' => '12345',
		] );

		// Non-Optimole URLs should be returned as-is
		$url = 'http://example.org/wp-content/uploads/image.jpg';
		$this->assertEquals( $url, $this->get_unoptimized_url( $url ) );

		$url = 'https://example.com/image.png';
		$this->assertEquals( $url, $this->get_unoptimized_url( $url ) );

		$url = '/wp-content/uploads/image.jpg';
		$this->assertEquals( $url, $this->get_unoptimized_url( $url ) );
	}

	/**
	 * Test get_unoptimized_url with Optimole URLs (regular images).
	 */
	function test_get_unoptimized_url_optimole_regular() {
		// Initialize config for testing
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Config::init( [
			'key'    => 'test123',
			'secret' => '12345',
		] );

		// Optimole URL with regular image - should extract original URL after second 'http'
		$optimole_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/http://example.org/wp-content/uploads/image.jpg';
		$expected = 'http://example.org/wp-content/uploads/image.jpg';
		$this->assertEquals( $expected, $this->get_unoptimized_url( $optimole_url ) );

		// Optimole URL with https in original
		$optimole_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/https://example.org/wp-content/uploads/image.jpg';
		$expected = 'https://example.org/wp-content/uploads/image.jpg';
		$this->assertEquals( $expected, $this->get_unoptimized_url( $optimole_url ) );

		// Optimole URL with query parameters
		$optimole_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/http://example.org/wp-content/uploads/image.jpg?param=value';
		$expected = 'http://example.org/wp-content/uploads/image.jpg?param=value';
		$this->assertEquals( $expected, $this->get_unoptimized_url( $optimole_url ) );
	}

	/**
	 * Test get_unoptimized_url with uploaded images (offloaded images).
	 */
	function test_get_unoptimized_url_uploaded_image() {
		// Initialize config for testing
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Config::init( [
			'key'    => 'test123',
			'secret' => '12345',
		] );

		// Uploaded image URL with /id: pattern - should extract original URL
		$uploaded_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/id:abc123/http://example.org/wp-content/uploads/image.jpg';
		$expected = '/id:abc123/http://example.org/wp-content/uploads/image.jpg';
		$result = $this->get_unoptimized_url( $uploaded_url );
		$this->assertStringContainsString( '/id:abc123/', $result );
		$this->assertStringContainsString( 'http://example.org/wp-content/uploads/image.jpg', $result );

		// Uploaded image URL with https in original
		$uploaded_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/id:xyz789/https://example.org/wp-content/uploads/image.jpg';
		$expected = '/id:xyz789/https://example.org/wp-content/uploads/image.jpg';
		$result = $this->get_unoptimized_url( $uploaded_url );
		$this->assertStringContainsString( '/id:xyz789/', $result );
		$this->assertStringContainsString( 'https://example.org/wp-content/uploads/image.jpg', $result );
	}

	/**
	 * Test get_unoptimized_url edge cases.
	 */
	function test_get_unoptimized_url_edge_cases() {
		// Initialize config for testing
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
		] );
		Optml_Config::init( [
			'key'    => 'test123',
			'secret' => '12345',
		] );

		// Optimole URL without second 'http' - should return as-is
		$optimole_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto';
		$this->assertEquals( $optimole_url, $this->get_unoptimized_url( $optimole_url ) );

		// Empty string
		$this->assertEquals( '', $this->get_unoptimized_url( '' ) );

		// URL with only one 'http' occurrence
		$optimole_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/image.jpg';
		$this->assertEquals( $optimole_url, $this->get_unoptimized_url( $optimole_url ) );

		// Uploaded image URL without matching pattern - should return as-is
		$uploaded_url = 'https://test123.i.optimole.com/cb:test/w:800/h:600/q:mauto/id:abc123/image.jpg';
		$result = $this->get_unoptimized_url( $uploaded_url );
		// Should return the URL as-is since pattern doesn't match
		$this->assertEquals( $uploaded_url, $result );
	}

	/**
	 * Test get_unoptimized_url with custom domain configuration.
	 */
	function test_get_unoptimized_url_custom_domain() {
		// Initialize config with custom domain
		$settings = new Optml_Settings();
		$settings->update( 'service_data', [
			'cdn_key'    => 'test123',
			'cdn_secret' => '12345',
			'whitelist'  => [ 'example.com', 'example.org' ],
			'domain'     => 'cdn.example.com',
			'is_cname_assigned' => 'yes',
		] );
		Optml_Config::init( [
			'key'    => 'test123',
			'secret' => '12345',
			'domain' => 'cdn.example.com',
		] );

		// Custom domain Optimole URL
		$optimole_url = 'https://cdn.example.com/cb:test/w:800/h:600/q:mauto/http://example.org/wp-content/uploads/image.jpg';
		$expected = 'http://example.org/wp-content/uploads/image.jpg';
		$this->assertEquals( $expected, $this->get_unoptimized_url( $optimole_url ) );

		// Non-Optimole URL should still return as-is
		$url = 'http://example.org/wp-content/uploads/image.jpg';
		$this->assertEquals( $url, $this->get_unoptimized_url( $url ) );
	}
}
