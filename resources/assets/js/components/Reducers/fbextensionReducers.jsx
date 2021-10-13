export default function reducer(state={
    thread_context :[],
    message:null,
    fetching: false,
    fetched: false,
    error: null
}, action ) {
    switch( action.type ){
        case 'FETCH_FB_THREAD': {
            return { ...state,  fetching: true, fetched: false }
        }

        case 'FETCH_FB_THREAD_COMPLETE': {
            return { ...state,  fetching: false, fetched: false }
        }

        case "FETCH_FB_THREAD_REJECTED": {
            return { ...state, fetching: false, error: action.payload, message: action.message }
        }
        case "FETCH_FB_THREAD_FULFILLED" : {
            return { ...state, fetching: true, fetched:true,  thread_context : action.payload, message: action.message }
        }

        default :
            return state;
    }


}