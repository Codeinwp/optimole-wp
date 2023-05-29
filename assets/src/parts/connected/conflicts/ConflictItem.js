/**
 * External dependencies.
 */
import classnames from "classnames";

import { rotateRight } from '@wordpress/icons';

/**
 * WordPress dependencies.
 */
import { Button } from "@wordpress/components";

import { useDispatch } from "@wordpress/data";

import { useState } from "@wordpress/element";

const ConflictItem = ({ conflict }) => {
	const [ isLoading, setLoading ] = useState( false );

	const { dismissConflict } = useDispatch( 'optimole' );

	const dismiss = () => {
		setLoading( true );

		dismissConflict(
			conflict.id,
			() => {
				setLoading( false );
			}
		);
	};

	return (
		<div
			className={ classnames(
				'flex gap-2 text-white rounded relative px-6 py-5 mb-5',
				{
					'bg-danger': conflict.severity === 'high',
					'bg-info': conflict.severity === 'medium',
					'bg-success': conflict.severity !== 'high' && conflict.severity !== 'medium',
				}
			) }
		>
			<p
				className="m-0 text-white"
				dangerouslySetInnerHTML={ { __html: conflict.message } }
			/>

			<Button
				icon={ isLoading ? rotateRight : 'no-alt' }
				label={ optimoleDashboardApp.strings.conflicts.conflict_close }
				disabled={ isLoading }
				className={ classnames(
					'text-white hover:text-white',
					{
						'animate-spin': isLoading,
					}
				) }
				onClick={ dismiss }
			/>
		</div>
	);
};

export default ConflictItem;
