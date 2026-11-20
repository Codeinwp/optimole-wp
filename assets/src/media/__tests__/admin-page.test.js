/**
 * Tests for the Cloud Library admin page script.
 */

jest.mock( '../scss/admin-page.scss', () => ({}) );

const SITE_URL = 'https://example.test';

describe( 'Cloud Library admin page (issue #1211)', () => {
	let iframe;
	let loader;

	beforeAll( () => {
		window.optmlAdminPage = { siteUrl: SITE_URL };

		// The script is printed in the head, before the iframe markup exists.
		require( '../admin-page.js' );

		document.body.innerHTML = '<div class="om-dam-loader"></div><iframe id="om-dam" style="display: none;"></iframe>';
		iframe = document.getElementById( 'om-dam' );
		loader = document.querySelector( '.om-dam-loader' );
	});

	test( 'reveals the iframe when it loads after the script ran', () => {
		iframe.dispatchEvent( new Event( 'load' ) );

		expect( loader.style.display ).toBe( 'none' );
		expect( iframe.style.display ).toBe( '' );
	});

	test( 'answers getUrl from an iframe added after the script ran', () => {
		const postMessage = jest.spyOn( iframe.contentWindow, 'postMessage' ).mockImplementation( () => {});

		window.dispatchEvent( new MessageEvent( 'message', { data: { type: 'om-dam', action: 'getUrl' }}) );

		expect( postMessage ).toHaveBeenCalledWith({ siteUrl: SITE_URL, type: 'om-dam', context: 'browse' }, '*' );
	});
});
