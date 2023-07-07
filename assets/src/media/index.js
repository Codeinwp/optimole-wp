import extendFrame from './mediaFrame';

window.addEventListener( 'DOMContentLoaded', () => {

	// Extend the Select frame.
	wp.media.view.MediaFrame.Select = extendFrame( wp.media.view.MediaFrame.Select );

	// Extend the Post frame.
	wp.media.view.MediaFrame.Post = extendFrame( wp.media.view.MediaFrame.Post );
});
