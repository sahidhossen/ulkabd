import React, { Component } from 'react';

export default class TextPopup extends Component {

    constructor(props){
        super(props);
        this.state= {
            warning: null
        }
        this.onCloseAction = this.onCloseAction.bind(this);

    }

    onCloseAction(event){
        event.preventDefault();
        this.props.onCloseAction(event);
    }

    textBroadcastChange(event){
        this.props.textBroadcastChange(event);
    }

    onSaveTextBroadCast(event){
        event.preventDefault();
        let { warning } = this.state;
        warning = null;
        if( this.props.title.length <= 0 )
            warning = 1;
        if( this.props.message.length <= 0 )
            warning = 2;
        if( warning === null ){
            this.props.saveTextBroadCast();
        }
        this.setState({ warning })
    }

    render(){
        let { className } = this.props;
        return (
            <div className={"u-popup-shadow "+className}>
                <div className="main-modal">
                    <div className="modal-title">
                        <h3 className="title"> Broadcast Schedule </h3>
                    </div>
                    <div className="modal-body">
                        <div className="text-popup-body">
                            <form action="#" className="form-horizontal">
                                <div className={(this.props.title.length<=0 && this.state.warning === 1) ? "form-group has-error" : "form-group" }>
                                    <input name="title" onChange={this.textBroadcastChange.bind(this)} type="text" placeholder="Broadcast Name" value={this.props.title} className="form-control"/>
                                </div>
                                <div className={(this.props.message.length<=0 && this.state.warning === 2) ? "form-group has-error" : "form-group"}>
                                    <textarea onChange={this.textBroadcastChange.bind(this)} name="message" className="form-control" placeholder="Broadcast message">{this.props.message}</textarea>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div className="modal-footer">
                        <p>
                            <a onClick={this.onSaveTextBroadCast.bind(this)} href="#" className="btn btn-theme btn-theme-success pull-left"> Save </a>
                            <a onClick={this.onCloseAction} href="#" className="btn btn-admin btn-admin-danger pull-right"> Cancel </a>
                        </p>
                    </div>
                </div>
            </div>
        )
    }
}