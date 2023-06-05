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
