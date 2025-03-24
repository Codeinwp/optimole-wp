import { createHigherOrderComponent } from '@wordpress/compose';
import { useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { createBlock } from '@wordpress/blocks';
import { isOptimoleURL } from '../common/utils';
const BlockReplacer = ({ clientIDToReplace, url }) => {
	const { replaceBlock } = useDispatch( 'core/block-editor' );

	useEffect( ()=> {
		const block = createBlock( 'optimole/video-player', {
			url: url
		});

		replaceBlock( clientIDToReplace, block );
	}, [ clientIDToReplace ]);

	return null;
};

/**
 * Higher order component to check URLs and auto-detect custom embeds
 */
export const withCustomEmbedURLDetection = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		const [ validOptimoleURL, setValidOptimoleURL ] = useState( false );

		const { attributes } = props;
		const { url } = attributes;

		useEffect( ()=> {
			isOptimoleURL( props.attributes.url ).then( ( valid ) => {
				setValidOptimoleURL( valid );
			});
		}, [ props.attributes.url ]);

		if ( 'core/embed' !== props.name ) {
			return <BlockEdit {...props} />;
		}


		if ( url && validOptimoleURL ) {
			return <BlockReplacer clientIDToReplace={props.clientId} url={attributes.url} />;
		}

		return <BlockEdit {...props} />;
	};
}, 'withCustomEmbedURLDetection' );
