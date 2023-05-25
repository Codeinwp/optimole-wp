/**
 * WordPress dependencies.
 */
import apiFetch from '@wordpress/api-fetch';

import { createReduxStore, register } from '@wordpress/data';

const DEFAULT_STATE = {
	autoConnectError: '',
	isConnected: optimoleDashboardApp.connection_status === 'yes',
	isConnecting: false,
	isLoading: false,
	apiKeyValidity: true,
	hasApplication: optimoleDashboardApp.has_application === 'yes',
	apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
	userStatus: optimoleDashboardApp.user_status ? optimoleDashboardApp.user_status : 'inactive',
	availableApps: optimoleDashboardApp.available_apps ? optimoleDashboardApp.available_apps : null,
	apiError: false,
	is_loaded: optimoleDashboardApp.connection_status === 'yes',
	showDisconnect: false,
	userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
};

const updateUserData = ( state, data ) => {
	if ( data && data.app_count && data.app_count >= 1 && data.cdn_key ) {
		for ( let app of data.available_apps ) {
			if ( app.key && app.key === data.cdn_key && app.status && app.status === 'active' ) {
				state.userStatus = 'active';
			}
		}
	}
	state.userData = data;
};

const actions = {
	setAutoConnectError( autoConnectError ) {
		return {
			type: 'SET_AUTO_CONNECT_ERROR',
			autoConnectError,
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
	registerAccount( data, callback = () => {} ) {
		return ( { dispatch } ) => {
			dispatch.setIsConnecting( true );
			dispatch.setIsLoading( true );
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
				}

				if ( callback ) {
					callback( response );
				}

				return response.data;
			})
			.catch( error => {
				dispatch.setIsConnecting( false );
				dispatch.setIsLoading( false );

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
				if ( response.ok ) {
					dispatch.setIsLoading( false );
					dispatch.setIsConnected( false );
					console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
				} else {
					console.error( response );
				}
			} );
		}
	}
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
		case 'SET_AUTO_CONNECT_ERROR':
			return {
				...state,
				autoConnectError: action.autoConnectError,
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
		default:
			return state;
	}
};

const selectors = {
	getAutoConnectError( state ) {
		return state.autoConnectError;
	},
	isConnected( state ) {
		return state.isConnected;
	},
	isConnecting( state ) {
		return state.isConnecting;
	},
	isLoading( state ) {
		return state.isLoading;
	}
};

const store = createReduxStore( 'optimole', {
	reducer,
	actions,
	selectors,
} );

register( store );
