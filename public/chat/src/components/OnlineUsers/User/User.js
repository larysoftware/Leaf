import './User.less';
import {UserStruct} from "./UserStruct";
import React from "react";

class User extends React.Component {
    onClick = (e) => this.props.onClick ? this.props.onClick(e, this.props.user) : null;
    getUser = (user) => Object.assign(UserStruct, user)

    render() {
        return (
            <li className={'User'} onClick={this.onClick}>
                <div className={'User_container'}>
                    <img className={'User_avatar'} src={this.getUser(this.props.user).avatar}/>
                    <div className={'User_right'}>
                        <p className={'User__username'}>{this.getUser(this.props.user).username}</p>
                        <p className={'User__lastMessage'}>{this.getUser(this.props.user).lastMessage}</p>
                    </div>
                </div>
            </li>
        );
    }
}

export default User;