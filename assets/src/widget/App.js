import MetricBoxes from './components/MetricBoxes';
import Usage from './components/Usage';
import WidgetFooter from './components/WidgetFooter';


export default function App() {
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
