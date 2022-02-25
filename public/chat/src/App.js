import './colors.less'
import './App.css';
import OnlineUsers from "./components/OnlineUsers/OnlineUsers";
import WindowChat from "./components/WindowChat/WindowChat";
import Window from "./models/Window";
import Message from "./models/Message";
import User from "./models/User";
import Ws from "./services/Ws";
import {v4 as uuid} from 'uuid';
import classNames from "classnames";
import React from "react";

class App extends React.Component {

    state = {
        userList: [],
        windows: [],
        status: 0,
        username: null,
    };

    ws = null

    updateUsers = (users) => {
        this.setState({
            ...this.state,
            userList: users
        })
    }
    updateStatus = () => {
        this.setState({
            ...this.state,
            status: this.ws.status() == 1 ? 1 : 0,
        })
    }
    onOpen = (e) => this.updateStatus();

    onMessage = (e, data) => {
        this.updateStatus();
        if (data.type == 'text') {
            let user = this.state.userList.find((u) => u.key == data.from);
            if (user) {
                user.addMessage(new Message({
                    message: data.value,
                    uniq: data.uniq,
                    isAccept: true,
                }));
                this.updateUsers(this.state.userList);
            }
        } else if (data.type == 'confirm_text') {
            let user = this.state.userList.find((u) => u.key == data.from);
            if (!user) {
                return;
            }
            let message = user.messages.find((m) => m.uniq == data.value);
            message.isAccept = true;
            this.updateUsers(this.state.userList);
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

    onSendMessage = (e, user) => {
        if (e.code != 'Enter') {
            return;
        }
        let uniq = uuid();
        this.ws.send(JSON.stringify({'uniq': uniq, 'value': e.target.value, type: 'text', to: [user.key]}));
        user.addMessage(new Message({message: e.target.value, uniq: uniq, isAccept: false}));
        this.updateUsers(this.state.userList);
        e.target.value = '';
    }

    onLogin = (e) => {
        let login = document.getElementById('inputLogin').value;
        if (login.length) {
            this.setState({
                ...this.state,
                username: login,
            });
            if (!this.ws) {
                this.ws = new Ws('ws://0.0.0.0:12345', login);
                this.ws
                    .open(this.onOpen)
                    .onMessage(this.onMessage)
                    .onClose((e) => this.updateStatus())
            }
        }
    }

    render() {
        return (
            <div className="App">
                <div className={classNames({'LoginContainer': true}, {'Disabled': this.state.status != 0})}>
                    <input id={'inputLogin'} type={'text'}/>
                    <button onClick={this.onLogin}>Zaloguj</button>
                </div>
                <div className={classNames({'ChatContainer': true}, {'Disabled': this.state.status === 0})}>
                    <div className={'WindowsChat'}>
                        {this.state.windows.map(w => <WindowChat onKeyPress={this.onSendMessage}
                                                                 onClose={this.closeChatWindow} key={w.user.key}
                                                                 user={w.user}></WindowChat>)}
                    </div>
                    <OnlineUsers onClickUser={this.createWindowMessage} users={this.state.userList}/>
                </div>
            </div>
        );
    }
}

export default App;
