import { BlockControls, InspectorControls } from '@wordpress/block-editor';
import {
	Button,
	PanelBody,
	Placeholder,
	SelectControl,
	ToolbarButton,
	ToolbarGroup,
	Notice,
	CheckboxControl,
	ColorPalette,
	BaseControl
} from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { useEffect, useMemo, useState } from '@wordpress/element';
import { edit } from '@wordpress/icons';
import { logo } from '../common/icons';
import { ASPECT_RATIO_OPTIONS } from '../common/constants';
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


	const onAttributeUpdate = ( value, name ) => {
		setAttributes({ [name]: value });
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
						options={ASPECT_RATIO_OPTIONS}
						onChange={( value ) => onAttributeUpdate( value, 'aspectRatio' )}
					/>

					<CheckboxControl
						label={OMVideoPlayerBlock.loopLabel}
						checked={attributes.loop}
						onChange={( value ) => onAttributeUpdate( value, 'loop' )}
					/>

					<CheckboxControl
						label={OMVideoPlayerBlock.hideControlsLabel}
						checked={attributes.hideControls}
						onChange={( value ) => onAttributeUpdate( value, 'hideControls' )}
					/>

				</PanelBody>
			</InspectorControls>
			<InspectorControls group="styles">
				<PanelBody title={OMVideoPlayerBlock.blockTitle} initialOpen={true}>
					<BaseControl label={OMVideoPlayerBlock.primaryColorLabel}>
						<ColorPalette
							clearable={false}
							value={attributes.primaryColor}
							onChange={( value ) => onAttributeUpdate( value, 'primaryColor' )}
						/>
					</BaseControl>
				</PanelBody>
			</InspectorControls>
			<div style={{ display: 'flex', width: '100%' }}>
				{! editing && <optimole-video-player video-src={props.attributes.url} style={style} loop={attributes.loop} hide-controls={attributes.hideControls}></optimole-video-player>}
				{editing && <Placeholder
					label={OMVideoPlayerBlock.urlLabel}
					className="wp-block-embed"
				>
					<form onSubmit={onSave}>
						<input type="url" id="url" defaultValue={attributes.url} className="wp-block-embed__placeholder-input" onChange={onUrlChange} />
						<Button isPrimary type="submit">{OMVideoPlayerBlock.save}</Button>
					</form>

					{error && <Notice status="error" isDismissible={false}><span dangerouslySetInnerHTML={{ __html: OMVideoPlayerBlock.invalidUrlError }}/></Notice>}

				</Placeholder>
				}
			</div>
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
			default: 'auto'
		},
		primaryColor: {
			type: 'string',
			default: '#577BF9'
		},
		loop: {
			type: 'boolean',
			default: false
		},
		hideControls: {
			type: 'boolean',
			default: false
		}
	},
	edit: Edit,
	save: ( props ) => {
		return null;
	}
};
