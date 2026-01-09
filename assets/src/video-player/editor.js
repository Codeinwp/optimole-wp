/** global OMVideoPlayerBlock */
import { registerBlockType, registerBlockVariation } from '@wordpress/blocks';
import { addFilter } from '@wordpress/hooks';

import { withCustomEmbedURLDetection } from './block/hoc';
import VideoPlayerBlock from './block/VideoPlayerBlock';
import { logo } from './common/icons';

import './web-components/player';

/**
 * Register the video player block.
 */
registerBlockType( 'optimole/video-player', VideoPlayerBlock );

/**
 * Register the embed video player block variation.
 */
registerBlockVariation( 'core/embed', {
	name: 'optimole',
	attributes: {
		providerNameSlug: 'optimole'
	},
	icon: logo,
	title: OMVideoPlayerBlock.blockTitle,
	description: OMVideoPlayerBlock.blockDescription,
	category: 'embed',
	isActive: ( blockAttributes, variationAttributes ) => {
		return 'optimole' === blockAttributes.providerNameSlug;
	},
	patterns: [
		`^https?:\\/\\/(?:www\\.)?${OMVideoPlayerBlock.domain}\\/.*\\.(mp4|webm|ogg|mov|avi|mkv|m4v)$`
	]
});

/**
 * Filter to add custom embed detection to the editor.
 */
addFilter(
	'editor.BlockEdit',
	'custom-embed/with-custom-embed-detection',
	withCustomEmbedURLDetection
);
