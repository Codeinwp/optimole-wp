/*jshint esversion: 6 */
import Vue from 'vue';
import App from './components/main.vue';
import store from './store/store';

import VueResize from 'vue-resize'
import ToggleButton from 'vue-js-toggle-button';
import VueTextareaAutosize from 'vue-textarea-autosize'


Vue.use( ToggleButton );
Vue.use( VueTextareaAutosize );
Vue.use( VueResize );


window.addEventListener( 'load', function () {
	new Vue(
		{
			el: '#optimole-app',
			store,
			components: {
				App
			},
		}
	);
} );