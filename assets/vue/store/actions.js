/* jshint esversion: 6 */
/* global optimoleDashboardApp */
import Vue from 'vue';
import VueResource from 'vue-resource';

Vue.use( VueResource );

const selectOptimoleDomain = function ( {commit, state}, data ) {
	commit( 'toggleConnecting', true );
	commit( 'restApiNotWorking', false );
	Vue.http(
		{
			url: optimoleDashboardApp.routes['select_application'],
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			body: {
				'api_key': data.apiKey,
				'application': data.application,
			},
			responseType: 'json',
			emulateJSON: true,
		}
	).then(
		function ( response ) {
			commit( 'toggleConnecting', false );
			if ( response.body.code === 'success' ) {
				commit( 'toggleKeyValidity', true );
				commit( 'toggleHasOptmlApp', true );
				commit( 'updateApiKey', data.apiKey );
				commit( 'updateUserData', response.body.data );
				commit( 'updateAvailableApps', response.body.data );
				console.log( '%c OptiMole API connection successful.', 'color: #59B278' );

			} else {
				commit( 'toggleKeyValidity', false );
				commit( 'updateServiceError', response.body.data );
				console.log( '%c Invalid API Key.', 'color: #E7602A' );
			}
		},
		function () {
			commit( 'toggleConnecting', false );
			commit( 'restApiNotWorking', true );
		}
	);
};

const getOffloadConflicts = function ( {commit, state} ) {
	Vue.http( {
		url: optimoleDashboardApp.routes['get_offload_conflicts'],
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		emulateJSON: true,
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleCheckedOffloadConflicts', true );
		if( response.body.data.length !== 0 ) {
			commit( 'updateOffloadConflicts', response );
		}
	} ).catch( function ( err ) {

	} );
};


export default {
	selectOptimoleDomain,
	getOffloadConflicts
};
