import React from 'react';
import {NavLink} from 'react-router-dom';

export default class userLogin extends React.Component{
    constructor(props){
        super(props)
    }
    render(){
        return (
            <div className="webview-login-signup-view">
                <div className="user-login-signup-container">
                    <div className="usha-header">
                        <div className="img-box"><a href="/"><img src="/images/usha-login-logo.png" alt="Usha Logo"/></a> </div>
                    </div>
                    <div className="login-body">
                        <form className="user-form">
                            <div className="form-group">
                                <label htmlFor="username">Username / Email</label>
                                <input type="text" className="form-control" name="username" placeholder="Username or Email"/>
                            </div>
                            <div className="form-group">
                                <label htmlFor="password">Password</label>
                                <input type="password" className="form-control" name="password" placeholder="Password"/>
                            </div>
                            <div className="form-group btn-box"><a href="#" className="login-btn"> Login </a></div>
                        </form>
                        <div className="signup-suggest">
                            <p className="motivate-speech"> First Time Shopping With USHA? </p>
                            <NavLink to={'/webview/signup'} className="signup-btn"> Sign Up </NavLink>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

