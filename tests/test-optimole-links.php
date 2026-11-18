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
 * Class Test_Optimole_Links.
 *
 * optimole.com has no localized pages, so its links must never get a language code in the path,
 * whatever the locale of the current user. Links to dashboard.optimole.com keep their `?lang=` argument.
 */
class Test_Optimole_Links extends WP_UnitTestCase {

	/**
	 * Email of the connected Optimole account.
	 */
	const ACCOUNT_EMAIL = 'jane@example.com';

	/**
	 * Matches an optimole.com link whose path starts with a language code the SDK knows about.
	 */
	const LOCALIZED_LINK_PATTERN = '~//optimole\.com/(?:de|es|fr|it|ja|nl|ro)(?![a-z0-9-])~i';

	/**
	 * Service data before the test.
	 *
	 * @var mixed
	 */
	private $initial_service_data;

	/**
	 * Frontend badge setting before the test.
	 *
	 * @var mixed
	 */
	private $initial_banner_frontend;

	/**
	 * Connect a free account that is close to its visits limit.
	 */
	public function setUp(): void {
		parent::setUp();

		$settings                      = new Optml_Settings();
		$this->initial_service_data    = $settings->get( 'service_data' );
		$this->initial_banner_frontend = $settings->get( 'banner_frontend' );

		$settings->update(
			'service_data',
			[
				'cdn_key'        => 'example',
				'cdn_secret'     => 'test',
				'whitelist'      => [ 'example.org' ],
				'user_email'     => self::ACCOUNT_EMAIL,
				'plan'           => 'free',
				'visitors_limit' => 2000,
				'visitors_left'  => 100,
			]
		);
	}

	/**
	 * Restore the settings, which outlive the database rollback because they are cached statically.
	 */
	public function tearDown(): void {
		$settings = new Optml_Settings();
		$settings->update( 'service_data', $this->initial_service_data );
		$settings->update_frontend_banner_from_remote( 'enabled' === $this->initial_banner_frontend );

		remove_all_filters( 'themeisle_sdk_is_black_friday_sale' );
		remove_all_filters( 'themeisle_sdk_current_date' );
		set_current_screen( 'front' );

		parent::tearDown();
	}

	/**
	 * Locales for which the SDK adds a language code to a link, with that code.
	 *
	 * @return array<string, array{0: string, 1: string}>
	 */
	public function localized_locales(): array {
		return [
			'de_DE'        => [ 'de_DE', 'de' ],
			'de_DE_formal' => [ 'de_DE_formal', 'de' ],
			'es_ES'        => [ 'es_ES', 'es' ],
			'fr_FR'        => [ 'fr_FR', 'fr' ],
			'it_IT'        => [ 'it_IT', 'it' ],
			'ja'           => [ 'ja', 'ja' ],
			'nl_NL'        => [ 'nl_NL', 'nl' ],
			'nl_NL_formal' => [ 'nl_NL_formal', 'nl' ],
			'ro_RO'        => [ 'ro_RO', 'ro' ],
		];
	}

	/**
	 * Log in an administrator who uses the given locale, and check the SDK really localizes links for them.
	 *
	 * @param string $locale User locale.
	 * @param string $code   Language code the SDK uses for that locale.
	 */
	private function login_admin_with_locale( string $locale = 'de_DE', string $code = 'de' ): void {
		wp_set_current_user(
			self::factory()->user->create(
				[
					'role'   => 'administrator',
					'locale' => $locale,
				]
			)
		);

		$this->assertSame( $locale, get_user_locale() );
		$this->assertSame( "https://example.org/$code/page/", tsdk_translate_link( 'https://example.org/page/' ) );
	}

	/**
	 * Assert a link is the upgrade page without a language code, carrying the encoded account email.
	 *
	 * @param string $link    Link to check.
	 * @param string $message Failure message.
	 */
	private function assert_upgrade_link( string $link, string $message = '' ): void {
		$this->assertStringStartsWith( 'https://optimole.com/upgrade/?email=', $link, $message );

		parse_str( (string) wp_parse_url( $link, PHP_URL_QUERY ), $query );
		$this->assertSame( self::ACCOUNT_EMAIL, base64_decode( $query['email'] ), $message );
	}

	/**
	 * Get the optimole.com links of some markup.
	 *
	 * @param string $html Markup.
	 *
	 * @return string[]
	 */
	private function get_optimole_links( string $html ): array {
		preg_match_all( '~href="(https://optimole\.com/[^"]*)"~', $html, $matches );

		return array_map( 'html_entity_decode', $matches[1] );
	}

	/**
	 * The upgrade link is the same for every locale: no language code, and the account email is kept.
	 *
	 * @dataProvider localized_locales
	 *
	 * @param string $locale User locale.
	 * @param string $code   Language code the SDK uses for that locale.
	 */
	public function test_upgrade_link_has_no_language_prefix( string $locale, string $code ): void {
		$this->login_admin_with_locale( $locale, $code );

		$this->assert_upgrade_link( Optml_Admin::get_upgrade_base_link() );
	}

	/**
	 * Without an account email the upgrade link is the bare upgrade page.
	 */
	public function test_upgrade_link_without_account_email_has_no_language_prefix(): void {
		$this->login_admin_with_locale();
		( new Optml_Settings() )->update( 'service_data', [] );

		$this->assertSame( 'https://optimole.com/upgrade/', Optml_Admin::get_upgrade_base_link() );
	}

	/**
	 * The contact link has no language code.
	 */
	public function test_contact_link_has_no_language_prefix(): void {
		$this->login_admin_with_locale();

		$this->assertSame( 'https://optimole.com/contact/', Optml_Admin::get_contact_base_link() );
	}

	/**
	 * The Media Library upgrade notice links to the upgrade page without a language code.
	 */
	public function test_upgrade_notice_link_has_no_language_prefix(): void {
		$this->login_admin_with_locale();
		set_current_screen( 'upload' );

		ob_start();
		( new Optml_Admin() )->add_notice_upgrade();
		$output = (string) ob_get_clean();

		$links = $this->get_optimole_links( $output );

		$this->assertCount( 1, $links );
		$this->assert_upgrade_link( $links[0] );
	}

	/**
	 * Both Black Friday notices link to the upgrade page without a language code, and keep the account email.
	 */
	public function test_black_friday_links_have_no_language_prefix(): void {
		$this->login_admin_with_locale();
		add_filter( 'themeisle_sdk_is_black_friday_sale', '__return_true' );
		add_filter(
			'themeisle_sdk_current_date',
			static function () {
				return new DateTime( 'last Friday of November ' . gmdate( 'Y' ) );
			}
		);

		$notices = ( new Optml_Admin() )->get_bf_notices( 'free' );

		foreach ( [ 'sidebar', 'banner' ] as $notice ) {
			$this->assertArrayHasKey( $notice, $notices );
			$this->assert_upgrade_link( $notices[ $notice ]['cta_link'], "Wrong upgrade link in the $notice notice." );
		}
	}

	/**
	 * Nothing the dashboard app receives links to a localized optimole.com page.
	 */
	public function test_dashboard_data_has_no_localized_optimole_links(): void {
		$this->login_admin_with_locale();

		$localize = new ReflectionMethod( Optml_Admin::class, 'localize_dashboard_app' );
		$localize->setAccessible( true );
		$data = $localize->invoke( new Optml_Admin() );

		$this->assertSame( 'https://optimole.com/', $data['optimoleHome'] );
		$this->assert_upgrade_link( $data['upgrade_url'] );
		$this->assertStringStartsWith( 'https://optimole.com/pricing/?', $data['offload_upgrade_url'] );
		$this->assertStringStartsWith( 'https://optimole.com/contact/?', $data['report_issue_url'] );
		$this->assertSame(
			[ 'https://optimole.com/terms/', 'https://optimole.com/privacy-policy/' ],
			$this->get_optimole_links( $data['strings']['signup_terms'] )
		);

		$disabled_account_links = $this->get_optimole_links( $data['strings']['notice_disabled_account'] );
		$this->assertCount( 1, $disabled_account_links );
		$this->assert_upgrade_link( $disabled_account_links[0] );

		array_walk_recursive(
			$data,
			function ( $value, $key ) {
				if ( is_string( $value ) ) {
					$this->assertDoesNotMatchRegularExpression( self::LOCALIZED_LINK_PATTERN, $value, "Localized optimole.com link in '$key'." );
				}
			}
		);

		// Links to dashboard.optimole.com carry the language as a query argument and are left as they are.
		$this->assertSame( 'https://dashboard.optimole.com/?lang=de', $data['optimoleDashHome'] );
	}

	/**
	 * The frontend badge links to optimole.com without a language code.
	 */
	public function test_frontend_badge_link_has_no_language_prefix(): void {
		if ( defined( 'REST_REQUEST' ) ) {
			$this->markTestSkipped( 'The badge is not rendered during a REST request.' );
		}

		$this->login_admin_with_locale();
		( new Optml_Settings() )->update_frontend_banner_from_remote( true );

		ob_start();
		Optml_Manager::instance()->banner();
		$output = (string) ob_get_clean();

		$this->assertSame( [ 'https://optimole.com/wordpress/?from=badgeOn' ], $this->get_optimole_links( $output ) );
	}
}
