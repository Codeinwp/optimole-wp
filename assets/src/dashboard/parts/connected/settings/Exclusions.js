
/**
 * Internal dependencies.
 */
import FilterControl from './FilterControl';

const Exclusions = ({
	settings,
	setSettings,
	setCanSave
}) => {
	const isLazyloadEnabled = 'disabled' !== settings.lazyload;

	return (
		<>
			<FilterControl
				label={ optimoleDashboardApp.strings.options_strings.exclude_title_optimize }
				help={ optimoleDashboardApp.strings.options_strings.exclude_desc_optimize }
				type="optimize"
				settings={ settings }
				setSettings={ setSettings }
				setCanSave={ setCanSave }
			/>

			{ isLazyloadEnabled && (
				<>
					<hr className="my-8 border-grayish-blue"/>

					<FilterControl
						label={ optimoleDashboardApp.strings.options_strings.exclude_title_lazyload }
						help={ optimoleDashboardApp.strings.options_strings.exclude_desc_lazyload }
						type="lazyload"
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				</>
			) }
		</>
	);
};

export default Exclusions;
