/**
 * External dependencies.
 */
import classnames from 'classnames';

/**
 * WordPress dependencies.
 */
import {
	__experimentalNumberControl as NumberControl,
	BaseControl,
	TextareaControl,
	ToggleControl,
	ColorPicker,
	ColorIndicator,
	Button,
	Popover,
	CheckboxControl,
	Tooltip
} from '@wordpress/components';
import { sprintf } from '@wordpress/i18n';

import { useSelect } from '@wordpress/data';

import {
	useState,
	createInterpolateElement,
	useMemo,
	useCallback
} from '@wordpress/element';
import RadioBoxes from '../../components/RadioBoxes';
import {
	GroupSettingsContainer,
	GroupSettingsTitle,
	GroupSettingsOption
} from '../../components/GroupSettingsContainer';
import Notice from '../../components/Notice';
import Modal from '../../components/Modal';
import {
	DescriptionWithTags,
	TextWithWarningBadge
} from '../../components/Miscellaneous';
import { Icon, help } from '@wordpress/icons';

const { options_strings } = optimoleDashboardApp.strings;

const DISABLE_OPTION_MODAL_TYPE = {
	scale: 'image-scaling',
	lazyLoad: 'lazy-load',
	javascriptLoading: 'javascript-loading'
};

const Lazyload = ({ settings, setSettings, setCanSave }) => {
	const { isLoading } = useSelect( ( select ) => {
		const { isLoading } = select( 'optimole' );

		return {
			isLoading: isLoading()
		};
	});

	const isLazyLoadPlaceholderEnabled = useMemo(
		() => 'disabled' !== settings['lazyload_placeholder'],
		[ settings ]
	);
	const isNativeLazyLoadEnabled = useMemo(
		() => 'disabled' !== settings['native_lazyload'],
		[ settings ]
	);
	const isBGReplacerEnabled = useMemo(
		() => 'disabled' !== settings['bg_replacer'],
		[ settings ]
	);
	const isVideoLazyLoadEnabled = useMemo(
		() => 'disabled' !== settings['video_lazyload'],
		[ settings ]
	);
	const isNoScriptEnabled = useMemo(
		() => 'disabled' !== settings['no_script'],
		[ settings ]
	);
	const placeholderColor = useMemo(
		() => settings['placeholder_color'],
		[ settings.placeholder_color ]
	);
	const isScaleEnabled = useMemo(
		() => 'disabled' === settings.scale,
		[ settings.scale ]
	);
	const isLazyLoadEnabled = useMemo(
		() => 'disabled' !== settings.lazyload,
		[ settings.lazyload ]
	);
	const isViewPortLoadingEnabled = useMemo(
		() => settings['lazyload_type']?.includes( 'viewport' ),
		[ settings?.lazyload_type ]
	);
	const isFixedSkipLazyEnabled = useMemo(
		() => settings['lazyload_type']?.includes( 'fixed' ),
		[ settings?.lazyload_type ]
	);

	const [ phPicker, setPhPicker ] = useState( false );
	const [ showModal, setShowModal ] = useState( false );

	const supportPrefilledContactFormUrl = useMemo( () => {
		const contactUrl = new URL( window.optimoleDashboardApp.report_issue_url );
		const { contact_support } = window.optimoleDashboardApp.strings;

		let subject = contact_support.disable_lazy_load_scaling;
		if ( DISABLE_OPTION_MODAL_TYPE.scale === showModal ) {
			subject = contact_support.disable_image_scaling;
		} else if ( DISABLE_OPTION_MODAL_TYPE.javascriptLoading === showModal ) {
			subject = contact_support.enable_native_lazy_load;
		}

		contactUrl.searchParams.set(
			'contact_subject',
			sprintf( contact_support.title_prefix, subject )
		);

		return contactUrl;
	}, [ showModal ]);

	const toggleOption = useCallback(
		( option, value ) => {
			setCanSave( true );
			const data = { ...settings };
			data[option] = value ? 'enabled' : 'disabled';
			setSettings( data );
		},
		[ setCanSave, settings, setSettings ]
	);

	const updateValue = useCallback(
		( option, value ) => {
			setCanSave( true );
			setSettings( ( prevSettings ) => ({
				...prevSettings,
				[option]: value
			}) );
		},
		[ setCanSave, setSettings ]
	);

	const setColor = useCallback(
		( value ) => {
			updateValue( 'placeholder_color', value );
		},
		[ updateValue ]
	);

	const toggleLoadingBehavior = useCallback(
		( value, slug ) => {
			const lazyLoadValue = new Set(
				( settings?.lazyload_type ?? '' )
					?.split( '|' )
					.filter( ( i ) => 'viewport' === i || 'fixed' === i ) ?? []
			);
			if ( value ) {
				lazyLoadValue.add( slug );
			} else {
				lazyLoadValue.delete( slug );
			}
			const updatedValue = Array.from( lazyLoadValue ).toSorted().join( '|' ) || 'all';
			updateValue( 'lazyload_type', updatedValue );
		},
		[ settings?.lazyload_type, updateValue ]
	);

	if ( ! isLazyLoadEnabled ) {
		return (
			<>
				<ToggleControl
					label={options_strings.toggle_lazyload}
					help={() => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.lazyload_desc } } />}
					checked={isLazyLoadEnabled}
					disabled={isLoading}
					className={classnames({
						'is-disabled': isLoading
					})}
					onChange={( value ) => toggleOption( 'lazyload', value )}
				/>
			</>
		);
	}

	return (
		<>
			<ToggleControl
				label={options_strings.toggle_lazyload}
				help={() => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.lazyload_desc } } />}
				checked={isLazyLoadEnabled}
				disabled={isLoading}
				className={classnames({
					'is-disabled': isLoading
				})}
				onChange={() => setShowModal( DISABLE_OPTION_MODAL_TYPE.lazyLoad )}
			/>
			{showModal && (
				<Modal
					icon="warning"
					variant="warning"
					labels={{
						title: options_strings.performance_impact_alert_title,
						description:
							'scale' === showModal ?
								options_strings.performance_impact_alert_scale_desc :
								options_strings.performance_impact_alert_lazy_desc,
						action: options_strings.performance_impact_alert_action_label,
						secondaryAction:
							options_strings.performance_impact_alert_secondary_action_label
					}}
					onRequestClose={() => setShowModal( false )}
					onConfirm={() => {
						window?.formbricks?.track( 'disable_lazy_load_feature', {
							hiddenFields: {
								feature: `${showModal}`
							}
						});

						if ( DISABLE_OPTION_MODAL_TYPE.javascriptLoading === showModal ) {
							toggleOption( 'native_lazyload', true );
						} else if ( DISABLE_OPTION_MODAL_TYPE.scale === showModal ) {
							toggleOption( 'scale', true );
						} else if ( DISABLE_OPTION_MODAL_TYPE.lazyLoad === showModal ) {
							toggleOption( 'lazyload', false );
						}

						setShowModal( false );
					}}
					onSecondaryAction={() => {
						window.open( supportPrefilledContactFormUrl, '_blank' );
						setShowModal( false );
					}}
				/>
			)}
			<hr className="my-8 border-grayish-blue" />
			<BaseControl
				className="mt-2"
				label={options_strings.lazyload_behaviour_title}
			>
				<div className="ml-4">
					<RadioBoxes
						label={''}
						options={[
							{
								title: options_strings.smart_loading_title,
								description: (
									<DescriptionWithTags
										text={options_strings.smart_loading_desc}
										tags={[
											{
												text: options_strings.viewport_detection
											},
											{
												text: options_strings.placeholders_color
											},
											{
												text: options_strings.auto_scaling
											}
										]}
									/>
								),
								value: 'disabled'
							},
							{
								title: (
									<TextWithWarningBadge
										text={options_strings.toggle_native}
										badgeLabel={options_strings.not_recommended}
									/>
								),
								description: (
									<DescriptionWithTags
										text={options_strings.native_desc}
										tags={[
											{
												text: options_strings.viewport_detection
											},
											{
												text: options_strings.lightweight_native
											},
											{
												text: options_strings.auto_scaling,
												disabled: true
											}
										]}
									/>
								),
								value: 'enabled'
							}
						]}
						value={isNativeLazyLoadEnabled ? 'enabled' : 'disabled'}
						onChange={( value ) => {
							if ( 'disabled' === value ) {
								toggleOption( 'native_lazyload', false );
							} else {
								setShowModal( DISABLE_OPTION_MODAL_TYPE.javascriptLoading );
							}
						}}
					/>

					<GroupSettingsContainer>
						<GroupSettingsTitle>
							{options_strings.lazyload_behaviour_title} (
							{options_strings.global_option})
						</GroupSettingsTitle>
						<GroupSettingsOption className="flex flex-row items-center gap-4">
							<CheckboxControl
								className="optml-skip-lazy-load-images"
								label={options_strings.lazyload_behaviour_fixed}
								checked={isFixedSkipLazyEnabled}
								onChange={( value ) => {
									toggleLoadingBehavior( value, 'fixed' );
								}}
								disabled={false}
								__nextHasNoMarginBottom={true}
							/>
							<div className="w-12">
								<NumberControl
									className={''}
									label={''}
									labelPosition="side"
									value={settings['skip_lazyload_images']}
									type="number"
									min={0}
									onChange={( value ) =>
										updateValue( 'skip_lazyload_images', value )
									}
									disabled={! isFixedSkipLazyEnabled}
									__nextHasNoMarginBottom={true}
								/>
							</div>
						</GroupSettingsOption>
						<GroupSettingsOption className="mt-2">
							<CheckboxControl
								label={options_strings.lazyload_behaviour_viewport}
								checked={isViewPortLoadingEnabled}
								onChange={( value ) => {
									toggleLoadingBehavior( value, 'viewport' );
								}}
								disabled={false}
								__nextHasNoMarginBottom={true}
							/>
						</GroupSettingsOption>
						{isFixedSkipLazyEnabled && isViewPortLoadingEnabled && (
							<Notice
								type="warning"
								title={''}
								text={options_strings.viewport_skip_images_notice}
							/>
						)}
					</GroupSettingsContainer>

					<GroupSettingsContainer>
						<GroupSettingsTitle>
							{options_strings.visual_settings}
						</GroupSettingsTitle>
						<GroupSettingsOption>

							<div className="grow flex flex-row gap-1" htmlFor="optml-lazyload-placeholder">
								<CheckboxControl
									label={options_strings.enable_lazyload_placeholder_title}
									checked={isLazyLoadPlaceholderEnabled}
									onChange={( value ) =>
										toggleOption( 'lazyload_placeholder', value )
									}
									disabled={isLoading}
									__nextHasNoMarginBottom={true}
								/>
								<Tooltip
									text={<p
										className=""
										dangerouslySetInnerHTML={{
											__html: options_strings.enable_lazyload_placeholder_desc
										}}
									/>}
								>
									<Icon
										icon={ help }
										size={ 18 }
										className="text-gray-400 hover:text-gray-600"
									/>
								</Tooltip>
							</div>
							{isLazyLoadPlaceholderEnabled && (
								<div className="relative inline-block">
									<Button
										disabled={isLoading}
										className=""
										onClick={() => {
											setPhPicker( ! phPicker );
										}}
									>
										<ColorIndicator colorValue={placeholderColor} />
									</Button>

									{phPicker && (
										<Popover
											placement="bottom-end"
											variant={'unstyled'}
											className={
												'shadow-md border-grayish-blue border border-solid rounded bg-white p-2'
											}
											onFocusOutside={() => setPhPicker( false )}
										>
											<ColorPicker
												color={placeholderColor}
												onChange={setColor}
												enableAlpha
												defaultValue=""
											/>
											<Button
												isDestructive={true}
												className={'w-full text-center flex justify-center'}
												variant={'secondary'}
												onClick={() => {
													setColor( '' );
												}}
											>
												{options_strings.clear}
											</Button>
										</Popover>
									)}
								</div>
							)}
						</GroupSettingsOption>
						<GroupSettingsOption className="mt-2">
							<CheckboxControl
								checked={isNoScriptEnabled}
								label={createInterpolateElement(
									options_strings.enable_noscript_title,
									{
										custom_component: <code>&lt;noscript&gt;</code>
									}
								)}
								onChange={( value ) => toggleOption( 'no_script', value )}
								disabled={isLoading}
								__nextHasNoMarginBottom={true}
							/>
						</GroupSettingsOption>
					</GroupSettingsContainer>
				</div>
			</BaseControl>

			{isNativeLazyLoadEnabled && (
				<Notice
					type="warning"
					title={''}
					text={options_strings.native_lazy_load_warning}
				/>
			)}

			<hr className="my-8 border-grayish-blue" />

			<ToggleControl
				label={options_strings.toggle_scale}
				help={options_strings.scale_desc}
				checked={isScaleEnabled}
				disabled={isLoading}
				className={classnames({
					'is-disabled': isLoading
				})}
				onChange={( value ) => {
					if ( value ) {
						toggleOption( 'scale', ! value );
					} else {
						setShowModal( DISABLE_OPTION_MODAL_TYPE.scale );
					}
				}}
			/>

			<GroupSettingsContainer>
				<GroupSettingsTitle>
					{options_strings.extended_features}
				</GroupSettingsTitle>
				<ToggleControl
					label={options_strings.enable_bg_lazyload_title}
					help={() => (
						<p
							dangerouslySetInnerHTML={{
								__html: options_strings.enable_bg_lazyload_desc
							}}
						/>
					)}
					checked={isBGReplacerEnabled}
					disabled={isLoading}
					className={classnames( 'text-sm', {
						'is-disabled': isLoading
					})}
					onChange={( value ) => toggleOption( 'bg_replacer', value )}
				/>

				{isBGReplacerEnabled && (
					<>
						<TextareaControl
							id="optml-css-watchers"
							placeholder={
								options_strings.watch_placeholder_lazyload +
								'\n' +
								options_strings.watch_placeholder_lazyload_example
							}
							value={settings.watchers}
							onChange={( value ) => updateValue( 'watchers', value )}
							help={options_strings.watch_desc_lazyload}
						/>
					</>
				)}

				<ToggleControl
					label={options_strings.enable_video_lazyload_title}
					help={() => (
						<p
							dangerouslySetInnerHTML={{
								__html: options_strings.enable_video_lazyload_desc
							}}
						/>
					)}
					checked={isVideoLazyLoadEnabled}
					disabled={isLoading}
					className={classnames( 'mt-8', {
						'is-disabled': isLoading
					})}
					onChange={( value ) => toggleOption( 'video_lazyload', value )}
					__nextHasNoMarginBottom={true}
				/>
			</GroupSettingsContainer>
		</>
	);
};

export default Lazyload;
