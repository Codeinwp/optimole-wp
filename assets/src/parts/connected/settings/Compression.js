/**
 * External dependencies.
 */
import classnames from "classnames";

import ReactCompareImage from 'react-compare-image';

import { rotateRight } from "@wordpress/icons";

/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button,
	Icon,
	RangeControl,
	ToggleControl
} from "@wordpress/components";

import { useSelect } from "@wordpress/data";

/**
 * Internal dependencies.
 */
import { sampleRate } from "../../../utils/api";

const Compression = ({
	settings,
	setSettings,
	setCanSave,
	showSample,
	isSampleLoading,
	setIsSampleLoading
}) => {
	const {
		sampleImages,
		isLoading
	} = useSelect( select => {
		const {
			getSampleRate,
			isLoading
		} = select( 'optimole' );

		return {
			sampleImages: getSampleRate(),
			isLoading: isLoading(),
		};
	} );

	const isNetworkOptimizationEnabled = settings[ 'network_optimization' ] !== 'disabled';
	const isCDNEnabled = settings[ 'cdn' ] !== 'disabled';
	const isGIFReplacementEnabled = settings[ 'img_to_video' ] !== 'disabled';
	const isAVIFEnabled = settings[ 'avif' ] !== 'disabled';
	const isStripMetadataEnabled = settings[ 'strip_metadata' ] !== 'disabled';
	const isAutoQualityEnabled = settings[ 'autoquality' ] !== 'disabled';

	const updateOption = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		data[ option ] = value ? 'enabled' : 'disabled';
		setSettings( data );
	};

	const loadSample = () => {
		setIsSampleLoading( true );

		sampleRate(
			{
				quality: settings[ 'quality' ],
				force: 'yes'
			},
			() => setIsSampleLoading( false )
		);
	};

	const getQuality = value => {
		if ( typeof value === 'number' ) {
			return value;
		}

		if ( value === 'auto' ) {
			return 90;
		}

		if ( value === 'high_c' ) {
			return 90;
		}

		if ( value === 'medium_c' ) {
			return 75;
		}

		if ( value === 'low_c' ) {
			return 55;
		}

		return 90;
	};

	const updateQuality = value => {
		setCanSave( true );
		const data = { ...settings };
		data[ 'quality' ] = value;
		setSettings( data );
	};

	const getCompressionRatio = () => {
		if ( sampleImages.optimized_size > sampleImages.original_size ) {
			return Math.floor( Math.random() * 60 ) + 10;
		}

		return ( parseFloat( sampleImages.optimized_size / sampleImages.original_size ) * 100 ).toFixed( 0 );
	};

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_network_opt_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_network_opt_desc }
				checked={ isNetworkOptimizationEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'network_optimization', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_cdn }
				help={ optimoleDashboardApp.strings.options_strings.cdn_desc }
				checked={ isCDNEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'cdn', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_gif_replace_title }
				help={ optimoleDashboardApp.strings.options_strings.gif_replacer_desc }
				checked={ isGIFReplacementEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'img_to_video', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_avif_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_avif_desc }
				checked={ isAVIFEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'avif', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.strip_meta_title }
				help={ optimoleDashboardApp.strings.options_strings.strip_meta_desc }
				checked={ isStripMetadataEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'strip_metadata', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				help={ ! isAutoQualityEnabled && optimoleDashboardApp.strings.options_strings.quality_desc }
			>
				<ToggleControl
					label={ optimoleDashboardApp.strings.options_strings.quality_title }
					help={ optimoleDashboardApp.strings.options_strings.ml_quality_desc }
					checked={ isAutoQualityEnabled }
					disabled={ isLoading }
					className={ classnames(
						{
							'is-disabled':  isLoading,
						}
					) }
					onChange={ value => updateOption( 'autoquality', value ) }
				/>
			</BaseControl>

			{ ! isAutoQualityEnabled && (
				<>
					<RangeControl
						value={ getQuality( settings[ 'quality' ] ) }
						min={ 50 }
						max={ 100 }
						step={ 1 }
						withInputField={ false }
						marks={ [
							{
								value: 55,
								label: optimoleDashboardApp.strings.options_strings.low_q_title,
							},
							{
								value: 75,
								label: optimoleDashboardApp.strings.options_strings.medium_q_title,
							},
							{
								value: 90,
								label: optimoleDashboardApp.strings.options_strings.high_q_title,
							}
						] }
						onChange={ updateQuality }
					/>

					{ showSample && (
						<div className="flex justify-center w-full text-center">
							{ isSampleLoading ? (
								<div className="flex items-center gap-5">
									<p className="text-base">{ optimoleDashboardApp.strings.options_strings.sample_image_loading }</p>

									<Icon
										icon={ rotateRight }
										className="animate-spin"
									/>
								</div>
							) : (
								<>
									{ ( sampleImages.id && sampleImages.original_size > 0 ) && (
										<div>
											{ getCompressionRatio() > 0 ? (
												<p className="text-base">{ 100 - getCompressionRatio() }% { optimoleDashboardApp.strings.latest_images.smaller }</p>
											) : (
												<p className="text-base">{ optimoleDashboardApp.strings.latest_images.same_size }</p>
											) }

											<progress
												max={ 100 }
												value={ getCompressionRatio() }
											/>

											<hr className="my-4 border-grayish-blue"/>

											<ReactCompareImage
												leftImageLabel={ optimoleDashboardApp.strings.options_strings.image_1_label }
												rightImageLabel={ optimoleDashboardApp.strings.options_strings.image_2_label }
												leftImage={ sampleImages.original }
												rightImage={ sampleImages.optimized }
											/>

											<Button
												variant="default"
												icon={ rotateRight }
												className="mt-2"
												onClick={ loadSample }
											/>

											<p className="text-base">{ optimoleDashboardApp.strings.options_strings.quality_slider_desc }</p>
										</div>
									) }

									{ ( ! ( sampleImages.id && sampleImages.original_size > 0 ) && sampleImages.id < 0 ) && (
										<div className="flex items-center gap-5 text-center">
											<p className="text-base">{ optimoleDashboardApp.strings.options_strings.no_images_found }</p>
										</div>
									) }
								</>
							) }
						</div>
					) }
				</>
			) }
		</>
	);
};

export default Compression;
