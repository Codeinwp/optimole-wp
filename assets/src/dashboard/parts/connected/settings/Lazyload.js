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
	Popover
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

const Lazyload = ({
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

	const isLazyloadPlaceholderEnabled = 'disabled' !== settings[ 'lazyload_placeholder' ];
	const isNativeLazyloadEnabled = 'disabled' !== settings[ 'native_lazyload' ];
	const isScaleEnabled = 'disabled' === settings.scale;
	const isBGReplacerEnabled = 'disabled' !== settings[ 'bg_replacer' ];
	const isVideoLazyloadEnabled = 'disabled' !== settings[ 'video_lazyload' ];
	const isNoScriptEnabled = 'disabled' !== settings[ 'no_script' ];
	const placeholderColor = settings[ 'placeholder_color' ];

	const [ phPicker, setPhPicker ] = useState( false );

	const updateOption = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		data[ option ] = value ? 'enabled' : 'disabled';
		setSettings( data );
	};

	const updateValue = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		data[ option ] = value;
		setSettings( data );
	};

	const setColor = ( value ) => {
		updateValue( 'placeholder_color', value );
	};

	return (
		<>
			<BaseControl>
				<ToggleControl
					label={ optimoleDashboardApp.strings.options_strings.enable_lazyload_placeholder_title }
					help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_lazyload_placeholder_desc } } /> }
					checked={ isLazyloadPlaceholderEnabled }
					disabled={ isLoading }
					className={ classnames(
						{
							'is-disabled': isLoading
						}
					) }
					onChange={ value => updateOption( 'lazyload_placeholder', value ) }
				/>

				{ isLazyloadPlaceholderEnabled &&
					<div className="relative inline-block">
						<Button
							disabled={isLoading}
							className="gap-3 my-2 py-3 h-auto rounded text-inherit border-gray-400 border-2 border-solid font-medium"
							onClick={() => {
								setPhPicker( ! phPicker );
							}}
						>
							<ColorIndicator colorValue={placeholderColor}/>
							<span>{optimoleDashboardApp.strings.options_strings.lazyload_placeholder_color}</span>
						</Button>

						{ phPicker &&
							<Popover
								placement="bottom"
								position={'middle center'}
								variant={'unstyled'}
								className={'shadow-md border-grayish-blue border border-solid rounded bg-white p-2'}
								onFocusOutside={() => setPhPicker( false )}
							>
								<ColorPicker
									color={placeholderColor}
									onChange={setColor}
									enableAlpha
									defaultValue=""
								/>
								<Button isDestructive={true}
									className={'w-full text-center flex justify-center' }
									variant={'secondary'} onClick={() => {
										setColor( '' );
									}}>{optimoleDashboardApp.strings.options_strings.clear}</Button>
							</Popover>
						}
					</div>
				}
			</BaseControl>

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				label={ optimoleDashboardApp.strings.options_strings.exclude_first_images_title }
			>
				<p
					className="components-base-control__help mt-0"
					dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.exclude_first_images_desc } }
				/>

				<div className="flex gap-8">
					<NumberControl
						label={ optimoleDashboardApp.strings.options_strings.exclude_first }
						labelPosition="side"
						value={ settings[ 'skip_lazyload_images' ] }
						type="number"
						min={ 0 }
						className="basis-1/2 md:basis-1/3"
						onChange={ value => updateValue( 'skip_lazyload_images', value ) }
					/>
				</div>
			</BaseControl>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_native }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.native_desc } } /> }
				checked={ isNativeLazyloadEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'native_lazyload', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_scale }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.scale_desc } } /> }
				checked={ isScaleEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'scale', ! value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>



			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_video_lazyload_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_video_lazyload_desc } } /> }
				checked={ isVideoLazyloadEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'video_lazyload', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_noscript_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_noscript_desc } } /> }
				checked={ isNoScriptEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'no_script', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_bg_lazyload_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_bg_lazyload_desc } } /> }
				checked={ isBGReplacerEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'bg_replacer', value ) }
			/>
			{ isBGReplacerEnabled && (
				<>
					<hr className="my-8 border-grayish-blue"/>

					<BaseControl
						label={ optimoleDashboardApp.strings.options_strings.watch_title_lazyload }
						id="optml-css-watchers" // We add this to insure that the label is clickable
					>
						<p
							className="components-base-control__help mt-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.watch_desc_lazyload } }
						/>

						<TextareaControl
							id="optml-css-watchers"
							placeholder={ optimoleDashboardApp.strings.options_strings.watch_placeholder_lazyload }
							value={ settings.watchers }
							onChange={ value => updateValue( 'watchers', value ) }
						/>
					</BaseControl>
				</>
			) }
		</>
	);
};

export default Lazyload;
