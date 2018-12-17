/* jshint esversion: 6 */
const toggleLoading = ( state, data ) => {
	state.loading = data;
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
const updateUserData = ( state, data ) => {
	state.userData = data;
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
		state.site_settings[setting] = data[setting]; }

};

export default {
	toggleLoading,
	toggleConnecting,
	toggleKeyValidity,
	toggleConnectedToOptml,
	updateUserData,
	updateApiKey,
	updateSampleRate,
	restApiNotWorking,
	updateSettings,
	updateOptimizedImages
};
