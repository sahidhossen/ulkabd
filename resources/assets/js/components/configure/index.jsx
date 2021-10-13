import React from "react";
import request from 'superagent';
import Config from "../../components/config";
import Switch from '../elements/Switch';
import Dropzone from 'react-dropzone';

let config = new Config();
class Configure extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            counter_value:0,
            maximum_counter_value: 100,
            entity_name:"Entity",

            customerOrderState: false,
            menuBoxSwitcher:true,

            orderStates:[
                { value:'New'},
                { value:'Delivery'}
            ],

            greetingMsg: "Hi there {name} ",
            error:{ greetingError: false },
            isPopup: false,
            isWelcomePopup: false,
            welcomeImage:null,
            showDefaultOption:false,
            tuppleOption:false,
            welcomeMessages:
                {
                    settings: { imageCounter:0, textBoxCounter:0 },
                    data: []
                },

            co_ordinate:{ x:0, y:0 },


            keyLetterFinder:''
        };
        this.onChangeState = this.onChangeState.bind(this);
        this.onDeleteState = this.onDeleteState.bind(this);
        this.onSaveState = this.onSaveState.bind(this);
        this.onCounterUpdate = this.onCounterUpdate.bind(this);
        this.onImageDrop = this.onImageDrop.bind(this);
    }

    componentDidMount(){
        if( this.state.orderStates.length == 0 || this.state.orderStates.length < 5 ){
            let orderStates = this.state.orderStates;
            orderStates.push({ value:'', isNew: true});
            this.setState({orderStates: orderStates})
        }
    }
     _handlerOnChange( event ){
         if(event.target.name === 'entity_name')
             this.setState({ entity_name: event.target.value });

         if(event.target.name === 'counter')
             this.setState({ counter_value: event.target.value })

         if(event.target.name === 'max_order_counter')
             this.setState({ maximum_counter_value: event.target.value })

     }

    onCounterUpdate(step, event){
        event.preventDefault();
        let currentCounter = this.state.counter_value;
        if( step ){
            if( currentCounter < 5 )
                currentCounter++
        }else {
            if(currentCounter > 0 )
                currentCounter--
        }
        this.setState({ counter_value: currentCounter })
    }

    onMaximumOrderCounterUpdate(step, event ){
        event.preventDefault();
        let currentCounter = parseInt(this.state.maximum_counter_value);
        if( step ){
            if( currentCounter >= 1000 )
                currentCounter += 1000;
            else
                currentCounter += 100;
        }
        else {
            if (currentCounter - 1000 >= 1000)
                currentCounter -= 1000;
            else if ( currentCounter >= 200 && currentCounter <= 2000)
                currentCounter -= 100;
            else
                currentCounter = 100
        }
        this.setState({ maximum_counter_value: currentCounter })

    }

    onCustomerOrderStateChange(event){
        this.setState({ customerOrderState: !this.state.customerOrderState })
    }

    onChangeState(index, event){
        event.preventDefault();
        let orderState = this.state.orderStates;
        orderState[index].value = event.target.value;

        this.setState({ orderStates: orderState })
    }
    onDeleteState( index, event ){
        event.preventDefault();
        let orderState = this.state.orderStates;
        orderState.splice(index, 1);
        if( this.state.orderStates.length === index  )
            orderState.push({ value:'', isNew: true});

        this.setState({orderStates: orderState});
    }
    onSaveState( index, event ){
        event.preventDefault();

        let orderStates = this.state.orderStates;
        if( orderStates[index].value.length < 2 )
            return false;

        orderStates[index].isNew = null;
        if(  this.state.orderStates.length < 5 ){
            orderStates.push({ value:'', isNew: true});
        }
        this.setState({orderStates: orderStates});

    }

    menuBoxSwitcher(event){
        this.setState({ menuBoxSwitcher: !this.state.menuBoxSwitcher })
    }

    /*
     Trigger for show Popup
     */
    onShowPopup(event){
        event.preventDefault();
        this.setState({ isPopup: !this.state.isPopup });
    }

    onPopupCancel(event){
        event.preventDefault();
        this.setState({ isPopup: false, isWelcomePopup: false });
    }

    onSaveActionGreetingMsg(event){
        event.preventDefault();
        if( !this.state.error.greetingError ) {
            this.setState({ isPopup: !this.state.isPopup });
        }

    }

    onChangeGreetingMsg(event){
        let error = this.state.error;
        let value = event.target.value;
        error.greetingError =  (event.target.value.length < 5) ? error.greetingError = true : error.greetingError = false
        let lastTwoLetter = value.slice(-2);
        if( lastTwoLetter == '{{' || lastTwoLetter == ' {{' || lastTwoLetter == '{{ ')
            this.setState({ tuppleOption: true })
        else
            this.setState({ tuppleOption: false })
        this.setState({ greetingMsg: value })


    }
    detectOnMouseMove(event){
        let coordinate = this.state.co_ordinate;
        coordinate.x = event.screenX;
        coordinate.y = event.screenY;

        this.setState({ co_ordinate: coordinate });
        console.debug("Event: ", event);
        console.debug("Type: ", event.target.value);

    }

    toppleList(){
        return(
            <div className="topple-previewContainer">
                <ul>
                    <li> Name  </li>
                    <li> First Name  </li>
                    <li> Last Name  </li>
                    <li> Greeting  </li>
                </ul>
            </div>
        )
    }

    onShowWelcomePopup(event){
        event.preventDefault();
        this.setState({ isWelcomePopup: !this.state.isWelcomePopup });
    }

    onImageDrop(file){
        this.setState({ welcomeImage : file[0].preview });
    }

    removeWelcomeImage(event){
        event.preventDefault();
        this.setState({ welcomeImage : null });
    }

    toggleDefaultOption(event){
        this.setState({ showDefaultOption: !this.state.showDefaultOption })
    }
    setWelcomeOption(optionType, event){
        event.preventDefault();
        let welcomeMsgs = this.state.welcomeMessages;
        let { settings, data } = welcomeMsgs;
        if( optionType === 'text' ){
            if( settings.textBoxCounter === 5 ) return false;
            data.push({ message: 'This is new message', error: false, status: 1 })
            settings.textBoxCounter = settings.textBoxCounter+1;
        }else {
            if( settings.imageCounter == 1 ) return false;
            data.push({ image:null, file: null, status: 2 })
            settings.imageCounter = settings.imageCounter+1;
        }
        this.setState({ welcomeMessages: welcomeMsgs, showDefaultOption: !this.state.showDefaultOption })
    }

    welcomeImages(){
        let { data } = this.state.welcomeMessages;
        return data.map( (image, index) =>{

            if( image.status === 1 ){
                return (
                    <div key={index} className="welcome-msg-area">
                        <textarea name="greeting_msg" onChange={this.onChangeWelcomeMsg.bind(this, index)} className={(image.error) ? "form-control has-error": "form-control"}  value={image.message}></textarea>
                    </div>
                )
            }else {
                return (
                    <div key={index} className="welcome-image">
                        <div className="form-group">
                            {(image.image === null) ? null : <span className="remove-img-btn"
                                                                   onClick={this.removeWelcomeImage.bind(this)}> X </span>}
                            <Dropzone
                                className="welcomeImageUpload"
                                multiple={false}
                                accept="image/*"
                                onDrop={this.onImageDrop}>
                                <div className="dropArea">
                                    {(image.image === null) ? <span className="fa fa-camera"></span> :
                                        <img id="uploadImageId" src={ image.image }
                                             alt="category Image"/> }
                                </div>
                            </Dropzone>
                        </div>
                    </div>
                )
            }
        })
    }

    onChangeWelcomeMsg(index, event){
        let welcomeMsg = this.state.welcomeMessages;
        let { data } = welcomeMsg;
        data[index].error = ( event.target.value.length < 2 ) ? data[index].error = true : data[index].error = false;
        data[index].message = event.target.value;
        this.setState({ welcomeMessage: welcomeMsg });
    }


    dashboard(){
        let orderState = this.state.orderStates.map( (State, index) =>{
            return (
                <li key={index}>
                    <span className="number"> {index+1} </span>
                    <input type="text" onChange={this.onChangeState.bind(this, index)} className="order_state_field" value={State.value}/>
                    {( State.isNew !== 'undefined' && State.isNew === true ) ?  <a href="#" className="saveBtn" onClick={this.onSaveState.bind(this,index)} > <i className="fa fa-check"></i> </a> :
                                                <a href="#" onClick={this.onDeleteState.bind(this, index)} className="cancelBtn"> X </a> }
                </li>)
        })
        return (
            <div className="configure-dashboard">
                <div className="row margin-0 page-section">
                    <div className="widget widget-greeting-message">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Greeting Message </h3>
                            <span className="edit-btn btn btn-admin btn-admin-white btn-md" onClick={this.onShowPopup.bind(this)}> Edit </span>
                        </div>
                        <div className={(this.state.isPopup) ? "u-popup-shadow onVision" : "u-popup-shadow" }>
                            <div className="main-modal">
                                <div className="modal-title">
                                    <h3 className="title"> Greeting Message </h3>
                                </div>
                                <div className="modal-body">
                                    <textarea name="greeting_msg" ref='myTextarea'  onSelect={this.detectOnMouseMove.bind(this)} onChange={this.onChangeGreetingMsg.bind(this)} className={(this.state.error.greetingError) ? "form-control has-error": "form-control"}  value={this.state.greetingMsg}></textarea>
                                    { (this.state.tuppleOption) ?  this.toppleList() : null }
                                </div>
                                <div className="modal-footer">
                                    <div className="flex-box">
                                        <a href="#" onClick={this.onPopupCancel.bind(this)} className="btn btn-theme btn-theme-red btn-md"> Cancel </a>
                                        <a href="#" onClick={this.onSaveActionGreetingMsg.bind(this)} className="btn btn-admin btn-admin-white btn-md"> Save </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="widget widget-welcome-message">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Welcome Message  </h3>
                            <span className="edit-btn btn btn-admin btn-admin-white btn-md" onClick={this.onShowWelcomePopup.bind(this)}> Edit </span>
                        </div>
                        <div className={(this.state.isWelcomePopup) ? "u-popup-shadow onVision" : "u-popup-shadow" }>
                            <div className="main-modal">
                                <div className="modal-title">
                                    <h3 className="title"> Welcome Message </h3>
                                    <div className="option-dropdown">
                                        <div className="dropdown-icon" onClick={this.toggleDefaultOption.bind(this)}>
                                            <span> Add </span><i className="fa fa-chevron-down"></i>
                                        </div>
                                        <ul className={(this.state.showDefaultOption) ? "dropdown-list show" : "dropdown-list" }>
                                            <li onClick={this.setWelcomeOption.bind(this, 'text')}> Add Text </li>
                                            <li onClick={this.setWelcomeOption.bind(this, 'img')}> Add Image </li>
                                        </ul>
                                    </div>
                                </div>
                                <div className="modal-body">
                                    { this.welcomeImages() }
                                    <div className="go-btn text-center">
                                        <a href="#" className="btn btn-admin btn-admin-white btn-md"> Go </a>
                                    </div>
                                </div>
                                <div className="modal-footer">
                                    <div className="flex-box">
                                        <a href="#" onClick={this.onPopupCancel.bind(this)} className="btn btn-theme btn-theme-red btn-md"> Cancel </a>
                                        <a href="#" onClick={this.onSaveActionGreetingMsg.bind(this)} className="btn btn-admin btn-admin-white btn-md"> Save </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="row margin-0 page-section">
                    <div className="widget widget-out-of-stock">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Out Of Stack Default value </h3>
                            <div className="count-engine">
                                <a href="#" onClick={this.onCounterUpdate.bind(this, false)}> <i className="fa fa-minus"></i></a>
                                <input type="text" name="counter" value={this.state.counter_value} onChange={this._handlerOnChange.bind(this)}/>
                                <a href="#" onClick={this.onCounterUpdate.bind(this, true)}> <i className="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div className="widget widget-out-of-stock">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Maximum Order Items </h3>
                            <div className="count-engine">
                                <a href="#" onClick={this.onMaximumOrderCounterUpdate.bind(this, false)}> <i className="fa fa-minus"></i></a>
                                <input type="text" name="max_order_counter" value={this.state.maximum_counter_value} onChange={this._handlerOnChange.bind(this)}/>
                                <a href="#" onClick={this.onMaximumOrderCounterUpdate.bind(this, true)}> <i className="fa fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div className="widget widget-entry-name">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Entity Name </h3>
                            <div className="input-box">
                                <input type="text" className="form-control" onChange={this._handlerOnChange.bind(this)} value={this.state.entity_name} name="entity_name"/>
                            </div>
                        </div>
                    </div>
                    <div className="widget widget-order-states">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Order States <small>(Note: Add upto 5 state)</small> </h3>
                        </div>
                        <div className="widget-body">
                            <ul className="stateList">
                                {orderState}
                            </ul>
                        </div>
                        <div className="widget-footer-area">
                            <h3 className="widget-title"> Notify Customer About Order state </h3>
                            <div className="input-switch-box">
                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                            </div>
                        </div>
                    </div>
                    <div className="widget widget-entry-name">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Notify Customer About Order state </h3>
                            <div className="input-switch-box">
                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                            </div>
                        </div>
                    </div>
                    <div className="widget widget-composer-menu">
                        <div className="widget-title-area">
                            <h3 className="widget-title"> Menu Bar </h3>
                        </div>
                        <div className="widget-body">
                          <div className="flex-box composer-section">
                              <h3 className="widget-title"> Composer Menu </h3>
                              <div className="input-switch-box">
                                  <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                              </div>
                          </div>

                          <div className="menu-box-container">
                              <div className="menu-box">
                                  <div className="editable-box">
                                      <input type="text" value="GO" className="editable-field"/>
                                  </div>
                                  <div className="flex-box menu-switcher">
                                      <h3 className="widget-title"> Menu </h3>
                                      <div className="input-switch-box">
                                          <Switch onChange={this.menuBoxSwitcher.bind(this)} isChecked={ this.state.menuBoxSwitcher } />
                                      </div>
                                  </div>
                                  <div className="powered-by">
                                      <h3 className="widget-title"> Powered By Usha </h3>
                                  </div>
                              </div>
                                <div className={(this.state.menuBoxSwitcher) ? "menu-item-box active" : "menu-item-box" }>
                                    <div className="menu-item-box-inner">
                                        <div className="flex-box show-cart">
                                            <h3 className="widget-title"> Show Cart/Profile </h3>
                                            <div className="input-switch-box">
                                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                                            </div>
                                        </div>
                                        <div className="flex-box human-asistant">
                                            <h3 className="widget-title"> Get Human Assist </h3>
                                            <div className="input-switch-box">
                                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                                            </div>
                                        </div>
                                        <div className="flex-box feedback">
                                            <h3 className="widget-title"> Feedback/Complaint </h3>
                                            <div className="input-switch-box">
                                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                                            </div>
                                        </div>
                                        <div className="flex-box feedback">
                                            <h3 className="widget-title"> Faq </h3>
                                            <div className="input-switch-box">
                                                <Switch onChange={this.onCustomerOrderStateChange.bind(this)} isChecked={ this.state.customerOrderState } />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }

    render(){
        return this.dashboard();
    }
}


export default Configure;
