/*jshint esversion: 6 */
import Vue from 'vue';
import App from './components/main.vue';
import store from './store/store';
import ToggleButton from 'vue-js-toggle-button';

Vue.use( ToggleButton );

window.onload = function () {
	new Vue( {
		el: '#optimole-app',
		store,
		components: {
			App
		},
	} );
};