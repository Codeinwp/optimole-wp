import { Button } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';

import Menu from './Menu';
import General from './General';
import Compression from './Compression';
import Resize from './Resize';
import Lazyload from './Lazyload';
import Exclusions from './Exclusions';
import OffloadMedia from './OffloadMedia';
import CloudLibrary from './CloudLibrary';
import { sampleRate, saveSettings } from '../../../utils/api';
import { toggleDamSidebarLink } from '../../../utils/helpers';

const Settings = ({
	tab,
	setTab,
	canSave,
	setCanSave,
	settings,
	setSettings
}) => {
	const {
		siteSettings,
		isLoading,
		damEnabled
	} = useSelect( select => {
		const {
			getSiteSettings,
			getQueryArgs,
			isLoading
		} = select( 'optimole' );

		const siteSettings = getSiteSettings();

		return {
			siteSettings,
			damEnabled: siteSettings['cloud_images'],
			isLoading: isLoading(),
			queryArgs: getQueryArgs()
		};
	}, []);

	const [ showSample, setShowSample ] = useState( false );
	const [ isSampleLoading, setIsSampleLoading ] = useState( false );

	useEffect( () => {
		toggleDamSidebarLink( 'enabled' === damEnabled );
	}, [ damEnabled ]);

	useEffect( () => {
		const visits = localStorage.getItem( 'optimole_settings_visits' );

		if ( 3 < visits ) {
			return;
		}

		localStorage.setItem( 'optimole_settings_visits', visits ? parseInt( visits ) + 1 : 1 );
	}, [ tab ]);

	const loadSample = () => {
		if ( ! showSample ) {
			setIsSampleLoading( true );

			sampleRate(
				{
					quality: settings.quality
				},
				() => setIsSampleLoading( false )
			);
		}

		setShowSample( ! showSample );
	};

	const onSaveSettings = () => {
		saveSettings( settings, siteSettings['banner_frontend'] !== settings['banner_frontend']);

		setCanSave( false );
	};

	return (
		<div className="optml-settings flex flex-col sm:flex-row bg-white p-8 border-0 rounded-lg shadow-md gap-8">
			<Menu
				tab={ tab }
				setTab={ setTab }
			/>

			<div className="basis-4/5">
				{ 'general' === tab && (
					<General
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ 'compression' === tab && (
					<Compression
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
						showSample={ showSample }
						isSampleLoading={ isSampleLoading }
						setIsSampleLoading={ setIsSampleLoading }
					/>
				) }

				{ 'resize' === tab && (
					<Resize
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ 'lazyload' === tab && (
					<Lazyload
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ 'exclusions' === tab && (
					<Exclusions
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }
				{ 'cloud_library' === tab && (
					<CloudLibrary
						settings={ settings }
						canSave={ canSave }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
						onSaveSettings={ onSaveSettings }
					/>
				) }
				{ 'offload_media' === tab && (
					<OffloadMedia
						settings={ settings }
						canSave={ canSave }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
						onSaveSettings={ onSaveSettings }
					/>
				) }

				<div className="flex justify-start items-center gap-5 mt-8">
					<Button
						variant="primary"
						isBusy={ isLoading }
						disabled={ ! canSave }
						className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
						onClick={ onSaveSettings }
					>
						{ optimoleDashboardApp.strings.options_strings.save_changes }
					</Button>

					{ ( 'disabled' === settings.autoquality && 'compression' === tab ) && (
						<Button
							variant="default"
							disabled={ isLoading }
							onClick={ loadSample }
						>
							{ optimoleDashboardApp.strings.options_strings.view_sample_image }
						</Button>
					) }
				</div>
			</div>
		</div>
	);
};

export default Settings;
