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
	CheckboxControl
} from '@wordpress/components';

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
			const setting = new Set(
				( settings?.lazyload_type ?? '' )
					?.split( '|' )
					.filter( ( i ) => 'viewport' === i || 'fixed' === i ) ?? []
			);
			if ( value ) {
				setting.add( slug );
			} else {
				setting.delete( slug );
			}
			updateValue( 'lazyload_type', Array.from( setting ).toSorted().join( '|' ) );
		},
		[ settings?.lazyload_type ]
	);

	const NotRecommendedWarning = useCallback( ( props ) => {
		return (
			<>
				{props.label}
				<span className="ml-4 text-xs font-bold p-1 rounded bg-yellow-400 text-yellow-800 uppercase">
					{optimoleDashboardApp.strings.options_strings.not_recommended}
				</span>
			</>
		);
	}, []);

	const Tag = useCallback(
		({ text, disabled }) => (
			<span
				className={classnames(
					'inline-block  text-xs px-2 py-1 rounded mr-2 mt-2 font-medium',
					{
						'bg-gray-200 text-gray-800 line-through': disabled,
						'bg-blue-200 text-blue-800': ! disabled
					}
				)}
			>
				{text}
			</span>
		),
		[]
	);

	const DescriptionWithTags = useCallback(
		({ text, tags }) => {
			return (
				<>
					{text}
					<div className="mt-2">
						{tags.map( ({ text, disabled }) => (
							<Tag key={text} text={text} disabled={disabled} />
						) )}
					</div>
				</>
			);
		},
		[ Tag ]
	);

	if ( ! isLazyLoadEnabled ) {
		return (
			<>
				<ToggleControl
					label={optimoleDashboardApp.strings.options_strings.toggle_lazyload}
					help={optimoleDashboardApp.strings.options_strings.lazyload_desc}
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
				label={optimoleDashboardApp.strings.options_strings.toggle_lazyload}
				help={optimoleDashboardApp.strings.options_strings.lazyload_desc}
				checked={isLazyLoadEnabled}
				disabled={isLoading}
				className={classnames({
					'is-disabled': isLoading
				})}
				onChange={() => setShowModal( DISABLE_OPTION_MODAL_TYPE.lazyLoad )}
			/>
			{
				showModal && (
					<Modal
						icon="warning"
						variant="warning"
						labels={{
							title: optimoleDashboardApp.strings.options_strings.performance_impact_alert_title,
							description: 'scale' === showModal ? optimoleDashboardApp.strings.options_strings.performance_impact_alert_scale_desc : optimoleDashboardApp.strings.options_strings.performance_impact_alert_lazy_desc,
							action: optimoleDashboardApp.strings.options_strings.performance_impact_alert_action_label,
							secondaryAction: optimoleDashboardApp.strings.options_strings.performance_impact_alert_secondary_action_label
						}}
						onRequestClose={ () => setShowModal( false ) }
						onConfirm={ () => {
							window?.formbricks?.track( 'disable_lazy_load_feature', {
								hiddenFields: {
									feature: `${ showModal }`
								}
							});

							if ( DISABLE_OPTION_MODAL_TYPE.javascriptLoading === showModal ) {
								toggleOption( 'native_lazyload', true );
							} else if ( DISABLE_OPTION_MODAL_TYPE.scale === showModal ) {
								toggleOption( 'scale', false );
							} else if ( DISABLE_OPTION_MODAL_TYPE.lazyLoad === showModal ) {
								toggleOption( 'lazyload', false );
							}

							setShowModal( false );
						} }
						onSecondaryAction={ () => setShowModal( false ) }
					/>
				)
			}
			<hr className="my-8 border-grayish-blue" />
			<BaseControl
				className="mt-2"
				label={
					optimoleDashboardApp.strings.options_strings.lazyload_behaviour_title
				}
			>
				<div className="ml-4">
					<RadioBoxes
						label={''}
						options={[
							{
								title:
									optimoleDashboardApp.strings.options_strings
										.smart_loading_title,
								description: (
									<DescriptionWithTags
										text={
											optimoleDashboardApp.strings.options_strings
												.smart_loading_desc
										}
										tags={[
											{
												text: optimoleDashboardApp.strings.options_strings
													.viewport_detection
											},
											{
												text: optimoleDashboardApp.strings.options_strings
													.placeholders_color
											},
											{
												text: optimoleDashboardApp.strings.options_strings
													.auto_scaling
											}
										]}
									/>
								),
								value: 'disabled'
							},
							{
								title: (
									<NotRecommendedWarning label={'Native Browser Loading'} />
								),
								description: (
									<DescriptionWithTags
										text={'Uses the browser\'s build-in "lazy" behavior'}
										tags={[
											{
												text: optimoleDashboardApp.strings.options_strings
													.viewport_detection
											},
											{
												text: optimoleDashboardApp.strings.options_strings
													.lightweight_native
											},
											{
												text: optimoleDashboardApp.strings.options_strings
													.auto_scaling,
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
							{
								optimoleDashboardApp.strings.options_strings
									.lazyload_behaviour_title
							}{' '}
							({optimoleDashboardApp.strings.options_strings.global_option})
						</GroupSettingsTitle>
						<GroupSettingsOption className="flex flex-row items-center gap-4">
							<CheckboxControl
								className="optml-skip-lazy-load-images"
								label={
									optimoleDashboardApp.strings.options_strings
										.lazyload_behaviour_fixed
								}
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
									__nextHasNoMarginBottom={true}
								/>
							</div>
						</GroupSettingsOption>
						<GroupSettingsOption className="mt-2">
							<CheckboxControl
								label={
									optimoleDashboardApp.strings.options_strings
										.lazyload_behaviour_viewport
								}
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
								text={
									optimoleDashboardApp.strings.options_strings
										.viewport_skip_images_notice
								}
							/>
						)}
					</GroupSettingsContainer>

					<GroupSettingsContainer>
						<GroupSettingsTitle>
							{optimoleDashboardApp.strings.options_strings.visual_settings}
						</GroupSettingsTitle>
						<GroupSettingsOption>
							<div className="grow" htmlFor="optml-lazyload-placeholder">
								<CheckboxControl
									label={
										optimoleDashboardApp.strings.options_strings
											.enable_lazyload_placeholder_title
									}
									checked={isLazyLoadPlaceholderEnabled}
									onChange={( value ) =>
										toggleOption( 'lazyload_placeholder', value )
									}
									disabled={isLoading}
									__nextHasNoMarginBottom={true}
								/>
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
												{optimoleDashboardApp.strings.options_strings.clear}
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
									optimoleDashboardApp.strings.options_strings
										.enable_noscript_title,
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

			{
				isNativeLazyLoadEnabled && (
					<Notice
						type="warning"
						title={''}
						text={
							optimoleDashboardApp.strings.options_strings
								.native_lazy_load_warning
						}
					/>
				)
			}

			<hr className="my-8 border-grayish-blue" />

			<ToggleControl
				label={optimoleDashboardApp.strings.options_strings.toggle_scale}
				help={optimoleDashboardApp.strings.options_strings.scale_desc}
				checked={isScaleEnabled}
				disabled={isLoading}
				className={classnames({
					'is-disabled': isLoading
				})}
				onChange={() => setShowModal( DISABLE_OPTION_MODAL_TYPE.scale ) }
			/>

			<GroupSettingsContainer>
				<GroupSettingsTitle>
					{optimoleDashboardApp.strings.options_strings.extended_features}
				</GroupSettingsTitle>
				<ToggleControl
					label={
						optimoleDashboardApp.strings.options_strings
							.enable_bg_lazyload_title
					}
					help={() => (
						<p
							dangerouslySetInnerHTML={{
								__html:
									optimoleDashboardApp.strings.options_strings
										.enable_bg_lazyload_desc
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
								optimoleDashboardApp.strings.options_strings
									.watch_placeholder_lazyload +
								'\n' +
								optimoleDashboardApp.strings.options_strings
									.watch_placeholder_lazyload_example
							}
							value={settings.watchers}
							onChange={( value ) => updateValue( 'watchers', value )}
							help={
								optimoleDashboardApp.strings.options_strings.watch_desc_lazyload
							}
						/>
					</>
				)}

				<ToggleControl
					label={
						optimoleDashboardApp.strings.options_strings
							.enable_video_lazyload_title
					}
					help={() => (
						<p
							dangerouslySetInnerHTML={{
								__html:
									optimoleDashboardApp.strings.options_strings
										.enable_video_lazyload_desc
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
