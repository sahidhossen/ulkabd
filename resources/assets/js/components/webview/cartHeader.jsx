import React from 'react';
import {Link, NavLink} from 'react-router-dom';
import { END_USER_PROF_PIC_PLACEHOLDER_URL } from '../const/config';

class CartHeader extends React.Component{
    constructor(props){
        super(props);
        this.state = {
            picture: this.props.user.profile_img_url ? this.props.user.profile_img_url : END_USER_PROF_PIC_PLACEHOLDER_URL
        };
    }
    render(){
        //let {user} = this.props;
        //console.debug("user: ", user);
        //let profileImg = ( user === null ) ? null : user.profile_img_url;
        return (
            <header className="header">
                <div className="profile-icon"> <Link to="#"> <img src={ this.state.picture } /> </Link></div>
            </header>
        )
    }
}

export default CartHeader;