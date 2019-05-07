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
			site_settings: optimoleDashboardApp.site_settings,
			connected: optimoleDashboardApp.connection_status === 'yes',
			apiKey: optimoleDashboardApp.api_key ? optimoleDashboardApp.api_key : '',
			apiKeyValidity: true,
			sample_rate: {},
			apiError: false,
			userData: optimoleDashboardApp.user_data ? optimoleDashboardApp.user_data : null,
			optimizedImages: [],
			watermarks: [],
			conflicts: [],
		},
		mutations,
		actions
	}
);

export default store;
