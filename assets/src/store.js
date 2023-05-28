/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

import { createReduxStore, register } from '@wordpress/data';

const DEFAULT_STATE = {
	autoConnect: optimoleDashboardApp.auto_connect === 'yes',
	autoConnectError: '',
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
};

const actions = {
	setAutoConnect( autoConnect ) {
		return {
			type: 'SET_AUTO_CONNECT',
			autoConnect,
		};
	},
	setAutoConnectError( autoConnectError ) {
		return {
			type: 'SET_AUTO_CONNECT_ERROR',
			autoConnectError,
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
				}

				dispatch.sendOnboardingImages();

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
				if ( ! response ) {
				  return;
				}

				dispatch.setIsLoading( false );
				dispatch.setUserData( response.data );

				if ( response.code === 'disconnected' ) {
					dispatch.setIsConnected( false );
					dispatch.sethasDashboardLoaded( false );
					console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
				}
			} );
		}
	}
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case 'SET_HAS_REST_ERROR':
			return {
				...state,
				hasRestError: action.hasRestError,
			};
		case 'SET_AUTO_CONNECT_ERROR':
			return {
				...state,
				autoConnectError: action.autoConnectError,
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
		default:
			return state;
	}
};

const selectors = {
	getAutoConnect( state ) {
		return state.autoConnect;
	},
	getAutoConnectError( state ) {
		return state.autoConnectError;
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
	}
};

const store = createReduxStore( 'optimole', {
	reducer,
	actions,
	selectors,
} );

register( store );
