import classNames from 'classnames';

export const GroupSettingsContainer = ({ children, className = '' }) => {
	return (
		<div className={classNames( 'my-4 p-4 rounded border-solid border-gray-200 bg-slate-100', className )}>
			{ children }
		</div>
	);
};

export const GroupSettingsTitle = ({ children, className = '' }) => {
	return (
		<h3 className={classNames( 'text-sm text-slate-500 m-0 font-semibold mb-4 uppercase', className )}>
			{ children }
		</h3>
	);
};

export const GroupSettingsOption = ({ children, className = '' }) => {
	return (
		<div className={classNames( 'text-sm text-gray-600 px-4 py-2 bg-white flex flex-wrap items-center rounded', className )}>
			{ children }
		</div>
	);
};
