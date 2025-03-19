import { useMemo } from '@wordpress/element';

import {
	compressionPercentage,
	traffic
} from '../../dashboard/utils/icons';

import DashboardMetricBox from '../../dashboard/parts/components/DashboardMetricBox';

export default function MetricBoxes() {
	const { i18n, serviceData } = optimoleDashboardWidget;

	const METRICS = [
		{
			label: i18n.averageCompression,
			description: i18n.duringLastMonth,
			value: 'compression_percentage',
			icon: compressionPercentage
		},
		{
			label: i18n.traffic,
			description: i18n.duringLastMonth,
			value: 'traffic',
			icon: traffic
		}
	];

	const getMetricValue = ( metric ) => useMemo( () => {
		const metricValue = serviceData[ metric ];

		if ( 'compression_percentage' === metric ) {
			return metricValue.toFixed( 2 ) + '%';
		}

		if ( 'traffic' === metric ) {
			if ( 1000 < metricValue ) {
				return ( metricValue / 1000 ).toFixed( 2 ) + 'GB';
			}

			return metricValue.toFixed( 2 ) + 'MB';
		}

		return metricValue;
	}, [ serviceData ]);

	return (
		<div className="grid grid-cols-2 gap-4">
			{ METRICS.map( metric => (
				<DashboardMetricBox
					key={ metric.value }
					value={ getMetricValue( metric.value ) }
					label={ metric.label }
					description={ metric.description }
					icon={ metric.icon }
					compact={true}
				/>
			) ) }
		</div>
	);
}
