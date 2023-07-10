<?php

/**
 * Class Test_Dam.
 */
class Test_Dam extends WP_UnitTestCase {

	const TEST_API_KEY = 'testkey';

	/**
	 * @var Optml_Settings
	 */
	private $settings;

	/**
	 * @var Optml_Dam
	 */
	private $dam;

	public function setUp() {
		parent::setUp();

		$plugin         = Optml_Main::instance();
		$this->dam      = $plugin->dam;
		$this->settings = $plugin->admin->settings;

		$this->settings->update( 'api_key', self::TEST_API_KEY );
	}

	public function test_iframe_url_selected_sites_enabled() {
		$this->settings->update( 'cloud_sites', [
			'all'           => 'false',
			'example.com'   => 'true',
			'othersite.com' => 'false'
		] );

		$iframe_url = $this->dam->build_iframe_url();

		$data = $this->get_iframe_args_from_url( $iframe_url );

		$this->assertIsArray( $data );
		$this->assertNotEmpty( $data );
		$this->assertEquals( self::TEST_API_KEY, $data['token'] );
		$this->assertNotEmpty( $data['sites'] );
		$this->assertContains( 'example.com', $data['sites'] );
		$this->assertNotEmpty( $data['site'] );
		$this->assertEquals( $data['site'], get_site_url() );
	}

	public function test_iframe_url_all_cloud_sites_enabled() {
		$this->settings->update( 'cloud_sites', [
			'all'           => 'true',
			'example.com'   => 'false',
			'othersite.com' => 'false',
		] );

		$iframe_url = $this->dam->build_iframe_url();

		$data = $this->get_iframe_args_from_url( $iframe_url );

		$this->assertIsArray( $data );
		$this->assertEmpty( $data['sites'] );
	}

	/**
	 * Get the data array for the DAM authorization from the iframe url.
	 *
	 * @param string $url The url to parse.
	 *
	 * @return array
	 */
	private function get_iframe_args_from_url( $url ) {
		$url = parse_url( $url );

		parse_str( $url['query'], $params );

		$data = $params['data'];
		$data = base64_decode( $data );

		return json_decode( $data, true );
	}
}
