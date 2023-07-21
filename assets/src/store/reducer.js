const DEFAULT_STATE = {
	autoConnect: optimoleDashboardApp.auto_connect,
	isConnected: 'yes' === optimoleDashboardApp.connection_status,
	isConnecting: false,
	isLoading: false,
	hasRestError: false,
	hasValidKey: true,
	hasApplication: 'yes' === optimoleDashboardApp.has_application,
	apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
	userStatus: optimoleDashboardApp.user_status ? optimoleDashboardApp.user_status : 'inactive',
	userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
	availableApps: optimoleDashboardApp.available_apps ? optimoleDashboardApp.available_apps : null,
	hasDashboardLoaded: 'yes' === optimoleDashboardApp.connection_status,
	showDisconnect: false,
	conflicts: [],
	optimizedImages: [],
	siteSettings: optimoleDashboardApp.site_settings,
	sampleRate: {},
	totalNumberOfImages: 0,
	remainingImages: 0,
	offloadLibraryLink: false,
	rollbackLibraryLink: false,
	loadingRollback: false,
	loadingSync: false,
	queryArgs: [],
	errorMedia: false,
	checkedOffloadConflicts: false,
	offloadConflicts: []
};

const reducer = ( state = DEFAULT_STATE, action ) => {
	switch ( action.type ) {
	case 'SET_AUTO_CONNECT':
		return {
			...state,
			autoConnect: action.autoConnect
		};
	case 'SET_HAS_REST_ERROR':
		return {
			...state,
			hasRestError: action.hasRestError
		};
	case 'SET_API_KEY':
		return {
			...state,
			apiKey: action.apiKey
		};
	case 'SET_AVAILABLE_APPS':
		return {
			...state,
			availableApps: action.availableApps
		};
	case 'SET_IS_CONNECTED':
		return {
			...state,
			isConnected: action.isConnected
		};
	case 'SET_IS_CONNECTING':
		return {
			...state,
			isConnecting: action.isConnecting
		};
	case 'SET_IS_LOADING':
		return {
			...state,
			isLoading: action.isLoading
		};
	case 'SET_HAS_DASHBOARD_LOADED':
		return {
			...state,
			hasDashboardLoaded: action.hasDashboardLoaded
		};
	case 'SET_HAS_REST_ERROR':
		return {
			...state,
			hasRestError: action.hasRestError
		};
	case 'SET_HAS_VALID_KEY':
		return {
			...state,
			hasValidKey: action.hasValidKey
		};
	case 'SET_HAS_APPLICATION':
		return {
			...state,
			hasApplication: action.hasApplication
		};
	case 'SET_USER_DATA':
		let status = 'inactive';
		const { data } = action;

		if ( data && data.app_count && 1 <= data.app_count && data.cdn_key ) {
			for ( let app of data.available_apps ) {
				if ( app.key && app.key === data.cdn_key && app.status && 'active' === app.status ) {
					status = 'active';
				}
			}
		}

		return {
			...state,
			userData: action.data,
			userStatus: status
		};
	case 'SET_USER_STATUS':
		return {
			...state,
			userStatus: action.status
		};
	case 'SET_SHOW_DISCONNECT':
		return {
			...state,
			showDisconnect: action.showDisconnect
		};
	case 'SET_CONFLICTS':
		return {
			...state,
			conflicts: action.conflicts
		};
	case 'SET_OPTIMIZED_IMAGES':
		return {
			...state,
			optimizedImages: action.optimizedImages
		};
	case 'SET_SITE_SETTINGS':
		let { siteSettings } = state;

		for ( let setting in action.siteSettings ) {
			siteSettings[setting] = action.siteSettings[setting];
		}

		return {
			...state,
			siteSettings
		};
	case 'SET_EXTRA_VISITS': {
		let { siteSettings } = state;

		siteSettings['banner_frontend'] = action.extraVisitsStatus ? 'enabled' : 'disabled';

		return {
			...state,
			siteSettings
		};
	}
	case 'SET_SAMPLE_RATE':
		return {
			...state,
			sampleRate: action.sampleRate
		};
	case 'SET_TOTAL_NUMBER_OF_IMAGES':
		return {
			...state,
			totalNumberOfImages: action.totalNumberOfImages
		};
	case 'SET_REMAINING_IMAGES':
		return {
			...state,
			remainingImages: action.remainingImages
		};
	case 'SET_OFFLOAD_LIBRARY_LINK':
		return {
			...state,
			offloadLibraryLink: action.offloadLibraryLink
		};
	case 'SET_ROLLBACK_LIBRARY_LINK':
		return {
			...state,
			rollbackLibraryLink: action.rollbackLibraryLink
		};
	case 'SET_LOADING_ROLLBACK':
		if ( Object.prototype.hasOwnProperty.call( state.queryArgs, 'optimole_action' ) ) {
			state.rollbackLibraryLink = ! action.loadingSync;
		}

		return {
			...state,
			loadingRollback: action.loadingRollback
		};
	case 'SET_LOADING_SYNC':
		if ( Object.prototype.hasOwnProperty.call( state.queryArgs, 'optimole_action' ) ) {
			state.offloadLibraryLink = ! action.loadingSync;
		}

		return {
			...state,
			loadingSync: action.loadingSync
		};
	case 'SET_QUERY_ARGS':
		return {
			...state,
			queryArgs: action.queryArgs
		};
	case 'SET_ERROR_MEDIA':
		return {
			...state,
			errorMedia: action.errorMedia
		};
	case 'SET_CHECKED_OFFLOAD_CONFLICTS':
		return {
			...state,
			checkedOffloadConflicts: action.checkedOffloadConflicts
		};
	case 'SET_OFFLOAD_CONFLICTS':
		return {
			...state,
			offloadConflicts: action.offloadConflicts
		};
	default:
		return state;
	}
};

export default reducer;
