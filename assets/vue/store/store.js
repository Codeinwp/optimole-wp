/* global optimoleDashboardApp */
/*jshint esversion: 6 */
import Vue from 'vue';
import Vuex from 'vuex';
import VueResource from 'vue-resource';
import mutations from './mutations';
import actions from './actions';

Vue.use( Vuex );
Vue.use( VueResource );

const store = new Vuex.Store(
	{
		strict: true,
		state: {
			isConnecting: false,
			loading: false,
			loadingRollback: false,
			loadingSync: false,
			errorMedia: false,
			site_settings: optimoleDashboardApp.site_settings,
			connected: optimoleDashboardApp.connection_status === 'yes',
			is_loaded: optimoleDashboardApp.connection_status === 'yes',
			apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
			apiKeyValidity: true,
			connectError:'',
			sample_rate: {},
			apiError: false,
			userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
			hasApplication: optimoleDashboardApp.has_application === 'yes',
			availableApps: optimoleDashboardApp.available_apps ? optimoleDashboardApp.available_apps : null,
			optimizedImages: [],
			watermarks: [],
			conflicts: [],
			pushedImagesProgress : 0,
			totalNumberOfImages : 1,
			estimatedTime : 0,
			averageTime : 0,
		},
		mutations,
		actions
	}
);

export default store;
