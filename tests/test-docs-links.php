<?php
/**
 * Docs links tests.
 *
 * The docs links must point at the current docs.optimole.com pages: the old
 * /article/ URLs redirect, and the redirect drops the UTM parameters.
 *
 * @package     Optimole-WP
 * @subpackage  Tests
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/**
 * Class Test_Docs_Links.
 */
class Test_Docs_Links extends WP_UnitTestCase {

	/**
	 * The helper adds the UTMs to the docs page URL.
	 *
	 * @covers Optml_Admin::docs_url
	 */
	public function test_docs_url_adds_utms() {
		$this->assertSame(
			'https://docs.optimole.com/wordpress-plugin/best-format?utm_source=wpadmin&utm_medium=settings&utm_campaign=best-format&utm_content=docsoptimolecomwordpress-pluginbest-format',
			Optml_Admin::docs_url( 'wordpress-plugin/best-format', 'best-format', 'settings' )
		);
	}

	/**
	 * A settings description links to its current docs page with UTMs.
	 */
	public function test_settings_description_links_to_current_page() {
		$strings = $this->get_dashboard_strings();

		$this->assertStringContainsString(
			'href="https://docs.optimole.com/wordpress-plugin/best-format?utm_source=wpadmin&#038;utm_medium=settings&#038;utm_campaign=best-format&#038;utm_content=docsoptimolecomwordpress-pluginbest-format"',
			$strings['options_strings']['best_format_desc']
		);
	}

	/**
	 * No dashboard string links to an old /article/ URL, and every docs link has UTMs.
	 */
	public function test_no_string_uses_old_article_urls() {
		$strings = wp_json_encode( $this->get_dashboard_strings(), JSON_UNESCAPED_SLASHES );

		$this->assertStringNotContainsString( 'docs.optimole.com/article/', $strings );
		// Curly quotes make the browser read the target as a window named ”_blank”, so every click reuses one tab.
		$this->assertStringNotContainsString( 'target=\u201d', $strings );
		preg_match_all( '#href=\\\\"(https://docs\.optimole\.com/[^"\\\\]*)#', $strings, $links );
		$this->assertNotEmpty( $links[1] );
		foreach ( $links[1] as $link ) {
			$this->assertStringContainsString( 'utm_source=wpadmin', $link );
		}
	}

	/**
	 * The dashboard app gets the Help tab and Watermark docs links with UTMs.
	 *
	 * @covers Optml_Admin::get_dashboard_docs_links
	 */
	public function test_dashboard_docs_links() {
		$links = Optml_Admin::get_dashboard_docs_links();

		$this->assertSame(
			'https://docs.optimole.com/image-processing/how-to-use-the-custom-integration-in-optimole?utm_source=wpadmin&utm_medium=help&utm_campaign=custom-integration&utm_content=docsoptimolecomimage-processinghow-to-use-the-custom-integration-in-optimole',
			$links['custom_integration']
		);
		$this->assertStringContainsString( 'utm_medium=settings&utm_campaign=watermark', $links['watermark_settings'] );
		foreach ( $links as $link ) {
			$this->assertStringStartsWith( 'https://docs.optimole.com/', $link );
			$this->assertStringNotContainsString( '/article/', $link );
			$this->assertStringContainsString( 'utm_source=wpadmin', $link );
		}
	}

	/**
	 * Get the dashboard strings.
	 *
	 * @return array Dashboard strings.
	 */
	private function get_dashboard_strings() {
		$method = new ReflectionMethod( Optml_Admin::class, 'get_dashboard_strings' );
		$method->setAccessible( true );

		return $method->invoke( new Optml_Admin() );
	}
}
