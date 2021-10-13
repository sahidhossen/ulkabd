import React from 'react';
import BotList from '../components/BotList';
import NewBot from '../components/NewBot';
import Config from "../components/config";
import request from 'superagent';
import Loader from "../components/Loader";

import axios from 'axios';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';
import i18n from "../plugins/i18n.js"

let config = new Config();


axios.defaults.headers.common = {
    'X-Requested-With': 'XMLHttpRequest',
    'X-CSRF-TOKEN' : config._token
};

class MainBot extends React.Component {

    constructor(props) {
        super(props);

        this.state ={
            isNewBot : false,
            agent_name : '',
            uploadedFile: 'images/usha.png',
            uploadImageUrl:'images/usha.png',
            has_error: false,
            fb_access_token: '',
            fb_varfify_token:'',
            fb_webhook_url:'',
            is_webhook:null,
            agents: [],
            user:[],

            editMode:null,

            isLoader : false,
            api_error: false,
            api_error_msg:null,

            is_send_verify_email:0,
        }
        //New agent Create
        this.AddNewBot = this.AddNewBot.bind(this);
        this.handleFormOnChange = this.handleFormOnChange.bind(this);
        this.handleFormSubmit = this.handleFormSubmit.bind(this);
        this.onImageDrop =  this.onImageDrop.bind(this);
        this.onEditMode = this.onEditMode.bind(this);
        this.onCancelMode = this.onCancelMode.bind(this);
        this.CancelNewBotAction = this.CancelNewBotAction.bind(this);

        this.sendVarificationEmail = this.sendVarificationEmail.bind(this);
        this.checkEmailVerified = this.checkEmailVerified.bind(this);
    }

    AddNewBot(event){
        event.preventDefault();
        if( this.state.user.verified ) {
            this.setState({isNewBot: true})
        }else {
            this.setState({ is_send_verify_email: 1 })
        }
    }

    CancelNewBotAction( event ){
       // event.preventDefault();
        this.setState({ isNewBot:false })
    }

    componentDidMount() {
        this.setState({ isLoader: true });
         let agent = request
                .post('api/agent_lists')
                .set('X-CSRF-TOKEN', config._token)
                .set('X-Requested-With', 'XMLHttpRequest')
                .set('Accept', 'application/json');
            agent.end((err, response) => {
                if( response.body.error == false ) {
                    this.setState({agents: response.body.agents, user: response.body.user });
                }
                this.setState({ isLoader: false });
            });
    }

    handleFormOnChange(event){
        let target = event.target;
        let name = target.name;
        let value = target.value;
         this.setState({ [name] : value  });
        }

    handleFormSubmit(event) {
        event.preventDefault();

        this.handleImageUpload();
    }

    onImageDrop(files) {

        this.setState({
            uploadedFile: files[0]
        });
        this.setState({ uploadImageUrl : files[0].preview })
    }

    handleImageUpload() {
        console.debug("submited");
        let upload = request
            .post('/api/add_agent')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .field('agent_name', this.state.agent_name)
            .field('file', this.state.uploadedFile);
        upload.end((err, response) => {
            if (err) {
                console.error(err);
            }
            console.debug("resonse: ", response);
            if(response.body.error == false ){
                 this.setState({isNewBot: false});
                let agents = this.state.agents;
                let newAgent = response.body.agents;
                agents.unshift(newAgent);
                this.setState({agents:  agents});
            }
            if( response.body.error == true ){
                this.setState({ api_error: true, api_error_msg : response.body.message })
            }

        });
    }

    onEditMode(agent_id){
        this.setState({ editMode : agent_id })
        // (this.state.editMode == null) ? this.setState({ editMode : agent_id }) : this.setState({ editMode : null });
    }
    onCancelMode(event){
        this.setState({ editMode : null });
    }

    sendVarificationEmail( event ) {
        event.preventDefault();
        let verify = request
            .post('/api/resend_verification_mail')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .set('Accept', 'application/json');
        verify.end((err, response) => {
            console.debug( response.body );
            if( response.body.error == false ) {
                this.setState({ is_send_verify_email: 2 });
            }

        });
    }
    checkEmailVerified(){
        this.setState({ is_send_verify_email:1 })
    }

    renderView() {
        let NewAgent = null;
        if(this.state.isNewBot){
            NewAgent =
                <NewBot
                        user={ this.state.user }
                        has_error={this.state.has_error}
                        onCreateMod={this.state.isNewBot}
                        uploadImageUrl= {this.state.uploadImageUrl}
                        agent_name={this.state.agent_name}
                        handleOnChange={this.handleFormOnChange}
                        handleOnSubmit={this.handleFormSubmit}
                        onDropZoneDrop={this.onImageDrop}
                        onCancelNewAgent = { this.CancelNewBotAction }
                        />
        }

        let bot_list;
        bot_list = this.state.agents.map((agent, i) =>

        <BotList key={agent.id}
                 user={ this.state.user }
                 agent={ agent }
                 agent_code={agent.agent_code}
                 agent_id={agent.id}
                 agent_name={agent.agent_name}
                 agent_image={agent.image_path}
                 fb_verify_token={this.state.fb_verify_token}
                 fb_access_token={this.state.fb_access_token}
                 is_webhook={this.state.is_webhook}
                 is_fb_webhook={ agent.is_fb_webhook }
                 onEditMode={this.onEditMode}
                 onCancelMode={this.onCancelMode}
                 editMode={this.state.editMode}
                 checkEmailVerified={ this.checkEmailVerified }
            />
        )

        return (
            <div className="row">

                <div className="clearfix page-section" id="create_bot_section">
                    { ( this.state.api_error )  ? <div className="alert alert-danger"> <p> {this.state.api_error_msg} </p></div> : null }
                    { ( this.state.is_send_verify_email == 1 ) ? <div className="alert alert-danger"><p> {i18n.t('pages.bots.alert.verifyEmail.paragraph1')}  <a href="#" onClick={ this.sendVarificationEmail }> {i18n.t('pages.bots.alert.verifyEmail.link')}</a> {i18n.t('pages.bots.alert.verifyEmail.paragraph2')} </p> </div> : null }
                    { ( this.state.is_send_verify_email == 2 ) ? <div className="alert alert-warning"> <p> {i18n.t('pages.bots.alert.resendEmail.paragraph')}  <a href="#" onClick={ this.sendVarificationEmail }> {i18n.t('pages.bots.alert.resendEmail.link')}</a>!</p> </div> : null }


                    <div className="col-lg-12 col-md-12 col-sm-12">
                        <div className="card-bg">

                            <div className="row create-bot">
                                <div className="col-lg-12 col-md-12 col-sm-12 text-center">
                                    <img src="/images/create_bot.png"/>
                                </div>
                                <div className="col-lg-12 col-md-12 col-sm-12">
                                    <p className="text-center">{i18n.t('pages.bots.createBot.card.paragraph')}<br/>{i18n.t('pages.bots.createBot.card.paragraphBreak')}</p>
                                </div>
                                <div className="col-lg-12 col-md-12 col-sm-12 text-center">
                                    <a href="#" onClick={ this.AddNewBot } id="CreateNewBot" className="btn btn-primary btn-lg"> {i18n.t('pages.bots.createBot.card.button.newAgent')} </a>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div className="clearfix page-section all_bots" id="bot_lists_section">
                    { (this.state.isLoader ) ? <Loader/> : null }
                    <ReactCSSTransitionGroup
                        transitionName="fade"
                        transitionEnterTimeout={500}
                        transitionLeaveTimeout={300}>
                        {NewAgent}
                        {bot_list}
                    </ReactCSSTransitionGroup>

                </div>
            </div>
        )
    }

    render(){
        return this.renderView()

    }
}



export default MainBot;