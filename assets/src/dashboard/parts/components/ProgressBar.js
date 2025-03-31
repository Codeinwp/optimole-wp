/**
 * External dependencies.
 */
import classnames from 'classnames';

const ProgressBar = ({
	value,
	max = 100,
	className,
	colorOverage = false,
	...props
}) => {
	const progress = Math.round( ( value / max ) * 100 );
	const background = props.background || '#FFF';

	const wrapClasses = classnames(
		'w-full h-2.5 border rounded-md border-solid bg-white border-light-gray relative overflow-hidden',
		className
	);

	const progressClasses = classnames(
		'absolute left-0 h-full',
		{
			'bg-info': colorOverage ? 70 > progress : true,
			'bg-red-500': colorOverage && 100 < progress,
			'bg-amber-500': colorOverage && 70 < progress && 100 > progress
		}
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
			<div className={ progressClasses } style={{ width: `${progress}%` }}></div>

		</div>
	);
};

export default ProgressBar;
