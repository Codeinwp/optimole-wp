const VIDEO_EXTENSIONS = [ 'mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv', 'm4v' ];

const hasVideoExtension = ( url ) => {
	try {
		const { pathname } = new URL( url );
		const extension = pathname.split( '.' ).pop()?.toLowerCase();
		return VIDEO_EXTENSIONS.includes( extension );
	} catch {
		return false;
	}
};

export const isOptimoleURL = async( url ) => {
	const customEmbedRegexPattern = new RegExp( `^https?:\\/\\/(?:www\\.)?${OMVideoPlayerBlock.domain}\\/.*$` );

	if ( ! customEmbedRegexPattern.test( url ) ) {
		return false;
	}

	if ( ! hasVideoExtension( url ) ) {
		return false;
	}

	try {
		const response = await fetch( url, { method: 'HEAD' });
		if ( response.ok ) {
			const contentType = response.headers.get( 'content-type' );

			return contentType.includes( 'video' );
		}
		return false;
	} catch ( error ) {
		return false;
	}
};
