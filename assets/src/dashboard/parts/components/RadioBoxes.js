import classNames from 'classnames';

export default function RadioBoxes({ options, value, onChange, label, disabled = false, className }) {

	const handleClick = ( e ) => {
		onChange( e.target.value );
	};

	const fieldsetClasses = classNames({
		'opacity-50': disabled
	}, 'transition-all my-6 gap-6 grid opacity-100' );

	return (
		<fieldset
			className={`my-6 gap-6 grid ${className}`}
			onChange={handleClick}
		>

			{label && <legend className="uppercase font-semibold text-s text-light-black mb-6">{label}</legend>}

			{options.map( ( option, index ) => {
				const { title, value: buttonValue, description } = option;

				const isActive = value === buttonValue;
				const buttonClasses = classNames({
					'outline-info': isActive,
					'outline-transparent': ! isActive
				}, 'flex gap-6 items-start bg-gray-50 rounded-md p-4 outline -outline-offset-3 outline-3 transition-all' );

				return (
					<label
						htmlFor={buttonValue}
						key={buttonValue}
						className={buttonClasses}
					>
						<RadioDot isActive={isActive} />

						<div className="grid space-y-2">
							{title && <div className="text-base font-medium text-gray-700">{title}</div>}
							{description && <div className="text-sm text-gray-500">{description}</div>}
						</div>
						<input
							type="radio"
							name="label"
							value={buttonValue}
							id={buttonValue}
							className="!opacity-0 !w-0 !h-0 !overflow-hidden !absolute !pointer-events-none"
							disabled={disabled}
						/>
					</label>
				);

			})}

		</fieldset>
	);
}

const RadioDot = ({ isActive }) => {
	const wrapClasses = classNames({
		'bg-white outline-info': isActive,
		'bg-gray-200 outline-transparent': ! isActive
	}, 'w-[24px] h-[24px] rounded-full flex items-center justify-center flex-shrink-0 outline outline-3 -outline-offset-3 transition-all' );

	const dotClasses = classNames({
		'bg-info': isActive
	}, 'w-[12px] h-[12px] rounded-full background-info transition-all' );

	return (
		<div className={wrapClasses}>
			{isActive && <div className={dotClasses} />}
		</div>
	);
};
