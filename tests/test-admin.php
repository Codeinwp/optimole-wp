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

		$this->black_friday = new DateTime( "last Friday of November $current_year" );
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
	 * Test get_bf_notices returns empty array for yearly plans.
	 */
	public function test_get_bf_notices_returns_empty_for_yearly_plans() {
		// Mock Black Friday status as true
		add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_true' );
		
		// Test various yearly plans
		$yearly_plans = [ 'starter-yearly', 'growth-yearly', 'business-yearly', 'agency-yearly' ];
		
		foreach ( $yearly_plans as $plan ) {
			$result = $this->admin->get_bf_notices( $plan );
			$this->assertIsArray( $result, "Failed for plan: $plan" );
			$this->assertEmpty( $result, "Expected empty array for yearly plan: $plan" );
		}
	}

	/**
	 * Test get_bf_notices returns empty array for empty or invalid plans.
	 */
	public function test_get_bf_notices_returns_empty_for_invalid_plans() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Test invalid plan (yearly suffix makes it invalid for BF notices)
		$result = $this->admin->get_bf_notices( 'invalid-plan-yearly' );
		$this->assertIsArray( $result );
		$this->assertEmpty( $result );
		
		// Verify the plan is actually being evaluated, not just returning empty by default
		$result_valid = $this->admin->get_bf_notices( 'starter' );
		$this->assertNotEmpty( $result_valid, 'Valid plan should return notices' );
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
		$this->assertStringContainsString( 'BFCM2425', $sidebar['subtitle'] );
		
		// Check CTA link is not empty
		$this->assertNotEmpty( $sidebar['cta_link'] );
		
		// Check banner CTA text
		$this->assertNotEmpty( $banner['cta_text'] );
	}
	
	/**
	 * Test get_bf_notices returns correct structure for monthly plan during Black Friday.
	 */
	public function test_get_bf_notices_structure_for_monthly_plan() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Test various monthly plans
		$monthly_plans = [ 'starter', 'growth', 'business', 'agency' ];
		
		foreach ( $monthly_plans as $plan ) {
			$result = $this->admin->get_bf_notices( $plan );
			
			$this->assertIsArray( $result, "Failed for plan: $plan" );
			$this->assertArrayHasKey( 'sidebar', $result, "Missing sidebar for plan: $plan" );
			$this->assertArrayHasKey( 'banner', $result, "Missing banner for plan: $plan" );
			
			$banner = $result['banner'];
			
			// Check banner subtitle contains discount information for monthly users
			$this->assertStringContainsString( '15', $banner['subtitle'], "Missing 15% discount for plan: $plan" );
			$this->assertStringContainsString( 'yearly', $banner['subtitle'], "Missing yearly mention for plan: $plan" );
			
			// Check CTA for monthly users
			$this->assertStringContainsString( 'Contact us', $banner['cta_text'], "Wrong CTA for plan: $plan" );
			$this->assertNotEmpty( $banner['cta_link'], "Empty link for plan: $plan" );
		}
	}

	/**
	 * Test get_bf_notices banner is hidden when dismissed.
	 */
	public function test_get_bf_notices_banner_hidden_when_dismissed() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Set dismissal option
		update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, 'yes' );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		// Should only have sidebar, not banner
		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'sidebar', $result );
		$this->assertArrayNotHasKey( 'banner', $result );
	}

	/**
	 * Test get_bf_notices banner appears when not dismissed.
	 */
	public function test_get_bf_notices_banner_appears_when_not_dismissed() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Ensure option is not set
		delete_option( Optml_Admin::BF_PROMO_DISMISS_KEY );
		
		$result = $this->admin->get_bf_notices( 'free' );
		
		// Should have both sidebar and banner
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
	 * Test get_bf_notices with non-'yes' dismissal values.
	 */
	public function test_get_bf_notices_banner_appears_with_non_yes_dismissal() {
		$this->setup_black_friday_sale_period();
		$this->mock_date_to( clone $this->black_friday );
		
		// Test with 'no' value
		update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, 'no' );
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertArrayHasKey( 'banner', $result, "Banner should appear when dismiss key is 'no'" );
		
		// Test with '0' value
		update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, '0' );
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertArrayHasKey( 'banner', $result, "Banner should appear when dismiss key is '0'" );
		
		// Test with empty string
		update_option( Optml_Admin::BF_PROMO_DISMISS_KEY, '' );
		$result = $this->admin->get_bf_notices( 'free' );
		$this->assertArrayHasKey( 'banner', $result, "Banner should appear when dismiss key is empty string" );
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
}
