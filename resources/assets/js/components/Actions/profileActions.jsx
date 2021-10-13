import { API_BASE } from '../const/config';

import axios from 'axios';

export function fetchProfile( data ){
    return function (dispatch) {
        // let data  = {external_id:"1200611636723498", extension_platform:'facebook'};
        console.debug('profile data: ',data );
        axios.post(API_BASE+'/bot_web_ext/profile_cart', data)
            .then(function (response) {
                console.debug("profile: ", response);
                if( response.data.error === false  ) {
                    dispatch({
                        type: 'FETCH_PROFILE_FULFILLED',
                        payload: response.data.data,
                        message: response.data.message
                    });
                }else {
                    dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error, message: response.data.message })
                }
            })
            .catch(function (error) {
                dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error })
            });
    }
}


export function updateProfile( profile_data, profile ){
    return function (dispatch) {
        axios.post(API_BASE+'/bot_web_ext/user_profile', profile_data)
            .then(function (response) {
                console.debug("update profile: ", response );
                if(response.data.error !== true ) {
                    profile.profile = response.data.data;
                    dispatch({
                        type: 'UPDATE_PROFILE_FULFILLED',
                        payload: response.data.data,
                        message: response.data.message
                    });
                }else {
                    dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error, message: response.data.message })
                }
            })
            .catch(function (error) {
                dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error })
            });
    }
}


export function updateCart( cart_data ){
    return function (dispatch) {
        axios.post(API_BASE+'/bot_web_ext/cart_update_checkout', cart_data)
            .then(function (response) {
                console.debug("update profile: ", response );
                if( response.data.error === false ) {
                    dispatch({
                        type: 'UPDATE_CART_FULFILLED',
                        message: response.data.message
                    });
                }else {
                    dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error, message: response.data.message })
                }
            })
            .catch(function (error) {
                dispatch({ type:'FETCH_PROFILE_REJECTED', payload: error })
            });
    }
}
