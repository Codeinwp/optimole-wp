export const isOptimoleURL = async( url ) => {
	const customEmbedRegexPattern = new RegExp( `^https?:\\/\\/(?:www\\.)?${OMVideoPlayerBlock.domain}\\/.*$` );

	if ( !  customEmbedRegexPattern.test( url ) ) {
		return false;
	}

	try {
		const response = await fetch( url, { method: 'HEAD' });
		if ( response.ok ) {
			const contentType = response.headers.get( 'content-type' );

			return contentType.includes( 'video' );
		}
	} catch ( error ) {
		return false;
	}
};
