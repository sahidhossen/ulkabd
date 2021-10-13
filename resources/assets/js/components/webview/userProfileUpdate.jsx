import React from 'react';
import { connect } from 'react-redux';
import moment from 'moment';
import { updateProfile, updateCart } from '../Actions/profileActions';
import Loader from '../Loader';
import { LoadFbMessengerExtensionThread } from '../Actions/fbextensionActions';
import WeekendCalenderModal from '../modal/WeekendCalenderModal.jsx';
import Switch from '../elements/Switch';

@connect( (store) => {
    return {
        profile : store.profile,
        thread_context : store.thread_context,

    }
})
export default class userProfileUpdate extends React.Component{
    constructor(props){
        super(props)
        this.state = {
            update_cart:{},
            profile:null,
            day:'',
            month:'',
            year:'',
            current_date: '',
            current_time: '', //moment().format('hh:mm A'),
            current_time_index:null,

            date_error: false,

            errors:{},

            deliveryTriggerState: false
        }
    }

    componentDidMount(){
        let { thread_context } = this.props.thread_context;
        let { profile } = this.props.profile;
        let { cart } = profile;
        let { update_cart } = this.state;
        update_cart.external_id = thread_context.psid;
        update_cart.extension_platform = "facebook";
        update_cart.is_checkedout = true;
        update_cart.entities =  cart.entities;
        let dob = (profile.profile.date_of_birth === "" || profile.profile.date_of_birth.length <=0 ) ? null : profile.profile.date_of_birth.split('-');
        profile.profile.day = (dob === null) ? '' : dob[1];
        profile.profile.month = (dob === null) ? '' : dob[0];
        profile.profile.year = (dob === null) ? '' : dob[2];
        this.setState({ profile: profile.profile, update_cart:update_cart, profile_update: true })

    }

    getSelectedDate(day){
        let { errors } = this.state;
        if( day.pastDay === false )
            delete errors.delivery;

        this.setState({ current_date: day.date, date_error: day.pastDay  });
    }
    showWeek( event ){
        event.preventDefault();
        this.setState({  week_popup: !this.state.week_popup })
    }

    onSelectTime(time, index){
        this.setState({ current_time: time, current_time_index: index })
    }

    onPopupCancel( type = 'save' ){
        if( type === 'cancel' )
            this.setState({ current_time: '', current_date: '', current_time_index:null });

        this.setState({  week_popup: !this.state.week_popup })

    }


    IsEmail(email) {
        let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
        return regex.test(email);
    }

    onChangeProfile(event){
        let { profile, errors } = this.state;

        if( event.target.name === 'name' ){
            if( event.target.value.length < 2 )
                errors.name = true;
            else
                delete errors.name;

            profile.name.value = event.target.value;
        }

        if( event.target.name === 'email'){

            if( event.target.value.length > 0 && (event.target.value.length < 2 ||  !this.IsEmail(event.target.value)) ){
                errors.email = true
            }else {
                delete errors.email;
            }
            profile.email.values = [event.target.value];
        }

        if( event.target.name === 'phone'){

            if( event.target.value.length < 11 ){
                errors.phone = true
            }else {
                delete errors.phone;
            }
            profile.phone.values = [event.target.value];
        }

        if( event.target.name === 'day' ){
            if( parseInt(event.target.value) === 0 || parseInt( event.target.value ) > 31 )
                errors.dob = true;
            else
                delete errors.dob;

            profile.day = event.target.value;
        }

        if( event.target.name === 'month' ){
            if( parseInt(event.target.value) === 0 || parseInt( event.target.value ) > 12 )
                errors.dob = true;
            else
                delete errors.dob;

            profile.month = event.target.value;
        }

        if( event.target.name === 'year' ){
            if( event.target.value.length !== 4 )
                errors.dob = true;
            else
                delete errors.dob;

            profile.year = event.target.value;
        }

        if( event.target.name === 'sex' ){
            profile.gender = event.target.value;
        }

        let { mailing_address } = profile;

        if( event.target.name === 'street_1' ){
            if( event.target.value.length < 2  )
                errors.street = true;
            else
                delete errors.street;
            mailing_address.street_1.value = event.target.value;
        }

        if( event.target.name === 'street_2' ){
            mailing_address.street_2.value = event.target.value;
        }

        if( event.target.name === 'city' ){
            if( event.target.value.length <= 0  )
                errors.city = true;
            else
                delete errors.city;

            mailing_address.city.value = event.target.value;
        }

        if( event.target.name === 'country' ){
            if( event.target.value.length <= 0  )
                errors.country = true
            else
                delete errors.country;
            mailing_address.country = event.target.value;
        }

        if( event.target.name === 'zip' ){
            if( event.target.value.length > 0 && event.target.value.length < 4  )
                errors.zip = true
            else
                delete errors.zip;
            mailing_address.zip.value = event.target.value;
        }

        profile.mailing_address = mailing_address;

        this.setState({ profile: profile  })

    }

    /*
     * Process profile data  and send to the API
     */
    onConfirmCheckout(event){
        event.preventDefault();
        let profile_data = {};
        let { profile, errors, current_date, deliveryTriggerState } = this.state;
        let user = this.props.profile.profile.user;

        let { name, email, phone, day, month, year, mailing_address, gender } = profile;

        if( name.value.length < 2 ) errors.name = true;

        // if( Array.isArray( email.values ) ){
        //     if( !this.IsEmail( email.values[0] ) )
        //         errors.email = true;
        // }else{
        //     errors.email = true;
        // }

        if( Array.isArray( phone.values ) ){
            if( phone.values[0].length < 10 )
                errors.phone = true;
        }else {
            errors.phone = true;
        }

        if( current_date === '' && deliveryTriggerState === true )
            errors.delivery = true;

        // if( parseInt(day) > 31 && parseInt(day) <= 0 )
        //     errors.dob = true;
        // if( parseInt(month) > 12 && parseInt(day) <= 0 )
        //     errors.dob = true;
        // if( year.length !== 4 )
        //     errors.dob = true;
        if(mailing_address.street_1.value.length < 3 )
            errors.street = true;
        if( mailing_address.city.value.length <= 1 )
            errors.city = true;
        if( mailing_address.country.length < 2 )
            errors.country = true;


        console.debug("ext: ", user.external_id);

        if( this.isObjEmpty( errors ) ){
            let { thread_context } = this.props.thread_context;
            profile_data.name = profile.name.value;

            profile_data.external_id = thread_context.psid;  //user.external_id;
            profile_data.extension_platform = "facebook";
            profile_data.system_user_id = user.system_id;
            if (month != '' && day != '' && year != '') {
                profile_data.date_of_birth = month+"-"+day+"-"+year;
            }
            profile_data.gender = (gender === "" ) ? "male" : gender;
            if ( Array.isArray( email.values ) && email.values[0].length > 0 ) {
                profile_data.email = email.values;
            }
            profile_data.phone = phone.values;

            let new_mailing_address = {};
            new_mailing_address.street_1 = mailing_address.street_1.value;
            new_mailing_address.street_2 = mailing_address.street_2.value;
            new_mailing_address.city = mailing_address.city.value;
            new_mailing_address.zip = mailing_address.zip.value;
            new_mailing_address.country = mailing_address.country;

            profile_data.mailing_address = new_mailing_address;

            this.props.dispatch({ type: "FETCH_UPDATE_PROFILE" });
            this.props.dispatch( updateProfile( profile_data, this.props.profile) );
        }
        this.setState({ errors });
        return false;
    }

    componentWillReceiveProps(nextProps){
        let profile = nextProps.profile;

        if( profile.profile_update === true  ){
            this.props.dispatch({ type: "FETCH_PROFILE" });
            let { update_cart } = this.state;
            let userLocalTime = moment(new Date()).local();
            update_cart.order_time = userLocalTime.format("YYYY-MM-DD hh:mm:ss A");
            update_cart.expected_delivery_time = !this.state.deliveryTriggerState ? null : {
                    date: moment(this.state.current_date).format('ddd, MMM DD, YYYY'),
                    time: this.state.current_time
                };
            this.props.dispatch( updateCart(update_cart ) );
        }

        if( profile.checkout_fetched === true ){
            console.debug("profile checked...");
            this.props.dispatch( LoadFbMessengerExtensionThread('close') );
        }
    }

    isObjEmpty(obj) {
        for(let key in obj) {
            if(obj.hasOwnProperty(key))
                return false;
        }
        return true;
    }

    onToggleDeliveryBox(event){
        // event.preventDefault();
        this.setState({ deliveryTriggerState: !this.state.deliveryTriggerState });
    }

    renderDeliveryBox(){
        return (
            <div className="main_delivery_box">
                <div className="flex-box">
                    <div className="col meta-field"> <p> Date </p> </div>
                    <div className="col-1 input-field"><input disabled="disabled" type="text" name="name" className="form-control" value={(this.state.current_date === '') ? this.state.current_date : moment(this.state.current_date).format('ddd, MMM DD, YYYY')} placeholder="Date"/></div>
                </div>
                <div className="flex-box">
                    <div className="col meta-field"> <p> Time </p> </div>
                    <div className="col-1 input-field"><input disabled="disabled" type="text" name="name" className="form-control" value={this.state.current_time} placeholder="Time"/></div>
                </div>
                <div className="delivery_control text-right">
                    <a href="#" onClick={this.showWeek.bind(this)} className="delivery_btn"> Delivery Time </a>
                </div>
            </div>
        )
    }

    renderProfileView(){
        let { profile, errors } = this.state;
        let { profile_update_fetching, checkout_fetched } = this.props.profile;
        return (
            <div className="webview-usha-profile-view">
                <div className="usha-profile-container">

                    <div className={( typeof errors.delivery !== 'undefined' )? "delivery_container form-group has-error" :"delivery_container form-group"}>
                        <div className="flex-box">
                            <h3 className="delivery-title col-1"> Delivery Time </h3>
                            <div className="col switch-box">
                                <Switch onChange={this.onToggleDeliveryBox.bind(this)} isChecked={ this.state.deliveryTriggerState }/>
                            </div>
                        </div>
                        { (this.state.deliveryTriggerState) ? this.renderDeliveryBox() : null }
                    </div>

                    <div className={( typeof errors.name !== 'undefined' )? "flex-box form-group has-error" :"flex-box form-group"}>
                        <div className="col meta-field"> <p> Name </p> </div>
                        <div className="col-1 input-field"><input style={{ textTransform: 'capitalize' }} onChange={this.onChangeProfile.bind(this)} type="text" name="name" className="form-control" value={profile.name.value} placeholder={profile.name.placeholder}/></div>
                    </div>

                    <div className={( typeof errors.dob !== 'undefined' )? "flex-box form-group has-error" :"flex-box form-group"}>
                        <div className="col dob-field"> <p> DOB </p></div>
                        <div className="col-1 dob-input-field">
                            <input type="text" name="day" onChange={this.onChangeProfile.bind(this)} className="form-control day-field" value={profile.day} placeholder="DD"/>
                            <input type="text" name="month" onChange={this.onChangeProfile.bind(this)} className="form-control month-field" value={profile.month}  placeholder="MM"/>
                            <input type="text" name="year" onChange={this.onChangeProfile.bind(this)} className="form-control year-field" value={profile.year} placeholder="YYYY"/>
                        </div>
                    </div>
                    <div className="flex-box form-group">
                        <div className="col meta-field"> <p> Sex </p></div>
                        <div className="col-1 input-field">
                            <select name="sex" onChange={this.onChangeProfile.bind(this)} defaultValue={(profile.gender === 'male') ? 1 : 2 } className="form-control">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div className={( typeof errors.email !== 'undefined' )? "flex-box form-group has-error" :"flex-box form-group"}>
                        <div className="col meta-field"> <p> Email </p></div>
                        <div className="col-1 input-field"><input name="email" type="email" onChange={this.onChangeProfile.bind(this)} value={profile.email.values[0]} className="form-control" placeholder={profile.email.placeholder}/></div>
                    </div>
                    <div className={( typeof errors.phone !== 'undefined' )? "flex-box form-group has-error" :"flex-box form-group"}>
                        <div className="col meta-field"> <p> Phone </p></div>
                        <div className="col-1 input-field"><input type="text" name="phone" onChange={this.onChangeProfile.bind(this)} value={profile.phone.values[0]} className="form-control" placeholder={profile.phone.placeholder}/></div>
                    </div>
                    <div className={( typeof errors.street !== 'undefined' || typeof errors.city !== 'undefined' || typeof errors.zip !== 'undefined' )? "form-group address-area has-error" :"form-group address-area"}>
                        <label htmlFor="address"> Mailing Address </label>
                        <input type="text"  name="street_1" onChange={this.onChangeProfile.bind(this)} value={profile.mailing_address.street_1.value} className="form-control street-field" placeholder={profile.mailing_address.street_1.placeholder}/>
                        <input type="text"  name="street_2" onChange={this.onChangeProfile.bind(this)} value={profile.mailing_address.street_2.value} className="form-control street-field" placeholder={profile.mailing_address.street_2.placeholder}/>
                        <div className="flex-box">
                            <div className="col-1">
                                <input type="text" onChange={this.onChangeProfile.bind(this)} value={profile.mailing_address.city.value} className="form-control" placeholder={profile.mailing_address.city.placeholder} name="city"/>
                            </div>
                            <div className="col-1">
                                <input type="text" onChange={this.onChangeProfile.bind(this)} value={profile.mailing_address.zip.value} className="form-control" placeholder={profile.mailing_address.zip.placeholder} name="zip"/>
                            </div>
                        </div>
                    </div>
                    <div className={( typeof errors.country !== 'undefined' )? "flex-box form-group has-error" :"flex-box form-group"}>
                        <div className="col meta-field"> <p> Country </p></div>
                        <div className="col-1 input-field"><input type="text" name="country" className="form-control" onChange={this.onChangeProfile.bind(this)} value={profile.mailing_address.country} placeholder="Country"/></div>
                    </div>
                    <div className="form-group submit-btn-area">
                        { (profile_update_fetching === true && checkout_fetched === false ) ? <Loader/> : <a href="#" onClick={this.onConfirmCheckout.bind(this)} className="submit-btn"> {profile.action_button_title}</a>  }
                    </div>
                </div>

                { (this.state.week_popup) ?  <WeekendCalenderModal {...this.state}
                                                                   className="active"
                                                                   modalDismiss={this.onPopupCancel.bind(this)}
                                                                   getSelectedDate={this.getSelectedDate.bind(this)}
                                                                   onSelectTime={this.onSelectTime.bind(this)} /> : null }

            </div>
        )
    }

    renderEmptyCart(){
        let { message } = this.props.profile;
        return (
            <div className="empty-cart-cartList">
                <h3 className="title"> { message } </h3>
            </div>
        )
    }

    render(){
        let { profile_update } = this.state;
        return ( profile_update ) ? this.renderProfileView() : this.renderEmptyCart();
    }
}

