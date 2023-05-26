/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./assets/src/**/*.js',
	],
	theme: {
		extend: {
			colors: {
				'primary': '#EF686B',
				'success': '#5F9D61',
				'danger': '#E77777',
				'info': '#577BF9',
				'light-blue': '#EEF2FE',
				'dark-blue': '#3557A6',
				'purple-gray': '#757296',
				'grayish-blue': '#f6f6fa'
			},
			fontFamily: {
				'serif': [ '-apple-system', 'BlinkMacSystemFont', 'sans-serif' ],
			},
			fontSize: {
				'2': '2rem',
			},
			maxWidth: {
				'64': '64px',
			},
			minHeight: {
				'40': '40px',
			}
		},
	},
}
