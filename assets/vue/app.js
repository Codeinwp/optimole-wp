/*jshint esversion: 6 */
import Vue from 'vue';
import App from './components/main.vue';
import store from './store/store';

import ToggleButton from 'vue-js-toggle-button';
import TwentyTwenty from 'vue-twentytwenty';

Vue.use( TwentyTwenty );

Vue.use( ToggleButton );


window.onload = function () {
	new Vue ( {
		el: '#optimole-app',
		store,
		components: {
			App
		},
	} );
};