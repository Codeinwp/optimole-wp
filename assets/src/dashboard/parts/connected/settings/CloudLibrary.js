/* global optimoleDashboardApp */
import classnames from 'classnames';
import {
	BaseControl,
	FormTokenField,
	ToggleControl,
	Icon
} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import {
	arrowRight
} from '@wordpress/icons';

export default function CloudLibrary( props ) {
	const { user_data, strings } = optimoleDashboardApp;
	const { options_strings } = strings;
	const {
		settings,
		setSettings,
		setCanSave
	} = props;

	const storeData = useSelect( select => {
		const { isLoading, isSubApiKey } = select( 'optimole' );
		return {
			isLoading: isLoading(),
			isSubApiKey: isSubApiKey()
		};
	}, []);

	const { isLoading, isSubApiKey } = storeData;


	const isCloudLibraryEnabled = 'disabled' !== settings[ 'cloud_images' ];
	const showSiteSelector = ! isSubApiKey && isCloudLibraryEnabled;
	const whitelistedDomains = user_data.whitelist || [];


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

		if ( 0 === value.length ) {
			sites.all = 'true';
		} else {
			value.forEach( ( site ) => {
				sites[ site ] = 'true';
			});
		}

		Object.keys( data[ 'cloud_sites' ]).forEach( ( site ) => {
			if ( ! Object.prototype.hasOwnProperty.call( sites, site ) ) {
				sites[ site ] = 'false';
			}
		});

		data[ 'cloud_sites' ] = sites;
		setSettings( data );
	};


	return (
		<>
			<ToggleControl
				label={options_strings.enable_cloud_images_title}
				help={() => <p
					dangerouslySetInnerHTML={{ __html: options_strings.enable_cloud_images_desc }}/>}
				checked={isCloudLibraryEnabled}
				disabled={isLoading}
				className={classnames(
					{
						'is-disabled': isLoading
					}
				)}
				onChange={value => updateOption( 'cloud_images', value )}
			/>

			{isCloudLibraryEnabled && (
				<div className="m-0">
					<a href={options_strings.cloud_library_btn_link} className="font-semibold text-info text-sm hover:text-info inline-flex items-center">
						{options_strings.cloud_library_btn_text}
						<Icon
						icon={arrowRight}
						className="inline-block ml-2 fill-current"
					/>
					</a>
				</div>
			)}

			<hr className="my-8 border-grayish-blue"/>

			{showSiteSelector && (
				<BaseControl
					label={options_strings.cloud_site_title}
					help={options_strings.cloud_site_desc}
				>
					<div
						className="optml__token__base flex p-6 bg-light-blue border border-blue-300 rounded-md items-center gap-8">
						<FormTokenField
							value={Object.keys( settings['cloud_sites']).filter( site => 'all' !== site && 'false' !== settings['cloud_sites'][site]).map( site => site ) || []}
							suggestions={whitelistedDomains}
							onChange={updateSites}
							__experimentalExpandOnFocus={true}
							__experimentalShowHowTo={false}
							__experimentalValidateInput={newValue => whitelistedDomains.includes( newValue )}
							className="optml__token"/>
					</div>
				</BaseControl>
			)}
		</>
	);
}
