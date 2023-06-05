/**
 * External dependencies.
 */
import classnames from "classnames";

/**
 * WordPress dependencies.
 */
import {
	BaseControl,
	Button,
	FormTokenField,
	Icon,
	SelectControl,
	ToggleControl
} from "@wordpress/components";

import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import { useState } from "@wordpress/element";

/**
 * Internal dependencies.
 */
import { warning } from '../../../utils/icons'

const OffloadMedia = ({
	settings,
	setSettings,
	setCanSave,
}) => {
	const {
		isLoading,
		loadingSync
	} = useSelect( select => {
		const {
			getLoadingSync,
			isLoading,
		} = select( 'optimole' );

		return {
			isLoading: isLoading(),
			loadingSync: getLoadingSync(),
		};
	} );

	const {
		callSync,
		setErrorMedia
	} = useDispatch( 'optimole' );

	const [ rollback, setRollback ] = useState( true );
	const [ showOffloadDisabled, setShowOffloadDisabled ] = useState( false );
	const [ showOffloadEnabled, setShowOffloadEnabled ] = useState( false );

	const isCloudLibraryEnabled = settings[ 'cloud_images' ] !== 'disabled';
	const isOffloadMediaEnabled = settings[ 'offload_media' ] !== 'disabled';
	const whitelistedDomains = settings[ 'whitelist_domains' ] || [];

	const updateOption = ( option, value ) => {
		setCanSave( true );
		const data = { ...settings };
		data[ option ] = value ? 'enabled' : 'disabled';
		setSettings( data );
	};

	const updateSites = value => {
		setCanSave( true );

		const sites = {};

		const data = { ...settings };

		if ( value.length === 0 ) {
			sites.all = true;
		} else {
			value.forEach( ( site ) => {
				sites[ site ] = true;
			} );
		}

		data[ 'cloud_sites' ] = sites;
		setSettings( data );
	};

	const onChangeOffload = value => {
		setCanSave( true );
		const data = { ...settings };
		data[ 'offload_media' ] = value ? 'enabled' : 'disabled';
		setSettings( data );

		setShowOffloadDisabled( optimoleDashboardApp.site_settings[ 'offload_media' ] === 'enabled' && ! value );
		setShowOffloadEnabled( optimoleDashboardApp.site_settings[ 'offload_media' ] === 'disabled' && !! value );
	};

	const onOffloadMedia = () => {
		setErrorMedia( false );

		callSync( {
			action: 'offload_images',
			images: 'none'
		} );
	};

	return (
		<>
			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_cloud_images_title }
				help={ () => <p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.enable_cloud_images_desc } } /> }
				checked={ isCloudLibraryEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ value => updateOption( 'cloud_images', value ) }
			/>

			<hr className="my-8 border-grayish-blue"/>

			{ isCloudLibraryEnabled && (
				<>
					<BaseControl
						label={ optimoleDashboardApp.strings.options_strings.cloud_site_title }
						help={ optimoleDashboardApp.strings.options_strings.cloud_site_desc }
					>
						<div className="optml__token__base flex p-6 bg-light-blue border border-blue-300 rounded-md items-center gap-8">
							<FormTokenField
								value={Object.keys(settings['cloud_sites']).filter(site => site !== 'all').map(site => site) || []}
								suggestions={whitelistedDomains}
								onChange={updateSites}
								__experimentalExpandOnFocus={true}
								__experimentalShowHowTo={false}
								__experimentalValidateInput={newValue => whitelistedDomains.includes(newValue)}
								className="optml__token" />
						</div>
					</BaseControl>

					<hr className="my-8 border-grayish-blue" />
				</>
			) }

			<ToggleControl
				label={ optimoleDashboardApp.strings.options_strings.enable_offload_media_title }
				help={ optimoleDashboardApp.strings.options_strings.enable_offload_media_desc }
				checked={ isOffloadMediaEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled':  isLoading,
					}
				) }
				onChange={ onChangeOffload }
			/>

			{ showOffloadEnabled && (
				<div className="flex gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 mb-5">
					<p
						className="m-0"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.offload_enable_info_desc } }
					/>
				</div>
			) }

			{ showOffloadDisabled && (
				<div className="flex flex-col gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 mb-5">
					<div className="flex gap-2 items-center">
						<Icon
							icon={ warning } 
							size={ 16 }
						/>

						<p className="font-bold text-base m-0">{ optimoleDashboardApp.strings.options_strings.offload_disable_warning_title }</p>
					</div>

					<p
						className="m-0"
						dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.options_strings.offload_disable_warning_desc } }
					/>

					<SelectControl
						value={ rollback }
						options={ [
							{
								label: optimoleDashboardApp.strings.options_strings.yes,
								value: true,
							},
							{
								label: optimoleDashboardApp.strings.options_strings.no,
								value: false,
							}
						] }
						onChange={ setRollback }
						className="optml__select"
						__nextHasNoMarginBottom={ true }
					/>
				</div>
			) }

			{ isOffloadMediaEnabled && (
				<>
					<hr className="my-8 border-grayish-blue"/>
		
					<BaseControl
						label={ optimoleDashboardApp.strings.options_strings.sync_title }
						help={ optimoleDashboardApp.strings.options_strings.sync_desc }
						className={ classnames(
							{
								'is-disabled':  isLoading,
							}
						) }
					>
						<div className="flex my-2 gap-3">
							<Button
								variant="default"
								isBusy={ isLoading }
								disabled={ isLoading }
								className="optml__button flex justify-center rounded font-bold min-h-40"
								onClick={ onOffloadMedia }
							>
								{ optimoleDashboardApp.strings.options_strings.sync_media }
							</Button>
						</div>
					</BaseControl>

					{ loadingSync && (
						<p>Loading sync</p>
					) }
				</>
			) }
		</>
	);
};

export default OffloadMedia;
