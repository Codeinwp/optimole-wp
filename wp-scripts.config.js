// wp-scripts.config.js
const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	devServer: {
		...defaultConfig.devServer,
		allowedHosts: [ 'all', '.test' ],
		headers: {
			'Access-Control-Allow-Origin': '*',
			'Access-Control-Allow-Methods': 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
			'Access-Control-Allow-Headers': 'X-Requested-With, content-type, Authorization'
		},
		host: '0.0.0.0',
		port: 8887,
		hot: true,
		https: true,
		client: {
			webSocketURL: 'wss://localhost:8887/ws',
			overlay: true
		},
		setupMiddlewares: ( middlewares, devServer ) => {
			if ( ! devServer ) {
				throw new Error( 'webpack-dev-server is not defined' );
			}

			// Add CORS headers to all responses
			devServer.app.use( ( req, res, next ) => {
				res.header( 'Access-Control-Allow-Origin', '*' );
				next();
			});

			return middlewares;
		}
	},
	watchOptions: {
		ignored: /node_modules/,
		aggregateTimeout: 300
	}
};
