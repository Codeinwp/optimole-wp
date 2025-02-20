/* global optimoleDashboardApp */

import classnames from 'classnames';
import { useEffect, useState } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { Icon } from '@wordpress/icons';

import { warning, rollback as rollbackIcon, offload, warningAlt, sync } from '../../../utils/icons';
import { callSync, clearOffloadErrors, checkOffloadConflicts, saveSettings } from '../../../utils/api';
import Notice from '../../components/Notice';
import RadioBoxes from '../../components/RadioBoxes';
import ProgressTile from '../../components/ProgressTile';
import Modal from '../../components/Modal';
import Logs from './Logs';

const OffloadMedia = ({ settings, canSave, setSettings, setCanSave }) => {
	const { strings, cron_disabled } = optimoleDashboardApp;
	const { conflicts, options_strings } = strings;

	const MODAL_STATE_OFFLOAD = 'offload';
	const MODAL_STATE_ROLLBACK = 'rollback';
	const MODAL_STATE_STOP_OFFLOAD = 'stopOffload';
	const MODAL_STATE_STOP_ROLLBACK = 'stopRollback';

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
		processedImages,
		offloadFinishNotice,
		offloadLimitReached,
		offloadLimit
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
			isLoading,
			getSiteSettings,
			getOffloadLimit
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
			processedImages: getProcessedImages(),
			offloadFinishNotice: getSiteSettings( 'show_offload_finish_notice' ),
			offloadLimitReached: 'enabled' === getSiteSettings( 'offload_limit_reached' ),
			offloadLimit: getOffloadLimit()
		};
	}, []);

	const {
		setErrorMedia,
		setCheckedOffloadConflicts,
		setOffloadConflicts,
		setProcessedImages,
		setOffloadLimitReached
	} = useDispatch( 'optimole' );

	const [ modal, setModal ] = useState( null );

	const isOffloadingInProgress = 'disabled' !== settings['offloading_status'];
	const isRollbackInProgress = 'disabled' !== settings['rollback_status'];

	useEffect( () => {
		if ( ! queryArgs?.optimole_action || ! queryArgs?.images ) {
			return;
		}

		if ( 'offload_images' === queryArgs.optimole_action ) {
			onOffloadMedia( queryArgs.images );
		}

		if ( 'rollback_images' === queryArgs.optimole_action ) {
			onRollbackdMedia( queryArgs.images );
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


	const onOffloadMedia = async( imageIds = []) => {
		await clearOffloadErrors();

		const nextSettings = { ...settings };
		nextSettings['show_offload_finish_notice'] = '';
		nextSettings['offloading_status'] = 'enabled';
		setSettings( nextSettings );
		saveSettings( nextSettings, false, true, () => {
			setErrorMedia( false );
			setProcessedImages( 0 );
			setOffloadLimitReached( false );

			callSync({
				action: 'offload_images',
				images: imageIds
			});

			setCanSave( false );
		});
	};

	const onRollbackdMedia = ( imageIds = []) => {
		setCheckedOffloadConflicts( false );
		setOffloadConflicts([]);

		checkOffloadConflicts( response => {
			if ( 0 === response.data.length ) {
				const nextSettings = { ...settings };
				nextSettings['show_offload_finish_notice'] = '';
				nextSettings['rollback_status'] = 'enabled';
				saveSettings( nextSettings, false, true, () => {
					setErrorMedia( false );
					setProcessedImages( 0 );

					callSync({
						action: 'rollback_images',
						images: imageIds
					});

					setCanSave( false );
				});
			}
		});
	};

	const radioBoxesOptions = [
		{
			title: options_strings.offload_radio_option_rollback_title,
			description: options_strings.offload_radio_option_rollback_desc,
			value: 'rollback'
		},
		{
			title: options_strings.offload_radio_option_offload_title,
			description: options_strings.offload_radio_option_offload_desc,
			value: 'offload'
		}
	];

	const radioBoxValue = 'enabled' === settings['offload_media'] ? 'offload' : 'rollback';

	const updateRadioBoxValue = value => {
		const offloadEnabled = 'offload' === value;
		const nextSettings = { ...settings };
		nextSettings['offload_media'] = offloadEnabled ? 'enabled' : 'disabled';
		setSettings( nextSettings );
		setCanSave( true );

		if ( offloadEnabled ) {
			setModal( MODAL_STATE_OFFLOAD );

			return;
		}

		setModal( MODAL_STATE_ROLLBACK );
	};

	const getModalProps = ( type ) => {
		const props = {
			[MODAL_STATE_OFFLOAD]: {
				icon: offload,
				onConfirm: () => {
					onOffloadMedia();
					setModal( null );
				},
				labels: {
					title: options_strings.offloading_start_title,
					description: options_strings.offloading_start_description,
					action: options_strings.offloading_start_action
				}
			},
			[MODAL_STATE_ROLLBACK]: {
				icon: rollbackIcon,
				onConfirm: () => {
					onRollbackdMedia();
					setModal( null );
				},
				labels: {
					title: options_strings.rollback_start_title,
					description: options_strings.rollback_start_description,
					action: options_strings.rollback_start_action
				}

			},
			[MODAL_STATE_STOP_OFFLOAD]: {
				variant: 'warning',
				icon: warningAlt,
				onConfirm: () => {
					setModal( null );
					const options = settings;
					options.offloading_status = 'disabled';
					saveSettings( options );
				},
				labels: {
					title: options_strings.offloading_stop_title,
					description: options_strings.offloading_stop_description,
					action: options_strings.offloading_stop_action
				}
			},
			[MODAL_STATE_STOP_ROLLBACK]: {
				variant: 'warning',
				icon: warningAlt,
				onConfirm: () => {
					setModal( null );
					const options = settings;
					options.rollback_status = 'disabled';
					saveSettings( options );
				},
				labels: {
					title: options_strings.rollback_stop_title,
					description: options_strings.rollback_stop_description,
					action: options_strings.rollback_stop_action
				}
			}
		};

		return {
			onRequestClose: () => setModal( null ),
			...props[type]
		};
	};

	const isInProgress = loadingSync || loadingRollback;

	const onCancelProgress = () => {
		if ( loadingSync ) {
			setModal( MODAL_STATE_STOP_OFFLOAD );
		}

		if ( loadingRollback ) {
			setModal( MODAL_STATE_STOP_ROLLBACK );
		}
	};

	const handleForceSync = ( e ) => {
		e.preventDefault();

		if ( 'offload' === radioBoxValue ) {
			setModal( MODAL_STATE_OFFLOAD );
		}

		if ( 'rollback' === radioBoxValue ) {
			setModal( MODAL_STATE_ROLLBACK );
		}
	};

	const dismissFinishNotice = () => {
		saveSettings({ show_offload_finish_notice: '' }, false, true  );
	};

	return (
		<div>
			<h1 className="text-xl font-bold">{options_strings.enable_offload_media_title}</h1>
			<p dangerouslySetInnerHTML={{ __html: options_strings.enable_offload_media_desc }}/>

			{offloadFinishNotice && (
				<Notice
					title={'offload' === offloadFinishNotice ? options_strings.offloading_success : options_strings.rollback_success }
					type="primary"
					onDismiss={dismissFinishNotice}
				/>
			)}

			{cron_disabled && (
				<Notice type='error' text={strings.cron_error}/>
			)}

			{! isInProgress &&
				<RadioBoxes
					className={classnames({ 'opacity-50 pointer-events-none': isLoading || cron_disabled })}
					label={options_strings.offloading_radio_legend}
					options={radioBoxesOptions}
					value={radioBoxValue}
					onChange={updateRadioBoxValue}
				/>}

			{isInProgress && (
				<>
					<ProgressTile
						title={loadingSync ? options_strings.sync_media_progress : options_strings.rollback_media_progress}
						progress={Math.round( ( processedImages / totalNumberOfImages ) * 100 )}
						onCancel={onCancelProgress}
						description={0 === totalNumberOfImages ? options_strings.calculating_estimated_time : options_strings.images_processing}/>
					<Logs type={loadingSync ? 'offload' : 'rollback'}/>
				</>
			)}

			{'offload_images' === errorMedia && (
				<Notice type="error" smallTitle title={options_strings.sync_media_error} text={options_strings.sync_media_error_desc}/>
			)}

			{'rollback_images' === errorMedia && (
				<Notice type="error" smallTitle title={options_strings.rollback_media_error} text={options_strings.rollback_media_error_desc}/>
			)}

			{ 0 < offloadConflicts.length && (
				<Notice type="warning" title={options_strings.offload_conflicts_part_1}>
					<div className="list-disc grid gap-3 mt-2">
						<ul className="grid gap-1">
							{offloadConflicts.map( ( conflict ) => (
								<li key={conflict} className="font-semibold text-base m-0">{conflict}</li>
							) )}
						</ul>

						<p className="m-0 text-sm">{options_strings.offload_conflicts_part_2}</p>

						<button
							disabled={isLoading}
							className={classnames(
								{ 'opacity-50 pointer-events-none': isLoading },
								'justify-self-start font-semibold flex items-center appearance-none border border-info text-info bg-white rounded px-4 py-2 text-sm hover:text-white hover:bg-info cursor-pointer'
							)}
							onClick={() => setOffloadConflicts([])}
						>
							{conflicts.conflict_close}
						</button>
					</div>
				</Notice>
			)}


			{! isInProgress && (
				<button
					onClick={handleForceSync}
					disabled={isLoading || cron_disabled}
					className={classnames(
						{ 'opacity-50 pointer-events-none': isLoading || cron_disabled },
						'font-semibold flex items-center gap-2 appearance-none border border-info text-info bg-transparent rounded px-4 py-2 ml-auto text-sm hover:text-white hover:bg-info cursor-pointer'
					)}
				>
					<Icon icon={sync} className="w-4 h-4"/>
					<span>{options_strings.sync_media}</span>
				</button>
			)}

			{ ! isInProgress && offloadLimitReached && (
				<Notice type='warning' text={options_strings.offload_limit_reached.replace( '#offload_limit#', offloadLimit ) }/>
			)}

			{ modal && (
				<Modal {...getModalProps( modal )} />
			)}

			{ offloadLibraryLink && (
				<p className="m-0">{options_strings.sync_media_link} <a href={queryArgs.url}>{options_strings.here}</a></p>
			)}

			{ rollbackLibraryLink && (
				<p className="m-0">{ options_strings.rollback_media_link } <a href={ queryArgs.url }>{ optimoleDashboardApp.strings.options_strings.here }</a></p>
			) }

		</div>
	);
};

export default OffloadMedia;
