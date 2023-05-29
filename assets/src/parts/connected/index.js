/**
 * Internal dependencies.
 */
import Dashboard from "./Dashboard";
import Sidebar from "./Sidebar";

const ConnectedLayout = ({
    tab
}) => {
    return (
        <div className="optml-connected max-w-screen-xl flex flex-col lg:flex-row mx-auto gap-5">
            <div
                className="flex flex-col justify-between mt-8 mb-5 p-0 transition-all ease-in-out duration-700 relative text-gray-700 basis-9/12"
            >
                { tab === 'dashboard' && <Dashboard/> }
            </div>

            <Sidebar/>
        </div>
    );
}

export default ConnectedLayout;


// Todo: Show upgrade based on should_show_upgrade
// Todo: Conflict tabs
