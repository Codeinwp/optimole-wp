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
			loadingRollback: false,
			loadingSync: false,
			errorMedia: false,
			checkedOffloadConflicts: false,
			offloadConflicts: [],
			sample_rate: {},
			watermarks: [],
			pushedImagesProgress : 0,
			totalNumberOfImages : 1,
			estimatedTime : 0,
			sumTime : 0,
			offloadLibraryLink : false,
			rollbackLibraryLink: false,
			queryArgs : [],
		},
		mutations,
		actions
	}
);

export default store;
