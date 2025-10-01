import classNames from 'classnames';

export const TextWithWarningBadge = ({ text, badgeLabel }) => {
	return (
		<>
			{text}
			<span className="ml-4 text-xs font-bold p-1 rounded bg-yellow-400 text-yellow-800 uppercase">
				{badgeLabel}
			</span>
		</>
	);
};

const DescriptionTag = ({ text, showAsDisabled }) => (
	<span
		className={classNames(
			'inline-block  text-xs px-2 py-1 rounded mr-2 mt-2 font-medium',
			{
				'bg-gray-200 text-gray-800 line-through': showAsDisabled,
				'bg-blue-200 text-blue-800': ! showAsDisabled
			}
		)}
	>
		{text}
	</span>
);

export const DescriptionWithTags = ({ text, tags }) => (
	<>
		{text}
		<div className="mt-2">
			{tags.map( ({ text, disabled }) => (
				<DescriptionTag key={text} text={text} showAsDisabled={disabled} />
			) )}
		</div>
	</>
);
