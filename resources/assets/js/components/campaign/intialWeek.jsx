import React from "react";
import request from 'superagent';
import Config from "../../components/config";
import moment from "moment";

let config = new Config();
class InitialWeek extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            date : this.props.date.clone(),
            month: this.props.month.clone(),
            dayName:['Sun',"Mon","Tue","Wed","Thu","Fri","Sat"]
        }

        this.onSelect = this.onSelect.bind(this);
        this.previous = this.previous.bind(this);
        this.next = this.next.bind(this);
    }
    onSelect(day, event){
        console.debug(day );
        //this.props.select( day );
    }

    previous( event ){
        event.preventDefault();
        let date = this.state.date;
        date.add(-7, "d");
        this.setState({date: date });
    }

    next( event ){
        event.preventDefault();
        let date = this.state.date;
        date.add(7, "d");
        this.setState({date: date });
    }

    renderMonthLabel() {
        return <span>{this.state.date.format("MMMM, YYYY")}</span>;
    }

    getHours(){

        let startHours = moment().startOf('hour');
        let endHours = moment().endOf('hour');
        console.debug( startHours + " "+ endHours );
        let hours = [];
        let hour = startHours;
        while (hour <= endHours) {
            //startHours.add(1,'h');
            console.debug("Echo ", hour  );
            hour = hour.clone().add(1, 'h');
        }
    }

    getDateItems(hours) {
        let toDate = new Date();
        let fromDate = new Date();
        fromDate.setTime(fromDate.getTime() - (hours * 60 * 60 * 1000));
        let result = [];

        while (toDate >= fromDate) {
            let time = ("00" + toDate.getHours()).slice(-2) + ":" + ("00" + toDate.getMinutes()).slice(-2) + ":" + ("00" + toDate.getSeconds()).slice(-2);

            result.push(time.format("H"));
            // consider using moment.js library to format date

            toDate.setTime(toDate.getTime() - (1 * 60 * 60 * 1000));
        }

        return result;
    }

    render(){

       this.getHours();

        let days = [],
            date = this.state.date,
            month = this.state.month;

         for (let i = 0; i < 7; i++) {
            let day = {
                name: date.format("dd").substring(0, 1),
                number: date.date(),
                isCurrentMonth: date.month() === month.month(),
                isToday: date.isSame(new Date(), "day"),
                date: date
            };

            days.push(<span key={day.date.toString()} className={"day" + (day.isToday ? " today" : "") + (day.isCurrentMonth ? "" : " different-month") + (day.date.isSame(this.props.selected) ? " selected" : "")} onClick={this.onSelect.bind(event,day) }>{day.number}</span>);
            date = date.clone();
            date.add(1, "d");

        }
        let dayNames = this.state.dayName.map( (day, index )=>{
            return <span className="day" key={ index} > { day } </span>
        })

        return (


            <div className="initial_week_container">
                    { this.renderMonthLabel() }
                <div className="day_names">
                    <span className="control prev"><i className="fa fa-angle-left" onClick={this.previous}></i></span>
                    {dayNames}
                    <span className="control next"><i className="fa fa-angle-right" onClick={this.next}></i></span>
                </div>
                <div className="week">
                    {days}
                </div>
            </div>

        )
    }
}


export default InitialWeek;
