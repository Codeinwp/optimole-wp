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
	getTotalNumberOfImages( state ) {
		return state.totalNumberOfImages;
	},
	getProcessedImages( state ) {
		return state.processedImages;
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
	getCheckedOffloadConflicts( state ) {
		return state.checkedOffloadConflicts;
	},
	getOffloadConflicts( state ) {
		return state.offloadConflicts;
	},
	getOffloadLimit( state ) {
		return state.offloadLimit;
	},
	getLogs( state, type = undefined ) {
		if ( type ) {
			return undefined !== state.logs[type] ? state.logs[type] : undefined;
		}

		return state.logs;
	},
	isSubApiKey( state ) {
		return state.apiKey && state.apiKey.startsWith( 'optml-s' );
	},
	canShowFreeUserWithOffloadNotice( state ) {
		return Boolean( state?.canShowFreeUserWithOffloadNotice );
	}
};

export default selectors;
