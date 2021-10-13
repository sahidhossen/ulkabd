export default function reducer(state={
    profile :[],
    message:null,
    fetching: false,
    fetched: false,
    cart_update: false,
    profile_update: false,

    checkout_fetched: false,
    profile_update_fetching: false,
    error: null
}, action ) {
    switch( action.type ){
        case 'FETCH_PROFILE': {
            return { ...state,  fetching: true, fetched: false,  profile_update:false, cart_update: false }
        }
        case 'FETCH_UPDATE_PROFILE': {
            return { ...state,   profile_update_fetching: true }
        }

        case "FETCH_PROFILE_REJECTED": {
            return { ...state, fetching: false, error: action.payload, message: action.message }
        }
        case "FETCH_PROFILE_FULFILLED" : {
            return { ...state, fetching: true, fetched:true,  profile : action.payload, message: action.message }
        }
        case "PROFILE_UPDATE_CART_FULFILLED" : {
            return { ...state, profile : action.payload, cart_update : true  }
        }
        case "UPDATE_PROFILE_FULFILLED" : {
            return { ...state, profile_update:true, profile_update_fetching:true,  profile : action.payload, message: action.message }
        }
        case "UPDATE_CART_FULFILLED" : {
            return { ...state, checkout_fetched:true, profile_update:false,  message: action.message }
        }

        default :
            return state;
    }


}