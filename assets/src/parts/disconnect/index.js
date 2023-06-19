/**
 * WordPress dependencies.
 */
import {
	Button,
	Modal
} from '@wordpress/components';

import {
	useDispatch,
	useSelect
} from '@wordpress/data';

/**
 * Internal dependencies.
 */
import { disconnectAccount } from '../../utils/api';

const DisconnectLayout = () => {
	const { setShowDisconnect } = useDispatch( 'optimole' );

	const { isLoading } = useSelect( select => {
		const { isLoading } = select( 'optimole' );

		return {
			isLoading: isLoading()
		};
	});

	return (
		<Modal
			title={ optimoleDashboardApp.strings.disconnect_title }
			className="optml__modal"
			onRequestClose={ () => setShowDisconnect( false ) }
		>
			<p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.disconnect_desc } } />

			<div className="flex gap-2">
				<Button
					variant="default"
					className="optml__button flex w-full justify-center rounded font-bold mt-4 min-h-40"
					onClick={ () => setShowDisconnect( false ) }
				>
					{ optimoleDashboardApp.strings.keep_connected }
				</Button>

				<Button
					isDestructive
					className="optml__button flex w-full justify-center rounded font-bold mt-4 min-h-40"
					isBusy={ isLoading }
					disabled={ isLoading }
					onClick={ disconnectAccount }
				>
					{ optimoleDashboardApp.strings.disconnect_btn }
				</Button>
			</div>
		</Modal>
	);
};

export default DisconnectLayout;
