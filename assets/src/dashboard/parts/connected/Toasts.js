import { filter } from 'lodash';
import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';

export default function() {
	const notices = useSelect(
		( select ) => select( 'core/notices' ).getNotices(),
		[]
	);

	const { removeNotice } = useDispatch( 'core/notices' );

	const snackbarNotices = filter( notices, {
		type: 'snackbar'
	});

	return (
		<SnackbarList
			notices={snackbarNotices}
			className="px-2 bottom-4 fixed"
			onRemove={removeNotice}
		/>
	);
};
