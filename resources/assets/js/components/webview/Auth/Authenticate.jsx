import { CLIENT_SECRET } from '../../const/config';
import axios from 'axios';
window.axios = require('axios');
/**
 * @return {null}
 */
export function Authentication(){
    console.debug("laravel csrf: ",window.Laravel.csrfToken);
    window.axios.defaults.headers.common = {
        'X-CSRF-TOKEN': window.Laravel.csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
        'usha-app-secret' : CLIENT_SECRET
    };
    //window.axios.defaults.headers.common['usha-app-secret'] = CLIENT_SECRET;
}