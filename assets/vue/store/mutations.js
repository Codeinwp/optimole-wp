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
const updateOptimizedImages = ( state, data ) => {
	state.optimizedImages = data.body.data;
};

export default {
	toggleLoading,
	toggleConnecting,
	toggleKeyValidity,
	toggleConnectedToOptml,
	updateUserData,
	updateOptimizedImages
};
