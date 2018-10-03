/*jshint esversion: 6 */
import Vue from 'vue';
import App from './components/main.vue';
import store from './store/store';

import VueResize from 'vue-resize'
import ToggleButton from 'vue-js-toggle-button';

Vue.use( ToggleButton );

Vue.use( VueResize );

window.onload = function () {
	new Vue ( {
		el: '#optimole-app',
		store,
		components: {
			App
		},
	} );
};