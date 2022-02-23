import './OnlineUsers.less';
import User from "./User/User";
import React from "react";

class OnlineUsers extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            users: this.props.users
        };
    }

    onClickUser = (e, user) =>  this.props.onClickUser ? this.props.onClickUser(e, user) : null;

    render() {
        return (
            <div className={'OnlineUsers'}>
                <ul>
                    {this.state.users.map(user => <User onClick={this.onClickUser} key={user.key} user={user}></User>)}
                </ul>
            </div>
        )
    }
}

export default OnlineUsers;