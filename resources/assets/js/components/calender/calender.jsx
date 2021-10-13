import React from "react";
import Week from "./week";
import next from './next.png';
import prev from './back.png';

class Calendar extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            selected: this.props.selected.clone(),
            month: this.props.selected.clone(),
            dayName:['S',"M","T","W","T","F","S"]
        }
        this.next = this.next.bind(this);
        this.previous = this.previous.bind(this);
        this.select = this.select.bind(this);
    }

    previous() {
        let month = this.state.month;
        month.add(-1, "M");
        this.setState({ month: month });
    }

    next() {
        let month = this.state.month;
        month.add(1, "M");
        this.setState({ month: month });
    }

    select(day) {
        this.setState({ selected: day.date });
        this.props.getSelectedDate(day);
        this.forceUpdate();
    }

    renderWeeks(){
        let weeks = [],
            done = false,
            date = this.state.month.clone().startOf("month").add("w" -1).day("Sunday"),
            monthIndex = date.month(),
            count = 0;

        while (!done) {
            weeks.push(<Week key={date.toString()} date={date.clone()} month={this.state.month} select={this.select} selected={this.state.selected} />);
            date.add(1, "w");
            done = count++ > 2 && monthIndex !== date.month();
            monthIndex = date.month();
        }

        return weeks;
    }

    renderMonthLabel() {
        return <span>{this.state.month.format("MMMM, YYYY")}</span>;
    }

    calendar(){
        let days = this.state.dayName.map( (day, index )=>{
            return <span className="day" key={ index} > { day } </span>
        })
        return (
            <div className="row margin-0 calender-container">

                <div className="header">
                    <span className="control left_control" onClick={this.previous}>
                        <img src={prev} alt="image"/>
                    </span>
                    <span className="month_label">{this.renderMonthLabel()}</span>
                    <span className="control right_control" onClick={this.next}>
                        <img src={next} alt="image"/>
                    </span>
                </div>
                <div className="initial_week_name">
                    { days }
                </div>
                <div className="number_of_dates">
                    {this.renderWeeks()}
                </div>
            </div>
        )
    }

    render(){
        return this.calendar();
    }
}


export default Calendar;