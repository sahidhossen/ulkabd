/**
 * Created by Ulka on 4/18/18.
 */

import React, { Component } from 'react';

class ConfirmationAlert extends Component {

    constructor(props) {
        super(props);

        this.message = '';

        this.state = {
            inputError: false
        }
    }

    confirmationAlert() {
        const inputValidation = (confirm) => {
            this.setState({inputError: (this.message.length <= 0)});

            console.debug("inputError:" + this.state.inputError);

            if (!this.state.inputError && confirm)
                this.props.saveAction(this.message);
        };

        const handleMessageChange = (event) => {
            this.message = event.target.value.trim();
            inputValidation(false);
        };

        let alertHeader = (this.props.titleHeader != null) ? <p className="title title-thin">{ this.props.titleHeader }</p> : null;
        let subHeader = (this.props.subTitleHeader != null) ? <p className="subHeader">{ this.props.subTitleHeader }</p> : null;
        let textInput = (this.props.inputMsgPlaceHolder != null) ?
            <textarea className={ this.state.inputError == true ? "invalidInput" : 'validInput' } rows="2" placeholder={ this.props.inputMsgPlaceHolder } onChange={ handleMessageChange } autoFocus></textarea>
            :
            null;
        let cancelTitle = (this.props.cancelButtonTitle != null) ? this.props.cancelButtonTitle : "Cancel";
        let saveTitle = (this.props.saveButtonTitle != null) ? this.props.saveButtonTitle : "Save";

        return (
            <div className="category-form-shadow">
                <div id="banner-message">
                    { alertHeader }
                    { subHeader }
                    { textInput }

                    <div className="buttonContainer">
                        <button className="btn btn-theme btn-admin-red pull-left" onClick={ this.props.cancelAction }>{ cancelTitle }</button>
                        <button className="btn btn-theme btn-admin-success pull-right" onClick={() => inputValidation(true)}>{ saveTitle }</button>
                    </div>
                </div>
            </div>
        )
    }

    render() {
        return (
            this.confirmationAlert()
        )
    }
}

export default ConfirmationAlert;
