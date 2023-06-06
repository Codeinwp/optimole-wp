/* jshint esversion: 6 */
const toggleCheckedOffloadConflicts = ( state, data ) => {
	state.checkedOffloadConflicts = data;
};

const updateOffloadConflicts = ( state, data ) => {
	state.offloadConflicts = data.body.data;
};
export default {
	toggleCheckedOffloadConflicts,
	updateOffloadConflicts,
};
