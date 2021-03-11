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
			url: optimoleDashboardApp.root + '/select',
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
}

const connectOptimole = function ( {commit, state}, data ) {
	commit( 'toggleConnecting', true );
	commit( 'restApiNotWorking', false );
	Vue.http(
		{
			url: optimoleDashboardApp.root + '/connect',
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			body: {
				'api_key': data.apiKey,
			},
			responseType: 'json',
			emulateJSON: true,
		}
	).then(
		function ( response ) {
			commit( 'toggleConnecting', false );
			if ( response.body.code === 'success' ) {
				  commit( 'toggleKeyValidity', true );
				  commit( 'toggleConnectedToOptml', true );
				  commit( 'updateApiKey', data.apiKey );
				  // commit( 'updateUserData', response.body.data );
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

const registerOptimole = function ( {commit, state}, data ) {

	commit( 'restApiNotWorking', false );
	commit( 'toggleLoading', true );
	return Vue.http(
		{
			url: optimoleDashboardApp.root + '/register',
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			body: {
				'email': data.email,
			},
			emulateJSON: true,
			responseType: 'json'
		}
	).then(
		function ( response ) {
			commit( 'toggleLoading', false );
			return response.data;
		},
		function ( response ) {
			commit( 'toggleLoading', false );
			commit( 'restApiNotWorking', true );
			return response.data;
		}
	);
};


const disconnectOptimole = function ( {commit, state}, data ) {
	commit( 'toggleLoading', true, 'loading' );
	Vue.http(
		{
			url: optimoleDashboardApp.root + '/disconnect',
			method: 'GET',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			responseType: 'json'
		}
	).then(
		function ( response ) {
			commit( 'updateUserData', null );
			commit( 'toggleLoading', false );
			commit( 'updateApiKey', '' );
			if ( response.ok ) {
				  commit( 'toggleConnectedToOptml', false );
				  commit( 'toggleIsServiceLoaded', false );
				  console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
			} else {
				  console.error( response );
			}
		}
	);
};

const clearCache = function ( {commit, state}, data ) {
	commit( 'toggleLoading', true );
	return Vue.http(
		{
			url: optimoleDashboardApp.root + '/clear_cache',
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			responseType: 'json'
		}
	).then(
		function ( response ) {
			if ( response.body.code === 'success' ) {
				console.log( '%c New cache token generated.', 'color: #59B278' );
			} else {
				console.log( '%c Could not generate cache token.', 'color: #E7602A' );
			}
			commit( 'toggleLoading', false );

		}
	);
};

const saveSettings = function ( {commit, state}, data ) {
	commit( 'toggleLoading', true );
	return Vue.http(
		{
			url: optimoleDashboardApp.root + '/update_option',
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			body: {
				'settings': data.settings
			},
			responseType: 'json'
		}
	).then(
		function ( response ) {
			if ( response.body.code === 'success' ) {
				  commit( 'updateSettings', response.body.data );
			}
			commit( 'toggleLoading', false );

		}
	);
};
const sampleRate = function ( {commit, state}, data ) {

	data.component.loading_images = true;
	return Vue.http(
		{
			url: optimoleDashboardApp.root + '/images-sample-rate',
			method: 'POST',
			emulateJSON: true,
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			params: {
				'quality': data.quality,
				'force': data.force
			},
			responseType: 'json'
		}
	).then(
		function ( response ) {

			data.component.loading_images = false;
			if ( response.body.code === 'success' ) {
				  commit( 'updateSampleRate', response.body.data );
			}
		}
	);
};

const retrieveOptimizedImages = function ( {commit, state}, data ) {
	let self = this;

	setTimeout(
		function () {
			if ( self.state.optimizedImages.length > 0 ) {
				  console.log( '%c Images already exsist.', 'color: #59B278' );
				  return false;
			}
			Vue.http(
				{
					url: optimoleDashboardApp.root + '/poll_optimized_images',
					method: 'GET',
					emulateJSON: true,
					headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
					responseType: 'json',
					timeout: 10000
				}
			).then(
				function ( response ) {
					if ( response.body.code === 'success' ) {
							  commit( 'updateOptimizedImages', response );
						if ( data.component !== null ) {
							data.component.loading = false;
							data.component.startTime = data.component.maxTime;
							if ( response.body.data.length === 0 ) {
									  data.component.noImages = true;
							}
						}
						console.log( '%c Images Fetched.', 'color: #59B278' );
					} else {
							  component.noImages = true;
							  data.component.loading = false;
							  console.log( '%c No images available.', 'color: #E7602A' );
					}
				}
			);
		},
		data.waitTime
	);
};

const retrieveWatermarks = function ( {commit, state}, data ) {

	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/poll_watermarks',
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if( response.status === 200 ) {
			data.component.watermarkData = [];

			for( let row in response.data.data ) {
				let tmp = response.data.data[row];
				let item = {
					ID: tmp.ID,
					post_title: tmp.post_title,
					post_mime_type: tmp.post_mime_type,
					guid: tmp.post_content || tmp.guid,
				}
				data.component.watermarkData.push( item )
				data.component.noImages = false;
			}
		}
	} );
};

const removeWatermark = function ( {commit, state}, data ) {
	let self = this;
	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/remove_watermark',
		method: 'POST',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		params: { 'postID': data.postID },
		responseType: 'json',
	} ).then( function ( response ) {

		commit( 'toggleLoading', false );
		retrieveWatermarks( {commit, state}, data );
	} );
};


const requestStatsUpdate = function ( {commit, state}, data ) {
	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/request_update',
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if( response.status === 200 ) {
			commit( 'updateUserData', response.body.data );
		}
	} );
};

const retrieveConflicts = function ( {commit, state}, data ) {

	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/poll_conflicts',
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if( response.status === 200 ) {
			console.log( response );
			commit( 'updateConflicts', response );
		}
	} );
};

const dismissConflict = function ( {commit, state}, data ) {
	let self = this;
	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/dismiss_conflict',
		method: 'POST',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		params: { 'conflictID': data.conflictID },
		responseType: 'json',
	} ).then( function ( response ) {

		commit( 'toggleLoading', false );
		if( response.status === 200 ) {
			console.log( response );
			commit( 'updateConflicts', response );
		}
	} );
};
const pushBatch = function ( commit,batch,action, consecutiveErrors = 0 ) {
	Vue.http(
		{
			url: optimoleDashboardApp.root + '/' + action,
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			timeout: 0,
			responseType: 'json',
			body: {
				'batch': batch,
			},
		}
	).then(
		function ( response ) {
			if ( response.body.code === 'success' && response.body.data.found_images > 0 ) {
				commit( 'updatePushedImagesProgress', batch );
				pushBatch( commit, batch, action, 0 );
			} else {
				commit( 'updatePushedImagesProgress', 'finish' );
				action === "offload_images" ? commit( 'toggleLoadingSync', false ) : commit( 'toggleLoadingRollback', false );
			}
		}
	).catch( function ( err ) {
		if ( consecutiveErrors < 10 ) {
			setTimeout( function () { pushBatch( commit, batch, action, consecutiveErrors + 1 ) }, consecutiveErrors*1000 + 5000 );
		} else {
			commit( 'toggleActionError', action );
			commit( 'toggleLoadingSync', false );
			commit( 'toggleLoadingRollback', false );
		}
	} );
};
const getNumberOfImages = function ( data, commit, consecutiveErrors = 0 ) {
	Vue.http( {
		url: optimoleDashboardApp.root + '/number_of_library_images',
		method: 'POST',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		emulateJSON: true,
		responseType: 'json',
		body: {
			'action': data.action,
		},
	} ).then( function ( response ) {
		if( response.status === 200 && response.body.data > 0 ) {
			commit( 'totalNumberOfImages', response.body.data );
			let batch = 5;
			if ( Math.ceil( response.body.data/10 ) <= batch ) {
				batch = Math.ceil( response.body.data/10 );
			}
			pushBatch( commit, batch, data.action );
		} else {
			if ( data.action === "offload_images" ) {
				commit( 'toggleLoadingSync', false );
			}
			if ( data.action === "rollback_images" ) {
				commit( 'toggleLoadingRollback', false );
			}
		}
	} ).catch( function ( err ) {
		if ( consecutiveErrors < 10 ) {
			setTimeout( function () { getNumberOfImages ( data, commit, consecutiveErrors + 1 ) }, consecutiveErrors*1000 + 1000 );
		} else {
			commit( 'toggleActionError', data.action );
			commit( 'toggleLoadingSync', false );
			commit( 'toggleLoadingRollback', false );
		}
	} );
};
const callSync = function ( {commit, state}, data ) {
	commit( 'updatePushedImagesProgress', 'init' );
	if ( data.action === "offload_images" ) {
		commit( 'toggleLoadingSync', true );
	}
	if ( data.action === "rollback_images" ) {
		commit( 'toggleLoadingRollback', true );
	}
	getNumberOfImages( data, commit, 0 );

};

export default {
	clearCache,
	connectOptimole,
	disconnectOptimole,
	selectOptimoleDomain,
	dismissConflict,
	registerOptimole,
	removeWatermark,
	requestStatsUpdate,
	retrieveConflicts,
	retrieveOptimizedImages,
	retrieveWatermarks,
	sampleRate,
	saveSettings,
	callSync
};
