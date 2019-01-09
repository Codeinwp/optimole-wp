/* jshint node:true */
// https://github.com/stephenharris/grunt-wp-deploy
module.exports = {
	source: {
		options: {
			plugin_slug: '<%= package.name %>',
			svn_user: 'optimole',
			build_dir: 'dist',
			skip_confirmation: true,
			deploy_trunk: true,
			force_interactive: false,
			deploy_tag: true,
			assets_dir: '.wporg'
		},
	},
	assets: {
		options: {
			plugin_slug: '<%= package.name %>',
			svn_user: 'optimole',
			build_dir: 'dist',
			skip_confirmation: true,
			force_interactive: false,
			deploy_trunk: true,
			deploy_tag: false,
			assets_dir: '.wporg'
		},
	}
};
