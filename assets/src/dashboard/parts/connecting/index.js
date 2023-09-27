/**
 * WordPress dependencies.
 */
import { useDispatch } from '@wordpress/data';

import {
	useEffect,
	useState
} from '@wordpress/element';

/**
 * Internal dependencies.
 */
import ProgressBar from '../components/ProgressBar';

const progressMessages = [
	optimoleDashboardApp.strings.options_strings.connect_step_0,
	optimoleDashboardApp.strings.options_strings.connect_step_1,
	optimoleDashboardApp.strings.options_strings.connect_step_2,
	optimoleDashboardApp.strings.options_strings.connect_step_3
];

const ConnectingLayout = () => {
	const [ progress, setProgress ] = useState( 0 );
	const [ step, setStep ] = useState( 0 );
	const [ timer, setTimer ] = useState( 0 );
	const maxTime = 25;

	const { sethasDashboardLoaded } = useDispatch( 'optimole' );

	useEffect( () => {
		if ( timer <= maxTime ) {
			setTimeout( () => {
				updateProgress();
			}, 1000 );
		}
	}, [ timer ]);

	const updateProgress = () => {
		setTimer( timer + 1 );

		if ( timer >= maxTime ) {
			sethasDashboardLoaded( true );
			setTimer( 0 );
			setStep( 0 );
			setProgress( 0 );
			return;
		}

		setProgress( Math.floor( ( timer / maxTime ) * 100 ) );

		if ( progress > ( ( step + 1 ) * 30 ) ) {
			setStep( step + 1 );
		}
	};

	return (
		<div className="optml-connecting flex flex-col justify-between max-w-screen-xl mt-8 mb-5 mx-auto p-0 transition-all ease-in-out duration-700 relative bg-white text-gray-700 border-0 rounded-lg shadow-md">
			<div className="flex flex-col md:flex-row items-center justify-center gap-5 px-8 sm:px-0 py-24">
				<img className="max-w-full" src={ optimoleDashboardApp.assets_url + 'img/connecting.png' } />

				<div className="text-center md:text-left">
					<div className="font-bold text-2xl">{ optimoleDashboardApp.strings.account_connecting_title }</div>
					<div className="text-base">{ optimoleDashboardApp.strings.account_connecting_subtitle }</div>

					<ProgressBar
						className="mt-2.5 mb-1.5 mx-0 h-5 border-gray-200"
						value={ timer }
						max={ maxTime }
						background="#e6e6e6"
					/>

					<div className="text-xs">{ progressMessages[step] + ' ' + '(' + progress + '%)' }</div>
				</div>
			</div>
		</div>
	);
};

export default ConnectingLayout;
