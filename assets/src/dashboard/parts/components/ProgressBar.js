/**
 * External dependencies.
 */
import classnames from 'classnames';

const ProgressBar = ({
	value,
	max = 100,
	className,
	...props
}) => {
	const progress = Math.round( ( value / max ) * 100 );
	const background = props.background || '#FFF';

	const wrapClasses = classnames(
		'w-full h-2.5 border rounded-md border-solid bg-white border-light-gray relative overflow-hidden',
		className
	);

	return (
		<div
			className={ wrapClasses }
			role="progressbar"
			aria-valuemin="0"
			aria-valuemax={ max }
			aria-valuenow={ value }
			{ ...props }
		>
			<div className="absolute left-0 h-full bg-info" style={{ width: `${progress}%` }}></div>

		</div>
	);
};

export default ProgressBar;
