import './User.less';
import React from "react";

class User extends React.Component {
    onClick = (e) => this.props.onClick ? this.props.onClick(e, this.props.user) : null;

    render() {
        return (
            <li className={'User'} onClick={this.onClick}>
                <div className={'User_container'}>
                    <img className={'User_avatar'} src={this.props.user.avatar}/>

                    <div className={'User_right'}>
                        <p className={'User__username'}>{this.props.user.username}</p>
                        <p className={'User__lastMessage'}>{this.props.user.getLastMessage()}</p>
                    </div>
                </div>
            </li>
        );
    }
}

export default User;