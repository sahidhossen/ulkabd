import React from 'react';
import Dropzone from 'react-dropzone';
import i18n from "./../plugins/i18n.js"

class NewBot extends React.Component {

    constructor(props) {
        super(props);


        this.handleChange = this.handleChange.bind(this);
        this.handleSubmit = this.handleSubmit.bind(this);
        this.onImageDrop = this.onImageDrop.bind(this);
        this.onCancelAction = this.onCancelAction.bind(this);

    }

    onCancelAction( event ){
        event.preventDefault();
        this.props.onCancelNewAgent( event );
    }

    handleChange(event) {
        this.checkValidation( event );
        this.props.handleOnChange(event);
    }

    handleSubmit(event){
        event.preventDefault();
        console.debug("submit");
        this.props.handleOnSubmit(event);
    }

    checkValidation(event){
        let Value = event.target.value;
        if( Value == '' || Value.length<3 ){
            $(event.target).addClass('has-error');
        }else {
            $(event.target).removeClass('has-error');
        }
    }

    onImageDrop(file){
        this.props.onDropZoneDrop(file);
    }

    render() {

        let isNewBot = this.props.isNewBot;
        let button = null;
            if(this.props.agent_name.length < 4 ){
                button = <input type="submit" value={i18n.t('common.buttons.submit')} className="disabled" disabled="disabled"/>
            }else {
                button = <input type="submit" value={i18n.t('common.buttons.submit')}/>
            }

        return (
            <div className="NewBot col-md-4 bot born" >
                <form onSubmit={this.handleSubmit}  encType="multipart/form-data">
                    <div className="body-main card-bg">

                        <div className="bot-header">
                            <div className="bot-logo">
                                <Dropzone
                                    className="agentImageUpload"
                                    multiple={false}
                                    accept="image/*"
                                    onDrop={this.onImageDrop}>
                                    <div className="dropArea">  <img className="img-circle" src={this.props.uploadImageUrl} alt={this.props.agent_name}/> </div>
                                </Dropzone>
                            </div>
                            <div className="bot-title">
                                <div className="bot_title_field">
                                    <input type="text" placeholder={i18n.t('pages.bots.newBot.name.placeholder')} value={this.props.agent_name} ref="agent_name" name="agent_name"  onChange={this.handleChange} className= { (this.props.agent_name.length < 4) ? 'has-error' : '' } />
                                </div>
                            </div>
                            <a href="#" onClick={this.onCancelAction} className="bot-cancel"> <i className="fa fa-times"></i> </a>
                        </div>
                        <div className="bot-body" id="filsss">
                            <ul>
                                <li> <span className='badge badge-warning'>-</span> <div className="bot-info"> {i18n.t('pages.bots.newBot.info.orders')} </div> </li>
                                <li> <span className='badge badge-warning'>-</span> <div className="bot-info"> {i18n.t('pages.bots.newBot.info.users')} </div> </li>
                                <li> <span className='badge badge-warning'>-</span> <div className="bot-info"> {i18n.t('pages.bots.newBot.info.entities')} </div></li>
                            </ul>
                        </div>
                        <div className="bot-footer">

                            { button }

                        </div>
                    </div>
                </form>
            </div>
        );
    }


}

export default NewBot;