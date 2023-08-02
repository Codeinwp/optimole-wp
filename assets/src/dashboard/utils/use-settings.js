/**
 * WordPress dependencies.
 */
import api from '@wordpress/api';

import { __ } from '@wordpress/i18n';

import {
	useEffect,
	useState
} from '@wordpress/element';

/**
 * useSettings Hook.
 *
 * useSettings hook to get/update WordPress' settings database.
 *
 * Setting field needs to be registered to REST for this function to work.
 *
 * @author  Hardeep Asrani <hardeepasrani@gmail.com>
 * @version 1.0
 *
 */
const useSettings = () => {
	const [ settings, setSettings ] = useState({});
	const [ status, setStatus ] = useState( 'loading' );

	const getSettings = () => {
		api.loadPromise.then( async() => {
			try {
				const settings = new api.models.Settings();
				const response = await settings.fetch();
				setSettings( response );
			} catch ( error ) {
				setStatus( 'error' );
			} finally {
				setStatus( 'loaded' );
			}
		});
	};

	useEffect( () => {
		getSettings();
	}, []);

	const getOption = option => {
		return settings?.[option];
	};

	const updateOption = ( option, value, success = __( 'Settings saved.', 'textdomain' ) ) => {
		setStatus( 'saving' );

		const save = new api.models.Settings({ [option]: value }).save();

		save.success( ( response, status ) => {
			if ( 'success' === status ) {
				setStatus( 'loaded' );
			}

			if ( 'error' === status ) {
				setStatus( 'error' );
			}

			getSettings();
		});

		save.error( ( response ) => {
			setStatus( 'error' );
		});
	};

	return [ getOption, updateOption, status ];
};

export default useSettings;
