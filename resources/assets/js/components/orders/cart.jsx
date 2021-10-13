import React from 'react';
import { Provider } from 'react-redux';
import store from '../store';
import { BrowserRouter as Router, Route, Switch } from 'react-router-dom';
import cartList from '../webview/cartList';
import userProfile from '../webview/userProfile';
import userLogin from '../webview/userLogin';
import userSignUp from '../webview/userSignUp';
import userProfileUpdate from '../webview/userProfileUpdate';
import NotFound from '../webview/NotFound';

class Cart extends React.Component{
    constructor(props){
        super(props)
    }
    render(){
        return (
            <Provider store={store}>
                <Router>
                    <Switch>
                        <Route exact path="/webview/cart" component={cartList} />
                        <Route  path="/webview/profile" component={userProfile} />
                        <Route  path="/webview/profile_update" component={userProfileUpdate} />
                        <Route  path="/webview/login" component={userLogin} />
                        <Route  path="/webview/signup" component={userSignUp} />
                        <Route  path="/webview/*" component={NotFound} />
                    </Switch>
                </Router>
            </Provider>
        )
    }
}

export default Cart;