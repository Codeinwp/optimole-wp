/**
 * Version File for Grunt
 *
 * @package optimole-wp
 */

/* jshint node:true */
// https://github.com/kswedberg/grunt-version
module.exports = {
	options: {
		pkg: {
			version: '<%= package.version %>'
		}
	},
	project: {
		src: [
			'package.json'
		]
	},
	main_file_header: {
		options: {
			prefix: 'Version\\:\.*\\s'
		},
		src: [
			'optimole-wp.php',
		]
	},
	main_file: {
		options: {
			prefix: 'OPTML_VERSION\', \''
		},
		src: 'optimole-wp.php'
	},
};
