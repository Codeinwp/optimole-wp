import { filter } from 'lodash';
import { SnackbarList } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

export default function() {
	const notices = useSelect(
		( select ) => select( noticesStore ).getNotices(),
		[]
	);

	const { removeNotice } = useDispatch( noticesStore );

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
