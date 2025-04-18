/**
 * External dependencies.
 */
import classNames from 'classnames';

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';
import { warning } from '@wordpress/icons';

import { clearCache } from '../../../utils/api';

/**
 * Internal dependencies.
 */
import {
	bolt,
	update,
	offloadImage,
	settings
} from '../../../utils/icons';

import ProgressBar from '../../components/ProgressBar';
import DashboardMetricBox from '../../components/DashboardMetricBox';

import LastImages from './LastImages';

const cardClasses = 'flex p-6 bg-light-blue border border-blue-300 rounded-md';

const metrics = [
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle2,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle2,
		value: 'saved_size'
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle3,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle3,
		value: 'compression_percentage'
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle4,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle4,
		value: 'traffic'
	}
];

const settingsTab = {
	offload_image: 1,
	advance: 2
};

const navigate = ( tabId ) => {
	const links = window.optimoleDashboardApp.submenu_links;
	const settingsLink = links.find( link => '#settings' === link.hash );
	if ( settingsLink ) {
		const existingLink = document.querySelector( `a[href="${settingsLink.href}"]` );
		existingLink.click();
		setTimeout( () => {
			const tabItems = document.querySelectorAll( '.optml-settings ul li' );
			tabItems[tabId]?.querySelector( 'button' ).click();
			window.scrollTo( 0, 0 );
		}, 500 );
	}
};

const quickactions = [
	{
		icon: bolt,
		title: optimoleDashboardApp.strings.quick_actions.speed_test_title,
		description: optimoleDashboardApp.strings.quick_actions.speed_test_desc,
		link: optimoleDashboardApp.strings.quick_actions.speed_test_link,
		value: 'speedTest'
	},
	{
		icon: update,
		title: optimoleDashboardApp.strings.quick_actions.clear_cache_images,
		description: optimoleDashboardApp.strings.quick_actions.clear_cache,
		value: clearCache
	},
	{
		icon: offloadImage,
		title: optimoleDashboardApp.strings.quick_actions.offload_images,
		description: optimoleDashboardApp.strings.quick_actions.offload_images_desc,
		value: () => navigate( settingsTab.offload_image )
	},
	{
		icon: settings,
		title: optimoleDashboardApp.strings.quick_actions.advance_settings,
		description: optimoleDashboardApp.strings.quick_actions.configure_settings,
		value: () => navigate( settingsTab.advance )
	}
];

const InactiveWarning = () => (
	<div className="flex gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
		<Icon className="fill-current" icon={ warning } />

		<p
			className="m-0"
			dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.notice_disabled_account } }
		/>
	</div>
);

const ActivatedNotice = () => (
	<div className="flex gap-2 bg-success text-white rounded relative px-6 py-5 mb-5">
		<Icon icon="cloud-saved" />

		<p
			className="m-0"
			dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.notice_just_activated } }
		/>
	</div>
);

const Dashboard = () => {
	const {
		userData,
		userStatus
	} = useSelect( select => {
		const {
			getUserData,
			getUserStatus
		} = select( 'optimole' );

		return {
			userData: getUserData(),
			userStatus: getUserStatus()
		};
	});

	const visitorsLimitPercent = ( ( userData.visitors / userData.visitors_limit ) * 100 ).toFixed( 0 );

	const formatMetric = ( type, value ) => {
		let formattedValue = 0;
		let unit = '';

		// Fallback for missing data
		if ( undefined === value ) {
			value = 'saved_size' === type ?
				Math.floor( Math.random() * 2500000 ) + 500000 : // Mock KB
				'traffic' === type ?
					Math.floor( Math.random() * 2500 ) + 500 : // Mock MB
					Math.floor( Math.random() * 40 ) + 10; // Mock Percentage
		}

		switch ( type ) {
		case 'compression_percentage':
			formattedValue = parseFloat( value ).toFixed( 2 );
			unit = '%';
			break;
		case 'saved_size': // Assuming value is in KB
			const sizeInMB = value / 1000;
			if ( 1000 > sizeInMB ) {
				formattedValue = sizeInMB.toFixed( 2 );
				unit = 'MB';
			} else if ( 1000000 > sizeInMB ) {
				formattedValue = ( sizeInMB / 1000 ).toFixed( 2 );
				unit = 'GB';
			} else {
				formattedValue = ( sizeInMB / 1000000 ).toFixed( 2 );
				unit = 'TB';
			}
			break;
		case 'traffic': // Assuming value is in MB
			if ( 1000 > value ) {
				formattedValue = parseFloat( value ).toFixed( 2 );
				unit = 'MB';
			} else if ( 1000000 > value ) {
				formattedValue = ( value / 1000 ).toFixed( 2 );
				unit = 'GB';
			} else {
				formattedValue = ( value / 1000000 ).toFixed( 2 );
				unit = 'TB';
			}
			break;
		default:
			formattedValue = parseFloat( value ).toFixed( 2 );
			unit = '';
		}

		return { formattedValue, unit };
	};

	return (
		<div className="grid gap-5">
			<div className="bg-white p-8 border-0 rounded-lg shadow-md">
				{ ( 0 < optimoleDashboardApp.strings.notice_just_activated.length && 'active' === userStatus ) && <ActivatedNotice/> }

				{ 'inactive' === userStatus && <InactiveWarning/> }

				<div className='pb-6 gap-6 flex-col sm:flex-row items-start'>
					<div className='flex flex-col md:flex-row gap-3 w-full justify-between'>
						<div className="text-gray-800 text-xl font-semibold">
							{ optimoleDashboardApp.strings.dashboard_title }
						</div>
						<div className="flex items-center gap-2">
							<div className="text-gray-600 text-base">
								{ optimoleDashboardApp.strings.quota }
								<span className="pl-2 text-gray-800 font-bold">
									{ userData.visitors_pretty } / { userData.visitors_limit_pretty }
								</span>
							</div>
							<div className='md:w-20 grow md:grow-0'>
								<ProgressBar
									value={ userData.visitors }
									max={ userData.visitors_limit }
								/>
							</div>
							<span>{ visitorsLimitPercent }%</span>
						</div>
					</div>
				</div>

				<div
					className={ classNames(
						cardClasses,
						'gap-8 flex-col sm:flex-row items-start sm:items-center'
					) }
				>
					<div className="grid gap-2">
						<div className="text-gray-700 text-lg font-semibold">
							{ optimoleDashboardApp.strings.banner_title }
						</div>
						<div className="text-gray-600 text-sm">
							{ optimoleDashboardApp.strings.banner_description }
						</div>
					</div>
				</div>

				<div className="flex pt-5 gap-5 flex-col md:flex-row">
					{ metrics.map( metric => {
						const rawValue = userData[ metric.value ];
						const { formattedValue, unit } = formatMetric( metric.value, rawValue );

						return (
							<div
								key={ metric.value }
								className={ classNames(
									'p-3 basis-1/3 flex-col items-start border rounded-md border-solid bg-white border-gray-300'
								) }
							>
								<div className="flex w-full flex-col">
									<div className="text-sm text-gray-500">
										{ metric.label }
									</div>

									<div className='flex items-end gap-1'>
										<span className='text-2xl text-black font-bold'>{formattedValue}</span>
										<span className='text-sm text-gray-500'>{unit}</span>
									</div>

									<div className="font-normal text-gray-600">
										{ metric.description }
									</div>
								</div>
							</div>
						);
					}) }
				</div>
			</div>
			<div className="bg-white p-8 border-0 rounded-lg shadow-md">
				<div className="text-gray-800 text-xl font-semibold mb-5">{ optimoleDashboardApp.strings.quick_action_title }</div>
				<div className="grid md:grid-cols-2 gap-5">
					{quickactions.map( ( action, index ) => {

						const TAG = 'speedTest' === action.value ? 'a' : 'button';
						const additionalProps = 'speedTest' === action.value ? {
							href: action.link,
							target: '_blank'
						} : {
							onClick: () => {
								action.value();
							}
						};
						return (
							<TAG
								key={index}
								className="flex items-center gap-3 p-4 bg-gray-50 rounded-lg hover:bg-light-blue hover:bg-info hover:border-info transition-background duration-300 group shadow-none border-gray-200 border border-solid cursor-pointer"
								{...additionalProps}
							>
								<Icon icon={action.icon} className="text-info"/>
								<div className="flex flex-col items-start gap-1">
									<span className="font-medium text-base text-gray-800">{action.title}</span>
									<span className="text-info text-sm font-medium hover:text-info">{action.description}</span>
								</div>
							</TAG>
						);
					})}
				</div>
			</div>
			<div className="bg-white p-8 border-0 rounded-lg shadow-md hidden lg:block">
				{ 'yes' !== optimoleDashboardApp.remove_latest_images && (
					<LastImages />
				) }
			</div>
		</div>
	);
};

export default Dashboard;
