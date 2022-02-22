import './OnlineUsers.css';
import User from "./User/User";
import React from "react";

class OnlineUsers extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            users: this.props.users
        };
    }

    render() {
        return (
            <div className={'OnlineUsers'}>
                <ul>
                    {this.state.users.map(user => <User key={user.key} user={user}></User>)}
                </ul>
            </div>
        )
    }
}

export default OnlineUsers;