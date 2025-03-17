/** @type {import('tailwindcss').Config} */
module.exports = {
	content: [
		'./assets/src/**/*.js',
		'./inc/admin.php'
	],
	theme: {
		extend: {
			backgroundPosition: {
				'promo': '90% 70%'
			},
			colors: {
				'promo-orange': '#FF8811',
				'primary': '#EF686B',
				'success': '#5F9D61',
				'danger': '#E77777',
				'info': '#577BF9',
				'warning': '#ffebeb',
				'light-blue': '#EEF2FE',
				'dark-blue': '#3557A6',
				'purple-gray': '#757296',
				'grayish-blue': '#f6f6fa',
				'light-black': '#626262',
				'opaque-black': '#0000006E',
				'stale-yellow': '#FFF0C9',
				'mango-yellow': '#FBBF24',
				'disabled': '#6786F4',
				'light-gray': 'rgba(87, 123, 249, 0.36)'
			},
			fontFamily: {
				'serif': [ '-apple-system', 'BlinkMacSystemFont', 'sans-serif' ]
			},
			fontSize: {
				'2': '2rem',
				's': '13px',
				'26': '26px',
			},
			maxWidth: {
				'64': '64px',
				'screen-xl': 'min( 1320px, 90%)'
			},
			minHeight: {
				'40': '40px'
			},
			outlineWidth: {
				3: '3px'
			},
			outlineOffset: {
				3: '3px'
			}
		}
	}
};
