/**
 * callSync() polls the progress of an Image Storage transfer. apiFetch rejects on any non-2xx
 * response or network error; every loading flag callSync() set must then be cleared, or the
 * Image Storage controls (and other screens) stay disabled.
 */
const mockActions = {};

jest.mock( '@wordpress/api-fetch', () => jest.fn() );
jest.mock( '@wordpress/data', () => ({
	dispatch: () => new Proxy({}, {
		get: ( target, name ) => {
			if ( ! mockActions[ name ]) {
				mockActions[ name ] = jest.fn();
			}
			return mockActions[ name ];
		}
	}),
	select: () => ({
		getOptimizedImages: () => [],
		getQueryArgs: () => ({}),
		getTotalNumberOfImages: () => 0,
		getSiteSettings: () => ({})
	})
}) );
jest.mock( '@wordpress/url', () => ({ addQueryArgs: url => url }) );

// Provided by WordPress at runtime, not installed.
jest.mock( '@wordpress/notices', () => ({ store: 'core/notices' }), { virtual: true });
jest.mock( '../helpers', () => ({ toggleDashboardSidebarSubmenu: jest.fn() }) );

global.optimoleDashboardApp = {
	nonce: 'nonce',
	routes: { number_of_images_and_pages: '/optml/v1/number_of_images_and_pages' }
};
global.jQuery = { ajax: jest.fn() };

const apiFetch = require( '@wordpress/api-fetch' );
const { callSync } = require( '../api' );

const flushPromises = () => new Promise( resolve => setTimeout( resolve, 0 ) );

describe( 'callSync', () => {
	beforeEach( () => {
		Object.values( mockActions ).forEach( action => action.mockClear() );
	});

	test.each([ 'offload_images', 'rollback_images' ])( 'clears the loading state when the %s status request fails', async action => {
		apiFetch.mockReturnValueOnce( Promise.reject( new Error( 'Internal Server Error' ) ) );

		callSync({ action });
		await flushPromises();

		expect( mockActions.setErrorMedia ).toHaveBeenCalledWith( action );
		expect( mockActions.setIsLoading.mock.calls ).toEqual([[ true ], [ false ]]);
		expect( mockActions.setLoadingSync ).toHaveBeenLastCalledWith( false );
		expect( mockActions.setLoadingRollback ).toHaveBeenLastCalledWith( false );
	});
});
