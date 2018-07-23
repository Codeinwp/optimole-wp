/* jshint esversion: 6 */
/* global optimoleDashboardApp */
import Vue from 'vue';
import VueResource from 'vue-resource';

Vue.use( VueResource );

const connectOptimole = function ( { commit, state }, data ) {
	commit( 'toggleLoading', true );
	Vue.http( {
		url: optimoleDashboardApp.root + '/connect',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': data.req },
		body: {
			'api_key': data.apiKey,
		},
		responseType: 'json'
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if ( response.body.code === 'success' ) {
			commit( 'toggleKeyValidity', true );
			commit( 'toggleConnectedToOptml', true );
			commit( 'updateUserData', response.body.data );
			console.log( '%c OptiMole API connection successful.', 'color: #59B278' );
		} else {
			commit( 'toggleKeyValidity', false );
			console.log( '%c Invalid API Key.', 'color: #E7602A' );
		}
	} );
};

const disconnectOptimole = function ( { commit, state }, data ) {
	commit( 'toggleLoading', true, 'loading' );
	Vue.http( {
		url: optimoleDashboardApp.root + '/disconnect',
		method: 'GET',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: { 'req': data.req },
		responseType: 'json'
	} ).then( function ( response ) {
		commit( 'updateUserData', null );
		commit( 'toggleLoading', false );
		if ( response.ok ) {
			commit( 'toggleConnectedToOptml', false );
			console.log( '%c Disconnected from OptiMole API.', 'color: #59B278' );
		} else {
			console.error( response );
		}
	} );
};

const toggleSetting = function ( { commit, state }, data ) {
	commit( 'toggleLoading', true, 'loading' );
	Vue.http( {
		url: optimoleDashboardApp.root + '/update_option',
		method: 'POST',
		headers: { 'X-WP-Nonce': optimoleDashboardApp.nonce },
		params: {
			'req': data.req,
			'option_key': data.option_key,
			'type' : data.type ? data.type : ''
		},
		responseType: 'json'
	} ).then( function ( response ) {
		commit( 'toggleLoading', false );
		if ( response.ok ) {
			console.log( '%c Option Toggled.', 'color: #59B278' );
		} else {
			console.error( response );
		}
	} );
};

export default {
	connectOptimole,
	disconnectOptimole,
	toggleSetting,
};