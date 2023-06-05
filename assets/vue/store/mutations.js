/* jshint esversion: 6 */
const toggleCheckedOffloadConflicts = ( state, data ) => {
	state.checkedOffloadConflicts = data;
};

const estimatedTime = ( state, data ) => {
	state.sumTime = ( state.sumTime + data.batchTime );
	state.estimatedTime = ( ( state.sumTime/data.processedBatch )*( Math.ceil( state.totalNumberOfImages/data.batchSize ) - data.processedBatch ) / 60000 ).toFixed( 2 );
};
const updateOffloadConflicts = ( state, data ) => {
	state.offloadConflicts = data.body.data;
};
export default {
	estimatedTime,
	toggleCheckedOffloadConflicts,
	updateOffloadConflicts,
};
