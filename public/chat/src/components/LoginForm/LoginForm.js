import React from "react";
import  './LoginForm.less';
import classNames from "classnames";

export default class LoginForm extends React.Component {

    onLogin = (e) => this.props.onLogin ? this.props.onLogin(e) : null;

    render() {
        return (
            <div className={classNames({'LoginContainer': true}, {'Disabled': this.props.status != 0})}>
                <input id={'inputLogin'} type={'text'}/>
                <button onClick={this.onLogin}>Zaloguj</button>
            </div>
        );
    }
}