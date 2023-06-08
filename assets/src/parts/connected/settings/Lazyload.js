/**
 * External dependencies.
 */
import classnames from "classnames";

/**
 * WordPress dependencies.
 */
import {
	__experimentalNumberControl as NumberControl,
	BaseControl,
	TextareaControl,
	ToggleControl
} from "@wordpress/components";

import { useSelect } from "@wordpress/data";

const Lazyload = ({
	settings,
	setSettings,
	setCanSave,
}) => {
	const { isLoading } = useSelect( select => {
		const { isLoading } = select( 'optimole' );

		return {
			isLoading: isLoading(),
		};
	} );

	const isLazyloadPlaceholderEnabled = settings[ 'lazyload_placeholder' ] !== 'disabled';
	const isNativeLazyloadEnabled = settings[ 'native_lazyload' ] !== 'disabled';
	const isScaleEnabled = settings[ 'scale' ] === 'disabled';
	const isBGReplacerEnabled = settings[ 'bg_replacer' ] !== 'disabled';
	const isVideoLazyloadEnabled = settings[ 'video_lazyload' ] !== 'disabled';
	const isNoScriptEnabled = settings[ 'no_script' ] !== 'disabled';

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

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_lazyload_placeholder_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_lazyload_placeholder_desc }
				checked={ isLazyloadPlaceholderEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'lazyload_placeholder', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				label={ optimoleDashboardApp.strings.options_strings.exclude_first_images_title }
				help={ optimoleDashboardApp.strings.options_strings.exclude_first_images_desc }
			>
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
				help={ optimoleDashboardApp.strings.options_strings.native_desc }
				checked={ isNativeLazyloadEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'native_lazyload', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_scale }
				help={ optimoleDashboardApp.strings.options_strings.scale_desc }
				checked={ isScaleEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'scale', ! value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_bg_lazyload_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_bg_lazyload_desc }
				checked={ isBGReplacerEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'bg_replacer', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_video_lazyload_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_video_lazyload_desc }
				checked={ isVideoLazyloadEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'video_lazyload', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_noscript_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_noscript_desc }
				checked={ isNoScriptEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'no_script', value ) }
			/>

			{ isBGReplacerEnabled && (
				<>
					<hr className="my-8 border-grayish-blue"/>

					<TextareaControl
						label={ optimoleDashboardApp.strings.options_strings.watch_title_lazyload }
						help={ optimoleDashboardApp.strings.options_strings.watch_desc_lazyload }
						placeholder="e.g: .image, #item-id, div.with-background-image"
						value={ settings[ 'watchers' ] }
						onChange={ value => updateValue( 'watchers', value ) }
					/>
				</>
			) }
		</>
	);
};

export default Lazyload;
