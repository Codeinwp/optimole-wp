/**
 * External dependencies.
 */
import classnames from 'classnames';

/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button,
	ToggleControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { clearCache } from '../../../utils/api';
import Notice from '../../components/Notice';

const General = ({
	settings,
	setSettings,
	setCanSave
}) => {
	const { isLoading } = useSelect( select => {
		const { isLoading } = select( 'optimole' );

		return {
			isLoading: isLoading()
		};
	});

	const isReplacerEnabled = 'disabled' !== settings[ 'image_replacer' ];
	const isLazyloadEnabled = 'disabled' !== settings.lazyload;
	const isReportEnabled = 'disabled' !== settings[ 'report_script' ];
	const isAssetsEnabled = 'disabled' !== settings.cdn;
	const isBannerEnabled = 'disabled' !== settings[ 'banner_frontend'];

	const updateOption = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		data[ option ] = value ? 'enabled' : 'disabled';
		setSettings( data );
	};

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_image_replace }
				help={ optimoleDashboardApp.strings.options_strings.replacer_desc }
				checked={ isReplacerEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'image_replacer', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_lazyload }
				help={ optimoleDashboardApp.strings.options_strings.lazyload_desc }
				checked={ isLazyloadEnabled }
				disabled={ ! isReplacerEnabled || isLoading }
				className={ classnames(
					{
						'is-disabled': ! isReplacerEnabled || isLoading
					}
				) }
				onChange={ value => updateOption( 'lazyload', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_report_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_report_desc }
				checked={ isReportEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'report_script', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_badge_title }
				help={ () => <span dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_badge_description } } /> }
				checked={ isBannerEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'banner_frontend', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				label={ optimoleDashboardApp.strings.options_strings.cache_title }
				help={ optimoleDashboardApp.strings.options_strings.cache_desc }
				className={ classnames(
					{
						'is-disabled': isLoading || ! isReplacerEnabled
					}
				) }
			>
				<div className="flex my-2 gap-3">
					<Button
						variant="default"
						isBusy={ isLoading }
						disabled={ isLoading }
						className="optml__button flex justify-center rounded font-bold min-h-40"
						onClick={ () => clearCache() }
					>
						{ optimoleDashboardApp.strings.options_strings.clear_cache_images }
					</Button>

					{ isAssetsEnabled && (
						<Button
							variant="default"
							isBusy={ isLoading }
							disabled={ isLoading }
							className="optml__button flex justify-center rounded font-bold min-h-40"
							onClick={ () => clearCache( 'assets' ) }
						>
							{ optimoleDashboardApp.strings.options_strings.clear_cache_assets }
						</Button>
					) }
				</div>
			</BaseControl>

			<Notice type='warning' text={optimoleDashboardApp.strings.options_strings.clear_cache_notice} disableIcon/>
		</>
	);
};

export default General;
