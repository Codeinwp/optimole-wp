/**
 * External dependencies.
 */
import classnames from 'classnames';

import { rotateRight } from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import { Button } from '@wordpress/components';

import { useState } from '@wordpress/element';

/**
 * Internal dependencies.
 */
import { dismissConflict } from '../../../utils/api';

const ConflictItem = ({ conflict }) => {
	const [ isLoading, setLoading ] = useState( false );

	const dismiss = () => {
		setLoading( true );

		dismissConflict(
			conflict.id,
			() => {
				setLoading( false );
			}
		);
	};

	const getBorderColor = () => {
		if ( 'high' === conflict.severity ) {
			return '#E77777';
		} else if ( 'medium' === conflict.severity ) {
			return '#577BF9';
		}
		return '#5F9D61';
	};

	const noticeStyles = {
		border: '1px solid #c3c4c7',
		borderLeftWidth: '4px',
		borderLeftColor: getBorderColor(),
		margin: '5px 0 15px',
		padding: '12px',
		paddingRight: '38px',
		position: 'relative'
	};

	const messageStyles = {
		margin: '0',
		padding: '2px',
		lineHeight: '1.5',
		fontSize: '15px'
	};

	return (
		<div style={ noticeStyles }>
			<p
				style={ messageStyles }
				dangerouslySetInnerHTML={ { __html: conflict.message } }
			/>

			<Button
				icon={ isLoading ? rotateRight : 'no-alt' }
				label={ optimoleDashboardApp.strings.conflicts.conflict_close }
				disabled={ isLoading }
				className={ classnames(
					'text-black hover:text-black',
					{
						'animate-spin': isLoading
					}
				) }
				onClick={ dismiss }
				style={{
					position: 'absolute',
					top: '8px',
					right: '8px'
				}}
			/>
		</div>
	);
};

export default ConflictItem;
