const { defineConfig } = require( 'cypress' )

module.exports = defineConfig( {
	projectId: '767db5',
	videoUploadOnPasses: false,
	numTestsKeptInMemory: 0,
	experimentalStudio: true,
	e2e: {
		setupNodeEvents( on, config ) {},
		supportFile: false,
		baseUrl: 'http://testing.optimole.com',
		specPattern: 'cypress/e2e/**/*.{js,jsx,ts,tsx}',
		pageLoadTimeout : 300000,
	},
} )
