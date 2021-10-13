
import React from "react";
import Config from "../components/config";
import request from 'superagent';
import i18n from "../plugins/i18n.js"

let config = new Config();

class BotList extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            fb_access_token: this.props.fb_access_token,
            fb_varfify_token:this.props.fb_verify_token,
            is_fb_webhook:this.props.is_fb_webhook,
            agent_id:this.props.agent_id,
            agent_code:this.props.agent_code,
            editMode: this.props.editMode,
            changeMode: false
        }
        this.agentEditMode = this.agentEditMode.bind(this);
        this.onFormSubmit = this.onFormSubmit.bind(this);
        this.onChangeHandler = this.onChangeHandler.bind(this);
        this.onCancel = this.onCancel.bind(this);

        this.checkVerifiedEmail = this.checkVerifiedEmail.bind(this);
    }
    agentEditMode(event){
        event.preventDefault();

        let agent = request
            .get('/api/agent')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .query({ 'agent_id': this.state.agent_id});
        agent.end((err, response) => {
            if (err) {
                console.error(err);
            }
            if(response){
                if(response.body.error==false){

                    let fb_access_token = response.body.agent.fb_access_token;
                    let fb_verify_token = response.body.agent.fb_verify_token;
                    this.setState({ agent_id : response.body.agent.id });
                    this.setState({ fb_access_token : fb_access_token });
                    this.setState({ fb_verify_token : fb_verify_token });

                    if(this.props.editMode == null || this.props.editMode != response.body.agent.id ) {
                        this.props.onEditMode(response.body.agent.id);
                    }else {
                        this.props.onCancelMode(event);
                    }
                    //(this.state.editMode === false) ? this.setState({ editMode : true }) : this.setState({ editMode : false });
                }
            }
        });

    }
    onChangeHandler(event){
        if(event.target.value.length > 2 ){
            this.setState({ changeMode: true })
        }else {
            this.setState({ changeMode: false })
        }

        let target = event.target;
        let name = target.name;
        let value = target.value;

        this.setState({ [name] : value  });
    }
    onFormSubmit(event){
        event.preventDefault();
        let update = request
            .post('/api/update_agent')
            .set('X-CSRF-TOKEN', config._token)
            .set('X-Requested-With', 'XMLHttpRequest')
            .field('agent_id', this.state.agent_id)
            .field('fb_access_token', this.state.fb_access_token)

        update.end((err, response) => {
            if (err) {
                console.error(err);
            }
            if(response){
                console.debug(response.body);
                if(response.body.error==false){
                    let fb_access_token = response.body.agent.fb_access_token;
                    this.setState({ fb_access_token : fb_access_token });
                    this.setState({ is_fb_webhook: response.body.agent.is_fb_webhook });
                   // this.setState({ editMode : false })
                    this.props.onCancelMode(event);
                }
            }
        });

    }

    onCancel(event){
        event.preventDefault();
        this.props.onCancelMode(event);
    }

    checkVerifiedEmail(event){
        //event.preventDefault();
        if( !this.props.user.verified )
            this.props.checkEmailVerified()
    }

    render() {
        //console.debug("asdfasd", this.props.editMode);
        let { agent_id, agent_name, agent_image, agent_code } = this.props;
        if(agent_image === null || typeof agent_image === 'undefined') {
            agent_image = 'images/usha.png';
        }else {
            agent_image = "/uploads/"+agent_image;
        }
            let bodyClass, button ;
            if(this.props.editMode == this.state.agent_id ){
                bodyClass = "body-main card-bg enable-edit-mode";
                if(this.state.changeMode == true ) {
                    button = <input type="submit" value="Submit"/>
                }else {
                    button = <a href="#" onClick={this.onCancel } > {i18n.t('common.buttons.cancel')} </a>
                }

            }else {
                bodyClass = "body-main card-bg";
                button =  <a onClick={ this.checkVerifiedEmail } href={ (this.props.user.verified ) ?  "/dashboard/"+agent_code : '#app' }> {i18n.t('pages.bots.botLists.card.button.dashboard')} </a>
            }


        return (
            <div className="col-md-4 bot pending" key={agent_id}>
                <div className={ bodyClass }>
                    <form onSubmit={this.onFormSubmit}>
                        <div className="bot-header">
                            <div className="bot-logo">
                                <img className="img-circle" src={agent_image} alt="Bot Title"/>
                                <h3 className="title title-white text-center"> { agent_name }  </h3>
                            </div>
                            {/*<a href="#" className={(this.props.editMode == this.state.agent_id ) ? "bot-setting onSettingMode" : "bot-setting" }  onClick={this.agentEditMode}> <i className="fa fa-cog"></i> </a>*/}
                        </div>
                        <div className="bot-body">
                            <ul>
                                <li> <span className={ (this.props.agent.order_count == 0 ) ? 'badge badge-warning' : 'badge badge-success' }>{ this.props.agent.order_count }</span> <div className="bot-info"> {i18n.t('pages.bots.botLists.card.botBody.botInfo.orders')} </div> </li>
                                <li> <span className={ (this.props.agent.fb_opt_in_count == 0 ) ? 'badge badge-warning' : 'badge badge-success' }> { this.props.agent.fb_opt_in_count } </span> <div className="bot-info"> {i18n.t('pages.bots.botLists.card.botBody.botInfo.users')} </div> </li>
                                <li> <span className={ (this.props.agent.product_count == 0 ) ? 'badge badge-warning' : 'badge badge-success' } >{ this.props.agent.product_count }</span> <div className="bot-info"> {i18n.t('pages.bots.botLists.card.botBody.botInfo.entities')} </div></li>
                            </ul>
                        </div>
                        <div className="bot-footer">
                            { button }
                        </div>
                    </form>
                </div>
            </div>
        )
    }
}

export default BotList;