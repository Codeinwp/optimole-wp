/**
 * Tests for the video player web component.
 */

describe( 'optimole-video-player', () => {
	beforeAll( () => {
		global.OMVideoPlayerBlock = {
			play: 'Play',
			pause: 'Pause',
			mute: 'Mute',
			unmute: 'Unmute',
			fullscreen: 'Fullscreen',
			exitFullscreen: 'Exit fullscreen'
		};

		require( '../player' );
	});

	afterEach( () => {
		document.body.innerHTML = '';
	});

	const mount = ( src ) => {
		const player = document.createElement( 'optimole-video-player' );
		player.setAttribute( 'video-src', src );
		document.body.appendChild( player );

		return player.querySelector( 'video' );
	};

	test( 'uses the whole video-src attribute as the video source', () => {
		const sources = [
			'https://example.org/clip.mp4',
			'https://example.org/clip.mp4?" data-extra="1'
		];

		sources.forEach( ( src ) => {
			const video = mount( src );

			expect( video.getAttribute( 'src' ) ).toBe( src );
			expect( video.getAttributeNames().sort() ).toEqual([ 'class', 'src' ]);
		});
	});
});
