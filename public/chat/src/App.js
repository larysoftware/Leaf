import './colors.less'
import './App.css';
import OnlineUsers from "./components/OnlineUsers/OnlineUsers";
import WindowChat from "./components/WindowChat/WindowChat";
import LoginForm from "./components/LoginForm/LoginForm";
import Window from "./models/Window";
import Message from "./models/Message";
import User from "./models/User";
import Ws from "./services/Ws";
import {v4 as uuid} from 'uuid';
import classNames from "classnames";
import React from "react";
import {error} from "bfj/src/events";

class App extends React.Component {

    state = {
        userList: [],
        windows: [],
        status: 0,
    };

    ws = null;

    updateUsers = (users) => {
        this.setState({
            ...this.state,
            userList: users
        })
    }
    updateWindows = (windows) => {
        this.setState({
            ...this.state,
            windows: windows
        })
    }

    updateStatus = () => {
        this.setState({
            ...this.state,
            status: this.ws.status() == 1 ? 1 : 0,
        })
    }


    findUserByKey = (key) => this.state.userList.find((u) => u.key == key);

    findWindowByKey = (key) => this.state.windows.find(w => w.user.key == key)

    onOpen = (e) => this.updateStatus();

    onMessage = (e, data) => {
        this.updateStatus();
        if (data.type == 'text') {
            let user = this.findUserByKey(data.from);
            if (!user) {
                return;
            }
            user.addMessage(new Message({
                message: data.value,
                uniq: data.uniq,
                isAccept: true,
                timestamp: data.timestamp,
                username: data.username,
            }));
            this.updateUsers(this.state.userList);

        } else if (data.type == 'confirm_text') {
            let user = this.findUserByKey(data.from);
            if (!user) {
                return;
            }
            let message = user.messages.find((m) => m.uniq == data.value);
            message.isAccept = true;
            message.timestamp = data.timestamp;
            message.username = this.ws.username;
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
        if (this.findWindowByKey(window.user.key)) {
            return;
        }
        this.updateWindows([
            ...this.state.windows,
            window
        ])
    }

    closeChatWindow = (e, props) => this.updateWindows(this.state.windows.filter(window => window.user.key !== props.user.key))

    onError = (e) => alert('Przepraszamy ale wystąpił problem z połączeniem do serwera');

    onSendMessage = (target, user) => {
        let uniq = uuid();
        this.ws.send(JSON.stringify({'uniq': uniq, 'value': target.value, type: 'text', to: [user.key]}));
        user.addMessage(new Message({message: target.value, uniq: uniq, isAccept: false, username: this.ws.username}));
        this.updateUsers(this.state.userList);
        target.value = '';
    }

    onLogin = (e) => {
        let username = document.getElementById('inputLogin').value;
        if (!username.length) {
            return;
        }
        if (this.ws) {
            this.ws.close();
        }
        try {
            this.ws = new Ws(window.WS_SERVER, username);
            this.ws
                .open(this.onOpen)
                .onMessage(this.onMessage)
                .onError(this.onError)
                .onClose((e) => this.updateStatus())
        } catch (e) {
        }
    }

    render() {
        return (
            <div className="App">
                <LoginForm onLogin={this.onLogin} status={this.state.status}></LoginForm>
                <div className={classNames({'ChatContainer': true}, {'Disabled': this.state.status === 0})}>
                    <div className={'WindowsChat'}>
                        {this.state.windows.map(w => <WindowChat onSendMessage={this.onSendMessage}
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
