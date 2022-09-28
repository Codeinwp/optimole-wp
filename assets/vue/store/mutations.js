/* jshint esversion: 6 */
const toggleLoading = ( state, data ) => {
	state.loading = data;
};
const toggleLoadingRollback = ( state, data ) => {
	if ( Object.prototype.hasOwnProperty.call( state.queryArgs, 'optimole_action' ) ) {
		state.rollbackLibraryLink = ! data;
	}
	state.loadingRollback = data;
};
const toggleLoadingSync = ( state, data ) => {
	if ( Object.prototype.hasOwnProperty.call( state.queryArgs, 'optimole_action' ) ) {
		state.offloadLibraryLink = ! data;
	}
	state.loadingSync = data;
};
const toggleActionError = ( state, data ) => {
	state.errorMedia = data;
};
const toggleConnecting = ( state, data ) => {
	state.isConnecting = data;
};
const toggleKeyValidity = ( state, data ) => {
	state.apiKeyValidity = data;
};
const toggleConnectedToOptml = ( state, data ) => {
	state.connected = data;
};
const toggleShowDisconnectNotice = ( state, data ) => {
	state.showDisconnect = data;
};
const toggleCheckedOffloadConflicts = ( state, data ) => {
	state.checkedOffloadConflicts = data;
};
const toggleHasOptmlApp = ( state, data ) => {
	state.hasApplication = data;
};
const toggleIsServiceLoaded = ( state, data ) => {
	state.is_loaded = data;
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
const updateAvailableApps = ( state, data ) => {
	state.availableApps = data;
};
const updateServiceError = ( state, data ) => {
	state.connectError = data;
};
const updateApiKey = ( state, data ) => {
	state.apiKey = data;
};
const updateOptimizedImages = ( state, data ) => {
	state.optimizedImages = data.body.data;
};
const updateSampleRate = ( state, data ) => {
	state.sample_rate = data;
};
const restApiNotWorking = ( state, data ) => {
	state.apiError = data;
};
const updateSettings = ( state, data ) => {
	for ( var setting in data ) {
		state.site_settings[setting] = data[setting];
	}
};

const updateWatermark = ( state, data ) => {

	for ( var key in data ) {
		state.site_settings.watermark[key] = data[key]; }

};

const updateConflicts = ( state, data ) => {
	state.conflicts = data.body.data;
};
const totalNumberOfImages = ( state, data ) => {
	state.totalNumberOfImages = data;
};
const estimatedTime = ( state, data ) => {
	state.sumTime = ( state.sumTime + data.batchTime );
	state.estimatedTime = ( ( state.sumTime/data.processedBatch )*( Math.ceil( state.totalNumberOfImages/data.batchSize ) - data.processedBatch ) / 60000 ).toFixed( 2 );
};
const updatePushedImagesProgress = ( state, data ) => {
	if ( data === 'finish' ) {
		state.pushedImagesProgress = 100;
	}
	if ( data === 'init' ) {
		state.pushedImagesProgress = 0;
		return;
	}
	if ( state.pushedImagesProgress < 100 ) {
		state.pushedImagesProgress += data/state.totalNumberOfImages*100;
	}
};
export default {
	restApiNotWorking,
	toggleConnectedToOptml,
	toggleConnecting,
	toggleIsServiceLoaded,
	toggleKeyValidity,
	toggleLoading,
	updateApiKey,
	updateConflicts,
	updateOptimizedImages,
	updateSampleRate,
	updateServiceError,
	updateSettings,
	updateUserData,
	updateAvailableApps,
	toggleHasOptmlApp,
	updateWatermark,
	updatePushedImagesProgress,
	totalNumberOfImages,
	estimatedTime,
	toggleLoadingRollback,
	toggleLoadingSync,
	toggleActionError,
	toggleShowDisconnectNotice,
	toggleCheckedOffloadConflicts,
};
