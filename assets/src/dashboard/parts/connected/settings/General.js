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

import { Icon, chevronDown, chevronUp } from '@wordpress/icons';

import {
	useState
} from '@wordpress/element';

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
	const isAssetsEnabled = 'disabled' !== settings.cdn;
	const isBannerEnabled = 'disabled' !== settings[ 'banner_frontend'];
	const isShowBadgeIcon = 'disabled' !== settings[ 'show_badge_icon' ];
	const activeBadgePosition = settings[ 'badge_position' ] || 'right';

	const [ showBadgeSettings, setBadgeSettings ] = useState( isBannerEnabled );

	const updateOption = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		if ( 'badge_position' === option ) {
			data[ option ] = value;
		} else {
			data[ option ] = value ? 'enabled' : 'disabled';
		}
		setSettings( data );
	};

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_image_replace }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.replacer_desc } } /> }
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
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.lazyload_desc } } /> }
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
				label={ optimoleDashboardApp.strings.options_strings.enable_badge_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_badge_description } } /> }
				checked={ isBannerEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ ( value ) => {
					updateOption( 'banner_frontend', value );
					setBadgeSettings( value );
				} }
			/>

			{ isBannerEnabled && (
				<div className="mt-4 badge-settings">
					<Button
						className={ classnames(
							'border border-none bg-transparent text-blue-500 px-2 py-1 rounded-sm flex items-center cursor-pointer',
							{
								'is-disabled': isLoading
							}
						) }
						onClick={ () => setBadgeSettings( ! showBadgeSettings ) }
					>
						<span>{ optimoleDashboardApp.strings.options_strings.enable_badge_settings }</span>
						<Icon
							icon={ showBadgeSettings ? chevronUp : chevronDown }
							className="h-5 w-5"
							style={{ fill: '#3b82f6' }}
						/>
					</Button>
					{ showBadgeSettings && (
						<div class="mt-4 space-y-4 pl-4 pt-2">
							<div class="flex items-center justify-between mb-4">
								<label class="text-gray-600 font-medium">{ optimoleDashboardApp.strings.options_strings.enable_badge_show_icon }</label>
								<ToggleControl
									label=""
									checked={ isShowBadgeIcon }
									disabled={ isLoading }
									className={ classnames(
										'flex items-center justify-between mb-4',
										{
											'is-disabled': isLoading
										}
									) }
									onChange={ value => updateOption( 'show_badge_icon', value ) }
								/>
							</div>
							<div class="flex items-center justify-between">
								<label class="text-gray-600 font-medium">{ optimoleDashboardApp.strings.options_strings.enable_badge_position }</label>
								<div class="flex space-x-2">
									<Button
										className={ classnames(
											'px-4 py-2 border rounded border-[1px]',
											'left' === activeBadgePosition ? 'border-blue-500 text-blue-500 bg-blue-100' : 'border-gray-300 text-gray-500 bg-gray-100',
											{
												'is-disabled': isLoading
											}
										) }
										onClick={ () => updateOption( 'badge_position', 'left' ) }
									>{ optimoleDashboardApp.strings.options_strings.badge_position_text_1 }</Button>
									<Button
										className={ classnames(
											'px-4 py-2 border rounded',
											'right' === activeBadgePosition ? 'border-blue-500 text-blue-500 bg-blue-100' : 'border-gray-300 text-gray-500 bg-gray-100',
											{
												'is-disabled': isLoading
											}
										) }
										onClick={ () => updateOption( 'badge_position', 'right' ) }
									>{ optimoleDashboardApp.strings.options_strings.badge_position_text_2 }</Button>
								</div>
							</div>
						</div>
					)}
				</div>
			)}

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				label={ optimoleDashboardApp.strings.options_strings.cache_title }
				className={ classnames(
					{
						'is-disabled': isLoading || ! isReplacerEnabled
					}
				) }
			>
				<p
					className="components-base-control__help m-0"
					dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.cache_desc } }
				/>

				<div className="flex my-4 gap-3">
					<Button
						variant="default"
						isBusy={ isLoading }
						disabled={ isLoading || ! isReplacerEnabled }
						className="optml__button flex justify-center rounded font-bold min-h-40"
						onClick={ () => clearCache() }
					>
						{ optimoleDashboardApp.strings.options_strings.clear_cache_images }
					</Button>

					{ isAssetsEnabled && (
						<Button
							variant="default"
							isBusy={ isLoading }
							disabled={ isLoading || ! isReplacerEnabled }
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
