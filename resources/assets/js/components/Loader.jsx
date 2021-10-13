import React from "react";

class Loader extends React.Component {
    render() {
        return(
            <div className="loading">
                <div className="loader">
                    <div className="loading-bar"></div>
                    <div className="loading-bar"></div>
                    <div className="loading-bar"></div>
                    <div className="loading-bar"></div>
                </div>
            </div>
        )
    }

};


export default Loader;