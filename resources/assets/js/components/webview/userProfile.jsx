import React from 'react';
import {Link} from 'react-router-dom';

class userProfile extends React.Component{
    constructor(props){
        super(props)
    }
    render(){
        return (
            <div className="webview-profile-view">
                <div className="top-header">
                    <h3 className="page-title"> My Profile </h3>
                </div>
                <div className="user-profile-container">
                    <div className="profile-header">
                        <div className="col">
                            <div className="profile-img"><img src="/images/no-profile.png" alt="No Profile Image"/> </div>
                        </div>
                        <div className="col">
                            <div className="profile-details">
                                <p className="user-name"> Mr. Firoz Khan </p>
                                <p className="mailing-address"> 212/13 Mirpur-10, Dhaka-1216 </p>
                            </div>
                        </div>
                    </div>
                    <div className="profile-body">
                        <Link to="/webview/profile_update"> Edit My Profile </Link>
                        <Link to="/webview/cart"> My Order History </Link>
                    </div>
                </div>
            </div>
        )
    }
}

export default userProfile;