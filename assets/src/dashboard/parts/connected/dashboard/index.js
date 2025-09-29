/**
 * External dependencies.
 */
import classNames from 'classnames';

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon,
	Tooltip
} from '@wordpress/components';

import { sprintf } from '@wordpress/i18n';

import { useSelect, select } from '@wordpress/data';
import { warning, external, help } from '@wordpress/icons';

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
import { useMemo } from 'react';

const cardClasses = 'flex p-6 bg-light-blue border border-blue-300 rounded-md';

const metrics = [
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle2,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle2,
		value: 'saved_size',
		hasButton: true,
		buttonText: optimoleDashboardApp.strings.metrics.adjust_compression
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle3,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle3,
		value: 'compression_percentage',
		hasButton: true,
		buttonText: optimoleDashboardApp.strings.metrics.adjust_compression
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle4,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle4,
		value: 'traffic',
		hasButton: true,
		buttonText: optimoleDashboardApp.strings.metrics.view_analytics
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle5,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle5,
		value: 'offloaded_images',
		hasButton: true,
		buttonText: optimoleDashboardApp.strings.metrics.manage_offloading
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

	const renewalDate = useMemo( () => {
		const timestamp = userData.renews_on;

		if ( ! timestamp ) {
			return 'N/A';
		}

		const date = new Date( timestamp * 1000 );
		return date.toLocaleDateString( undefined, { year: 'numeric', month: 'short', day: 'numeric' });
	}, [ userData.renews_on ]);

	const formatMetric = ( type, value ) => {
		let formattedValue = 0;
		let unit = '';

		// Fallback for missing data
		if ( undefined === value ) {
			value = 'saved_size' === type ?
				Math.floor( Math.random() * 2500000 ) + 500000 : // Mock KB
				'traffic' === type ?
					Math.floor( Math.random() * 2500 ) + 500 : // Mock MB
					'offloaded_images' === type ?
						Math.floor( Math.random() * 500 ) + 50 : // Mock images count
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
		case 'offloaded_images':
			formattedValue = parseInt( value );
			unit = 1 === formattedValue ? optimoleDashboardApp.strings.metrics.image : optimoleDashboardApp.strings.metrics.images;
			break;
		default:
			formattedValue = parseFloat( value ).toFixed( 2 );
			unit = '';
		}

		return { formattedValue, unit };
	};

	const getMetricButtonAction = ( metricValue ) => {
		switch ( metricValue ) {
		case 'saved_size':
			return () => navigate( settingsTab.advance );
		case 'compression_percentage':
			return () => navigate( settingsTab.advance );
		case 'traffic':
			return () => {
				const newWindow = window.open( 'https://dashboard.optimole.com/metrics', '_blank' );
				if ( newWindow ) {
					newWindow.focus();
				}
			};
		case 'offloaded_images':
			return () => navigate( settingsTab.offload_image );
		}
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
							<div className="text-gray-600 text-base flex items-center gap-1">
								{ optimoleDashboardApp.strings.quota }
								<span className="text-gray-800 font-bold">
									{ userData.visitors_pretty } / { userData.visitors_limit_pretty }
								</span>
								<Tooltip
									text={
										<div className="p-2.5 max-w-[320px]">
											<div className="font-bold mb-2">
												{ optimoleDashboardApp.strings.tooltip_visits_title }
											</div>
											<div>
												{
													sprintf(
														optimoleDashboardApp.strings.tooltip_visits_description,
														renewalDate
													)
												}
											</div>
										</div>
									}
									placement="bottom"
								>
									<span className="inline-flex items-center cursor-help ml-1">
										<Icon
											icon={ help }
											size={ 18 }
											className="text-gray-400 hover:text-gray-600"
										/>
									</span>
								</Tooltip>
							</div>
							<div className='md:w-20 grow md:grow-0'>
								<ProgressBar
									value={ userData.visitors }
									max={ userData.visitors_limit }
								/>
							</div>
							<span>{ visitorsLimitPercent }%</span>
							<span className="text-gray-500 text-sm ml-2">
								{
									sprintf(
										optimoleDashboardApp.strings.renew_date,
										renewalDate
									)
								}
							</span>
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

									<div className="font-normal text-gray-600 mb-3">
										{ metric.description }
									</div>

									{ 'free' !== userData.plan && metric.hasButton && (
										<Button
											variant="secondary"
											size="small"
											className="mt-auto font-semibold rounded-md w-fit px-3 flex items-center gap-1"
											onClick={ getMetricButtonAction( metric.value ) }
										>
											{ metric.buttonText }
											{ ( 'traffic' === metric.value ) && (
												<Icon icon={ external } size={ 16 } />
											) }
										</Button>
									) }
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
