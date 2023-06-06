/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

import { addQueryArgs } from '@wordpress/url';

import { createReduxStore, register } from '@wordpress/data';

const DEFAULT_STATE = {
	autoConnect: optimoleDashboardApp.auto_connect,
	isConnected: optimoleDashboardApp.connection_status === 'yes',
	isConnecting: false,
	isLoading: false,
	hasRestError: false,
	hasValidKey: true,
	hasApplication: optimoleDashboardApp.has_application === 'yes',
	apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
	userStatus: optimoleDashboardApp.user_status ? optimoleDashboardApp.user_status : 'inactive',
	userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
	availableApps: optimoleDashboardApp.available_apps ? optimoleDashboardApp.available_apps : null,
	hasDashboardLoaded: optimoleDashboardApp.connection_status === 'yes',
	showDisconnect: false,
	conflicts: [],
	optimizedImages: [],
	siteSettings: optimoleDashboardApp.site_settings,
	sampleRate: {},
	pushedImagesProgress : 0,
	totalNumberOfImages : 1,
	offloadLibraryLink : false,
	rollbackLibraryLink: false,
	loadingRollback: false,
	loadingSync: false,
	queryArgs : [],
	errorMedia: false,
	updateStatus: 'pending',
	updatePageStatus: 'pending',
	estimatedTime : 0,
	sumTime : 0,
};

const actions = {
	setAutoConnect( autoConnect ) {
		return {
			type: 'SET_AUTO_CONNECT',
			autoConnect,
		};
	},
	setAPIKey( apiKey ) {
		return {
			type: 'SET_API_KEY',
			apiKey,
		};
	},
	setAvailableApps( availableApps ) {
		return {
			type: 'SET_AVAILABLE_APPS',
			availableApps,
		};
	},
	setIsConnected( isConnected ) {
		return {
			type: 'SET_IS_CONNECTED',
			isConnected,
		};
	},
	setIsConnecting( isConnecting ) {
		return {
			type: 'SET_IS_CONNECTING',
			isConnecting,
		};
	},
	setIsLoading( isLoading ) {
		return {
			type: 'SET_IS_LOADING',
			isLoading,
		};
	},
	sethasDashboardLoaded( hasDashboardLoaded ) {
		return {
			type: 'SET_HAS_DASHBOARD_LOADED',
			hasDashboardLoaded,
		};
	},
	setHasRestError( hasRestError ) {
		return {
			type: 'SET_HAS_REST_ERROR',
			hasRestError,
		};
	},
	setHasValidKey( hasValidKey ) {
		return {
			type: 'SET_HAS_VALID_KEY',
			hasValidKey,
		};
	},
	setHasApplication( hasApplication ) {
		return {
			type: 'SET_HAS_APPLICATION',
			hasApplication,
		};
	},
	setUserData( data ) {
		return {
			type: 'SET_USER_DATA',
			data,
		};
	},
	setUserStatus( status ) {
		return {
			type: 'SET_USER_STATUS',
			status,
		};
	},
	setShowDisconnect( showDisconnect ) {
		return {
			type: 'SET_SHOW_DISCONNECT',
			showDisconnect,
		};
	},
	setConflicts( conflicts ) {
		return {
			type: 'SET_CONFLICTS',
			conflicts,
		};
	},
	setOptimizedImages( optimizedImages ) {
		return {
			type: 'SET_OPTIMIZED_IMAGES',
			optimizedImages,
		};
	},
	setSiteSettings( siteSettings ) {
		return {
			type: 'SET_SITE_SETTINGS',
			siteSettings,
		};
	},
	setSampleRate( sampleRate ) {
		return {
			type: 'SET_SAMPLE_RATE',
			sampleRate,
		};
	},
	setPushedImagesProgress( pushedImagesProgress ) {
		return {
			type: 'SET_PUSHED_IMAGES_PROGRESS',
			pushedImagesProgress,
		};
	},
	setTotalNumberOfImages( totalNumberOfImages ) {
		return {
			type: 'SET_TOTAL_NUMBER_OF_IMAGES',
			totalNumberOfImages,
		};
	},
	setOffloadLibraryLink( offloadLibraryLink ) {
		return {
			type: 'SET_OFFLOAD_LIBRARY_LINK',
			offloadLibraryLink,
		};
	},
	setRollbackLibraryLink( rollbackLibraryLink ) {
		return {
			type: 'SET_ROLLBACK_LIBRARY_LINK',
			rollbackLibraryLink,
		};
	},
	setLoadingRollback( loadingRollback ) {
		return {
			type: 'SET_LOADING_ROLLBACK',
			loadingRollback,
		};
	},
	setLoadingSync( loadingSync ) {
		return {
			type: 'SET_LOADING_SYNC',
			loadingSync,
		};
	},
	setQueryArgs( queryArgs ) {
		return {
			type: 'SET_QUERY_ARGS',
			queryArgs,
		};
	},
	setErrorMedia( errorMedia ) {
		return {
			type: 'SET_ERROR_MEDIA',
			errorMedia,
		};
	},
	setUpdateStatus( updateStatus ) {
		return {
			type: 'SET_UPDATE_STATUS',
			updateStatus,
		};
	},
	setUpdatePageStatus( updatePageStatus ) {
		return {
			type: 'SET_UPDATE_PAGE_STATUS',
			updatePageStatus,
		};
	},
	setEstimatedTime( estimatedTime ) {
		return {
			type: 'SET_ESTIMATED_TIME',
			estimatedTime,
		};
	},
	setSumTime( sumTime ) {
		return {
			type: 'SET_SUM_TIME',
			sumTime,
		};
	},
	setTime( data ) {
		return ( { dispatch, select } ) => {
			const totalNumberOfImages = select.getTotalNumberOfImages();
			const sumTime = select.getSumTime() + data.batchTime;
			const estimatedTime = ( ( sumTime / data.processedBatch ) * ( Math.ceil( totalNumberOfImages / data.batchSize ) - data.processedBatch ) / 60000 ).toFixed( 2 );

			dispatch.setSumTime( sumTime );
			dispatch.setEstimatedTime( estimatedTime );
		};
	},
	registerAccount( data, callback = () => {} ) {
		return ( { dispatch } ) => {
			dispatch.setIsConnecting( true );
			dispatch.setIsLoading( true );
			dispatch.setHasRestError( false );

			apiFetch( {
				path: optimoleDashboardApp.routes['register_service'],
				method: 'POST',
				data,
				parse: false,
			} )
			.then( response => response.json() )
			.then( response => {
				dispatch.setIsConnecting( false );
				dispatch.setIsLoading( false );

				if ( response.code === 'success' ) {
					dispatch.setIsConnected( true );
					dispatch.setHasValidKey( true );
					dispatch.setHasApplication( true );
					dispatch.setAPIKey( response.data.api_key );
					dispatch.setUserData( response.data );
					dispatch.setAvailableApps( response.data );
					dispatch.sendOnboardingImages();
				}

				if ( callback ) {
					callback( response );
				}

				return response.data;
			})
			.catch( error => {
				dispatch.setIsConnecting( false );
				dispatch.setIsLoading( false );
				dispatch.setHasRestError( true );

				return error.data;
			});
		}
	},
	connectAccount( data, callback = () => {} ) {
		return ( { dispatch } ) => {
			dispatch.setIsConnecting( true );
			dispatch.setIsLoading( true );
			dispatch.setHasRestError( false );

			apiFetch( {
				path: optimoleDashboardApp.routes['connect'],
				method: 'POST',
				data,
				parse: false,
			} )
			.then( response => response.json() )
			.then( response => {
				dispatch.setIsConnecting( false );
				dispatch.setIsLoading( false );

				if ( response.code === 'success' ) {
					dispatch.setIsConnected( true );
					dispatch.setHasValidKey( true );
					dispatch.setAPIKey( response.data.api_key );

					if ( response.data['app_count'] !== undefined && response.data['app_count'] > 1 ) {
						dispatch.setAvailableApps( response.data );
					} else {
						dispatch.setHasApplication( true );
						dispatch.setUserData( response.data );
					}

					dispatch.sendOnboardingImages();

					console.log( '%c OptiMole API connection successful.', 'color: #59B278' );

				} else {
					dispatch.setHasValidKey( false );
					console.log( '%c Invalid API Key.', 'color: #E7602A' );
				}

				if ( callback ) {
					callback( response );
				}

				return response.data;
			})
			.catch( error => {
				dispatch.setIsConnecting( false );
				dispatch.setIsLoading( false );
				dispatch.setHasRestError( true );

				return error.data;
			});
		}
	},
	disconnectAccount() {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

			apiFetch( {
				path: optimoleDashboardApp.routes['disconnect'],
				method: 'GET',
				parse: false,
			} )
			.then( response => {
				dispatch.setIsLoading( false );
				dispatch.setHasApplication( false );
				dispatch.setAPIKey( '' );
				dispatch.setUserData( null );
				dispatch.setAvailableApps( null );

				if ( response.ok ) {
					dispatch.setIsConnected( false );
					dispatch.sethasDashboardLoaded( false );
					dispatch.setShowDisconnect( false );
					console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
				} else {
					console.error( response );
				}
			} );
		}
	},
	sendOnboardingImages( data = {} ) {
		data.offset = undefined !== data.offset ? data.offset : 0;

		return ( { dispatch } ) => {
			apiFetch( {
				path: optimoleDashboardApp.routes['upload_onboard_images'],
				method: 'POST',
				data,
			} )
			.then( response => {
				if ( false === response.data && data.offset < 1000 ) {
					dispatch.sendOnboardingImages( {
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
		}
	},
	requestStatsUpdate() {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

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
				dispatch.setIsLoading( false );

				if ( ! response ) {
				  return;
				}

				dispatch.setUserData( response.data );

				if ( response.code === 'disconnected' ) {
					dispatch.setIsConnected( false );
					dispatch.sethasDashboardLoaded( false );
					console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
				}
			} );
		}
	},
	retrieveConflicts() {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

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
				dispatch.setIsLoading( false );

				if ( ! response ) {
					return;
				}

				dispatch.setConflicts( response.data );
			} );
		}
	},
	dismissConflict( conflictID, callback = () => {} ) {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

			apiFetch( {
				path: optimoleDashboardApp.routes['dismiss_conflict'],
				method: 'POST',
				data: {
					conflictID,
				},
			} )
			.then( response => {
				dispatch.setIsLoading( false );

				if ( response.code === 'success' ) {
					dispatch.setConflicts( response.data );
				}

				if ( callback ) {
					callback( response );
				}
			} );
		}
	},
	retrieveOptimizedImages( callback = () => {} ) {
		return ( { dispatch, select } ) => {
			const optimizedImages = select.getOptimizedImages();

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
					dispatch.setOptimizedImages( response.data );
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
		}
	},
	saveSettings( settings ) {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

			apiFetch( {
				path: optimoleDashboardApp.routes['update_option'],
				method: 'POST',
				data: {
					settings
				},
			} )
			.then( response => {
				dispatch.setIsLoading( false );

				if ( response.code === 'success' ) {
					dispatch.setSiteSettings( response.data );
					console.log( '%c Settings Saved.', 'color: #59B278' );
				}
			} )
			.catch( error => {
				dispatch.setIsLoading( false );

				console.log( 'Error while saving settings', error );
				return error.data;
			});
		}
	},
	clearCache( type ) {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

			apiFetch( {
				path: optimoleDashboardApp.routes['clear_cache_request'],
				method: 'POST',
				data: {
					type
				},
				parse: false,
			} )
			.then( response => {
				dispatch.setIsLoading( false );

				if ( response.status >= 200 && response.status < 300 ) {
					console.log( '%c New cache token generated.', 'color: #59B278' );
					return;
				} else {
					console.log( '%c Could not generate cache token.', 'color: #E7602A' );
					return;
				}
			} );
		}
	},
	sampleRate( data, callback = () => {} ) {
		return ( { dispatch } ) => {
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
					dispatch.setSampleRate( response.data );
				}

				if ( callback ) {
					callback( response );
				}
			} );
		}
	},
	callSync( data ) {
		return ( { dispatch, select } ) => {
			dispatch.setPushedImagesProgress( 'init' );

			const queryArgs = select.getQueryArgs();

			if ( data.action === 'offload_images' ) {
				if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
					dispatch.setOffloadLibraryLink( false );
				}

				dispatch.setLoadingSync( true );
			}

			if ( data.action === 'rollback_images' ) {

				if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
					dispatch.setRollbackLibraryLink( false );
				}

				dispatch.setLoadingRollback( true );
			}

			dispatch.getNumberOfImages( data );
		}
	},
	getNumberOfImages( data ) {
		return ( { dispatch } ) => {
			dispatch.setIsLoading( true );

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
						dispatch.setLoadingSync( false );
					}

					if ( data.action === 'rollback_images' ) {
						dispatch.setLoadingRollback( false );
					}
				}
			})
			.then( response => {
				dispatch.setIsLoading( false );

				if ( ! response ) {
					return;
				}
				dispatch.setTotalNumberOfImages( response.data );

				let batch = 1;

				if ( Math.ceil( response.data / 10 ) <= batch ) {
					batch = Math.ceil( response.data / 10 );
				}

				dispatch.pushBatch( {
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
						dispatch.getNumberOfImages( {
							...data,
							consecutiveErrors: data.consecutiveErrors + 1,
						} );
					}, consecutiveErrors * 1000 + 1000 );
				} else {
					dispatch.setErrorMedia( data.action );
					dispatch.setLoadingSync( false );
					dispatch.setLoadingRollback( false );
				}
			} );
		}
	},
	pushBatch( data ) {
		return ( { dispatch, state } ) => {
			let time = new Date();
			let route = 'update_content';

			if ( data.unattached === true ) {
				if ( 'none' !== data.images && data.images.length === 0 ) {
					dispatch.setPushedImagesProgress( 'finish' );

					if ( 'offload_images' === data.action ) {
						dispatch.setLoadingSync( false );
					} else {
						dispatch.setLoadingRollback( false );
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

							dispatch.updateContent( {
								action: data.action,
								foundImages,
								postID,
								batch: data.batch,
								consecutiveErrors: 0
							} );

							let updateStatus = state.getUpdateStatus();

							let interval = setInterval( () => {
								if ( 'done' === updateStatus || ( 'fail' === updateStatus && 'rollback_images' === data.action ) ) {
									updateStatus = 'pending';

									dispatch.setPushedImagesProgress( data.batch );

									dispatch.setTime( {
										batchTime: new Date() - time,
										batchSize: data.batch,
										processedBatch: data.processedBatch + 1
									} );

									dispatch.pushBatch( {
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
						dispatch.setPushedImagesProgress( data.batch );

						dispatch.setTime( {
							batchTime: new Date() - time,
							batchSize: data.batch,
							processedBatch: data.processedBatch + 1
						} );

						dispatch.pushBatch( {
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
						dispatch.pushBatch( {
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
						dispatch.setPushedImagesProgress( 'finish' );

						if ( 'offload_images' === data.action ) {
							dispatch.setLoadingSync( false );
						} else {
							dispatch.setLoadingRollback( false );
						}
					}
				}
			} )
			.catch( error => {
				console.log( error );

				if ( data.consecutiveErrors < 10 ) {
					setTimeout( () => {
						pushBatch( commit, batch, page, action, processedBatch,images, unattached, consecutiveErrors + 1 )

						dispatch.pushBatch( {
							batch: data.batch,
							page: response.page,
							action: data.action,
							processedBatch: data.processedBatch,
							images: data.images,
							unattached: data.unattached,
							consecutiveErrors: data.consecutiveErrors + 1
						} );
					}, data.consecutiveErrors * 1000 + 5000 );
				} else {
					dispatch.setErrorMedia( data.action );
					dispatch.setLoadingSync( false );
					dispatch.setLoadingRollback( false );
				}
			} );
		};
	},
	updateContent( data ) {
		return ( { dispatch, state } ) => {
			if ( data.imageIds.length === 0 ) {
				dispatch.setUpdateStatus( 'done' );
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
						dispatch.updateContent( {
							action: data.action,
							imageIds: data.imageIds,
							postID: data.postID,
							batch: data.batch,
							consecutiveErrors: 0
						} );

						data.imageIds.splice( 0, data.batch );
					} else {
						dispatch.updatePage( {
							postID: data.postID,
							consecutiveErrors: 0
						} );

						let interval = setInterval(  () => {
							const updatePageStatus = state.getUpdatePageStatus();
							if ( 'done' === updatePageStatus ) {
								dispatch.setUpdatePageStatus( 'pending' );
								dispatch.setUpdateStatus( 'done' );
								clearInterval( interval );
							}
						}, 10000 );
					}
				} ).catch( error => {
					if ( data.consecutiveErrors < 10 ) {
						setTimeout( function () {
							dispatch.updateContent( {
								action: data.action,
								imageIds: data.imageIds,
								postID: data.postID,
								batch: data.batch,
								consecutiveErrors: data.consecutiveErrors + 1
							} );
						}, data.consecutiveErrors * 1000 + 5000 );
					} else {
						dispatch.setUpdatePageStatus( 'fail' );
					}
				} );
			}
		};
	},
	updatePage( data ) {
		apiFetch( {
			path: optimoleDashboardApp.routes[ 'update_page' ],
			method: 'POST',
			data: {
				post_id: data.postID,
			}
		} )
		.then( response => {
			if ( 'success' === response.code && response.data === true  ) {
				dispatch.setUpdatePageStatus( 'done' );
			} else {
				throw 'failed_update';
			}
		} ).catch( error => {
			if ( data.consecutiveErrors < 10 ) {
				setTimeout( () => {
					dispatch.updatePage( {
						postID: data.postID,
						consecutiveErrors: data.consecutiveErrors + 1
					} );
				}, data.consecutiveErrors * 1000 + 5000 );
			} else {
				dispatch.setUpdateStatus( 'fail' );
			}
		} );
	}
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case 'SET_AUTO_CONNECT':
			return {
				...state,
				autoConnect: action.autoConnect,
			};
		case 'SET_HAS_REST_ERROR':
			return {
				...state,
				hasRestError: action.hasRestError,
			};
		case 'SET_API_KEY':
			return {
				...state,
				apiKey: action.apiKey,
			};
		case 'SET_AVAILABLE_APPS':
			return {
				...state,
				availableApps: action.availableApps,
			};
		case 'SET_IS_CONNECTED':
			return {
				...state,
				isConnected: action.isConnected,
			};
		case 'SET_IS_CONNECTING':
			return {
				...state,
				isConnecting: action.isConnecting,
			};
		case 'SET_IS_LOADING':
			return {
				...state,
				isLoading: action.isLoading,
			};
		case 'SET_HAS_DASHBOARD_LOADED':
			return {
				...state,
				hasDashboardLoaded: action.hasDashboardLoaded,
			};
		case 'SET_HAS_REST_ERROR':
			return {
				...state,
				hasRestError: action.hasRestError,
			};
		case 'SET_HAS_VALID_KEY':
			return {
				...state,
				hasValidKey: action.hasValidKey,
			};
		case 'SET_HAS_APPLICATION':
			return {
				...state,
				hasApplication: action.hasApplication,
			};
		case 'SET_USER_DATA':
			let status = 'inactive';
			const data = action.data;

			if ( data && data.app_count && data.app_count >= 1 && data.cdn_key ) {
				for ( let app of data.available_apps ) {
					if ( app.key && app.key === data.cdn_key && app.status && app.status === 'active' ) {
						status = 'active';
					}
				}
			}

			return {
				...state,
				userData: action.data,
				userStatus: status,
			};
		case 'SET_USER_STATUS':
			return {
				...state,
				userStatus: action.status,
			};
		case 'SET_SHOW_DISCONNECT':
			return {
				...state,
				showDisconnect: action.showDisconnect,
			};
		case 'SET_CONFLICTS':
			return {
				...state,
				conflicts: action.conflicts,
			};
		case 'SET_OPTIMIZED_IMAGES':
			return {
				...state,
				optimizedImages: action.optimizedImages,
			};
		case 'SET_SITE_SETTINGS':
			let siteSettings = state.siteSettings;

			for ( let setting in action.siteSettings ) {
				siteSettings[setting] = action.siteSettings[setting];
			}

			return {
				...state,
				siteSettings,
			};
		case 'SET_SAMPLE_RATE':
			return {
				...state,
				sampleRate: action.sampleRate,
			};
		case 'SET_PUSHED_IMAGES_PROGRESS':
			let pushedImagesProgress;

			if ( action.pushedImagesProgress === 'finish' ) {
				pushedImagesProgress = 100;
			}

			if ( action.pushedImagesProgress === 'init' ) {
				pushedImagesProgress = 0;
			}

			if ( action.pushedImagesProgress !== 'init' && state.pushedImagesProgress < 100 ) {
				pushedImagesProgress += action.pushedImagesProgress / state.totalNumberOfImages * 100;
			}

			return {
				...state,
				pushedImagesProgress,
			};
		case 'SET_TOTAL_NUMBER_OF_IMAGES':
			return {
				...state,
				totalNumberOfImages: action.totalNumberOfImages,
			};
		case 'SET_OFFLOAD_LIBRARY_LINK':
			return {
				...state,
				offloadLibraryLink: action.offloadLibraryLink,
			};
		case 'SET_ROLLBACK_LIBRARY_LINK':
			return {
				...state,
				rollbackLibraryLink: action.rollbackLibraryLink,
			};
		case 'SET_LOADING_ROLLBACK':
			return {
				...state,
				loadingRollback: action.loadingRollback,
			};
		case 'SET_LOADING_SYNC':
			return {
				...state,
				loadingSync: action.loadingSync,
			};
		case 'SET_QUERY_ARGS':
			return {
				...state,
				queryArgs: action.queryArgs,
			};
		case 'SET_ERROR_MEDIA':
			return {
				...state,
				errorMedia: action.errorMedia,
			};
		case 'SET_UPDATE_STATUS':
			return {
				...state,
				updateStatus: action.updateStatus,
			};
		case 'SET_UPDATE_PAGE_STATUS':
			return {
				...state,
				updatePageStatus: action.updatePageStatus,
			};
		case 'SET_ESTIMATED_TIME':
			return {
				...state,
				estimatedTime: action.estimatedTime,
			};
		case 'SET_SUM_TIME':
			return {
				...state,
				sumTime: action.sumTime,
			};
		default:
			return state;
	}
};

const selectors = {
	getAutoConnect( state ) {
		return state.autoConnect;
	},
	getAPIKey( state ) {
		return state.apiKey;
	},
	getAvailableApps( state ) {
		return state.availableApps;
	},
	hasRestError( state ) {
		return state.hasRestError;
	},
	hasValidKey( state ) {
		return state.hasValidKey;
	},
	hasApplication( state ) {
		return state.hasApplication;
	},
	hasDashboardLoaded( state ) {
		return state.hasDashboardLoaded;
	},
	getUserData( state ) {
		return state.userData;
	},
	getUserStatus( state ) {
		return state.userStatus;
	},
	isConnected( state ) {
		return state.isConnected;
	},
	isConnecting( state ) {
		return state.isConnecting;
	},
	isLoading( state ) {
		return state.isLoading;
	},
	showDisconnect( state ) {
		return state.showDisconnect;
	},
	getConflicts( state ) {
		return state.conflicts;
	},
	getOptimizedImages( state ) {
		return state.optimizedImages;
	},
	getSiteSettings( state, setting = undefined ) {
		if ( setting ) {
			return undefined !== state.siteSettings[setting] ? state.siteSettings[setting] : undefined;
		}

		return state.siteSettings;
	},
	getSampleRate( state ) {
		return state.sampleRate;
	},
	getPushedImagesProgress( state ) {
		return state.pushedImagesProgress;
	},
	getTotalNumberOfImages( state ) {
		return state.totalNumberOfImages;
	},
	getOffloadLibraryLink( state ) {
		return state.offloadLibraryLink;
	},
	getRollbackLibraryLink( state ) {
		return state.rollbackLibraryLink;
	},
	getLoadingRollback( state ) {
		return state.loadingRollback;
	},
	getLoadingSync( state ) {
		return state.loadingSync;
	},
	getQueryArgs( state ) {
		return state.queryArgs;
	},
	getErrorMedia( state ) {
		return state.errorMedia;
	},
	getUpdateStatus( state ) {
		return state.updateStatus;
	},
	getUpdatePageStatus( state ) {
		return state.updatePageStatus;
	},
	getEstimatedTime( state ) {
		return state.estimatedTime;
	},
	getSumTime( state ) {
		return state.sumTime;
	}
};

const store = createReduxStore( 'optimole', {
	reducer,
	actions,
	selectors,
} );

register( store );
