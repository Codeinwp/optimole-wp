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
			checkedOffloadConflicts: false,
			offloadConflicts: [],
			estimatedTime : 0,
			sumTime : 0,
		},
		mutations,
		actions
	}
);

export default store;
