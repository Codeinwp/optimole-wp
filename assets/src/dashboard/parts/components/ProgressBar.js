/**
 * External dependencies.
 */
import classnames from 'classnames';

const ProgressBar = ({
	value,
	max,
	className,
	...props
}) => {
	const progress = Math.floor( ( value / max ) * 100 );
	const background = props.background || '#FFF';

	return (
		<div
			className={ classnames(
				'w-full h-2.5 border rounded-md border-solid bg-white border-light-gray',
				className
			) }
			role="progressbar"
			aria-valuemin="0"
			aria-valuemax={ max }
			aria-valuenow={ value }
			style={{
				background: 'linear-gradient(90deg, var( --optml-progress ) ' + progress + '%, ' + background + ' ' + progress + '%)'
			}}
			{ ...props }
		/>
	);
};

export default ProgressBar;
