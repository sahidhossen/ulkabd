import React from "react";
import moment from "moment";
import next from './next.png';
import prev from './back.png';

class InitialWeek extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            date : moment().startOf('day'),
            month: moment().startOf('month'),
            dayName:['Sun',"Mon","Tue","Wed","Thu","Fri","Sat"],

            selectedIndex: null,
            selectedDay: moment().startOf('day'),
        }

        this.onSelect = this.onSelect.bind(this);
        this.previous = this.previous.bind(this);
        this.next = this.next.bind(this);
    }

    componentDidMount(){
        if( this.props.selectedDate !== '' )
            this.setState({ selectedDay  : this.props.selectedDate, selectedIndex:1})
    }

    onSelect(day, index, event){
        event.preventDefault();
        console.debug("update: ", day )
        this.setState({ selectedIndex: index, selectedDay: day.date })
        this.props.getSelectedDate( day );
    }

    //Set current date and rollback current week
    setToday(event){
        this.setState({
            date : moment().startOf('day'),
            month: moment().startOf('month'),
        })
    }

    //Go to previous week
    previous( event ){
        event.preventDefault();
        let date = this.state.date;
        date.add(-7, "d");
        this.setState({date: date });
    }

    //Go to next week
    next( event ){
        event.preventDefault();
        let date = this.state.date;
        date.add(7, "d");
        this.setState({date: date });
    }

    //Current month
    renderMonthLabel() {
        return <span onClick={this.setToday.bind(this)} className="current_month_name">{this.state.date.format("MMMM, YYYY")}</span>;
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

        let days = [],
            date = this.state.date,
            month = this.state.month;

        for (let i = 0; i < 7; i++) {
            let day = {
                name: date.format("dddd"),
                number: date.date(),
                isCurrentMonth: date.month() === month.month(),
                pastDay: date.diff(new Date(), 'day') < 0,
                isToday: date.isSame(new Date(), "day"),
                date: date
            };
            //console.debug("current date, ",moment(day.date).format('DD-MM-YYYY'), " diff: ", moment(this.state.selectedDay).format('DD-MM-YYYY') )
            let isTodayClass = (day.isToday ? " today" : "") + (day.isCurrentMonth ? "" : " future-month");
            let pastDay = (!day.pastDay ? "" : " past-day");
            let selectedDate = ( this.state.selectedDay.isSame(day.date) && this.state.selectedIndex !== null ) ?  " selected" : ""
            days.push(
                <div key={day.date.toString()} className={"day" + isTodayClass + selectedDate + pastDay} onClick={this.onSelect.bind(event,day, i) }>
                    <div className="date"> {day.number} </div>
                    <div className="day_name">
                        <span>{day.name}</span>
                    </div>
                </div>);
            date = date.clone();
            date.add(1, "d");

        }
        return (
            <div className="initial_week_container">
                <div className="week_header">
                    <span className="control prev" onClick={this.previous}><img src={prev} alt="image"/></span>
                    { this.renderMonthLabel() }
                    <span className="control next" onClick={this.next}> <img src={next} alt="image"/></span>
                </div>
                <div className="week_body weeks">
                    {days}
                </div>
            </div>

        )
    }
}


export default InitialWeek;