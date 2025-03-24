/**
 * WordPress dependencies.
 */
import {
	Button,
	ExternalLink,
	Icon,
	TextControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { addQueryArgs } from '@wordpress/url';

import SPCRecommendation from './SPCRecommendation';
const reasons = [
	optimoleDashboardApp.strings.upgrade.reason_1,
	optimoleDashboardApp.strings.upgrade.reason_2,
	optimoleDashboardApp.strings.upgrade.reason_3,
	optimoleDashboardApp.strings.upgrade.reason_4
];

const Sidebar = () => {
	const {
		name,
		domain,
		plan,
		statuses
	} = useSelect( select => {
		const { getUserData, getSiteSettings } = select( 'optimole' );

		const user = getUserData();
		const siteSettings = getSiteSettings();

		const statuses = [
			{
				active: 'enabled' === siteSettings?.image_replacer,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle1,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle1
			},
			{
				active: 'enabled' === siteSettings?.lazyload,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle2,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle2
			},
			{
				active: 'enabled' === siteSettings?.scale,
				label: optimoleDashboardApp.strings.optimization_status.statusTitle3,
				description: optimoleDashboardApp.strings.optimization_status.statusSubTitle3
			}
		];

		let domain = user?.cdn_key + '.i.optimole.com';
		if ( user?.domain !== undefined && '' !== user?.domain ) {
			domain = user?.domain;
		}

		return {
			name: user?.display_name,
			domain,
			plan: user?.plan,
			statuses: statuses.filter( status => status.active )
		};
	});

	const showSPCRecommendation = null !== optimoleDashboardApp.spc_banner;

	return (
		<div className="grid md:grid-cols-2 xl:flex xl:flex-col xl:mt-8 xl:mb-5 p-0 transition-all ease-in-out duration-700 gap-5 shrink-0 xl:w-[350px]">
			<div className="bg-white gap-5 flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
				<TextControl
					label={ optimoleDashboardApp.strings.logged_in_as }
					value={ name }
					className="optml__placeholder"
					disabled
				/>

				<TextControl
					label={ optimoleDashboardApp.strings.private_cdn_url }
					value={ domain }
					className="optml__placeholder"
					disabled
				/>

				<hr className="m-0 border-grayish-blue"/>

				<p className="font-semibold text-xs text-light-black m-0">{ optimoleDashboardApp.strings.looking_for_api_key }</p>

				<p
					className="m-0 -mt-3"
					dangerouslySetInnerHTML={ {
						__html: optimoleDashboardApp.strings.optml_dashboard
					} }
				/>
			</div>

			{ 'free' === plan ? (
				<div
					className="bg-info flex flex-col text-white border-0 rounded-lg overflow-hidden shadow-md bg-promo bg-no-repeat"
					style={ {
						backgroundImage: `url( ${ optimoleDashboardApp.assets_url }/img/logo2.png )`
					} }
				>
					{optimoleDashboardApp?.bf_notices?.sidebar && (
						<a href={optimoleDashboardApp.bf_notices.sidebar.cta_link} className="flex flex-col gap-3 p-3 bg-black !no-underline text-center cursor-pointer hover:opacity-90 transition-opacity" target="_blank">
							<span className="font-extrabold text-[17px] uppercase italic" dangerouslySetInnerHTML={{ __html: optimoleDashboardApp.bf_notices.sidebar.title }}/>
							<span className="text-[11px] font-bold" dangerouslySetInnerHTML={{ __html: optimoleDashboardApp.bf_notices.sidebar.subtitle }}/>
						</a>
					)}

					<div className="p-8 flex flex-col">
						<h3 className="mt-0 text-white text-lg">{ optimoleDashboardApp.strings.upgrade.title_long }</h3>
						<ul>
							{ reasons.map( ( reason, index ) => (
								<li
									key={ index }
									className="flex items-center gap-2"
								>
									<Icon icon="yes-alt" className="text-white" />
									<span className="text-white font-normal text-base">{ reason }</span>
								</li>
							) ) }
						</ul>

						<Button
							variant="link"
							className="optml__button flex w-full justify-center font-bold min-h-40 !no-underline !text-white !bg-opaque-black !rounded"
							href={ optimoleDashboardApp.optimoleHome +  'pricing' }
							target="_blank"
						>
							{ optimoleDashboardApp.strings.upgrade.cta }
						</Button>
					</div>
				</div>
			) : (
				<Button
					variant="default"
					className="bg-white flex font-bold border-0 rounded-lg shadow-md p-8 text-sm justify-center"
					href={ addQueryArgs( optimoleDashboardApp.optimoleHome + 'contact/', {
						contact_name: window.optimoleDashboardApp.user_data.display_name,
						contact_email: optimoleDashboardApp.user_data.user_email,
						contact_website: optimoleDashboardApp.home_url
					}) }
					target="_blank"
				>
					{ optimoleDashboardApp.strings.premium_support }
				</Button>
			) }

			{ 0 < statuses.length && (
				<div className="bg-white flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">
					<h3 className="text-base m-0">{ optimoleDashboardApp.strings.optimization_status.title }</h3>
					<ul>
						{ statuses.map( ( status, index ) => (
							<li
								key={ index }
								className="flex items-start gap-2"
							>
								<Icon icon="yes-alt" className="text-light-black mt-1" />
								<div className="text-light-black font-normal text-base">
									<div className='font-semibold'>
										{ status.label }
									</div>
									<div>
										{ status.description }
									</div>
								</div>
							</li>
						) ) }
					</ul>
					<p
						className="m-0 -mt-3"
						dangerouslySetInnerHTML={ {
							__html: optimoleDashboardApp.strings.optimization_tips
						} }
					/>
				</div>
			) }
			{ showSPCRecommendation && (
				<SPCRecommendation />
			) }
		</div>
	);
};

export default Sidebar;
