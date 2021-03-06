import React from 'react';
import { render } from 'react-dom';
import { Provider } from 'react-redux';
import Thunk from 'redux-thunk';
import { combineReducers, compose, createStore, applyMiddleware } from 'redux';

import Index from '../containers/Index';
import user from '../modules/User';

const reducers = combineReducers({
  user,
});
const finalCreateStore = compose(applyMiddleware(Thunk))(createStore);
const store = finalCreateStore(reducers);

render(<Provider store={store}><Index /></Provider>, document.getElementById('react-content'));
