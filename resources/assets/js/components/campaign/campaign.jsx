import React from "react";
import request from 'superagent';
import moment from "moment";
import Schedule from "../../components/campaign/schedule";
import Config from "../../components/config";

let config = new Config();
class Campaign extends React.Component {
    constructor(props) {
        super(props);

        this.getSelectedDate = this.getSelectedDate.bind(this);
    }


    getSelectedDate(date){
        console.debug("selected date: ", date );
    }

    campaign(){
        return (
            <div className="campaign-container">
                <Schedule/>
            </div>
        )
    }

    render(){
        // console.debug("current Date: ",moment().startOf('day'));
        return this.campaign();
    }
}


export default Campaign;
