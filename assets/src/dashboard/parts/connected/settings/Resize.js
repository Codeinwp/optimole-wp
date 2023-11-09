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
	Button,
	TextControl,
	ToggleControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

const Resize = ({
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

	const [ width, setWidth ] = useState( '' );
	const [ height, setHeight ] = useState( '' );

	const isSmartResizeEnabled = 'disabled' !== settings[ 'resize_smart' ];
	const isRetinaEnabled = 'disabled' !== settings[ 'retina_images' ];
	const isLimitDimensionsEnabled = 'disabled' !== settings[ 'limit_dimensions' ];

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

	const addSize = () => {
		if ( ! width || ! height || isNaN( width ) || isNaN( height ) ) {
			return;
		}

		const size = 'optimole_' + width + '_' + height + '_crop';

		const newSizes = {
			...settings[ 'defined_image_sizes' ],
			[ size ]: {
				width,
				height
			}
		};

		setCanSave( true );
		const data = { ...settings };
		data[ 'defined_image_sizes' ] = newSizes;
		setSettings( data );

		setWidth( '' );
		setHeight( '' );
	};

	const removeSize = size => {
		const newSizes = {
			...settings[ 'defined_image_sizes' ],
			[ size ]: 'remove'
		};

		setCanSave( true );
		const data = { ...settings };
		data[ 'defined_image_sizes' ] = newSizes;
		setSettings( data );
	};

	const imageSizes = Object.keys( settings[ 'defined_image_sizes' ]).filter( size => 'object' === typeof settings[ 'defined_image_sizes' ][ size ]);

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_resize_smart_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_resize_smart_desc } } /> }
				checked={ isSmartResizeEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'resize_smart', value ) }
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
				label={ optimoleDashboardApp.strings.options_strings.enable_limit_dimensions_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_limit_dimensions_desc } } /> }
				checked={ isLimitDimensionsEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ value => updateOption( 'limit_dimensions', value ) }
			/>

			{ isLimitDimensionsEnabled && (
				<BaseControl>
					<div className="flex gap-8">
						<NumberControl
							label={ optimoleDashboardApp.strings.options_strings.width_field }
							labelPosition="side"
							value={ settings[ 'limit_width' ] }
							type="number"
							min={ 100 }
							max={ 10000 }
							className="basis-1/2 sm:basis-1/4"
							onChange={ value => updateValue( 'limit_width', value ) }
						/>

						<NumberControl
							label={ optimoleDashboardApp.strings.options_strings.height_field }
							labelPosition="side"
							value={ settings[ 'limit_height' ] }
							type="number"
							min={ 100 }
							max={ 10000 }
							className="basis-1/2 sm:basis-1/4"
							onChange={ value => updateValue( 'limit_height', value ) }
						/>
					</div>

					<div className="flex gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 my-5">
						<p
							className="m-0"
							dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_limit_dimensions_notice } }
						/>
					</div>
				</BaseControl>
			) }

			<hr className="my-8 border-grayish-blue"/>

			<BaseControl
				label={ optimoleDashboardApp.strings.options_strings.add_image_size_desc }
			>
				<p
					className="components-base-control__help mt-0"
					dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.image_size_notice } }
				/>

				<div className="flex flex-col sm:flex-row p-6 bg-light-blue border border-blue-300 rounded-md items-stretch sm:items-center gap-8">
					<TextControl
						label={ optimoleDashboardApp.strings.options_strings.width_field }
						placeholder={ optimoleDashboardApp.strings.options_strings.width_field }
						hideLabelFromVision={ true }
						type="number"
						min={ 1 }
						value={ width }
						onChange={ setWidth }
						className="optml__input sm:basis-2/5"
					/>

					<TextControl
						label={ optimoleDashboardApp.strings.options_strings.height_field }
						placeholder={ optimoleDashboardApp.strings.options_strings.height_field }
						value={ height }
						onChange={ setHeight }
						hideLabelFromVision={ true }
						type="number"
						min={ 1 }
						className="optml__input sm:basis-2/5"
					/>

					<Button
						variant="primary"
						isBusy={ isLoading }
						disabled={ isLoading }
						className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
						onClick={ addSize }
					>
						{ optimoleDashboardApp.strings.options_strings.add_image_size_button }
					</Button>
				</div>
			</BaseControl>

			{ 0 < imageSizes.length && (
				<BaseControl
					label={ optimoleDashboardApp.strings.options_strings.image_sizes_title }
					className="pt-4"
				>
					{ imageSizes.map( size => (
						<div
							key={ size }
							className="flex p-3 my-3 bg-light-blue border border-blue-300 rounded-md items-center justify-between gap-8"
						>
							<div className="flex flex-wrap items-center gap-2">
								<div>
									<p className="m-0 inline-block" dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.name } }/> { size }
								</div>
								<p className="m-0"><b>{ optimoleDashboardApp.strings.options_strings.width_field }</b>: { settings[ 'defined_image_sizes' ][ size ].width }</p>
								<p className="m-0"><b>{ optimoleDashboardApp.strings.options_strings.height_field }</b>: { settings[ 'defined_image_sizes' ][ size ].height }</p>
							</div>

							<Button
								variant="default"
								icon="trash"
								disabled={ isLoading }
								onClick={ () => removeSize( size ) }
							/>
						</div>
					) ) }
				</BaseControl>
			) }
		</>
	);
};

export default Resize;
