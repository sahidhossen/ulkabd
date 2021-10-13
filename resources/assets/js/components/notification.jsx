import React from "react";
import Config from "../components/config";
import request from 'superagent';
let config = new Config();
class Notification extends React.Component {
    constructor( props ){
        super( props );

        this.state = {
            notification_list:[],
            notification_status: 2,
        }
    }

    componentDidMount() {

    }

    render(){
        return (
            <div className="notification-body">

            </div>
        )
    }
}


export default Notification;