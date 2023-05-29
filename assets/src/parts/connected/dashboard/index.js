/**
 * External dependencies.
 */
import classNames from "classnames";

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon
} from "@wordpress/components";

import { useSelect } from "@wordpress/data";

/**
 * Internal dependencies.
 */
import {
	imagesNumber,
	savedSize,
	compressionPercentage,
	traffic,
	quota,
	warning
} from "../../../utils/icons";

import LastImages from "./LastImages";

const cardClasses = 'flex p-6 bg-light-blue border border-blue-300 rounded-md';

const metrics = [
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle1,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle1,
		value: 'images_number',
		icon: imagesNumber
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle2,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle2,
		value: 'saved_size',
		icon: savedSize
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle3,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle3,
		value: 'compression_percentage',
		icon: compressionPercentage
	},
	{
		label: optimoleDashboardApp.strings.metrics.metricsTitle4,
		description: optimoleDashboardApp.strings.metrics.metricsSubtitle4,
		value: 'traffic',
		icon: traffic
	},
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
	} );

	const visitorsLimitPercent = ( ( userData.visitors / userData.visitors_limit ) * 100 ).toFixed( 0 );

	const getMetricValue = metric => {
		if ( undefined !== userData[ metric ] ) {
			return userData[ metric ];
		}

		if ( metric === 'saved_size' ) {
			return Math.floor( Math.random() * 2500 ) + 500;
		}

		return Math.floor( Math.random() * 40 ) + 10;
	};

	const formatMetricValue = metric => {
		const value = getMetricValue( metric );

		if ( metric === 'saved_size' ) {
			return ( value / 1000 ).toFixed( 2 ) + 'MB';
		}

		if ( metric === 'compression_percentage' ) {
			return value.toFixed( 2 ) + '%';
		}

		if ( metric === 'traffic' ) {
			return value.toFixed( 2 ) + 'MB';
		}

		return value;
	};

	return (
		<div className="bg-white p-8 border-0 rounded-lg shadow-md">
			{ ( optimoleDashboardApp.strings.notice_just_activated.length > 0 && userStatus === 'active' ) && <ActivatedNotice/> }

			{ userStatus === 'inactive' && <InactiveWarning/> }

			<div
				className={ classNames(
					cardClasses,
					'items-center gap-8',
				) }
			>
				<Icon icon={ quota } />

				<div className="flex w-full flex-col">
					<div className="flex w-full justify-between">
						<div className="text-gray-800 text-2xl font-bold">
							{ userData.visitors_pretty } / { userData.visitors_limit_pretty }

							<span className="text-gray-600 text-base font-normal ml-2">
								{ optimoleDashboardApp.strings.quota }
							</span>
						</div>


						<Button
							variant="default"
							className="optml__button rounded font-bold min-h-40"
							href="https://optimole.com/pricing"
							target="_blank"
						>
							{ optimoleDashboardApp.strings.upgrade.title }
						</Button>
					</div>

					<div>
						<progress
							className="mt-2.5 mb-3 mx-0"
							value={ userData.visitors }
							max={ userData.visitors_limit }
						/>

						<div
							className="optml__tooltip"
							style={ {
								left: visitorsLimitPercent + '%',
								marginLeft: visitorsLimitPercent < 15 ? '-15px' : '-20px',
								display: visitorsLimitPercent > 100 ? 'none' : 'block'
							} }
						>
							<span>{ visitorsLimitPercent }%</span>
						</div>
					</div>
				</div>
			</div>

			<div className="flex py-5 gap-5 flex-col md:flex-row">
				{ metrics.map( metric => {
					return (
						<div
							key={ metric.value }
							className={ classNames(
								cardClasses,
								'basis-1/4 flex-col items-start',
							) }
						>
							<Icon icon={ metric.icon } />

							<div className="flex w-full flex-col">
								<div className="not-italic font-bold text-xl py-2 text-gray-800">
									{ formatMetricValue( metric.value ) }
								</div>

								<div className="not-italic font-normal text-sm text-gray-800">
									{ metric.label }
								</div>

								<div className="font-normal text-xs text-gray-600">
									{ metric.description }
								</div>
							</div>
						</div>
					);
				} ) }
			</div>

			{ optimoleDashboardApp.remove_latest_images !== 'yes' && (
				<LastImages />
			) }
		</div>
	);
}

export default Dashboard;
