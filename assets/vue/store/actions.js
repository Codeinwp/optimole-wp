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
}

const connectOptimole = function ( {commit, state}, data ) {
	commit( 'toggleConnecting', true );
	commit( 'restApiNotWorking', false );
	Vue.http(
		{
			url: optimoleDashboardApp.routes['connect'],
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
				  if ( response.body.data['app_count'] !== undefined && response.body.data['app_count'] > 1 ) {
					commit( 'updateAvailableApps', response.body.data );
				  } else {
					commit( 'updateUserData', response.body.data );
					commit( 'toggleHasOptmlApp', true );
				  }

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
	commit( 'toggleConnecting', true );
	commit( 'toggleLoading', true );
	return Vue.http(
		{
			url: optimoleDashboardApp.routes['register_service'],
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			body: {
				'email': data.email,
				'auto_connect' : data.autoConnect,
			},
			emulateJSON: true,
			responseType: 'json'
		}
	).then(
		function ( response ) {
			commit( 'toggleConnecting', false );
			commit( 'toggleLoading', false );
			if ( response.body.code === 'success' ) {
				commit( 'toggleConnectedToOptml', true );
				commit( 'toggleKeyValidity', true );
				commit( 'toggleHasOptmlApp', true );
				commit( 'updateApiKey', data.apiKey );
				commit( 'updateUserData', response.body.data );
				commit( 'updateAvailableApps', response.body.data );
			}
			return response.data;
		},
		function ( response ) {
			commit( 'toggleConnecting', false );
			commit( 'restApiNotWorking', true );
			return response.data;
		}
	);
};


const disconnectOptimole = function ( {commit, state}, data ) {
	commit( 'toggleLoading', true, 'loading' );
	Vue.http(
		{
			url: optimoleDashboardApp.routes['disconnect'],
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
			commit( 'updateAvailableApps', null );
			commit( 'toggleHasOptmlApp', false );
			if ( response.ok ) {
				  commit( 'toggleConnectedToOptml', false );
				  commit( 'toggleIsServiceLoaded', false );
				  commit( 'toggleShowDisconnectNotice', false );
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
			url: optimoleDashboardApp.routes['clear_cache_request'],
			method: 'POST',
			body: {
				'type': data.type,
			},
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			responseType: 'json'
		}
	).then(
		function ( response ) {
			if ( response.body.code === '200' ) {
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
			url: optimoleDashboardApp.routes['update_option'],
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
			url: optimoleDashboardApp.routes['get_sample_rate'],
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
					url: optimoleDashboardApp.routes['poll_optimized_images'],
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
						if ( data.component !== null ) {
							data.component.noImages = true;
							data.component.loading = false;
							console.log( '%c No images available.', 'color: #E7602A' );
						}
					}
				}
			)
				.catch( err => {
					if ( data.component !== null ) {
						data.component.noImages = true;
						data.component.loading = false;
						console.log( 'Error while polling images', err );
					}
				} );

		},
		data.waitTime
	);
};

const retrieveWatermarks = function ( {commit, state}, data ) {

	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.routes['poll_watermarks'],
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
		url: optimoleDashboardApp.routes['remove_watermark'],
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
		url: optimoleDashboardApp.routes['request_update'],
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if( response.status === 200 ) {
			commit( 'updateUserData', response.body.data );
		}
		if ( response.body.code === 'disconnected' ) {
			commit( 'toggleConnectedToOptml', false );
			commit( 'toggleIsServiceLoaded', false );
			console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
		}
	} );
};

const retrieveConflicts = function ( {commit, state}, data ) {

	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.routes['poll_conflicts'],
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
		url: optimoleDashboardApp.routes['dismiss_conflict'],
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

let updateStatus = 'pending';
let updatePageStatus = 'pending';
const updateContent =  function ( commit,action, imageIds, postID, batch, consecutiveErrors = 0 ) {
	if ( imageIds.length === 0 ) {
		updateStatus = 'done';
	} else {
		Vue.http(
			{
				url: optimoleDashboardApp.routes['upload_rollback_images'],
				method: 'POST',
				headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
				emulateJSON: true,
				timeout: 0,
				responseType: 'json',
				body: {
					'image_ids': imageIds,
					'job': action,
				},
			}
		).then(
			function ( response ) {
				if ( imageIds.length > 0 ) {
					updateContent( commit, action, imageIds, postID, batch );
					imageIds.splice( 0, batch );
				} else {
					updatePage( postID );
					let interval = setInterval( function () {
						if ( updatePageStatus === 'done' ) {
							updatePageStatus = 'pending';
							updateStatus = 'done';
							clearInterval( interval );
						}
					}, 10000 );
				}
			}
		).catch( function ( err ) {
			if ( consecutiveErrors < 10 ) {
				setTimeout( function () {
					updateContent( commit, action, imageIds, postID, batch, consecutiveErrors + 1 )
				}, consecutiveErrors * 1000 + 5000 );
			} else {
				updatePageStatus = 'fail';
			}
		} );
	}
};

const updatePage =  function ( postID, consecutiveErrors = 0 ) {
	Vue.http(
		{
			url: optimoleDashboardApp.routes['update_page'],
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			timeout: 0,
			responseType: 'json',
			body: {
				'post_id': postID,
			},
		}
	).then(
		function ( response ) {
			if ( response.body.code === 'success' && response.body.data === true  ) {
				updatePageStatus = 'done';
			} else {
				throw "failed_update";
			}
		}
	).catch( function ( err ) {
		if ( consecutiveErrors < 10 ) {
			setTimeout( function () {
				updatePage( postID, consecutiveErrors + 1 );
			}, consecutiveErrors * 1000 + 5000 );
		} else {
			updateStatus = 'fail';
		}
	} );
};
const pushBatch = function ( commit,batch, page, action, processedBatch, images, unattached = false, consecutiveErrors = 0 ) {
	let time = new Date();
	let route = 'update_content';
	if ( unattached === true ) {
		if ( images !== "none" && images.length === 0 ) {
			commit( 'updatePushedImagesProgress', 'finish' );
			action === "offload_images" ? commit( 'toggleLoadingSync', false ) : commit( 'toggleLoadingRollback', false );
			return;
		}
		route = action;
	}
	Vue.http(
		{
			url: optimoleDashboardApp.routes[route],
			method: 'POST',
			headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
			emulateJSON: true,
			timeout: 0,
			responseType: 'json',
			body: {
				'batch': batch,
				'page' : page,
				'job' : action,
				'images' : images,
			},
		}
	).then(
		function ( response ) {
			if ( response.body.code === 'success' && ( response.body.data.page > page || response.body.data.found_images > 0 ) ) {
				optimoleDashboardApp.nonce = response.body.data.nonce;
				if ( unattached === false && Object.keys( response.body.data.imagesToUpdate ).length !== 0  ) {
					for ( let postID of Object.keys( response.body.data.imagesToUpdate ) ) {
						let foundImages = response.body.data.imagesToUpdate[postID];
						if ( images !== "none" && images.length !== 0 ) {
							foundImages = foundImages.filter( imageID => {
								return images.includes( imageID );
							} );
						}
						updateContent( commit, action, foundImages, postID, batch, 0 );
						let interval = setInterval( function () {
							if ( updateStatus === 'done' || ( updateStatus === 'fail' && action === "rollback_images" ) ) {
								updateStatus = 'pending';
								commit( 'updatePushedImagesProgress', batch );
								commit( 'estimatedTime', {
									batchTime: new Date() - time,
									batchSize: batch,
									processedBatch: processedBatch + 1
								} );
								pushBatch( commit, batch, response.body.data.page, action, processedBatch + 1, images, unattached, 0 );
								clearInterval( interval );
							}
						}, 10000 );
					}
				} else {
					commit( 'updatePushedImagesProgress', batch );
					commit( 'estimatedTime', {
						batchTime: new Date() - time,
						batchSize: batch,
						processedBatch: processedBatch + 1
					} );
					pushBatch( commit, batch, response.body.data.page, action, processedBatch + 1, images, unattached, 0 );
					if ( unattached === true && images !== "none" ) {
						images.splice( 0, batch );
					}
				}
			} else {
				if ( unattached === false ) {
					pushBatch( commit, batch, response.body.data.page,  action, processedBatch + 1, images,true,0 );
					if ( images !== "none" ) {
						images.splice( 0, batch );
					}
				} else {
					commit( 'updatePushedImagesProgress', 'finish' );
					action === "offload_images" ? commit( 'toggleLoadingSync', false ) : commit( 'toggleLoadingRollback', false );
				}
			}
		}
	).catch( function ( err ) {
		console.log( err );
		if ( consecutiveErrors < 10 ) {
			setTimeout( function () { pushBatch( commit, batch, page, action, processedBatch,images, unattached, consecutiveErrors + 1 ) }, consecutiveErrors*1000 + 5000 );
		} else {
			commit( 'toggleActionError', action );
			commit( 'toggleLoadingSync', false );
			commit( 'toggleLoadingRollback', false );
		}
	} );
};
const getNumberOfImages = function ( data, commit, consecutiveErrors = 0 ) {
	Vue.http( {
		url: optimoleDashboardApp.routes['number_of_images_and_pages'],
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
			let batch = 1;
			if ( Math.ceil( response.body.data/10 ) <= batch ) {
				batch = Math.ceil( response.body.data/10 );
			}
			pushBatch( commit, batch, 1, data.action, 0, data.images );
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
const getOffloadConflicts = function ({commit, state} ) {
	Vue.http( {
		url: optimoleDashboardApp.routes['get_offload_conflicts'],
		method: 'GET',
		headers: {'X-WP-Nonce': optimoleDashboardApp.nonce},
		emulateJSON: true,
		responseType: 'json',
	} ).then( function ( response ) {
		commit( 'toggleCheckedOffloadConflicts', true);
		if( response.body.data.length !== 0 ) {
			commit( 'updateOffloadConflicts', response );
		}
	} ).catch( function ( err ) {

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
	callSync,
	getOffloadConflicts
};
