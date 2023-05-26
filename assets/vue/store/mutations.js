/* jshint esversion: 6 */
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
const toggleCheckedOffloadConflicts = ( state, data ) => {
	state.checkedOffloadConflicts = data;
};
const updateOptimizedImages = ( state, data ) => {
	state.optimizedImages = data.body.data;
};
const updateSampleRate = ( state, data ) => {
	state.sample_rate = data;
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
const updateOffloadConflicts = ( state, data ) => {
	state.offloadConflicts = data.body.data;
};
export default {
	updateConflicts,
	updateOptimizedImages,
	updateSampleRate,
	updateSettings,
	updateWatermark,
	updatePushedImagesProgress,
	totalNumberOfImages,
	estimatedTime,
	toggleLoadingRollback,
	toggleLoadingSync,
	toggleActionError,
	toggleCheckedOffloadConflicts,
	updateOffloadConflicts,
};
