import { applyMiddleware, createStore } from 'redux'

import logger from 'redux-logger'
import thunk from 'redux-thunk'
import promise from 'redux-promise'

import reducer from './Reducers'

const middleware  = applyMiddleware(promise, thunk, logger);

export default createStore(reducer, middleware );