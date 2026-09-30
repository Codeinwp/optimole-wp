<?php
/**
 * WordPress unit test plugin.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @copyright   Copyright (c) 2017, ThemeIsle
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

use WpOrg\Requests\Utility\CaseInsensitiveDictionary;

/**
 * Class Test_Admin.
 *
 * Tests for Optml_Admin class methods.
 */
class Test_Admin extends WP_UnitTestCase {

	/**
	 * Instance of Optml_Admin.
	 *
	 * @var Optml_Admin
	 */
	private $admin;

	/**
	 * Black Friday date for current year.
	 *
	 * @var DateTime
	 */
	private $black_friday;

	/**
	 * Sale start date.
	 *
	 * @var DateTime
	 */
	private $sale_start;

	/**
	 * Sale end date.
	 *
	 * @var DateTime
	 */
	private $sale_end;

	/**
	 * Set up test environment before each test.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->admin = new Optml_Admin();
		
		// Calculate Black Friday dates dynamically
		$now = new \DateTime( 'now' );
		$current_year = $now->format( 'Y' );

		$this->black_friday = new \DateTime( "last Friday of November $current_year" );
		$this->sale_start = clone $this->black_friday;
		$this->sale_start->modify( 'monday this week' );
		$this->sale_start->setTime( 0, 0 );

		$this->sale_end = clone $this->sale_start;
		$this->sale_end->modify( '+7 days' );
		$this->sale_end->setTime( 23, 59, 59 );
		
		// Clean up options before each test
		delete_option( Optml_Admin::BF_PROMO_DISMISS_KEY );
		
		// Remove any filters that might be set
		remove_all_filters( 'themeisle_sdk_is_black_friday_sale' );
		remove_all_filters( 'themeisle_sdk_current_date' );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		delete_option( Optml_Admin::BF_PROMO_DISMISS_KEY );
		remove_all_filters( 'themeisle_sdk_is_black_friday_sale' );
		remove_all_filters( 'themeisle_sdk_current_date' );
		parent::tearDown();
	}

	/**
	 * Helper to set up Black Friday sale period.
	 */
	private function setup_black_friday_sale_period(): void {
		add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_true' );
	}

	/**
	 * Helper to mock current date.
	 *
	 * @param DateTime $date The date to mock.
	 */
	private function mock_date_to( DateTime $date ): void {
		remove_all_filters( 'themeisle_sdk_current_date' );
		add_filter( 'themeisle_sdk_current_date', static function() use ( $date ) {
			return $date;
		} );
	}

	/**
	 * Test get_bf_notices returns empty array when not Black Friday period.
	 */
	public function test_get_bf_notices_returns_empty_when_not_black_friday() {
		// Mock Black Friday status as false
		add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_false' );

		$result = $this->admin->get_bf_notices( 'free' );
		
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
	}



	/**
	 * Test get_bf_notices returns empty array for non-free plans.
	 */
	public function test_get_bf_notices_returns_empty_for_non_free_plans() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Test various non-free plans - all should return empty
		$non_free_plans = [ 'starter', 'growth', 'business', 'agency', 'starter-yearly', 'invalid-plan' ];
		
		foreach ( $non_free_plans as $plan ) {
			$result = $this->admin->get_bf_notices( $plan );
			$this->assertIsArray( $result, "Failed for plan: $plan" );
			$this->assertEmpty( $result, "Expected empty array for non-free plan: $plan" );
		}
	}

	/**
	 * Test get_bf_notices returns correct structure for free plan during Black Friday.
	 */
	public function test_get_bf_notices_structure_for_free_plan() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		// Should return both sidebar and banner notices
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		$this->assertArrayHasKey( 'banner', $result );
		
		// Validate sidebar structure
		$sidebar = $result['sidebar'];
		$this->assertArrayHasKey( 'title', $sidebar );
		$this->assertArrayHasKey( 'subtitle', $sidebar );
		$this->assertArrayHasKey( 'cta_link', $sidebar );
		
		// Validate banner structure
		$banner = $result['banner'];
		$this->assertArrayHasKey( 'urgency', $banner );
		$this->assertArrayHasKey( 'title', $banner );
		$this->assertArrayHasKey( 'subtitle', $banner );
		$this->assertArrayHasKey( 'cta_text', $banner );
		$this->assertArrayHasKey( 'cta_link', $banner );
		$this->assertArrayHasKey( 'dismiss_key', $banner );
		
		// Check subtitle contains discount information for free users
		$this->assertStringContainsString( '25', $sidebar['subtitle'] );
		$this->assertStringContainsString( 'BFCM2525', $sidebar['subtitle'] );
		$this->assertStringContainsString( 'Use code', $sidebar['subtitle'] );
		
		// Check CTA link is not empty
		$this->assertNotEmpty( $sidebar['cta_link'] );
		
		// Check banner CTA text and message
		$this->assertNotEmpty( $banner['cta_text'] );
		$this->assertStringContainsString( 'Use coupon code', $banner['subtitle'] );
		$this->assertStringContainsString( 'BFCM2525', $banner['subtitle'] );
		$this->assertStringContainsString( '25', $banner['subtitle'] );
	}
	


	/**
	 * Test get_bf_notices banner visibility based on dismissal status.
	 */
	public function test_get_bf_notices_banner_dismissal() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Test banner is hidden when dismissed
		update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, 'yes' );
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		$this->assertArrayNotHasKey( 'banner', $result );
		
		// Test banner appears when not dismissed
		delete_option( Optml_Admin::BF_PROMO_DISMISS_KEY );
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		$this->assertArrayHasKey( 'banner', $result );
	}

	/**
	 * Test get_bf_notices contains correct dismiss key in banner.
	 */
	public function test_get_bf_notices_banner_has_correct_dismiss_key() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		$this->assertArrayHasKey( 'banner', $result );
		$this->assertArrayHasKey( 'dismiss_key', $result['banner'] );
		$this->assertEquals( Optml_Admin::BF_PROMO_DISMISS_KEY, $result['banner']['dismiss_key'] );
	}

	/**
	 * Test get_bf_notices banner contains time-sensitive information.
	 */
	public function test_get_bf_notices_contains_time_information() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		$this->assertArrayHasKey( 'banner', $result );
		$banner = $result['banner'];
		
		// Urgency message should contain time-related text
		$this->assertArrayHasKey( 'urgency', $banner );
		$this->assertStringContainsString( 'left', $banner['urgency'] );
	}
	/**
	 * Test get_bf_notices sidebar has correct title format.
	 */
	public function test_get_bf_notices_has_correct_title_format() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		$this->assertArrayHasKey( 'sidebar', $result );
		$this->assertArrayHasKey( 'title', $result['sidebar'] );
		$this->assertStringContainsString( 'Private Sale', $result['sidebar']['title'] );
	}

	/**
	 * Test get_bf_notices banner has correct title format.
	 */
	public function test_get_bf_notices_banner_has_correct_title() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		$this->assertArrayHasKey( 'banner', $result );
		$this->assertArrayHasKey( 'title', $result['banner'] );
		$this->assertStringContainsString( 'Black Friday', $result['banner']['title'] );
		$this->assertStringContainsString( 'private sale', $result['banner']['title'] );
	}



	/**
	 * Test get_bf_notices with different date scenarios.
	 */
	public function test_get_bf_notices_date_scenarios() {
		$this->setup_black_friday_sale_period();
		
		// Test at start of sale period
		$sale_start = clone $this->sale_start;
		$this->mock_date_to( $sale_start );
		
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		
		// Test during mid-sale
		$black_friday_noon = clone $this->black_friday;
		$black_friday_noon->setTime( 12, 0 );
		$this->mock_date_to( $black_friday_noon );
		
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		
		// Test near end of sale
		$sale_end_evening = clone $this->sale_end;
		$sale_end_evening->setTime( 23, 0 );
		$this->mock_date_to( $sale_end_evening );
		
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertNotEmpty( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
	}

	/**
	 * Test get_bf_notices banner appears with non-'yes' dismissal values.
	 */
	public function test_get_bf_notices_banner_dismissal_values() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Banner only hides when dismissal key is exactly 'yes'
		$test_values = [ 'no', '0', '', 'dismissed' ];
		
		foreach ( $test_values as $value ) {
			update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, $value );
			$result = $this->admin->get_bf_notices( 'free' );
			$this->assertArrayHasKey( 'banner', $result, "Banner should appear when dismiss key is '$value'" );
		}
	}

	// -------------------------------------------------------------------------
	// get_permissions_policy() tests
	// -------------------------------------------------------------------------

	/**
	 * Helper: set scale, network_optimization and retina_images settings, then
	 * return a fresh Optml_Admin instance that reads those values.
	 *
	 * @param string $scale              'enabled' or 'disabled'.
	 * @param string $network_opt        'enabled' or 'disabled'.
	 * @param string $retina             'enabled' or 'disabled'.
	 * @return Optml_Admin
	 */
	private function make_admin_with_policy_settings( string $scale, string $network_opt, string $retina ): Optml_Admin {
		$settings = new Optml_Settings();
		$settings->update( 'scale', $scale );
		$settings->update( 'network_optimization', $network_opt );
		$settings->update( 'retina_images', $retina );
		Optml_Config::$service_url = 'https://test123.i.optimole.com';
		return new Optml_Admin();
	}

	/**
	 * All three settings off → empty string.
	 */
	public function test_permissions_policy_empty_when_all_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertSame( '', $admin->get_permissions_policy() );
	}

	/**
	 * Scale ON → ch-viewport-width present; ch-dpr and ch-ect absent.
	 */
	public function test_permissions_policy_ch_viewport_width_when_scale_on() {
		$admin  = $this->make_admin_with_policy_settings( 'disabled', 'disabled', 'disabled' );
		$policy = $admin->get_permissions_policy();
		$this->assertStringContainsString( 'ch-viewport-width', $policy );
		$this->assertStringNotContainsString( 'ch-dpr', $policy );
		$this->assertStringNotContainsString( 'ch-ect', $policy );
	}

	/**
	 * Scale OFF → ch-viewport-width absent.
	 */
	public function test_permissions_policy_no_ch_viewport_width_when_scale_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertStringNotContainsString( 'ch-viewport-width', $admin->get_permissions_policy() );
	}

	/**
	 * Retina ON → ch-dpr present; ch-viewport-width absent.
	 */
	public function test_permissions_policy_ch_dpr_when_retina_on() {
		$admin  = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'enabled' );
		$policy = $admin->get_permissions_policy();
		$this->assertStringContainsString( 'ch-dpr', $policy );
		$this->assertStringNotContainsString( 'ch-viewport-width', $policy );
	}

	/**
	 * Retina OFF → ch-dpr absent.
	 */
	public function test_permissions_policy_no_ch_dpr_when_retina_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertStringNotContainsString( 'ch-dpr', $admin->get_permissions_policy() );
	}

	/**
	 * Network optimization ON → ch-ect present.
	 */
	public function test_permissions_policy_ch_ect_when_network_optimization_on() {
		$admin  = $this->make_admin_with_policy_settings( 'enabled', 'enabled', 'disabled' );
		$policy = $admin->get_permissions_policy();
		$this->assertStringContainsString( 'ch-ect', $policy );
	}

	/**
	 * All three ON → all three directives present in a single string.
	 */
	public function test_permissions_policy_all_features_on() {
		$admin  = $this->make_admin_with_policy_settings( 'disabled', 'enabled', 'enabled' );
		$policy = $admin->get_permissions_policy();
		$this->assertStringContainsString( 'ch-viewport-width', $policy );
		$this->assertStringContainsString( 'ch-ect', $policy );
		$this->assertStringContainsString( 'ch-dpr', $policy );
	}

	/**
	 * Service URL is embedded in the policy when any feature is ON.
	 */
	public function test_permissions_policy_service_url_embedded() {
		$admin  = $this->make_admin_with_policy_settings( 'disabled', 'disabled', 'disabled' );
		$policy = $admin->get_permissions_policy();
		$this->assertStringContainsString( 'test123.i.optimole.com', $policy );
	}

	// -------------------------------------------------------------------------
	// get_accept_ch_hints() tests
	// -------------------------------------------------------------------------

	/**
	 * All three settings off → empty string.
	 * Note: for the scale setting, 'enabled' = scale IS off (legacy inversion).
	 */
	public function test_accept_ch_hints_empty_when_all_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertSame( '', $admin->get_accept_ch_hints() );
	}

	/**
	 * Scale ON → Viewport-Width present; DPR and ECT absent.
	 * Note: 'disabled' raw value = scale IS on (legacy inversion).
	 */
	public function test_accept_ch_hints_viewport_width_when_scale_on() {
		$admin = $this->make_admin_with_policy_settings( 'disabled', 'disabled', 'disabled' );
		$hints = $admin->get_accept_ch_hints();
		$this->assertStringContainsString( 'Viewport-Width', $hints );
		$this->assertStringNotContainsString( 'DPR', $hints );
		$this->assertStringNotContainsString( 'ECT', $hints );
	}

	/**
	 * Scale OFF → Viewport-Width absent.
	 * Note: 'enabled' raw value = scale IS off (legacy inversion).
	 */
	public function test_accept_ch_hints_no_viewport_width_when_scale_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertStringNotContainsString( 'Viewport-Width', $admin->get_accept_ch_hints() );
	}

	/**
	 * Retina ON → DPR present; Viewport-Width absent (scale kept off).
	 */
	public function test_accept_ch_hints_dpr_when_retina_on() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'enabled' );
		$hints = $admin->get_accept_ch_hints();
		$this->assertStringContainsString( 'DPR', $hints );
		$this->assertStringNotContainsString( 'Viewport-Width', $hints );
	}

	/**
	 * Retina OFF → DPR absent.
	 */
	public function test_accept_ch_hints_no_dpr_when_retina_off() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'disabled', 'disabled' );
		$this->assertStringNotContainsString( 'DPR', $admin->get_accept_ch_hints() );
	}

	/**
	 * Network optimization ON → ECT present.
	 */
	public function test_accept_ch_hints_ect_when_network_optimization_on() {
		$admin = $this->make_admin_with_policy_settings( 'enabled', 'enabled', 'disabled' );
		$this->assertStringContainsString( 'ECT', $admin->get_accept_ch_hints() );
	}

	/**
	 * All three ON → all three tokens present.
	 * Note: 'disabled' raw value = scale IS on (legacy inversion).
	 */
	public function test_accept_ch_hints_all_features_on() {
		$admin = $this->make_admin_with_policy_settings( 'disabled', 'enabled', 'enabled' );
		$hints = $admin->get_accept_ch_hints();
		$this->assertStringContainsString( 'Viewport-Width', $hints );
		$this->assertStringContainsString( 'ECT', $hints );
		$this->assertStringContainsString( 'DPR', $hints );
	}

	/**
	 * Hints and policy directives stay in sync: each setting contributes
	 * to both get_accept_ch_hints() and get_permissions_policy().
	 * Note: scale uses legacy inversion — 'disabled' raw = scale ON.
	 */
	public function test_accept_ch_and_policy_are_in_sync() {
		foreach ( [ 'scale', 'network_optimization', 'retina_images' ] as $setting ) {
			// Legacy: 'disabled' = scale ON, 'enabled' = scale OFF.
			$scale   = $setting === 'scale' ? 'disabled' : 'enabled';
			$net_opt = $setting === 'network_optimization' ? 'enabled' : 'disabled';
			$retina  = $setting === 'retina_images' ? 'enabled' : 'disabled';

			$admin  = $this->make_admin_with_policy_settings( $scale, $net_opt, $retina );
			$hints  = $admin->get_accept_ch_hints();
			$policy = $admin->get_permissions_policy();

			$this->assertNotEmpty( $hints, "Expected non-empty hints for setting: $setting" );
			$this->assertNotEmpty( $policy, "Expected non-empty policy for setting: $setting" );
		}
	}

	/**
	 * Test get_bf_notices at exact boundary conditions.
	 */
	public function test_get_bf_notices_date_boundaries() {
		$this->setup_black_friday_sale_period();
		
		// Test exactly at midnight start
		$exact_start = clone $this->sale_start;
		$exact_start->setTime( 0, 0, 0 );
		$this->mock_date_to( $exact_start );
		
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertNotEmpty( $result, 'Should show notices at exact start time' );
		
		// Test exactly at end time
		$exact_end = clone $this->sale_end;
		$exact_end->setTime( 23, 59, 59 );
		$this->mock_date_to( $exact_end );
		
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertNotEmpty( $result, 'Should show notices at exact end time' );
	}

	/**
	 * An Author POSTing a scripted SVG as the raw request body to the REST media endpoint must get it sanitized.
	 *
	 * Core stores a raw-body upload through wp_handle_sideload(), which fires wp_handle_sideload_prefilter
	 * rather than wp_handle_upload_prefilter.
	 */
	public function test_svg_rest_body_upload_is_sanitized(): void {
		$this->assertNotFalse(
			has_filter( 'wp_handle_sideload_prefilter', [ Optml_Main::instance()->admin, 'sanitize_sideloaded_svg' ] ),
			'The sanitizer must run on the sideload path used by raw-body REST media uploads.'
		);

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

		$response = $this->dispatch_svg_rest_upload( 'rest.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script><rect width="1" height="1"/></svg>' );

		$this->assertSame( 201, $response->get_status(), 'The SVG REST upload should be created.' );

		$attachment_id = (int) $response->get_data()['id'];
		$stored_path   = (string) get_attached_file( $attachment_id );

		$this->assertFileExists( $stored_path );
		$this->assertStringNotContainsString( '<script', (string) file_get_contents( $stored_path ), 'The stored SVG kept its script.' );

		wp_delete_attachment( $attachment_id, true );
	}

	/**
	 * A malformed SVG the sanitizer cannot parse must be rejected on the sideload path, not stored as-is.
	 *
	 * Breaking the XML is how a scripted SVG would slip past the sanitizer, so the unparseable file is refused.
	 */
	public function test_svg_rest_body_upload_rejects_unsanitizable_svg(): void {
		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

		$attachments_before = $this->count_attachments();

		$response = $this->dispatch_svg_rest_upload( 'malformed.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</svg>' );

		$this->assertSame( 500, $response->get_status(), 'The unsanitizable SVG was stored.' );
		$this->assertSame( 'rest_upload_sideload_error', $response->get_data()['code'] );
		$this->assertSame( $attachments_before, $this->count_attachments() );
	}

	/**
	 * POST an SVG as the raw request body to the REST media endpoint.
	 *
	 * @param string $filename File name sent in Content-Disposition.
	 * @param string $body     Raw SVG markup.
	 *
	 * @return WP_REST_Response
	 */
	private function dispatch_svg_rest_upload( string $filename, string $body ): WP_REST_Response {
		$request = new WP_REST_Request( 'POST', '/wp/v2/media' );
		$request->set_header( 'Content-Type', 'image/svg+xml' );
		$request->set_header( 'Content-Disposition', 'attachment; filename=' . $filename );
		$request->set_body( $body );

		// SVGs have no raster size; skip core sub-size generation, which warns on them.
		add_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
		add_filter( 'wp_generate_attachment_metadata', '__return_empty_array', 0 );
		try {
			return rest_get_server()->dispatch( $request );
		} finally {
			remove_filter( 'intermediate_image_sizes_advanced', '__return_empty_array' );
			remove_filter( 'wp_generate_attachment_metadata', '__return_empty_array', 0 );
		}
	}

	/**
	 * Count all attachments, whatever their status.
	 *
	 * @return int
	 */
	private function count_attachments(): int {
		return count( get_posts( [ 'post_type' => 'attachment', 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ] ) );
	}

	/**
	 * A background restore of an offloaded SVG runs without a user and must still succeed, sanitized.
	 */
	public function test_svg_background_restore_is_sanitized_without_user(): void {
		$settings = new Optml_Settings();
		$settings->update(
			'service_data',
			[
				'cdn_key'    => 'example',
				'cdn_secret' => 'test',
				'whitelist'  => [ 'example.org' ],
			]
		);
		$settings->update( 'offload_media', 'enabled' );

		$attachment_id = self::factory()->attachment->create_upload_object( OPTML_PATH . 'tests/assets/sample.svg' );
		$meta          = wp_get_attachment_metadata( $attachment_id );
		$meta          = is_array( $meta ) ? $meta : [];
		$meta['file']  = '/' . Optml_Media_Offload::KEYS['uploaded_flag'] . 'svg/2026/09/sample.svg';
		wp_update_attachment_metadata( $attachment_id, $meta );

		// Scheduled/CLI restores run with no logged-in user.
		wp_set_current_user( 0 );

		add_filter( 'pre_http_request', [ $this, 'mock_offloaded_svg_download' ], 10, 3 );
		try {
			$restored = Optml_Media_Offload::instance()->rollback_and_update_images( [ $attachment_id ] );
		} finally {
			remove_filter( 'pre_http_request', [ $this, 'mock_offloaded_svg_download' ], 10 );
		}

		$restored_file = (string) get_attached_file( $attachment_id );

		$this->assertSame( 1, $restored, 'The SVG restore was rejected.' );
		$this->assertEmpty( get_post_meta( $attachment_id, Optml_Media_Offload::META_KEYS['rollback_error'], true ) );
		$this->assertFileExists( $restored_file );
		$this->assertStringNotContainsString( '<script', (string) file_get_contents( $restored_file ), 'The restored SVG kept its script.' );

		wp_delete_attachment( $attachment_id, true );
	}

	/**
	 * Mock the cloud URL lookup and the download of a scripted SVG during a restore.
	 *
	 * @param false|array<string, mixed>|WP_Error $preempt Short-circuit response.
	 * @param array<string, mixed>                $args    Request arguments.
	 * @param string                              $url     Request URL.
	 *
	 * @return false|array<string, mixed>|WP_Error
	 */
	public function mock_offloaded_svg_download( $preempt, array $args, string $url ) {
		$response = [
			'headers'  => new CaseInsensitiveDictionary( [ 'content-type' => 'application/json' ] ),
			'response' => [
				'code'    => 200,
				'message' => 'OK',
			],
			'cookies'  => [],
			'filename' => '',
			'body'     => '',
		];

		if ( 'https://generateurls-prod.i.optimole.com/upload' === $url ) {
			$response['body'] = '{"getUrl": "getUrl"}';
			return $response;
		}

		if ( 'getUrl' === $url ) {
			file_put_contents( $args['filename'], '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script><rect width="1" height="1"/></svg>' );
			return $response;
		}

		return $preempt;
	}
}
