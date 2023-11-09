/**
 * External dependencies.
 */
import { formatItalic } from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import {
	Button,
	Icon
} from '@wordpress/components';

import { useSelect } from '@wordpress/data';

import {
	useEffect,
	useRef,
	useState
} from '@wordpress/element';

const Logs = ({ type }) => {
	const { logs } = useSelect( select => {
		const { getLogs } = select( 'optimole' );

		return {
			logs: getLogs()
		};
	});

	const [ show, setShow ] = useState( false );
	const ref = useRef( null );

	useEffect( () => {
		const interval = setInterval( () => {
			if ( ref.current ) {
				ref.current.innerHTML = logs[ type ];
				ref.current.scrollTop = ref.current.scrollHeight;
			}
		}, 15000 );

		return () => clearInterval( interval );
	}, []);

	return (
		<>
			<div className="w-full flex justify-end text-info my-2">
				<Button
					variant="tertiary"
					className="py-2 px-0"
					target='_blank'
					href={ `${ window.ajaxurl }?action=optml_fetch_logs&nonce=${ optimoleDashboardApp.nonce }&type=${ type }` }
				>
					{ optimoleDashboardApp.strings.options_strings.view_logs }
				</Button>

				<Icon
					icon={ formatItalic }
					className="fill-info py-2"
				/>

				<Button
					variant="tertiary"
					className="py-2 px-0"
					onClick={ () => setShow( ! show )}
				>
					{ show ? optimoleDashboardApp.strings.options_strings.hide_logs : optimoleDashboardApp.strings.options_strings.show_logs }
				</Button>
			</div>

			{ show && (
				<div
					className="flex flex-col p-6 bg-light-blue border border-blue-300 rounded-md min-h-[200px] max-h-[200px] overflow-y-scroll"
					ref={ ref }
					dangerouslySetInnerHTML={{ __html: logs[ type ] }}
				/>
			) }
		</>
	);
};

export default Logs;
