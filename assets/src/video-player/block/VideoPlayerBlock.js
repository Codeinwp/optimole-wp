import { BlockControls, InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	Placeholder,
	SelectControl,
	ToolbarButton,
	ToolbarGroup,
	Notice
} from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { edit } from '@wordpress/icons';
import { logo } from '../common/icons';

import { isOptimoleURL } from '../common/utils';

const Edit = ( props ) => {
	const { replaceBlock } = useDispatch( 'core/block-editor' );
	const { attributes, setAttributes, isSelected } = props;

	const [ loading, setLoading ] = useState( false );
	const [ editing, setEditing ] = useState( false );
	const [ url, setUrl ] = useState( attributes.url );
	const [ error, setError ] = useState( false );

	const onUrlChange = ( value ) => {
		setUrl( value );
	};

	const onAspectRatioChange = ( value ) => {
		setAttributes({ aspectRatio: value });
	};

	const onEdit = () => {
		setEditing( true );
	};

	const onSave = ( event ) => {
		event.preventDefault();
		setError( false );


		const url = event.target.url.value;

		isOptimoleURL( url ).then( ( valid ) => {
			if ( valid ) {
				setAttributes({ url });
				setEditing( false );
				return;
			}

			setError( true );
		});

	};

	useEffect( () => {
		isOptimoleURL( attributes.url ).then( ( valid ) => {

			if ( ! valid ) {
				setEditing( true );
				setError( true );
			}
		});
	}, [ attributes.url ]);

	const aspectRatioOptions = useMemo( () => {
		return OMVideoPlayerBlock.aspectRatioOptions.map( ( value ) => ({
			label: value,
			value: value.replace( ':', '/' )
		}) );
	}, []);

	const style = useMemo( () => {


		const style = {
			'--om-primary-color': attributes.primaryColor,
			'--om-aspect-ratio': attributes.aspectRatio
		};

		if ( ! isSelected ) {
			style.pointerEvents = 'none';
		}

		return style;
	}, [ attributes.aspectRatio, attributes.primaryColor, isSelected ]);

	return (
		<>
			<BlockControls>
				<ToolbarGroup>
					<ToolbarButton
						icon={edit}
						label={OMVideoPlayerBlock.editLabel}
						onClick={onEdit}
					/>
				</ToolbarGroup>
			</BlockControls>

			<InspectorControls>
				<PanelBody title={OMVideoPlayerBlock.blockTitle} initialOpen={true}>

					<SelectControl
						value={attributes.aspectRatio}
						label={OMVideoPlayerBlock.aspectRatioLabel}
						options={aspectRatioOptions}
						onChange={onAspectRatioChange}
					/>

				</PanelBody>
			</InspectorControls>
			{! editing && <optimole-video-player video-src={props.attributes.url} style={style}></optimole-video-player>}
			{editing && <Placeholder
				label={OMVideoPlayerBlock.urlLabel}
				className="wp-block-embed"
			>
				<form onSubmit={onSave}>
					<input type="url" id="url" defaultValue={attributes.url} className="wp-block-embed__placeholder-input" onChange={onUrlChange} />
					<Button isPrimary type="submit">Save</Button>
				</form>

				{error && <Notice status="error" isDismissible={false}><span dangerouslySetInnerHTML={{ __html: OMVideoPlayerBlock.invalidUrlError }}/></Notice>}

			</Placeholder>
			}
		</>
	);
};

export default {
	icon: logo,
	title: OMVideoPlayerBlock.blockTitle,
	description: OMVideoPlayerBlock.blockDescription,

	supports: {
		align: true,
		inserter: false,
		spacing: {
			margin: true
		}
	},
	category: 'embed',
	attributes: {
		url: {
			type: 'string',
			default: ''
		},
		aspectRatio: {
			type: 'string',
			default: '16/9'
		},
		primaryColor: {
			type: 'string',
			default: '#577BF9'
		}
	},
	edit: Edit,
	save: ( props ) => {
		return null;
	}
};
