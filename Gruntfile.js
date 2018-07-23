/* jshint node:true */
/* global require */
module.exports = function (grunt) {
	'use strict';

	var loader = require('load-project-config'),
		config = require('grunt-plugin-fleet');
	config = config();
	config.files.js.push( '!assets/js/bundle.js' );

	loader(grunt, config).init();
};
