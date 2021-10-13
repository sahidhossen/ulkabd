import request from 'superagent';
import React from "react";
let instance = null;

class Config {
    constructor() {

        if(!instance){
            instance = this;
        }

        this._token = $('meta[name="csrf-token"]').attr('content');
        if($("#current_user_id").length) {
            // this.userID = $("#loginDashboard").attr('data-user_id');
            this.userID = $("#current_user_id").val();
            this.getCurrentUser();
        }
        if( $("#page-content-wrapper").length )
            this.activeAgent = $("#page-content-wrapper").attr('data-active_agent');

        if(typeof window !== 'undefined') {
            let spliced = location.host.split(':');
            let host = spliced[0];
            this.root = location.protocol + '//' + host;
            this.path_name = location.pathname;
        }

        return instance;
    }

    getCurrentUser(){


    }


}

export default Config;

