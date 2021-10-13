import React from "react";
import request from 'superagent';
import moment from "moment";
import Calender from  "../calender/calender";
import Config from "../../components/config";
import TextPopup from './popups/TextPopup';
import Echo from "laravel-echo";
import i18n from "./../../plugins/i18n.js"

let config = new Config();

class Schedule extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            schedule_type_list:[
                { name: 'Text', value:1 },
                { name: 'Image (coming...)', value:2 },
                { name: 'Card (coming...)', value:3 },
            ],
            schedule_type:false,
            cache_schedule_type:null,

            enum_type_list : [
                { name:'Instant', value:1 },
                { name:'Schedule', value:2 } ,
                { name:'Repeating', value:3 }
            ],

            title: '',
            message: '',
            fetching: false,
            fetched: false,

            enum_type:false,
            cache_enum_type:null,

            month: moment().startOf('day'),
            schedule_popup: false,

            calender_popup:false,
            schedule_popup_status:false,

            campaignList:[],
            new_schedule: false,
            scheduleSendError: null,

        }
        this.documentClickEventHandler = this.documentClickEventHandler.bind(this);

        this.showSchedulePopup = this.showSchedulePopup.bind(this);
        this.hideSchedulePopup = this.hideSchedulePopup.bind(this);
        this.showScheduleType = this.showScheduleType.bind(this);

        this.getSelectedDate = this.getSelectedDate.bind(this);
        this.showCalenderPopup = this.showCalenderPopup.bind(this);

        this.closeSchedulePopup = this.closeSchedulePopup.bind(this);
        this.textBroadcastChange = this.textBroadcastChange.bind(this);
        this.createSchedule = this.createSchedule.bind(this);
     }

    componentDidMount(){
         document.addEventListener("click",this.documentClickEventHandler);
         this.getAllSchedule();
         this.listenBroadCast();
    }

    componentWillUnmount() {
        document.removeEventListener("click",this.documentClickEventHandler);
    }

    listenBroadCast(){
        if (typeof io !== 'undefined') {
            window.Echo = new Echo({
                broadcaster: 'socket.io',
                host: config.root + ':6001'
            });
            window.Echo.private('channel-reach_estimate_' + config.userID)
                .listen('BroadcastReachEstimateNotification', (response) => {
                    this.updateBroadcastList( response.broadcast );
                    // this.setState({training_status: response.trainingStatus.status});
                });
        }
    }

    updateBroadcastList( newBroadcast ){
        let { campaignList } = this.state;
        campaignList.map( (campaign, index)=> {
             if( campaign.id === newBroadcast.id ) {
                 campaignList[index] = newBroadcast;
             }
        });

        this.setState({ campaignList })
    }

    getAllSchedule(){
        let schedule = request
            .get('/api/schedule/fetch')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json');
        schedule.end((err, response) => {
            //console.debug("Schedule response: ", response );
            if( response.body.error == false ) {
                let broadcasts = response.body.broadcasts;
                if( broadcasts.length > 0 )
                    broadcasts.reverse();
                this.setState({ campaignList: broadcasts })
            }
        });
    }

    createSchedule(){
        this.setState({ fetching: true, fetched: false });

        let schedule = request
            .post('/api/schedule/create')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json')
            .field('message', this.state.message);
        schedule.end((err, response) => {
            //console.debug("Schedule response: ", response );
            if( response.body.error === false ) {
                //Do something after save schedule!
                this.setState({ schedule_popup_status: false, title:'', message:'', fetching: true, fetched: true  })
            }
            window.alert(response.body.message)
        });
    }


    documentClickEventHandler(event){
        //hide schedule type on outside click
        let s_type = event.target.getAttribute("data-s_type");

        if( s_type === null )
            this.setState({schedule_type:false});

    }
    /*
     * Show Schedule popup based on schedule type
     */
    showSchedulePopup( type, event ){
        if( type !==1 )
            return false;

        this.setState({ cache_schedule_type: type, schedule_popup_status: true, schedule_type:false, fetching: false, fetched: false  });
    }

    /*
     * Hide Schedule popup by click on close button
    */
    hideSchedulePopup(event){
        event.preventDefault();
        this.setState({ schedule_popup: false })
    }

    /*
     * Show Schedule type by click on create button
     */
    showScheduleType(event){
        this.setState({ schedule_type: !this.state.schedule_type })
    }

    getSelectedDate(day){
        //console.debug("Date selected: ", day);
        //console.debug("current Date: ",moment().startOf('day'));
        this.setState({ calender_popup: !this.state.calender_popup, month: day.date  });
    }

    showCalenderPopup(event){
        this.setState({ calender_popup: !this.state.calender_popup })
    }

    /*
    Save state on change in textpopup
     */
    textBroadcastChange(event){
        let { title , message } = this.state;
        if( event.target.name === 'title' )
            title = event.target.value;
        if( event.target.name === 'message' )
            message = event.target.value;

        this.setState({ title, message });
    }
    /*
     * Close schedule popup
     */
    closeSchedulePopup( event ){
        this.setState({ schedule_popup_status: !this.state.schedule_popup_status});
    }

    onUpdateSchedule(index, event){
        let { campaignList } = this.state;
        let currentSchedule = campaignList[index];
        if( event.target.name === 'title' )
            currentSchedule.title = event.target.value;
        if( event.target.name === 'message' )
            currentSchedule.creative.text = event.target.value;

        this.setState({ campaignList })
    }

    onSendBroadCast(index, event) {
        event.preventDefault();
        let { campaignList, scheduleSendError } = this.state;
        let currentSchedule = campaignList[index];
        if( /*currentSchedule.title.length <=0 || */currentSchedule.creative.text.length <=0 ) {
            this.setState({scheduleSendError: index})
            return false;
        }else {
            this.setState({ scheduleSendError: null });
            let schedule = request
                .post('/api/schedule/create')
                .set('X-CSRF-TOKEN', config._token)
                .set('X-Requested-With', 'XMLHttpRequest')
                .set('Accept', 'application/json')
                .field('message', currentSchedule.creative.text)
                .field('id', (typeof currentSchedule.id === 'undefined') ? '' : currentSchedule.id);
            schedule.end((err, response) => {
                // console.debug("new brodcast: ", response)
                if( response.body.error === false ) {
                    campaignList[index] = response.body.broadcast;
                    this.setState({ campaignList });
                }
                window.alert(response.body.message)
            });
        }

    }

    broadCastList(){
        let { campaignList, scheduleSendError } = this.state;
        return campaignList.map( (campaign, index) => {
            let style = {
                width: (campaign.is_new) ? '80%' : '50%'
            }
            let stat = ( campaign.stat === null || typeof campaign.stat === 'undefined' ) ? null : campaign.stat ;

            return (
                <div key={index} className={ (scheduleSendError === index ) ? "list has_error" : 'list'} tabIndex={index}>
                    <div className="sch-item icon">
                        <span className="icon-big"> <i className="fa fa-clock-o"></i> </span>
                    </div>
                    <div className="sch-item details" style={style}>
                        <p> { (campaign.is_new === false || typeof campaign.is_new === 'undefined') ? <small> {campaign.creative.text} </small> : <textarea name="message" placeholder={i18n.t('pages.schedule.broadcastList.items.txtarea.placeholder')} className="form-control" onChange={this.onUpdateSchedule.bind(this,index)} value={campaign.creative.text} /> } </p>
                    </div>

                    { (campaign.is_new === true ) ? '' :
                        <div className="sch-item people_reach">
                            <h4 className="title"> {i18n.t('pages.schedule.broadcastList.items.peopleReach')} </h4>
                            <p> { (stat === null ) ? 0 : stat.reach_estimation } </p>
                        </div>
                    }

                    <div className="sch-item date">
                        <span className="date-big light-color"> { moment(campaign.created_at).format('D') } <sub> { moment(campaign.created_at).format('MMM') } </sub></span>
                    </div>
                    <div className="sch-item sendBtn">
                        <a href="#" onClick={this.onSendBroadCast.bind(this, index)} className="btn btn-admin btn-admin-white btn-lg"> <i className="fa fa-send-o"></i> {i18n.t('common.buttons.send')} </a>
                    </div>
                </div>
            )
        })
    }

    scheduleControlHeader(){
        return (
            <div className="col-md-6 buttons">
                <div className="pull-right btn-create_new">
                    <a href="#" className="btn btn-admin btn-admin-white" data-s_type="1" onClick={ this.showScheduleType }> {i18n.t('common.buttons.create')} </a>
                    <ul className="schedule_type_list" style={ (this.state.schedule_type) ? styles.show_schedule_type : styles.hide_schedule_type }>
                        { this.state.schedule_type_list.map( (type, index )=> {
                            return <li data-s_type="1" key={index} className={index===0 ? '' : 'disabled'} onClick={this.showSchedulePopup.bind(this, type.value) }> { type.name } </li>
                        } ) }
                    </ul>
                </div>
                <div className="btn_group pull-right">
                    <a href="#" className="btn btn-admin btn-admin-white active"> {i18n.t('pages.schedule.controlHeader.button.activeButton')} </a>
                    <a href="#" className="btn btn-admin btn-admin-white"> {i18n.t('pages.schedule.controlHeader.button.inactiveButton')}</a>
                </div>

            </div>
        )
    }

    onCreateSchedule(event){
        event.preventDefault();
        let { campaignList, new_schedule } = this.state;
        let newSchedule = {
            creative: {text:''},
            created_at: moment(),
            p_reach: '0',
            is_new: true,
        }
        campaignList.unshift( newSchedule );
        this.setState({campaignList, new_schedule: true })
    }

    schedule(){

        return (
            <div className="dashboard-widget">
                <div className="campaign-header row margin-0">
                    <div className="col-md-6 desc"> <h3 className="title"> {i18n.t('pages.schedule.widget.title')} </h3> </div>
                    <div className="col-md-6 buttons text-right">
                        <a href="#" className="btn btn-admin btn-admin-white" onClick={this.onCreateSchedule.bind(this)}> {i18n.t('pages.schedule.widget.createButton')} </a>
                    </div>
                </div>
                <div className="campaign-body row margin-0">
                    <div className="campaign-list">
                        { this.broadCastList() }
                    </div>
                </div>
            </div>
        )
    }
    newSchedule(){
        return (
            <div className="popup_shadow" style={ (this.state.schedule_popup) ? styles.show_schedule_popup : styles.hide_schedule_popup }>
                <div className="new_schedule_container">
                    <div className="schedule_header">
                        <h3 className="title"> {i18n.t('pages.schedule.popup.header.title')} </h3>
                    </div>
                    <div className="schedule_body">
                           <div className="row">
                               <div className="col-md-5">
                                   <div className="start_date">
                                       <div className="s_today"> {i18n.t('pages.schedule.popup.body.heading')} </div>
                                       <div className="s_start_calender" onClick={ this.showCalenderPopup }> {this.state.month.format("MMMM D, YYYY")} </div>
                                       <div className="s_calender_view" style={ (this.state.calender_popup) ? styles.show_calender_popup : styles.hide_calender_popup }>
                                           <Calender selected={ this.state.month } getSelectedDate={this.getSelectedDate} />
                                       </div>

                                   </div>
                               </div>
                           </div>
                           <div className="row">
                               <div className="schedule_action_body">

                               </div>
                           </div>
                     </div>
                    <div className="schedule_footer">
                        <p className="text-right"><a onClick={this.hideSchedulePopup} href="#" className="btn btn-admin btn-admin-danger"> {i18n.t('common.buttons.cancel')} </a></p>
                    </div>
                </div>
            </div>
        )
    }

    render(){

        return (
            <div className="main-schedule">
                { this.schedule() }
                { (this.state.schedule_popup_status) ?
                    <TextPopup
                        {...this.state}
                        saveTextBroadCast = { this.createSchedule }
                        textBroadcastChange={this.textBroadcastChange}
                        onCloseAction={this.closeSchedulePopup.bind(event)}
                        className="onVision scheduleModal"/>
                    : null }
            </div>
        )
    }
}

export default Schedule;


const styles = {
    hide_schedule_type:{
        display:'none'
    },
    show_schedule_type:{
        display:'block'
    },
    hide_schedule_popup:{
        display:'none'
    },
    show_schedule_popup:{
        display:'block'
    },
    hide_calender_popup:{
        display:'none'
    },
    show_calender_popup:{
        display:'block'
    }

}