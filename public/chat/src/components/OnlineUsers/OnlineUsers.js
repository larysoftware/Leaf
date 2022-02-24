import './OnlineUsers.less';
import User from "./User/User";
import React from "react";

class OnlineUsers extends React.Component {
    onClickUser = (e, user) =>  this.props.onClickUser ? this.props.onClickUser(e, user) : null;
    render() {
        return (
            <div className={'OnlineUsers'}>
                <ul>
                    {this.props.users.map(user => <User onClick={this.onClickUser} key={user.key} user={user}></User>)}
                </ul>
            </div>
        )
    }
}

export default OnlineUsers;