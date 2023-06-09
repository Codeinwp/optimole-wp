/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

import {
	dispatch,
	select
} from "@wordpress/data";

import { addQueryArgs } from '@wordpress/url';

const {
	setIsConnected,
	setIsConnecting,
	setIsLoading,
	setHasRestError,
	setHasValidKey,
	setHasApplication,
	setAPIKey,
	setUserData,
	setAvailableApps,
	sethasDashboardLoaded,
	setShowDisconnect,
	setConflicts,
	setOptimizedImages,
	setSiteSettings,
	setSampleRate,
	setCheckedOffloadConflicts,
	setOffloadConflicts,
	setUpdatePageStatus,
	setUpdateStatus,
	setPushedImagesProgress,
	setLoadingSync,
	setLoadingRollback,
	setTotalNumberOfImages,
	setErrorMedia,
	setOffloadLibraryLink,
	setRollbackLibraryLink,
	setSumTime,
	setEstimatedTime
} = dispatch( 'optimole' );

const {
	getOptimizedImages,
	getSumTime,
	getTotalNumberOfImages,
	getUpdatePageStatus,
	getUpdateStatus,
	getQueryArgs
} = select( 'optimole' );

export const sendOnboardingImages = ( data = {} ) => {
	data.offset = undefined !== data.offset ? data.offset : 0;

	apiFetch( {
		path: optimoleDashboardApp.routes['upload_onboard_images'],
		method: 'POST',
		data,
	} )
	.then( response => {
		if ( false === response.data && data.offset < 1000 ) {
			sendOnboardingImages( {
				offset: data.offset + 100
			} );
		}

		if ( response.code === 'success' ) {
			console.log( '%c Images Crawled.', 'color: #59B278' );
		}
	} )
	.catch( error => {
		console.log( 'Error while crawling images', error );
		return error.data;
	});
};

export const registerAccount = ( data, callback = () => {} ) => {
	setIsConnecting( true );
	setIsLoading( true );
	setHasRestError( false );

	apiFetch( {
		path: optimoleDashboardApp.routes['register_service'],
		method: 'POST',
		data,
		parse: false,
	} )
	.then( response => response.json() )
	.then( response => {
		setIsConnecting( false );
		setIsLoading( false );

		if ( response.code === 'success' ) {
			setIsConnected( true );
			setHasValidKey( true );
			setHasApplication( true );
			setAPIKey( response.data.api_key );
			setUserData( response.data );
			setAvailableApps( response.data );
			sendOnboardingImages();
		}

		if ( callback ) {
			callback( response );
		}

		return response.data;
	})
	.catch( error => {
		setIsConnecting( false );
		setIsLoading( false );
		setHasRestError( true );

		return error.data;
	});
};

export const connectAccount = ( data, callback = () => {} ) => {
	setIsConnecting( true );
	setIsLoading( true );
	setHasRestError( false );

	apiFetch( {
		path: optimoleDashboardApp.routes['connect'],
		method: 'POST',
		data,
		parse: false,
	} )
	.then( response => response.json() )
	.then( response => {
		setIsConnecting( false );
		setIsLoading( false );

		if ( response.code === 'success' ) {
			setIsConnected( true );
			setHasValidKey( true );
			setAPIKey( response.data.api_key );

			if ( response.data['app_count'] !== undefined && response.data['app_count'] > 1 ) {
				setAvailableApps( response.data );
			} else {
				setHasApplication( true );
				setUserData( response.data );
			}

			sendOnboardingImages();

			console.log( '%c OptiMole API connection successful.', 'color: #59B278' );

		} else {
			setHasValidKey( false );
			console.log( '%c Invalid API Key.', 'color: #E7602A' );
		}

		if ( callback ) {
			callback( response );
		}

		return response.data;
	})
	.catch( error => {
		setIsConnecting( false );
		setIsLoading( false );
		setHasRestError( true );

		return error.data;
	});
};

export const disconnectAccount = () => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['disconnect'],
		method: 'GET',
		parse: false,
	} )
	.then( response => {
		setIsLoading( false );
		setHasApplication( false );
		setAPIKey( '' );
		setUserData( null );
		setAvailableApps( null );

		if ( response.ok ) {
			setIsConnected( false );
			sethasDashboardLoaded( false );
			setShowDisconnect( false );
			console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
		} else {
			console.error( response );
		}
	} );
};

export const selectDomain = ( data, callback = () => {} ) => {
	setIsConnecting( true );
	setHasRestError( false );

	apiFetch( {
		path: optimoleDashboardApp.routes['select_application'],
		method: 'POST',
		data
	} )
	.then( response => {
		setIsConnecting( false );
		setIsLoading( false );

		if ( response.code === 'success' ) {
			setHasValidKey( true );
			setHasApplication( true );
			setAPIKey( response.data.api_key );
			setUserData( response.data );
			setAvailableApps( response.data );
			console.log( '%c OptiMole API connection successful.', 'color: #59B278' );
		} else {
			setHasValidKey( false );
			console.log( '%c Invalid API Key.', 'color: #E7602A' );
		}

		if ( callback ) {
			callback( response );
		}
	})
	.catch( error => {
		setIsConnecting( false );
		setHasRestError( true );

		return error.data;
	});
};

export const requestStatsUpdate = () => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['request_update'],
		method: 'GET',
		parse: false,
	} )
	.then( response => {
	  if ( response.status >= 200 && response.status < 300 ) {
		return response.json();
	  } else {
		console.log( `%c Request failed with status ${ response.status }`, 'color: #E7602A' );
		return Promise.resolve();
	  }
	})
	.then( response => {
		setIsLoading( false );

		if ( ! response ) {
		  return;
		}

		setUserData( response.data );

		if ( response.code === 'disconnected' ) {
			setIsConnected( false );
			sethasDashboardLoaded( false );
			console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
		}
	} );
};

export const retrieveConflicts = () => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['poll_conflicts'],
		method: 'GET',
		parse: false,
	} )
	.then( response => {
		if ( response.status >= 200 && response.status < 300 ) {
			return response.json();
		} else {
			console.log( `%c Request failed with status ${ response.status }`, 'color: #E7602A' );
			return Promise.resolve();
		}
	})
	.then( response => {
		setIsLoading( false );

		if ( ! response ) {
			return;
		}

		setConflicts( response.data );
	} );
};

export const dismissConflict = ( conflictID, callback = () => {} ) => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['dismiss_conflict'],
		method: 'POST',
		data: {
			conflictID,
		},
	} )
	.then( response => {
		setIsLoading( false );

		if ( response.code === 'success' ) {
			setConflicts( response.data );
		}

		if ( callback ) {
			callback( response );
		}
	} );
};

export const retrieveOptimizedImages = ( callback = () => {} ) => {
	const optimizedImages = getOptimizedImages();

	if ( optimizedImages.length > 0 ) {
		console.log( '%c Images already exist.', 'color: #59B278' );

		if ( callback ) {
			callback();
		}

		return false;
	}

	apiFetch( {
		path: optimoleDashboardApp.routes['poll_optimized_images'],
		method: 'GET',
	} )
	.then( response => {
		if ( response.code === 'success' ) {
			setOptimizedImages( response.data );
			console.log( '%c Images Fetched.', 'color: #59B278' );
		} else {
			console.log( '%c No images available.', 'color: #E7602A' );
		}

		if ( callback ) {
			callback( response );
		}
	} )
	.catch( error => {
		console.log( 'Error while polling images', error );
		return error.data;
	});
};

export const saveSettings = ( settings ) => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['update_option'],
		method: 'POST',
		data: {
			settings
		},
	} )
	.then( response => {
		setIsLoading( false );

		if ( response.code === 'success' ) {
			setSiteSettings( response.data );
			console.log( '%c Settings Saved.', 'color: #59B278' );
		}
	} )
	.catch( error => {
		setIsLoading( false );

		console.log( 'Error while saving settings', error );
		return error.data;
	});
};

export const clearCache = ( type ) => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['clear_cache_request'],
		method: 'POST',
		data: {
			type
		},
		parse: false,
	} )
	.then( response => {
		setIsLoading( false );

		if ( response.status >= 200 && response.status < 300 ) {
			console.log( '%c New cache token generated.', 'color: #59B278' );
			return;
		} else {
			console.log( '%c Could not generate cache token.', 'color: #E7602A' );
			return;
		}
	} );
};

export const sampleRate = ( data, callback = () => {} ) => {
	apiFetch( {
		path: addQueryArgs(
			optimoleDashboardApp.routes['get_sample_rate'],
			{
				quality: data.quality,
				force: data?.force || 'no',
			}
			),
		method: 'POST',
	} )
	.then( response => {
		if ( response.code === 'success' ) {
			setSampleRate( response.data );
		}

		if ( callback ) {
			callback( response );
		}
	} );
};

export const checkOffloadConflicts = ( callback = () => {} ) => {
	apiFetch( {
		path: optimoleDashboardApp.routes[ 'get_offload_conflicts' ],
		method: 'GET'
	} )
	.then( response => {
		setCheckedOffloadConflicts( true );

		if ( response.data.length !== 0 ) {
			setOffloadConflicts( response.data );
		}

		if ( callback ) {
			callback( response );
		}
	} );
};

export const setTime = ( data ) => {
	const totalNumberOfImages = getTotalNumberOfImages();
	const sumTime = getSumTime() + data.batchTime;
	const estimatedTime = ( ( sumTime / data.processedBatch ) * ( Math.ceil( totalNumberOfImages / data.batchSize ) - data.processedBatch ) / 60000 ).toFixed( 2 );

	setSumTime( sumTime );
	setEstimatedTime( estimatedTime );
};

export const callSync = ( data ) => {
	setPushedImagesProgress( 'init' );

	const queryArgs = getQueryArgs();

	if ( data.action === 'offload_images' ) {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			setOffloadLibraryLink( false );
		}

		setLoadingSync( true );
	}

	if ( data.action === 'rollback_images' ) {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			setRollbackLibraryLink( false );
		}

		setLoadingRollback( true );
	}

	getNumberOfImages( data );
};

export const getNumberOfImages = ( data ) => {
	setIsLoading( true );

	apiFetch( {
		path: optimoleDashboardApp.routes['number_of_images_and_pages'],
		method: 'POST',
		data: {
			action: data.action,
		},
		parse: false,
	} )
	.then( response => {
		if ( response.status >= 200 && response.status < 300 ) {
			return response.json();
		} else {
			if ( data.action === 'offload_images' ) {
				setLoadingSync( false );
			}

			if ( data.action === 'rollback_images' ) {
				setLoadingRollback( false );
			}
		}
	})
	.then( response => {
		setIsLoading( false );

		if ( ! response ) {
			return;
		}

		setTotalNumberOfImages( response.data );

		let batch = 1;

		if ( Math.ceil( response.data / 10 ) <= batch ) {
			batch = Math.ceil( response.data / 10 );
		}

		pushBatch( {
			batch,
			page: 1,
			action: data.action,
			processedBatch: 0,
			images: data.images,
			unattached: false,
			consecutiveErrors: 0
		} );
	} )
	.catch( error => {
		if ( undefined === data.consecutiveErrors ) {
			data.consecutiveErrors = 0;
		}

		if ( data.consecutiveErrors < 10 ) {
			setTimeout( () => {
				getNumberOfImages( {
					...data,
					consecutiveErrors: data.consecutiveErrors + 1,
				} );
			}, consecutiveErrors * 1000 + 1000 );
		} else {
			setErrorMedia( data.action );
			setLoadingSync( false );
			setLoadingRollback( false );
		}
	} );
};

export const pushBatch = ( data ) => {
	let time = new Date();
	let route = 'update_content';

	if ( data.unattached === true ) {
		if ( 'none' !== data.images && data.images.length === 0 ) {
			setPushedImagesProgress( 'finish' );

			if ( 'offload_images' === data.action ) {
				setLoadingSync( false );
			} else {
				setLoadingRollback( false );
			}

			return;
		}

		route = data.action;
	}

	apiFetch( {
		path: optimoleDashboardApp.routes[ route ],
		method: 'POST',
		data: {
			batch: data.batch,
			page : data.page,
			job : data.action,
			images : data.images
		}
	} )
	.then( response => {
		if ( response.code === 'success' && ( response.data.page > data.page || response.data.found_images > 0 ) ) {				
			optimoleDashboardApp.nonce = response.data.nonce;

			if ( data.unattached === false && Object.keys( response.data.imagesToUpdate ).length !== 0  ) {
				for ( let postID of Object.keys( response.data.imagesToUpdate ) ) {
					let foundImages = response.data.imagesToUpdate[ postID ]

					if ( 'none' !== data.images && data.images.length !== 0 ) {
						foundImages = foundImages.filter( imageID => {
							return data.images.includes( imageID );
						} );
					}

					updateContent( {
						action: data.action,
						imageIds: foundImages,
						postID,
						batch: data.batch,
						consecutiveErrors: 0
					} );

					let updateStatus = getUpdateStatus();

					let interval = setInterval( () => {
						if ( 'done' === updateStatus || ( 'fail' === updateStatus && 'rollback_images' === data.action ) ) {
							updateStatus = 'pending';

							setPushedImagesProgress( data.batch );

							setTime( {
								batchTime: new Date() - time,
								batchSize: data.batch,
								processedBatch: data.processedBatch + 1
							} );

							pushBatch( {
								batch: data.batch,
								page: response.data.page,
								action: data.action,
								processedBatch: data.processedBatch + 1,
								images: data.images,
								unattached: data.unattached,
								consecutiveErrors: 0
							} );

							clearInterval( interval );
						}
					}, 10000 );
				}
			} else {
				setPushedImagesProgress( data.batch );

				setTime( {
					batchTime: new Date() - time,
					batchSize: data.batch,
					processedBatch: data.processedBatch + 1
				} );

				pushBatch( {
					batch: data.batch,
					page: response.data.page,
					action: data.action,
					processedBatch: data.processedBatch + 1,
					images: data.images,
					unattached: data.unattached,
					consecutiveErrors: 0
				} );

				if ( data.unattached === true && 'none' !== data.images ) {
					data.images.splice( 0, data.batch );
				}
			}
		} else {
			if ( data.unattached === false ) {
				pushBatch( {
					batch: data.batch,
					page: response.data.page,
					action: data.action,
					processedBatch: data.processedBatch + 1,
					images: data.images,
					unattached: true,
					consecutiveErrors: 0
				} );

				if ( 'none' !== data.images ) {
					data.images.splice( 0, data.batch );
				}
			} else {
				setPushedImagesProgress( 'finish' );

				if ( 'offload_images' === data.action ) {
					setLoadingSync( false );
				} else {
					setLoadingRollback( false );
				}
			}
		}
	} )
	.catch( error => {
		console.log( error );

		if ( data.consecutiveErrors < 10 ) {
			setTimeout( () => {
				pushBatch( {
					batch: data.batch,
					page : data.page,
					action: data.action,
					processedBatch: data.processedBatch,
					images: data.images,
					unattached: data.unattached,
					consecutiveErrors: data.consecutiveErrors + 1
				} );
			}, data.consecutiveErrors * 1000 + 5000 );
		} else {
			setErrorMedia( data.action );
			setLoadingSync( false );
			setLoadingRollback( false );
		}
	} );
};

export const updateContent = ( data ) => {
	if ( data.imageIds.length === 0 ) {
		setUpdateStatus( 'done' );
	} else {
		apiFetch( {
			path: optimoleDashboardApp.routes[ 'upload_rollback_images' ],
			method: 'POST',
			data: {
				image_ids: data.imageIds,
				job: data.action,
			}
		} )
		.then( response => {
			if ( data.imageIds.length > 0 ) {
				updateContent( {
					action: data.action,
					imageIds: data.imageIds,
					postID: data.postID,
					batch: data.batch,
					consecutiveErrors: 0
				} );

				data.imageIds.splice( 0, data.batch );
			} else {
				updatePage( {
					postID: data.postID,
					consecutiveErrors: 0
				} );

				let interval = setInterval(  () => {
					const updatePageStatus = getUpdatePageStatus();
					if ( 'done' === updatePageStatus ) {
						setUpdatePageStatus( 'pending' );
						setUpdateStatus( 'done' );
						clearInterval( interval );
					}
				}, 10000 );
			}
		} ).catch( error => {
			if ( data.consecutiveErrors < 10 ) {
				setTimeout( function () {
					updateContent( {
						action: data.action,
						imageIds: data.imageIds,
						postID: data.postID,
						batch: data.batch,
						consecutiveErrors: data.consecutiveErrors + 1
					} );
				}, data.consecutiveErrors * 1000 + 5000 );
			} else {
				setUpdatePageStatus( 'fail' );
			}
		} );
	}
};

const updatePage = ( data ) => {
	apiFetch( {
		path: optimoleDashboardApp.routes[ 'update_page' ],
		method: 'POST',
		data: {
			post_id: data.postID,
		}
	} )
	.then( response => {
		if ( 'success' === response.code && response.data === true  ) {
			setUpdatePageStatus( 'done' );
		} else {
			throw 'failed_update';
		}
	} ).catch( error => {
		if ( data.consecutiveErrors < 10 ) {
			setTimeout( () => {
				updatePage( {
					postID: data.postID,
					consecutiveErrors: data.consecutiveErrors + 1
				} );
			}, data.consecutiveErrors * 1000 + 5000 );
		} else {
			setUpdateStatus( 'fail' );
		}
	} );
};
