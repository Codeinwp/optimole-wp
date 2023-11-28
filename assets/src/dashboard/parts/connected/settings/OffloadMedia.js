/* global optimoleDashboardApp */
import classnames from 'classnames';
import { BaseControl, Button, Icon, SelectControl, ToggleControl } from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { rotateRight } from '@wordpress/icons';

import { warning } from '../../../utils/icons';
import ProgressBar from '../../components/ProgressBar';
import { callSync, checkOffloadConflicts, saveSettings } from '../../../utils/api';

const maxTime = 100;

const OffloadMedia = ({
	settings,
	canSave,
	setSettings,
	setCanSave,
	onSaveSettings
}) => {
	const { strings, site_settings } = optimoleDashboardApp;
	const { conflicts, options_strings } = strings;

	const {
		offloadConflicts,
		errorMedia,
		offloadLibraryLink,
		isLoading,
		loadingSync,
		loadingRollback,
		rollbackLibraryLink,
		queryArgs,
		totalNumberOfImages,
		processedImages
	} = useSelect( select => {
		const {
			getOffloadConflicts,
			getErrorMedia,
			getLoadingSync,
			getLoadingRollback,
			getOffloadLibraryLink,
			getRollbackLibraryLink,
			getTotalNumberOfImages,
			getProcessedImages,
			getQueryArgs,
			isLoading
		} = select( 'optimole' );

		return {
			offloadConflicts: getOffloadConflicts(),
			errorMedia: getErrorMedia(),
			offloadLibraryLink: getOffloadLibraryLink(),
			isLoading: isLoading(),
			loadingSync: getLoadingSync(),
			loadingRollback: getLoadingRollback(),
			rollbackLibraryLink: getRollbackLibraryLink(),
			queryArgs: getQueryArgs(),
			totalNumberOfImages: getTotalNumberOfImages(),
			processedImages: getProcessedImages()
		};
	}, []);

	const {
		setErrorMedia,
		setCheckedOffloadConflicts,
		setOffloadConflicts
	} = useDispatch( 'optimole' );

	const [ rollback, setRollback ] = useState( 'yes' );
	const [ showOffloadDisabled, setShowOffloadDisabled ] = useState( false );
	const [ showOffloadEnabled, setShowOffloadEnabled ] = useState( false );

	const isOffloadMediaEnabled = 'disabled' !== settings[ 'offload_media' ];
	const isOffloadingInProgress = 'disabled' !== settings[ 'offloading_status' ];
	const isRollbackInProgress = 'disabled' !== settings[ 'rollback_status' ];

	useEffect( () => {
		if ( Object.prototype.hasOwnProperty.call( queryArgs, 'optimole_action' ) ) {
			if ( 'offload_images' === queryArgs.optimole_action ) {
				onOffloadMedia( queryArgs.images );
			}

			if ( 'rollback_images' === queryArgs.optimole_action ) {
				onRollbackdMedia( queryArgs.images );
			}
		}
	}, []);

	useEffect( () => {
		if ( isOffloadingInProgress ) {
			callSync({
				action: 'offload_images',
				refresh: true
			});
		}

		if ( isRollbackInProgress ) {
			callSync({
				action: 'rollback_images',
				refresh: true
			});
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
	}, [ canSave ]);

	const onChangeOffload = value => {
		setCanSave( true );
		const data = { ...settings };
		data[ 'offload_media' ] = value ? 'enabled' : 'disabled';
		setSettings( data );

		setShowOffloadDisabled( 'enabled' === site_settings[ 'offload_media' ] && ! value );
		setShowOffloadEnabled( 'disabled' === site_settings[ 'offload_media' ] && !! value );
	};

	const onOffloadMedia = ( imageIds = []) => {
		onSaveSettings();
		setErrorMedia( false );

		callSync({
			action: 'offload_images',
			images: imageIds
		});
	};

	const onRollbackdMedia = ( imageIds = []) => {
		setCheckedOffloadConflicts( false );
		setOffloadConflicts([]);

		checkOffloadConflicts( response => {
			if ( 0 === response.data.length ) {
				setErrorMedia( false );

				callSync({
					action: 'rollback_images',
					images: imageIds
				});
			}
		});
	};

	return (
		<>
			<ToggleControl
				label={ options_strings.enable_offload_media_title }
				help={ options_strings.enable_offload_media_desc }
				checked={ isOffloadMediaEnabled }
				disabled={ isLoading }
				className={ classnames(
					{
						'is-disabled': isLoading
					}
				) }
				onChange={ onChangeOffload }
			/>

			{ showOffloadEnabled && (
				<div className="flex gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 mb-5">
					<p
						className="m-0"
						dangerouslySetInnerHTML={ { __html: options_strings.offload_enable_info_desc } }
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

						<p className="font-bold text-base m-0">{ options_strings.offload_disable_warning_title }</p>
					</div>

					<p
						className="m-0"
						dangerouslySetInnerHTML={ { __html: options_strings.offload_disable_warning_desc } }
					/>

					<SelectControl
						value={ rollback }
						options={ [
							{
								label: options_strings.yes,
								value: 'yes'
							},
							{
								label: options_strings.no,
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
						label={ options_strings.sync_title }
						help={ options_strings.sync_desc }
					>
						<div className="flex my-2 gap-3">
							<Button
								variant="default"
								isBusy={ loadingSync || loadingRollback || isLoading }
								disabled={ loadingSync || loadingRollback || isLoading }
								className={ classnames(
									'optml__button flex justify-center rounded font-bold min-h-40',
									{
										'is-disabled': isLoading
									}
								) }
								onClick={ () => onOffloadMedia() }
							>
								{ options_strings.sync_media }
							</Button>

							{ loadingSync && (
								<Button
									variant="default"
									isDestructive={ true }
									className="optml__button flex justify-center rounded font-bold min-h-40"
									onClick={ () => {
										const options = settings;
										options.offloading_status = 'disabled';
										saveSettings( options );
									} }
								>
									{ options_strings.stop }
								</Button>
							) }
						</div>
					</BaseControl>

					{ loadingSync && (
						<div className="flex flex-col p-6 bg-light-blue border border-blue-300 rounded-md">
							<div className="flex justify-between w-full items-center">
								<p className="font-bold text-base m-0">{ options_strings.sync_media_progress }</p>

								<Icon icon={ rotateRight } className="animate-spin fill-dark-blue" />
							</div>

							<ProgressBar
								className="mt-2.5 mb-1.5 mx-0"
								value={ Math.round( ( processedImages / totalNumberOfImages ) * 100 ) }
								max={ maxTime }
							/>

							{ 0 === totalNumberOfImages ? (
								<p className="m-0">{ options_strings.calculating_estimated_time }</p>
							) : (
								<p className="m-0">{ options_strings.images_processing }</p>
							) }
						</div>
					) }

					{ true === offloadLibraryLink && (
						<p className="m-0">{ options_strings.sync_media_link } <a href={ queryArgs.url }>{ options_strings.here }</a></p>
					) }

					{ 'offload_images' === errorMedia && (
						<div className="flex flex-col gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
							<div className="flex items-center gap-2">
								<Icon
									icon={ warning }
									size={ 16 }
								/>

								<p className="font-bold m-0">{ options_strings.sync_media_error }</p>
							</div>

							<p className="m-0">{ options_strings.sync_media_error_desc }</p>
						</div>
					) }
				</>
			) }

			{ ( isOffloadMediaEnabled || loadingRollback ) && (
				<>
					<hr className="my-8 border-grayish-blue"/>

					<BaseControl
						label={ options_strings.rollback_title }
						help={ options_strings.rollback_desc }
					>
						<div className="flex my-2 gap-3">
							<Button
								variant="default"
								isBusy={ loadingSync || loadingRollback || isLoading }
								disabled={ loadingSync || loadingRollback || isLoading || Boolean( offloadConflicts.length ) }
								className={ classnames(
									'optml__button flex justify-center rounded font-bold min-h-40',
									{
										'is-disabled': isLoading
									}
								) }
								onClick={ () => onRollbackdMedia() }
							>
								{ options_strings.rollback_media }
							</Button>

							{ loadingRollback && (
								<Button
									variant="default"
									isDestructive={ true }
									className="optml__button flex justify-center rounded font-bold min-h-40"
									onClick={ () => {
										const options = settings;
										options.rollback_status = 'disabled';
										saveSettings( options );
									} }
								>
									{ options_strings.stop }
								</Button>
							) }
						</div>
					</BaseControl>

					{ loadingRollback && (
						<div className="flex flex-col p-6 bg-light-blue border border-blue-300 rounded-md">
							<div className="flex justify-between w-full items-center">
								<p className="font-bold text-base m-0">{ options_strings.rollback_media_progress }</p>

								<Icon icon={ rotateRight } className="animate-spin fill-dark-blue" />
							</div>

							<ProgressBar
								className="mt-2.5 mb-1.5 mx-0"
								value={ Math.round( ( processedImages / totalNumberOfImages ) * 100 ) }
								max={ maxTime }
							/>

							{ 0 === totalNumberOfImages ? (
								<p className="m-0">{ options_strings.calculating_estimated_time }</p>
							) : (
								<p className="m-0">{ options_strings.images_processing }</p>
							) }
						</div>
					) }

					{ Boolean( offloadConflicts.length ) && (
						<div className="flex flex-col gap-2 bg-stale-yellow text-gray-800 border border-solid border-yellow-300 rounded relative px-6 py-5 mb-5">
							<p className="m-0">{ options_strings.offload_conflicts_part_1 }</p>

							{ offloadConflicts.map( ( conflict, index ) => (
								<p
									key={ index }
									className="m-0 font-bold"
								>
									{ conflict }
								</p>
							) ) }

							<p className="m-0">{ options_strings.offload_conflicts_part_2 }</p>

							<Button
								variant="default"
								isBusy={ isLoading }
								disabled={ isLoading }
								className="optml__button flex justify-center rounded font-bold min-h-40"
								onClick={ () => setOffloadConflicts([]) }
							>
								{ conflicts.conflict_close }
							</Button>
						</div>
					) }

					{ true === rollbackLibraryLink && (
						<p className="m-0">{ options_strings.rollback_media_link } <a href={ queryArgs.url }>{ options_strings.here }</a></p>
					) }

					{ 'rollback_images' === errorMedia && (
						<div className="flex flex-col gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
							<div className="flex items-center gap-2">
								<Icon
									icon={ warning }
									size={ 16 }
								/>

								<p className="font-bold m-0">{ options_strings.rollback_media_error }</p>
							</div>

							<p className="m-0">{ options_strings.rollback_media_error_desc }</p>
						</div>
					) }
				</>
			) }
		</>
	);
};

export default OffloadMedia;
