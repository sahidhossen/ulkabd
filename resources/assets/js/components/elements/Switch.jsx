import React from 'react';

export default class Switch extends React.Component {

    constructor ( props ) {
        super( props );

        this.state = {
            isChecked: false
        }
    }
    _handleChange (event) {

        this.props.onChange(event)
    }

    render () {
        return(
            <div className="switch-container">
                <label>
                    <input ref="switch"  checked={ this.props.isChecked } onChange={ this._handleChange.bind(this) } className="switch" type="checkbox" />
                    <div>
                        <div></div>
                    </div>
                </label>
            </div>
        );
    }



}
