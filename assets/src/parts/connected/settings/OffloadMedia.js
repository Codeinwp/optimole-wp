/**
 * External dependencies.
 */
import classnames from "classnames";

import { rotateRight } from '@wordpress/icons';

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

import {
	useEffect,
	useState
} from "@wordpress/element";

import { addAction } from "@wordpress/hooks";

/**
 * Internal dependencies.
 */
import { warning } from '../../../utils/icons'

const maxTime = 100;

const OffloadMedia = ({
	settings,
	canSave,
	setSettings,
	setCanSave,
	onSaveSettings
}) => {
	const {
		offloadConflicts,
		estimatedTime,
		errorMedia,
		offloadLibraryLink,
		isLoading,
		loadingSync,
		loadingRollback,
		pushedImageProgress,
		rollbackLibraryLink,
		queryArgs
	} = useSelect( select => {
		const {
			getOffloadConflicts,
			getEstimatedTime,
			getErrorMedia,
			getLoadingSync,
			getLoadingRollback,
			getOffloadLibraryLink,
			getPushedImagesProgress,
			getRollbackLibraryLink,
			getQueryArgs,
			isLoading,
		} = select( 'optimole' );

		return {
			offloadConflicts: getOffloadConflicts(),
			estimatedTime: getEstimatedTime(),
			errorMedia: getErrorMedia(),
			offloadLibraryLink: getOffloadLibraryLink(),
			isLoading: isLoading(),
			loadingSync: getLoadingSync(),
			loadingRollback: getLoadingRollback(),
			pushedImageProgress: getPushedImagesProgress(),
			rollbackLibraryLink: getRollbackLibraryLink(),
			queryArgs: getQueryArgs(),
		};
	} );

	const {
		callSync,
		checkOffloadConflicts,
		setErrorMedia,
		setCheckedOffloadConflicts,
		setOffloadConflicts
	} = useDispatch( 'optimole' );

	const [ rollback, setRollback ] = useState( 'yes' );
	const [ showOffloadDisabled, setShowOffloadDisabled ] = useState( false );
	const [ showOffloadEnabled, setShowOffloadEnabled ] = useState( false );

	const isCloudLibraryEnabled = settings[ 'cloud_images' ] !== 'disabled';
	const isOffloadMediaEnabled = settings[ 'offload_media' ] !== 'disabled';
	const whitelistedDomains = settings[ 'whitelist_domains' ] || [];

	useEffect( () => {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			if ( queryArgs.optimole_action === 'offload_images' ) {
				onOffloadMedia( queryArgs.images );
			}

			if ( queryArgs.optimole_action === 'rollback_images' ) {
				onRollbackdMedia( queryArgs.images );
			}
		}
	}, []);

	useEffect( () => {
		if ( canSave ) {
			return;
		}

		setShowOffloadEnabled( false );

		if ( 'yes' === rollback && showOffloadDisabled ) {
			onRollbackdMedia();
			setRollback( 'no' );
		}

		setShowOffloadDisabled( false );
	}, [ canSave ] );

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

	const onOffloadMedia = ( imageIds = 'none' ) => {
		onSaveSettings();

		setErrorMedia( false );

		callSync( {
			action: 'offload_images',
			images: imageIds
		} );
	};

	const onRollbackdMedia = ( imageIds = 'none' ) => {
		setCheckedOffloadConflicts( false );
		setOffloadConflicts( [] );

		checkOffloadConflicts( response => {
			if ( response.data.length === 0 ) {
				setErrorMedia( false );

				callSync( {
					action: 'rollback_images',
					images: imageIds
				} );
			}
		} );
	};

	addAction(
		'optimole.settings.save',
		'optimole/rollback',
		() => {
			console.log( 'Trigger save!!!' );
		}
	);

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
								value={ Object.keys( settings['cloud_sites']).filter( site => site !== 'all' ).map( site => site ) || []}
								suggestions={ whitelistedDomains }
								onChange={ updateSites }
								__experimentalExpandOnFocus={ true }
								__experimentalShowHowTo={ false }
								__experimentalValidateInput={ newValue => whitelistedDomains.includes( newValue ) }
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
								value: 'yes'
							},
							{
								label: optimoleDashboardApp.strings.options_strings.no,
								value: 'no'
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
								isBusy={ loadingSync || isLoading }
								disabled={ loadingSync || isLoading }
								className="optml__button flex justify-center rounded font-bold min-h-40"
								onClick={ () => onOffloadMedia() }
							>
								{ optimoleDashboardApp.strings.options_strings.sync_media }
							</Button>
						</div>
					</BaseControl>

					{ loadingSync && (
						<div className="flex flex-col p-6 bg-light-blue border border-blue-300 rounded-md">
							<div className="flex justify-between w-full items-center">
								<p className="font-bold text-base m-0">{ optimoleDashboardApp.strings.options_strings.sync_media_progress }</p>

								<Icon icon={ rotateRight } className="animate-spin fill-dark-blue" />
							</div>

							<progress
								className="mt-2.5 mb-1.5 mx-0"
								value={ pushedImageProgress }
								max={ maxTime }
							/>

							{ estimatedTime === 0 ? (
								<p className="m-0">{ optimoleDashboardApp.strings.options_strings.calculating_estimated_time }</p>
							) : (
								<p className="m-0">{ optimoleDashboardApp.strings.options_strings.estimated_time } <b>{ estimatedTime } { optimoleDashboardApp.strings.options_strings.minutes }</b></p>
							) }
						</div>
					) }

					{ true === offloadLibraryLink && (
						<p className="m-0">{ optimoleDashboardApp.strings.options_strings.sync_media_link } <a href={ queryArgs.url }>{ optimoleDashboardApp.strings.options_strings.here }</a></p>
					) }

					{ 'offload_images' === errorMedia && (
						<div className="flex flex-col gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
							<div className="flex items-center gap-2">
								<Icon
									icon={ warning } 
									size={ 16 }
								/>

								<p className="font-bold m-0">{ optimoleDashboardApp.strings.options_strings.sync_media_error }</p>
							</div>

							<p className="m-0">{ optimoleDashboardApp.strings.options_strings.sync_media_error_desc }</p>
						</div>
					) }
				</>
			) }

			{ ( isOffloadMediaEnabled || loadingRollback ) && (
				<>
					<hr className="my-8 border-grayish-blue"/>
		
					<BaseControl
						label={ optimoleDashboardApp.strings.options_strings.rollback_title }
						help={ optimoleDashboardApp.strings.options_strings.rollback_desc }
						className={ classnames(
							{
								'is-disabled':  isLoading,
							}
						) }
					>
						<div className="flex my-2 gap-3">
							<Button
								variant="default"
								isBusy={ loadingSync || loadingRollback || isLoading }
								disabled={ loadingSync || loadingRollback || isLoading || Boolean( offloadConflicts.length ) }
								className="optml__button flex justify-center rounded font-bold min-h-40"
								onClick={ () => onRollbackdMedia() }
							>
								{ optimoleDashboardApp.strings.options_strings.rollback_media }
							</Button>
						</div>
					</BaseControl>

					{ loadingRollback && (
						<div className="flex flex-col p-6 bg-light-blue border border-blue-300 rounded-md">
							<div className="flex justify-between w-full items-center">
								<p className="font-bold text-base m-0">{ optimoleDashboardApp.strings.options_strings.rollback_media_progress }</p>

								<Icon icon={ rotateRight } className="animate-spin fill-dark-blue" />
							</div>

							<progress
								className="mt-2.5 mb-1.5 mx-0"
								value={ pushedImageProgress }
								max={ maxTime }
							/>

							{ estimatedTime === 0 ? (
								<p className="m-0">{ optimoleDashboardApp.strings.options_strings.calculating_estimated_time }</p>
							) : (
								<p className="m-0">{ optimoleDashboardApp.strings.options_strings.estimated_time } <b>{ estimatedTime } { optimoleDashboardApp.strings.options_strings.minutes }</b></p>
							) }
						</div>
					) }

					{ Boolean( offloadConflicts.length ) && (
						<div className="flex flex-col gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 mb-5">
							<p className="m-0">{ optimoleDashboardApp.strings.options_strings.offload_conflicts_part_1 }</p>

							{ offloadConflicts.map( ( conflict, index ) => (
								<p
									key={ index }
									className="m-0 font-bold"
								>
									{ conflict }
								</p>
							) ) }

							<p className="m-0">{ optimoleDashboardApp.strings.options_strings.offload_conflicts_part_2 }</p>

							<Button
								variant="default"
								isBusy={ isLoading }
								disabled={ isLoading }
								className="optml__button flex justify-center rounded font-bold min-h-40"
								onClick={ () => setOffloadConflicts( [] ) }
							>
								{ optimoleDashboardApp.strings.conflicts.conflict_close }
							</Button>
						</div>
					) }

					{ true === rollbackLibraryLink && (
						<p className="m-0">{ optimoleDashboardApp.strings.options_strings.rollback_media_link } <a href={ queryArgs.url }>{ optimoleDashboardApp.strings.options_strings.here }</a></p>
					) }

					{ 'rollback_images' === errorMedia && (
						<div className="flex flex-col gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
							<div className="flex items-center gap-2">
								<Icon
									icon={ warning } 
									size={ 16 }
								/>

								<p className="font-bold m-0">{ optimoleDashboardApp.strings.options_strings.rollback_media_error }</p>
							</div>

							<p className="m-0">{ optimoleDashboardApp.strings.options_strings.rollback_media_error_desc }</p>
						</div>
					) }
				</>
			) }
		</>
	);
};

export default OffloadMedia;
