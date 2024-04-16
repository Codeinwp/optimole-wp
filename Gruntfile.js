/* jshint node:true */
/* global require */

module.exports = function (grunt) {
	grunt.loadNpmTasks('grunt-version');
	grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
	grunt.initConfig({
		version: {
			json: {
				options: {
					flags: ''
				},
				src: [ 'package.json', 'package-lock.json' ]
			},
			metatag: {
				options: {
					prefix: 'Version:\\s*',
					flags: ''
				},
				src: [ 'optimole-wp.php' ]
			},
			php: {
				options: {
					prefix: 'OPTML_VERSION\', \'',
					flags: ''
				},
				src: [ 'optimole-wp.php' ]

			},
			readmetxt: {
				options: {
					prefix: 'Stable tag:\\s*'
				},
				src: [
					'readme.txt'
				]
			},
		},
		wp_readme_to_markdown: {
			plugin: {
				files: {
					'README.md': 'readme.txt'
				},
			},
		},
	});

};