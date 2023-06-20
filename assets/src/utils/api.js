/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

import {
	dispatch,
	select
} from '@wordpress/data';

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
	setPushedImagesProgress,
	setLoadingSync,
	setLoadingRollback,
	setTotalNumberOfImages,
	setErrorMedia,
	setOffloadLibraryLink,
	setRollbackLibraryLink,
	setSumTime,
	setEstimatedTime,
	setExtraVisits
} = dispatch( 'optimole' );

const {
	getOptimizedImages,
	getSumTime,
	getTotalNumberOfImages,
	getQueryArgs
} = select( 'optimole' );

let updateStatus = 'pending';
let updatePageStatus = 'pending';

export const sendOnboardingImages = ( data = {}) => {
	data.offset = undefined !== data.offset ? data.offset : 0;

	apiFetch({
		path: optimoleDashboardApp.routes['upload_onboard_images'],
		method: 'POST',
		data
	})
		.then( response => {
			if ( false === response.data && 1000 > data.offset ) {
				sendOnboardingImages({
					offset: data.offset + 100
				});
			}

			if ( 'success' === response.code ) {
				console.log( '%c Images Crawled.', 'color: #59B278' );
			}
		})
		.catch( error => {
			console.log( 'Error while crawling images', error );
			return error.data;
		});
};

export const registerAccount = ( data, callback = () => {}) => {
	setIsConnecting( true );
	setIsLoading( true );
	setHasRestError( false );

	apiFetch({
		path: optimoleDashboardApp.routes['register_service'],
		method: 'POST',
		data,
		parse: false
	})
		.then( response => response.json() )
		.then( response => {
			setIsConnecting( false );
			setIsLoading( false );

			if ( 'success' === response.code ) {
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

export const connectAccount = ( data, callback = () => {}) => {
	setIsConnecting( true );
	setIsLoading( true );
	setHasRestError( false );

	apiFetch({
		path: optimoleDashboardApp.routes.connect,
		method: 'POST',
		data,
		parse: false
	})
		.then( response => response.json() )
		.then( response => {
			setIsConnecting( false );
			setIsLoading( false );

			if ( 'success' === response.code ) {
				setIsConnected( true );
				setHasValidKey( true );
				setAPIKey( response.data.api_key );

				if ( response.data['app_count'] !== undefined && 1 < response.data['app_count']) {
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

	apiFetch({
		path: optimoleDashboardApp.routes.disconnect,
		method: 'GET',
		parse: false
	})
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
		});
};

export const selectDomain = ( data, callback = () => {}) => {
	setIsConnecting( true );
	setHasRestError( false );

	apiFetch({
		path: optimoleDashboardApp.routes['select_application'],
		method: 'POST',
		data
	})
		.then( response => {
			setIsConnecting( false );
			setIsLoading( false );

			if ( 'success' === response.code ) {
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

	apiFetch({
		path: optimoleDashboardApp.routes['request_update'],
		method: 'GET',
		parse: false
	})
		.then( response => {
			if ( 200 <= response.status && 300 > response.status ) {
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

			if ( response?.data?.extra_visits !== undefined ) {
				setExtraVisits( response.data.extra_visits );
			}

			if ( 'disconnected' === response.code ) {
				setIsConnected( false );
				sethasDashboardLoaded( false );
				console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
			}
		});
};

export const retrieveConflicts = () => {
	setIsLoading( true );

	apiFetch({
		path: optimoleDashboardApp.routes['poll_conflicts'],
		method: 'GET',
		parse: false
	})
		.then( response => {
			if ( 200 <= response.status && 300 > response.status ) {
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
		});
};

export const dismissConflict = ( conflictID, callback = () => {}) => {
	setIsLoading( true );

	apiFetch({
		path: optimoleDashboardApp.routes['dismiss_conflict'],
		method: 'POST',
		data: {
			conflictID
		}
	})
		.then( response => {
			setIsLoading( false );

			if ( 'success' === response.code ) {
				setConflicts( response.data );
			}

			if ( callback ) {
				callback( response );
			}
		});
};

export const retrieveOptimizedImages = ( callback = () => {}) => {
	const optimizedImages = getOptimizedImages();

	if ( 0 < optimizedImages.length ) {
		console.log( '%c Images already exist.', 'color: #59B278' );

		if ( callback ) {
			callback();
		}

		return false;
	}

	apiFetch({
		path: optimoleDashboardApp.routes['poll_optimized_images'],
		method: 'GET'
	})
		.then( response => {
			if ( 'success' === response.code ) {
				setOptimizedImages( response.data );
				console.log( '%c Images Fetched.', 'color: #59B278' );
			} else {
				console.log( '%c No images available.', 'color: #E7602A' );
			}

			if ( callback ) {
				callback( response );
			}
		})
		.catch( error => {
			console.log( 'Error while polling images', error );
			return error.data;
		});
};

export const saveSettings = ( settings, refreshStats = false ) => {
	setIsLoading( true );
	apiFetch({
		path: optimoleDashboardApp.routes['update_option'],
		method: 'POST',
		data: {
			settings
		}
	})
		.then( response => {
			setIsLoading( false );

			if ( 'success' === response.code ) {
				setSiteSettings( response.data );
				console.log( '%c Settings Saved.', 'color: #59B278' );
			}
		}).then( () => {
			if ( ! refreshStats ) {
				return;
			}
			requestStatsUpdate();
		})
		.catch( error => {
			setIsLoading( false );

			console.log( 'Error while saving settings', error );
			return error.data;
		});
};

export const clearCache = ( type ) => {
	setIsLoading( true );

	apiFetch({
		path: optimoleDashboardApp.routes['clear_cache_request'],
		method: 'POST',
		data: {
			type
		},
		parse: false
	})
		.then( response => {
			setIsLoading( false );

			if ( 200 <= response.status && 300 > response.status ) {
				console.log( '%c New cache token generated.', 'color: #59B278' );
				return;
			} else {
				console.log( '%c Could not generate cache token.', 'color: #E7602A' );
				return;
			}
		});
};

export const sampleRate = ( data, callback = () => {}) => {
	apiFetch({
		path: addQueryArgs(
			optimoleDashboardApp.routes['get_sample_rate'],
			{
				quality: data.quality,
				force: data?.force || 'no'
			}
		),
		method: 'POST'
	})
		.then( response => {
			if ( 'success' === response.code ) {
				setSampleRate( response.data );
			}

			if ( callback ) {
				callback( response );
			}
		});
};

export const checkOffloadConflicts = ( callback = () => {}) => {
	apiFetch({
		path: optimoleDashboardApp.routes[ 'get_offload_conflicts' ],
		method: 'GET'
	})
		.then( response => {
			setCheckedOffloadConflicts( true );

			if ( 0 !== response.data.length ) {
				setOffloadConflicts( response.data );
			}

			if ( callback ) {
				callback( response );
			}
		});
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

	if ( 'offload_images' === data.action ) {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			setOffloadLibraryLink( false );
		}

		setLoadingSync( true );
	}

	if ( 'rollback_images' === data.action ) {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			setRollbackLibraryLink( false );
		}

		setLoadingRollback( true );
	}

	getNumberOfImages({
		...data,
		consecutiveErrors: 0
	});
};

export const getNumberOfImages = ( data ) => {
	setIsLoading( true );

	apiFetch({
		path: optimoleDashboardApp.routes['number_of_images_and_pages'],
		method: 'POST',
		data: {
			action: data.action
		},
		parse: false
	})
		.then( response => {
			if ( 200 <= response.status && 300 > response.status ) {
				return response.json();
			} else {
				if ( 'offload_images' === data.action ) {
					setLoadingSync( false );
				}

				if ( 'rollback_images' === data.action ) {
					setLoadingRollback( false );
				}
			}
		})
		.then( response => {
			setIsLoading( false );

			if ( ! response.data ) {
				console.log( '%c No images available.', 'color: #E7602A' );
				setLoadingSync( false );
				setLoadingRollback( false );
				return;
			}

			setTotalNumberOfImages( response.data );

			let batch = 1;

			if ( Math.ceil( response.data / 10 ) <= batch ) {
				batch = Math.ceil( response.data / 10 );
			}

			pushBatch({
				batch,
				page: 1,
				action: data.action,
				processedBatch: 0,
				images: data.images,
				unattached: false,
				consecutiveErrors: 0
			});
		})
		.catch( error => {
			if ( undefined === data.consecutiveErrors ) {
				data.consecutiveErrors = 0;
			}

			if ( 10 > data.consecutiveErrors ) {
				setTimeout( () => {
					getNumberOfImages({
						...data,
						consecutiveErrors: data.consecutiveErrors + 1
					});
				}, data.consecutiveErrors * 1000 + 1000 );
			} else {
				setErrorMedia( data.action );
				setLoadingSync( false );
				setLoadingRollback( false );
			}
		});
};

export const pushBatch = ( data ) => {
	let time = new Date();
	let route = 'update_content';

	if ( true === data.unattached ) {
		if ( 'none' !== data.images && 0 === data.images.length ) {
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

	apiFetch({
		path: optimoleDashboardApp.routes[ route ],
		method: 'POST',
		data: {
			batch: data.batch,
			page: data.page,
			job: data.action,
			images: data.images
		}
	})
		.then( response => {
			if ( 'success' === response.code && ( response.data.page > data.page || 0 < response.data.found_images ) ) {
				optimoleDashboardApp.nonce = response.data.nonce;

				if ( false === data.unattached && 0 !== Object.keys( response.data.imagesToUpdate ).length  ) {
					for ( let postID of Object.keys( response.data.imagesToUpdate ) ) {
						let foundImages = response.data.imagesToUpdate[ postID ];

						if ( 'none' !== data.images && 0 !== data.images.length ) {
							foundImages = foundImages.filter( imageID => {
								return data.images.includes( imageID );
							});
						}

						updateContent({
							action: data.action,
							imageIds: foundImages,
							postID,
							batch: data.batch,
							consecutiveErrors: 0
						});

						let interval = setInterval( () => {
							if ( 'done' === updateStatus || ( 'fail' === updateStatus && 'rollback_images' === data.action ) ) {
								updateStatus = 'pending';

								setPushedImagesProgress( data.batch );

								setTime({
									batchTime: new Date() - time,
									batchSize: data.batch,
									processedBatch: data.processedBatch + 1
								});

								pushBatch({
									batch: data.batch,
									page: response.data.page,
									action: data.action,
									processedBatch: data.processedBatch + 1,
									images: data.images,
									unattached: data.unattached,
									consecutiveErrors: 0
								});

								clearInterval( interval );
							}
						}, 10000 );
					}
				} else {
					setPushedImagesProgress( data.batch );

					setTime({
						batchTime: new Date() - time,
						batchSize: data.batch,
						processedBatch: data.processedBatch + 1
					});

					pushBatch({
						batch: data.batch,
						page: response.data.page,
						action: data.action,
						processedBatch: data.processedBatch + 1,
						images: data.images,
						unattached: data.unattached,
						consecutiveErrors: 0
					});

					if ( true === data.unattached && 'none' !== data.images ) {
						data.images.splice( 0, data.batch );
					}
				}
			} else {
				if ( false === data.unattached ) {
					pushBatch({
						batch: data.batch,
						page: response.data.page,
						action: data.action,
						processedBatch: data.processedBatch + 1,
						images: data.images,
						unattached: true,
						consecutiveErrors: 0
					});

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
		})
		.catch( error => {
			console.log( error );

			if ( 10 > data.consecutiveErrors ) {
				setTimeout( () => {
					pushBatch({
						batch: data.batch,
						page: data.page,
						action: data.action,
						processedBatch: data.processedBatch,
						images: data.images,
						unattached: data.unattached,
						consecutiveErrors: data.consecutiveErrors + 1
					});
				}, data.consecutiveErrors * 1000 + 5000 );
			} else {
				setErrorMedia( data.action );
				setLoadingSync( false );
				setLoadingRollback( false );
			}
		});
};

export const updateContent = ( data ) => {
	if ( 0 === data.imageIds.length ) {
		updateStatus = 'done';
	} else {
		apiFetch({
			path: optimoleDashboardApp.routes[ 'upload_rollback_images' ],
			method: 'POST',
			data: {
				// eslint-disable-next-line camelcase
				image_ids: data.imageIds,
				job: data.action
			}
		})
			.then( response => {
				if ( 0 < data.imageIds.length ) {
					updateContent({
						action: data.action,
						imageIds: data.imageIds,
						postID: data.postID,
						batch: data.batch,
						consecutiveErrors: 0
					});

					data.imageIds.splice( 0, data.batch );
				} else {
					updatePage({
						postID: data.postID,
						consecutiveErrors: 0
					});

					let interval = setInterval( () => {
						if ( 'done' === updatePageStatus ) {
							updatePageStatus = 'pending';
							updateStatus = 'done';
							clearInterval( interval );
						}
					}, 10000 );
				}
			}).catch( error => {
				if ( 10 > data.consecutiveErrors ) {
					setTimeout( function() {
						updateContent({
							action: data.action,
							imageIds: data.imageIds,
							postID: data.postID,
							batch: data.batch,
							consecutiveErrors: data.consecutiveErrors + 1
						});
					}, data.consecutiveErrors * 1000 + 5000 );
				} else {
					updatePageStatus = 'fail';
				}
			});
	}
};

const updatePage = ( data ) => {
	apiFetch({
		path: optimoleDashboardApp.routes[ 'update_page' ],
		method: 'POST',
		data: {
			// eslint-disable-next-line camelcase
			post_id: data.postID
		}
	})
		.then( response => {
			if ( 'success' === response.code && true === response.data  ) {
				updatePageStatus = 'done';
			} else {
				throw 'failed_update';
			}
		}).catch( error => {
			if ( 10 > data.consecutiveErrors ) {
				setTimeout( () => {
					updatePage({
						postID: data.postID,
						consecutiveErrors: data.consecutiveErrors + 1
					});
				}, data.consecutiveErrors * 1000 + 5000 );
			} else {
				updateStatus = 'fail';
			}
		});
};
