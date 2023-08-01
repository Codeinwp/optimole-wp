const actions = {
	setAutoConnect( autoConnect ) {
		return {
			type: 'SET_AUTO_CONNECT',
			autoConnect
		};
	},
	setAPIKey( apiKey ) {
		return {
			type: 'SET_API_KEY',
			apiKey
		};
	},
	setAvailableApps( availableApps ) {
		return {
			type: 'SET_AVAILABLE_APPS',
			availableApps
		};
	},
	setIsConnected( isConnected ) {
		return {
			type: 'SET_IS_CONNECTED',
			isConnected
		};
	},
	setIsConnecting( isConnecting ) {
		return {
			type: 'SET_IS_CONNECTING',
			isConnecting
		};
	},
	setIsLoading( isLoading ) {
		return {
			type: 'SET_IS_LOADING',
			isLoading
		};
	},
	sethasDashboardLoaded( hasDashboardLoaded ) {
		return {
			type: 'SET_HAS_DASHBOARD_LOADED',
			hasDashboardLoaded
		};
	},
	setHasRestError( hasRestError ) {
		return {
			type: 'SET_HAS_REST_ERROR',
			hasRestError
		};
	},
	setHasValidKey( hasValidKey ) {
		return {
			type: 'SET_HAS_VALID_KEY',
			hasValidKey
		};
	},
	setHasApplication( hasApplication ) {
		return {
			type: 'SET_HAS_APPLICATION',
			hasApplication
		};
	},
	setUserData( data ) {
		return {
			type: 'SET_USER_DATA',
			data
		};
	},
	setUserStatus( status ) {
		return {
			type: 'SET_USER_STATUS',
			status
		};
	},
	setShowDisconnect( showDisconnect ) {
		return {
			type: 'SET_SHOW_DISCONNECT',
			showDisconnect
		};
	},
	setConflicts( conflicts ) {
		return {
			type: 'SET_CONFLICTS',
			conflicts
		};
	},
	setOptimizedImages( optimizedImages ) {
		return {
			type: 'SET_OPTIMIZED_IMAGES',
			optimizedImages
		};
	},
	setSiteSettings( siteSettings ) {
		return {
			type: 'SET_SITE_SETTINGS',
			siteSettings
		};
	},
	setExtraVisits( extraVisitsStatus ) {
		return {
			type: 'SET_EXTRA_VISITS',
			extraVisitsStatus
		};
	},
	setSampleRate( sampleRate ) {
		return {
			type: 'SET_SAMPLE_RATE',
			sampleRate
		};
	},
	setTotalNumberOfImages( totalNumberOfImages ) {
		return {
			type: 'SET_TOTAL_NUMBER_OF_IMAGES',
			totalNumberOfImages
		};
	},
	setProcessedImages( processedImages ) {
		return {
			type: 'SET_PROCESSED_IMAGES',
			processedImages
		};
	},
	setOffloadLibraryLink( offloadLibraryLink ) {
		return {
			type: 'SET_OFFLOAD_LIBRARY_LINK',
			offloadLibraryLink
		};
	},
	setRollbackLibraryLink( rollbackLibraryLink ) {
		return {
			type: 'SET_ROLLBACK_LIBRARY_LINK',
			rollbackLibraryLink
		};
	},
	setLoadingRollback( loadingRollback ) {
		return {
			type: 'SET_LOADING_ROLLBACK',
			loadingRollback
		};
	},
	setLoadingSync( loadingSync ) {
		return {
			type: 'SET_LOADING_SYNC',
			loadingSync
		};
	},
	setQueryArgs( queryArgs ) {
		return {
			type: 'SET_QUERY_ARGS',
			queryArgs
		};
	},
	setErrorMedia( errorMedia ) {
		return {
			type: 'SET_ERROR_MEDIA',
			errorMedia
		};
	},
	setEstimatedTime( estimatedTime ) {
		return {
			type: 'SET_ESTIMATED_TIME',
			estimatedTime
		};
	},
	setCheckedOffloadConflicts( checkedOffloadConflicts ) {
		return {
			type: 'SET_CHECKED_OFFLOAD_CONFLICTS',
			checkedOffloadConflicts
		};
	},
	setOffloadConflicts( offloadConflicts ) {
		return {
			type: 'SET_OFFLOAD_CONFLICTS',
			offloadConflicts
		};
	}
};

export default actions;
