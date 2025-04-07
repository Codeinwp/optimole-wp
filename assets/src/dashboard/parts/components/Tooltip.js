import { useState } from '@wordpress/element';
import classnames from 'classnames';

export default function({ children, text }) {
	const [ isVisible, setIsVisible ] = useState( false );

	if ( ! text ) {
		return children;
	}
	if ( ! children ) {
		return null;
	}

	const classes = classnames(
		'absolute text-white bg-gray-800 text-xs p-2 rounded shadow-lg z-10 transition-opacity duration-200 pointer-events-none',
		{
			'opacity-100': isVisible,
			'opacity-0': ! isVisible
		}
	);

	const tooltipStyles = {
		left: '50%',
		transform: 'translateX(-50%)',
		bottom: '120%'
	};

	return (
		<div
			className="relative"
			onMouseEnter={() => setIsVisible( true )}
			onMouseLeave={() => setIsVisible( false )}
		>
			<div className={classes} style={tooltipStyles}>
				{text.charAt( 0 ).toUpperCase() + text.slice( 1 ).toLowerCase()}
				<div className="absolute -bottom-1 w-2 h-2 bg-gray-800" style={{ left: '50%', transform: 'translateX(-50%) rotate(45deg)' }}></div>
			</div>
			{children}
		</div>
	);
}
