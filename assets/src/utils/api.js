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
	setLoadingSync,
	setLoadingRollback,
	setTotalNumberOfImages,
	setProcessedImages,
	setErrorMedia,
	setOffloadLibraryLink,
	setRollbackLibraryLink,
	setExtraVisits
} = dispatch( 'optimole' );

const {
	getOptimizedImages,
	getQueryArgs,
	getTotalNumberOfImages
} = select( 'optimole' );

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

export const callSync = ( data ) => {
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

	setIsLoading( true );

	apiFetch({
		path: optimoleDashboardApp.routes['number_of_images_and_pages'],
		method: 'POST',
		data: {
			action: data.action,
			refresh: data.refresh || false
		},
		parse: false
	})
		.then( response => {
			if ( 200 <= response.status && 300 > response.status ) {
				return response.json();
			} else {
				if ( 'offload_images' === data.action ) {
					setLoadingSync( false );
					setIsLoading( false );
				}

				if ( 'rollback_images' === data.action ) {
					setLoadingRollback( false );
					setIsLoading( false );
				}
			}
		})
		.then( response => {
			if ( ! response.data.count || 0 === response.data.count || false === response.data.status ) {
				console.log( '%c No images available.', 'color: #E7602A' );
				setLoadingSync( false );
				setLoadingRollback( false );
				setIsLoading( false );
				return;
			}

			if ( data.refresh ) {
				let images = getTotalNumberOfImages();
				setProcessedImages( 0 !== images ? Math.abs( images - response.data.count ) : 0 );

				if ( 0 !== response.data.count && 0 === images ) {
					setTotalNumberOfImages( response.data.count );
				}
			} else {
				setTotalNumberOfImages( response.data.count );
			}

			setTimeout( () => {
				callSync({
					action: data.action,
					refresh: true
				});
			}, 10000 );
		})
		.catch( error => {
			setErrorMedia( data.action );
			setLoadingSync( false );
			setLoadingRollback( false );
		});
};
