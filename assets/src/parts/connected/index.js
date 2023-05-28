/**
 * WordPress dependencies.
 */
import Sidebar from "./Sidebar";

const ConnectedLayout = () => {
    return (
        <div className="optml-connected max-w-screen-xl flex flex-col lg:flex-row mx-auto gap-5">
            <div
                className="flex flex-col justify-between mt-12 mb-5 p-0 transition-all ease-in-out duration-700 relative bg-white text-gray-700 border-0 rounded-lg shadow-md basis-9/12"
            >
                Content
            </div>

            <Sidebar/>
        </div>
    );
}

export default ConnectedLayout;
