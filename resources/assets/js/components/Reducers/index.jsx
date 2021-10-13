import { combineReducers } from 'redux'
import profile from './profileReducers'
import thread_context from './fbextensionReducers';

export default combineReducers({
    profile,
    thread_context
})