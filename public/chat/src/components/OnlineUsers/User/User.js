import './User.css';
import React from "react";

class User extends React.Component {
    onClick = (e) => this.props.onClick ? this.props.onClick(e, this.props.user) : null;
    getUser = (user) => Object.assign({key: '', username: '', description: 'default description'}, user);

    render() {
        return (
            <li className={'User'} onClick={this.onDoubleClick}>
                <p className={'User__username'}>{this.getUser(this.props.user).username}</p>
                <p className={'User__description'}>{this.getUser(this.props.user).description}</p>
            </li>
        );
    }
}

export default User;