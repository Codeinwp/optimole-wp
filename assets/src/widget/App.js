
import { useState, useEffect } from '@wordpress/element';

import MetricBoxes from './components/MetricBoxes';
import WidgetFooter from './components/WidgetFooter';
import Usage from './components/Usage';


export default function App() {

	const [ isLoading, setIsLoading ] = useState( true );

	return (
		<div className="antialiased">
			<div className="p-4 flex flex-col gap-4">
				<Usage />
				<MetricBoxes/>
			</div>

			<hr className="border-gray-300 m-0 border-0 border-b"/>

			<WidgetFooter />
		</div>
	);
}
