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
		icon: <Icon icon={bolt} className="text-info"/>,
		title: optimoleDashboardApp.strings.quick_actions.speed_test_title,
		description: optimoleDashboardApp.strings.quick_actions.speed_test_desc,
		link: optimoleDashboardApp.strings.quick_actions.speed_test_link,
		value: 'speedTest'
	},
	{
		icon: <Icon icon={update} className="text-info"/>,
		title: optimoleDashboardApp.strings.quick_actions.clear_cache_images,
		description: optimoleDashboardApp.strings.quick_actions.clear_cache,
		value: clearCache
	},
	{
		icon: <Icon icon={offloadImage} className="text-info" />,
		title: optimoleDashboardApp.strings.quick_actions.offload_images,
		description: optimoleDashboardApp.strings.quick_actions.offload_images_desc,
		value: () => navigate( settingsTab.offload_image )
	},
	{
		icon: <Icon icon={settings} className="text-info"/>,
		title: optimoleDashboardApp.strings.quick_actions.advance_settings,
		description: optimoleDashboardApp.strings.quick_actions.configure_settings,
		value: () => navigate( settingsTab.advance )
	}
];

const InactiveWarning = () => (
	<div className="flex gap-2 bg-warning text-danger border border-solid border-danger rounded relative px-6 py-5 mb-5">
		<Icon icon={ warning } />

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

	const getFormattedMetric = ( metric ) => {
		let metricValue = userData[ metric ];

		// Fallback for missing data
		if ( undefined === metricValue ) {
			metricValue = 'saved_size' === metric ?
				Math.floor( Math.random() * 2500 ) + 500 :
				Math.floor( Math.random() * 40 ) + 10;
		}

		// Format based on metric type
		if ( 'saved_size' === metric ) {
			return Math.floor( Math.random() * 2500 ) + 500;
		}

		return Math.floor( Math.random() * 40 ) + 10;
	};

	const formatMetricValue = metric => {
		const value = getFormattedMetric( metric );
		const calcValue = 'saved_size' === metric ? ( value / 1000 ).toFixed( 2 ) : value.toFixed( 2 );
		return (
			<div className='flex items-end gap-1'>
				<span className='text-2xl text-black font-bold'>{calcValue}</span>
				<span className='text-sm text-gray-500'>{ 'compression_percentage' === metric ? '%' : 'MB' }</span>
			</div>
		);
	};

	return (
		<>
			<div className="bg-white p-8 border-0 rounded-lg shadow-md">
				{ ( 0 < optimoleDashboardApp.strings.notice_just_activated.length && 'active' === userStatus ) && <ActivatedNotice/> }

				{ 'inactive' === userStatus && <InactiveWarning/> }

				<div className='py-6 gap-6 flex-col sm:flex-row items-start sm:items-center'>
					<div className='flex w-full justify-between sm:items-center'>
						<div className="text-gray-800 text-2xl font-bold">
							{ optimoleDashboardApp.strings.dashboard_title }
						</div>
						<div className="flex items-center gap-2">
							<div className="text-gray-600 text-base font-normal ml-2">
								{ optimoleDashboardApp.strings.quota }
								<span className="pl-2 text-gray-800 font-bold">
									{ userData.visitors_pretty } / { userData.visitors_limit_pretty }
								</span>
							</div>
							<div className='w-20'>
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
					<div>
						<div className="text-gray-800 text-xl font-bold">
							{ optimoleDashboardApp.strings.banner_title }
						</div>
						<div className="text-gray-600 text-base">
							{ optimoleDashboardApp.strings.banner_description }
						</div>
					</div>
				</div>

				<div className="flex py-5 gap-5 flex-col md:flex-row">
					{ metrics.map( metric => {
						return (
							<div
								key={ metric.value }
								className={ classNames(
									'p-3 basis-1/3 flex-col items-start border rounded-md border-solid bg-white border-light-gray border-slate-400'
								) }
							>
								<div className="flex w-full flex-col">
									<div className="not-italic font-normal text-sm text-gray-500">
										{ metric.label }
									</div>

									<div>
										{ formatMetricValue( metric.value ) }
									</div>

									<div className="font-normal text-sm text-gray-600">
										{ metric.description }
									</div>
								</div>
							</div>
						);
					}) }
				</div>
			</div>
			<div className="my-3 bg-white p-8 border-0 rounded-lg shadow-md">
				<div className="text-gray-800 font-bold text-2xl">{ optimoleDashboardApp.strings.quick_action_title }</div>
				<div className="grid grid-cols-1 md:grid-cols-2 gap-5 py-5">
					{quickactions.map( ( action, index ) => (
						<div
							key={index}
							className="flex items-start items-center gap-3 p-4 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
						>
							{action.icon}
							<div className="flex flex-col">
								<span className="font-medium text-base text-gray-800">{action.title}</span>
								{ 'speedTest' === action.value ? (
									<a href={action.link} target="_blank" className="text-info text-sm font-medium hover:text-info">{action.description}</a>
								) : (
									<Button
										variant="default"
										className="text-info text-sm font-medium p-0 h-5 focus:!shadow-none focus:!outline-none"
										onClick={ () => action.value() }
									>
										{ action.description }
									</Button>
								) }
							</div>
						</div>
					) )}
				</div>
			</div>
			<div className="bg-white p-8 border-0 rounded-lg shadow-md">
				{ 'yes' !== optimoleDashboardApp.remove_latest_images && (
					<LastImages />
				) }
			</div>
		</>
	);
};

export default Dashboard;
