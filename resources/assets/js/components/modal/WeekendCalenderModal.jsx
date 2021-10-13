/**
 * Created by sahidhossen on 25/11/17.
 */
import React from 'react';
import InitialWeek from '../calender/InitialWeek';

export default class WeekendCalenderModal extends React.Component {
    constructor(props){
        super(props);
        this.state = {
            timeList:[],
            current_time_index: null,
            delivery_error: false
        }

    }
    componentDidMount(){
        this.setState({ timeList: this._formatAMPM() });
    }
    getSelectedDate(day){
        if(this.props.date_error !== true )
            this.setState({ delivery_error: false });
        this.props.getSelectedDate(day);
    }

    onSaveInstance(event) {
        event.preventDefault();
        let { current_date, current_time, date_error } = this.props;
        if( current_date !=='' && current_time !== '' && date_error !== true ) {
            this.setState({ delivery_error: false });
            this.props.modalDismiss(event);
        }else {
            this.setState({ delivery_error: true })
        }
    }

    onPopupCancel(event){
        event.preventDefault();
        this.props.modalDismiss("cancel");
    }

    onSelectTime(time, index, event ){
        event.preventDefault();
        this.props.onSelectTime(time, index);
    }

    _formatAMPM() {
        let x = 30;
        let times = [];
        let tt = 600;
        let ap = ['AM', 'PM'];
        for (let i=0;tt<22*60; i++) {
            let hh = Math.floor(tt/60); // getting hours of day in 0-24 format
            let mm = (tt%60); // getting minutes of the hour in 0-55 format
            times[i] = ("0" + ( (hh % 12 === 0) ? 12 : hh % 12) ).slice(-2) + ':' + ("0" + mm).slice(-2) + " "+ap[Math.floor(hh/12)]; // pushing data in array in [00:00 - 12:00 AM/PM format]
            tt = tt + x;
            // if( i === 0 )
            //     this.setState({current_time : times[i] })
        }
        return times;
    }

    renderTimeList(){
        let { timeList } = this.state;
        return ( timeList.length <= 0 ) ? null : timeList.map( (time, index ) => {
                return ( <span className={ (this.props.current_time_index === index) ? "active" : "" } onClick={this.onSelectTime.bind(this, time, index)} key={index}> { time } </span>)
            })
    }

    render(){
        return (
            <div className={"usha-webcart-modal-shadow "+this.props.className}>
                <div className="modal-main">
                    <div className="calender-container-box">
                        <div className="flex-1">
                            <InitialWeek selectedDate={this.props.current_date} getSelectedDate={this.getSelectedDate.bind(this)} />
                        </div>
                        <div className="time-list-container">
                            <h3 className="title"> Time </h3>
                            <div className="timer-box">
                                { this.renderTimeList() }
                            </div>
                        </div>
                    </div>
                    <div className="modal-footer">
                        <div className="footer-btns">
                            <div className="col">
                                <a href="#" onClick={this.onPopupCancel.bind(this)} className="delivery_cancel"> ✕ </a>
                            </div>
                            <div className="flex-1">
                                { (this.props.date_error || this.state.delivery_error) ? <p className="warning-msg"> <small> Please select valid date & time! </small> </p> : null }
                            </div>
                            <div className="text-right col">
                                <a href="#" onClick={this.onSaveInstance.bind(this)} className="delivery_btn"> Confirm </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}