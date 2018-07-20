/* jshint esversion: 6 */
const toggleLoading = ( state, data ) => {
	state.loading = data;
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

export default {
	toggleLoading,
	toggleKeyValidity,
	toggleConnectedToOptml,
	updateUserData
};
