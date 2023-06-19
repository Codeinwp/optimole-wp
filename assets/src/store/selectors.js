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
	getEstimatedTime( state ) {
		return state.estimatedTime;
	},
	getSumTime( state ) {
		return state.sumTime;
	},
	getCheckedOffloadConflicts( state ) {
		return state.checkedOffloadConflicts;
	},
	getOffloadConflicts( state ) {
		return state.offloadConflicts;
	}
};

export default selectors;
