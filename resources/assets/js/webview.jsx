import React from 'react';
import ReactDOM from 'react-dom';

import Cart from './components/orders/cart';
if (document.getElementById('webview-root')) {
    ReactDOM.render(<Cart />, document.getElementById('webview-root'));
}
