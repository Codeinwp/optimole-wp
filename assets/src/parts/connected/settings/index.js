/**
 * WordPress dependencies.
 */
import { Button } from "@wordpress/components";

import {
	useDispatch,
	useSelect
} from "@wordpress/data";

import { useState } from "@wordpress/element";

/**
 * Internal dependencies.
 */
import Menu from "./Menu";
import General from "./General";
import Compression from "./Compression";
import Resize from "./Resize";
import Lazyload from "./Lazyload";
import Exclusions from "./Exclusions";

const Settings = () => {
	const {
		getSettings,
		isLoading,
	} = useSelect( select => {
		const {
			getSiteSettings,
			isLoading
		} = select( 'optimole' );

		return {
			getSettings: getSiteSettings,
			isLoading: isLoading()
		};
	} );

	const {
		sampleRate,
		saveSettings
	} = useDispatch( 'optimole' );

	const [ tab, setTab ] = useState( 'general' );
	const [ settings, setSettings ] = useState( getSettings() );
	const [ canSave, setCanSave ] = useState( false );
	const [ showSample, setShowSample ] = useState( false );
	const [ isSampleLoading, setIsSampleLoading ] = useState( false );

	const loadSample = () => {
		if ( ! showSample ) {
			setIsSampleLoading( true );
	
			sampleRate(
				{
					quality: settings[ 'quality' ],
				},
				() => setIsSampleLoading( false )
			);
		}

		setShowSample( ! showSample )
	};

	return (
		<div className="optml-settings flex bg-white p-8 border-0 rounded-lg shadow-md gap-8">
			<Menu
				tab={ tab }
				setTab={ setTab }
			/>

			<div className="basis-4/5">
				{ tab === 'general' && (
					<General
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ tab === 'compression' && (
					<Compression
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
						showSample={ showSample }
						isSampleLoading={ isSampleLoading }
						setIsSampleLoading={ setIsSampleLoading }
					/>
				) }

				{ tab === 'resize' && (
					<Resize
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ tab === 'lazyload' && (
					<Lazyload
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				{ tab === 'exclusions' && (
					<Exclusions
						settings={ settings }
						setSettings={ setSettings }
						setCanSave={ setCanSave }
					/>
				) }

				<div className="flex justify-start items-center gap-5 mt-8">
					<Button
						variant="primary"
						isBusy={ isLoading }
						disabled={ ! canSave }
						className="optml__button flex w-full justify-center rounded font-bold min-h-40 basis-1/5"
						onClick={ () => saveSettings( settings ) }
					>
						{ optimoleDashboardApp.strings.options_strings.save_changes }
					</Button>

					{ settings[ 'autoquality' ] === 'disabled' && (
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
