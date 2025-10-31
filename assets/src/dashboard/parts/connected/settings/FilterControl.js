
/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button,
	SelectControl,
	TextControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { useState } from '@wordpress/element';

const FILTER_TYPES = {
	EXT: 'extension',
	URL: 'page_url',
	URL_MATCH: 'page_url_match',
	FILENAME: 'filename',
	CLASS: 'class'
};

const FILTER_OPTIONS = [
	{
		label: optimoleDashboardApp.strings.options_strings.filter_filename,
		value: FILTER_TYPES.FILENAME
	},
	{
		label: optimoleDashboardApp.strings.options_strings.filter_ext,
		value: FILTER_TYPES.EXT
	},
	{
		label: optimoleDashboardApp.strings.options_strings.filter_url,
		value: FILTER_TYPES.URL
	},
	{
		label: optimoleDashboardApp.strings.options_strings.filter_class,
		value: FILTER_TYPES.CLASS
	}
];

const FILTER_VIEW = [
	{
		label: optimoleDashboardApp.strings.options_strings.exclude_filename_desc,
		value: FILTER_TYPES.FILENAME
	},
	{
		label: optimoleDashboardApp.strings.options_strings.exclude_ext_desc,
		value: FILTER_TYPES.EXT
	},
	{
		label: optimoleDashboardApp.strings.options_strings.exclude_url_desc,
		value: FILTER_TYPES.URL
	},
	{
		label: optimoleDashboardApp.strings.options_strings.exclude_url_match_desc,
		value: FILTER_TYPES.URL_MATCH
	},
	{
		label: optimoleDashboardApp.strings.options_strings.exclude_class_desc,
		value: FILTER_TYPES.CLASS
	}
];

const EXT_OPTIONS = [
	{
		label: '.SVG',
		value: 'svg'
	},
	{
		label: '.JPG',
		value: 'jpg'
	},
	{
		label: '.PNG',
		value: 'png'
	},
	{
		label: '.GIF',
		value: 'gif'
	},
	{
		label: '.WEBP',
		value: 'webp'
	}
];

const FilterControl = ({
	label,
	help,
	type,
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

	const defaultFilterOperator = optimoleDashboardApp.strings.options_strings.filter_operator_contains;
	const [ filterType, setFilterType ] = useState( FILTER_TYPES.FILENAME );
	const [ filterOperator, setFilterOperator ] = useState( optimoleDashboardApp.strings.options_strings.filter_operator_contains );
	const [ filterValue, setFilterValue ] = useState( '' );
	const [ filterMatchType, setFilterMatchType ] = useState( defaultFilterOperator );
	const [ lengthError, setLengthError ] = useState( false );

	const changeFilterType = value => {
		let selectedValue = '';

		if ( value === FILTER_TYPES.EXT ) {
			selectedValue = 'svg';
			setFilterOperator( optimoleDashboardApp.strings.options_strings.filter_operator_is );
		}

		if ( value === FILTER_TYPES.FILENAME ) {
			setFilterOperator( optimoleDashboardApp.strings.options_strings.filter_operator_contains );
		}

		if ( value === FILTER_TYPES.CLASS ) {
			setFilterOperator( optimoleDashboardApp.strings.options_strings.filter_operator_contains );
		}

		setLengthError( false );
		setFilterValue( selectedValue );
		setFilterType( value );
		setFilterMatchType( filterValue );
	};

	const updateFilterValue = value => {
		setFilterValue( value );
		setLengthError( false );
	};

	const addFilter = () => {
		if ( 3 > filterValue.length ) {
			setLengthError( true );

			return;
		}

		const filters = { ...settings.filters };
		let selectedFilterType = filterType;

		if ( filterType === FILTER_TYPES.URL && 'matches' === filterMatchType ) {
			selectedFilterType = FILTER_TYPES.URL_MATCH;
		}

		if ( ! filters[ type ] || 'object' !== typeof filters[ type ]) {
			filters[ type ] = {};
		}

		if ( ! filters[ type ][ selectedFilterType ] || Array.isArray( filters[ type ][ selectedFilterType ]) ) {
			filters[ type ][ selectedFilterType ] = {};
		}

		filters[ type ][ selectedFilterType ][ filterValue ] = true;

		setSettings({
			...settings,
			filters
		});

		if ( filterType !== FILTER_TYPES.EXT ) {
			setFilterValue( '' );
		}

		setCanSave( true );

		setFilterValue( '' );
		setFilterType( FILTER_TYPES.FILENAME );
		setFilterMatchType( defaultFilterOperator );
	};

	const hasItems = (
		(
			0 < Object.keys( settings.filters[type][FILTER_TYPES.EXT]).length &&
			Object.values( settings.filters[type][FILTER_TYPES.EXT]).some(
				value => false !== value
			)
		) ||
		(
			0 < Object.keys( settings.filters[type][FILTER_TYPES.CLASS]).length &&
			Object.values( settings.filters[type][FILTER_TYPES.CLASS]).some(
				value => false !== value
			)
		) ||
		(
			0 < Object.keys( settings.filters[type][FILTER_TYPES.URL]).length &&
			Object.values( settings.filters[type][FILTER_TYPES.URL]).some(
				value => false !== value
			)
		) ||
		(
			0 < Object.keys( settings.filters[type][FILTER_TYPES.URL_MATCH]).length &&
			Object.values( settings.filters[type][FILTER_TYPES.URL_MATCH]).some(
				value => false !== value
			)
		) ||
		(
			0 < Object.keys( settings.filters[type][FILTER_TYPES.FILENAME]).length &&
			Object.values( settings.filters[type][FILTER_TYPES.FILENAME]).some(
				value => false !== value
			)
		)
	);


	const removeFilter = ( group, value ) => {
		const filters = { ...settings.filters };
		filters[ type ][ group ][ value ] = false;

		setSettings({
			...settings,
			filters
		});

		setCanSave( true );
	};

	return (
		<>
			<BaseControl
				label={ label }
				help={ filterType === FILTER_TYPES.URL && optimoleDashboardApp.strings.options_strings.filter_helper }
			>
				<p
					className="components-base-control__help mt-0"
					dangerouslySetInnerHTML={ { __html: help } }
				/>

				<div className="flex flex-col md:flex-row p-6 my-3 bg-light-blue border border-blue-300 rounded-md items-center justify-between gap-8">
					<div className="flex justify-start items-center gap-4">
						<SelectControl
							value={ filterType }
							options={ FILTER_OPTIONS }
							onChange={ changeFilterType }
							className="optml__select"
							__nextHasNoMarginBottom={ true }
						/>

						{ filterType === FILTER_TYPES.URL ? (
							<SelectControl
								value={ filterMatchType }
								options={ [
									{
										label: optimoleDashboardApp.strings.options_strings.filter_operator_contains,
										value: optimoleDashboardApp.strings.options_strings.filter_operator_contains
									},
									{
										label: optimoleDashboardApp.strings.options_strings.filter_operator_matches,
										value: optimoleDashboardApp.strings.options_strings.filter_operator_matches
									}
								] }
								onChange={ setFilterMatchType }
								className="optml__select"
								__nextHasNoMarginBottom={ true }
							/>
						) : (
							<p className="m-0">{ filterOperator }</p>
						) }

						{ filterType === FILTER_TYPES.EXT ? (
							<SelectControl
								value={ filterValue }
								options={ EXT_OPTIONS }
								onChange={ setFilterValue }
								className="optml__select"
								__nextHasNoMarginBottom={ true }
							/>
						) : (
							<TextControl
								value={ filterValue }
								placeholder={ 'matches' === filterMatchType ? optimoleDashboardApp.strings.path : optimoleDashboardApp.strings.word }
								onChange={ updateFilterValue }
								className="optml__input"
							/>
						) }
					</div>

					<Button
						variant="primary"
						isBusy={ isLoading }
						disabled={ isLoading }
						className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
						onClick={ addFilter }
					>
						{ optimoleDashboardApp.strings.options_strings.add_filter }
					</Button>
				</div>
				{ lengthError && (
					<p className="text-red-500 bg-red-100 p-2 rounded-md border border-solid border-red-200">{ optimoleDashboardApp.strings.options_strings.filter_length_error }</p>
				)}
			</BaseControl>

			{ hasItems && (
				<BaseControl
					label={ 'optimize' === type ? optimoleDashboardApp.strings.options_strings.active_optimize_exclusions : optimoleDashboardApp.strings.options_strings.active_lazyload_exclusions }
					className="py-4"
				>
					{ FILTER_VIEW.map( filter => {
						return Object.keys( settings.filters[ type ][ filter.value ]).filter( i => false !== settings.filters[ type ][ filter.value ][ i ]).map( i => (
							<div
								key={ i }
								className="flex p-3 my-3 bg-light-blue border border-blue-300 rounded-md items-center justify-between gap-8"
							>
								<div className="flex items-center gap-1">
									<p className="m-0 inline-block" dangerouslySetInnerHTML={ { __html: filter.label } }/>
									<b>{ i }</b>
								</div>

								<Button
									variant="default"
									icon="trash"
									disabled={ isLoading }
									onClick={ () => removeFilter( filter.value, i ) }
								/>
							</div>
						) );
					}) }
				</BaseControl>
			) }
		</>
	);
};

export default FilterControl;
