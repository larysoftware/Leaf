import './colors.less'
import './App.css';
import OnlineUsers from "./components/OnlineUsers/OnlineUsers";
import WindowChat from "./components/WindowChat/WindowChat";
import WindowData from "./WindowData";
import React from "react";
class App extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            userList: [
                {username: 'Ala', key: '23232131'},
                {username: 'Łukasz', key: '23232132'},
                {username: 'Bartek', key: '23232133'},
                {username: 'Eugeniusz', key: '23232134'},
            ],
            windows: []
        }
    }

    createWindowMessage = (e, user) => {
        let window = new WindowData(user);
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
                {this.state.windows.map(w => <WindowChat onClose={this.closeChatWindow} key={w.user.key} user={w.user}></WindowChat>)}
                </div>
                <OnlineUsers onClickUser={this.createWindowMessage} users={this.state.userList}/>
            </div>
        );
    }
}

export default App;
