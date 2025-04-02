/**
 * External dependencies.
 */
import classnames from 'classnames';

import ReactCompareImage from 'react-compare-image';

import { rotateRight } from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button,
	Icon,
	RangeControl,
	ToggleControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { sampleRate } from '../../../utils/api';

import ProgressBar from '../../components/ProgressBar';

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
			isLoading: isLoading()
		};
	});

	const isNetworkOptimizationEnabled = 'disabled' !== settings[ 'network_optimization' ];
	const isCDNEnabled = 'disabled' !== settings.cdn;
	const isAVIFEnabled = 'disabled' !== settings.avif;
	const isStripMetadataEnabled = 'disabled' !== settings[ 'strip_metadata' ];
	const isAutoQualityEnabled = 'disabled' !== settings.autoquality;
	const isBestFormatEnabled = 'disabled' !== settings[ 'best_format' ];

	const isRetinaEnabled = 'disabled' !== settings[ 'retina_images' ];
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
				quality: settings.quality,
				force: 'yes'
			},
			() => setIsSampleLoading( false )
		);
	};

	const getQuality = value => {
		if ( 'number' === typeof value ) {
			return value;
		}

		if ( 'auto' === value ) {
			return 90;
		}

		if ( 'high_c' === value ) {
			return 90;
		}

		if ( 'medium_c' === value ) {
			return 75;
		}

		if ( 'low_c' === value ) {
			return 55;
		}

		return 90;
	};

	const updateQuality = value => {
		setCanSave( true );
		const data = { ...settings };
		data.quality = value;
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
				label={ optimoleDashboardApp.strings.options_strings.best_format_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.best_format_desc } } /> }
				checked={ isBestFormatEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'best_format', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_retina_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_retina_desc } } /> }
				checked={ isRetinaEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'retina_images', value ) }
			/> 
			<hr className="my-8 border-grayish-blue"/> 
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_network_opt_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_network_opt_desc } } /> }
				checked={ isNetworkOptimizationEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'network_optimization', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.toggle_cdn }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.cdn_desc } } /> }
				checked={ isCDNEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'cdn', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>


			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_avif_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_avif_desc } } /> }
				checked={ isAVIFEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'avif', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.strip_meta_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.strip_meta_desc } } /> }
				checked={ isStripMetadataEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
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
					help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.ml_quality_desc } } /> }
					checked={ isAutoQualityEnabled }
					disabled={ isLoading }
					className={ classnames(
						{
							'is-disabled': isLoading
						}
					) }
					onChange={ value => updateOption( 'autoquality', value ) }
				/>
			</BaseControl>

			{ ! isAutoQualityEnabled && (
				<>
					<RangeControl
						value={ getQuality( settings.quality ) }
						min={ 50 }
						max={ 100 }
						step={ 1 }
						withInputField={ false }
						marks={ [
							{
								value: 55,
								label: optimoleDashboardApp.strings.options_strings.low_q_title
							},
							{
								value: 75,
								label: optimoleDashboardApp.strings.options_strings.medium_q_title
							},
							{
								value: 90,
								label: optimoleDashboardApp.strings.options_strings.high_q_title
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
									{ ( sampleImages.id && 0 < sampleImages.original_size ) && (
										<div>
											{ 0 < getCompressionRatio() ? (
												<p className="text-base">{ 100 - getCompressionRatio() }% { optimoleDashboardApp.strings.latest_images.smaller }</p>
											) : (
												<p className="text-base">{ optimoleDashboardApp.strings.latest_images.same_size }</p>
											) }

											<ProgressBar
												max={ 100 }
												value={ 100 - getCompressionRatio() }
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

									{ ( ! ( sampleImages.id && 0 < sampleImages.original_size ) && 0 > sampleImages.id ) && (
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
