import extendFrame from './modal/mediaFrame';
import './scss/media-modal.scss';

window.addEventListener( 'DOMContentLoaded', () => {

	// Bail if the media frame is not available.
	if ( ! wp?.media?.view ) {
		return;
	}

	// Extend the Select frame - used for single selection.
	wp.media.view.MediaFrame.Select = extendFrame( wp.media.view.MediaFrame.Select );

	// Extend the Post frame - used for multiple selection (mainly galleries).
	wp.media.view.MediaFrame.Post = extendFrame( wp.media.view.MediaFrame.Post );
});
