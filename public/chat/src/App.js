import './colors.less'
import './App.css';
import OnlineUsers from "./components/OnlineUsers/OnlineUsers";
import WindowChat from "./components/WindowChat/WindowChat";
import Window from "./models/Window";
import Message from "./models/Message";
import User from "./models/User";
import Ws from "./services/Ws";
import React from "react";

class App extends React.Component {

    state = {
        userList: [],
        windows: [],
        status: 0,
    };

    ws = null

    constructor(pros) {
        super(pros);
        this.ws = new Ws('ws://0.0.0.0:12345', 'testowy');
    }

    updateUsers = (users) => {
        this.setState({
            ...this.state,
            userList: users
        })
    }
    updateStatus = (status) => {
        this.setState({
            ...this.state,
            status: this.ws.readyState == 1 ? 1 : 0,
        })
    }
    onOpen = (e) => this.updateStatus(this.ws.readyState);

    onMessage = (e, data) => {
        this.updateStatus(this.ws.readyState);
        if (data.type == 'text') {
            let user = this.state.userList.find((u) =>  u.key == data.from);
            if (user) {
                user.addMessage(new Message({
                    message: data.value,
                }));
                this.updateUsers(this.state.userList);
                console.log(this.state.userList);
            }
        } else if (data.type == 'available_list') {
            var users = [];
            data.value.forEach(function (user) {
                users.push(new User(user));
            });
            this.updateUsers(users);

        } else if (data.type == 'available') {
            if (data.value) {
                users = this.state.userList;
                users = [
                    ...users,
                    new User({key: data.from, username: data.username})
                ];
                this.updateUsers(users);
            } else {
                users = this.state.userList.filter((u) => {
                    return u.key != data.from;
                });
                this.updateUsers(users);
            }
        }

    }

    componentDidMount() {
        this.ws
            .open(this.onOpen)
            .onMessage(this.onMessage)
            .onClose((e) => this.updateStatus(this.ws.readyState));
    }

    createWindowMessage = (e, user) => {
        let window = new Window(user);
        if (this.state.windows.find(w => w.user.key == window.user.key)) {
            return;
        }
        let windows = [
            ...this.state.windows,
            window
        ];
        this.setState(
            {
                ...this.state,
                windows: windows
            }
        )
    }

    closeChatWindow = (e, props) => {
        let windows = this.state.windows.filter(window => window.user.key !== props.user.key);
        this.setState({
            ...this.state,
            windows: windows
        })
    }

    render() {
        return (
            <div className="App">
                <div className={'WindowsChat'}>
                    {this.state.windows.map(w => <WindowChat onClose={this.closeChatWindow} key={w.user.key}
                                                             user={w.user}></WindowChat>)}
                </div>
                <OnlineUsers onClickUser={this.createWindowMessage} users={this.state.userList}/>
            </div>
        );
    }
}

export default App;
