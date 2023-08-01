/**
 * WordPress dependencies.
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies.
 */
import ConflictItem from './ConflictItem';

const Conflicts = () => {
	const { conflicts } = useSelect( select => {
		const { getConflicts } = select( 'optimole' );

		const conflicts = getConflicts();

		return {
			conflicts: conflicts.conflicts || []
		};
	});

	return (
		<div className="bg-white p-8 border-0 rounded-lg shadow-md">
			{ ( Boolean( conflicts.length ) ) && (
				<>
					<p>{ optimoleDashboardApp.strings.conflicts.title }</p>

					{ conflicts.map( conflict => (
						<ConflictItem
							key={ conflict.id }
							conflict={ conflict }
						/>
					) ) }
				</>
			) }

			{ ! Boolean( conflicts.length ) && (
				<div className="text-center py-12">
					<p dangerouslySetInnerHTML={ { __html: optimoleDashboardApp.strings.conflicts.no_conflicts_found } } />
				</div>
			) }
		</div>
	);
};

export default Conflicts;
