/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	TextControl
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import { addQueryArgs } from '@wordpress/url';

import SPCRecommendation from './SPCRecommendation';
import OptimizationStatus from './OptimizationStatus';
import { globe, user } from '../../utils/icons';
import Tooltip from '../components/Tooltip';

const reasons = [
	optimoleDashboardApp.strings.upgrade.reason_1,
	optimoleDashboardApp.strings.upgrade.reason_2,
	optimoleDashboardApp.strings.upgrade.reason_3,
	optimoleDashboardApp.strings.upgrade.reason_4
];

const Sidebar = ({ settings, setSettings, setCanSave, setTab }) => {
	const {
		name,
		domain,
		plan
	} = useSelect( select => {
		const { getUserData } = select( 'optimole' );

		const user = getUserData();

		let domain = user?.cdn_key + '.i.optimole.com';
		if ( user?.domain !== undefined && '' !== user?.domain ) {
			domain = user?.domain;
		}

		if ( user?.domain_dns !== undefined && '' !== user?.domain_dns ) {
			domain = user?.domain_dns;
		}

		return {
			name: user?.display_name,
			domain,
			plan: user?.plan
		};
	});

	const showSPCRecommendation = null !== optimoleDashboardApp.spc_banner;

	return (
		<div className="grid md:grid-cols-2 xl:flex xl:flex-col xl:mt-8 xl:mb-5 p-0 transition-all ease-in-out duration-700 gap-5 shrink-0 xl:w-[350px]">
			<div className="bg-white gap-3 flex flex-col text-gray-700 border-0 rounded-lg shadow-md p-8">

				<div className="grid gap-4">
					<PlaceholderInput icon={user} value={name} tooltipText={optimoleDashboardApp.strings.logged_in_as}/>
					<PlaceholderInput icon={globe} value={domain} tooltipText={optimoleDashboardApp.strings.private_cdn_url}/>
				</div>

				<hr className="m-0 border-t-grayish-blue"/>

				<div className="border-t border-gray-300 block grid gap-2">
					<p className="font-semibold text-xs text-light-black m-0">{ optimoleDashboardApp.strings.looking_for_api_key }</p>
					<span
						dangerouslySetInnerHTML={ {
							__html: optimoleDashboardApp.strings.optml_dashboard
						} }
					/>
				</div>
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
							href={ optimoleDashboardApp.upgrade_url }
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

			<OptimizationStatus
				settings={settings}
				setSettings={setSettings}
				setCanSave={setCanSave}
				setTab={setTab}
			/>

			{ showSPCRecommendation && (
				<SPCRecommendation />
			) }
		</div>
	);
};

export default Sidebar;


const PlaceholderInput = ({ icon = null, value, tooltipText = '' }) => {
	return (
		<Tooltip text={tooltipText}>
			<div className="grid grid-cols-1 text-gray-700 hover:text-info transition-all ease-in-out duration-300">
				<input
					value={value}
					type="text"
					disabled
					className="col-start-1 row-start-1 block w-full rounded-md !bg-light-blue py-1.5 !pl-9 pr-3 !text-gray-500 !m-0 text-sm !py-0.5 !border-0"
				/>
				<span className="pointer-events-none col-start-1 row-start-1 flex items-center ml-3 w-4 h-4 self-center">
					{icon}
				</span>
			</div>
		</Tooltip>
	);
};
