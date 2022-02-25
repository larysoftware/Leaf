import React from "react";
import './WindowChat.less';

class WindowChat extends React.Component {
    static createIdByKey(key) {
        return `WindowChat${key}`;
    }

    onClose = (e) => this.props.onClose ? this.props.onClose(e, this.props) : null;
    onKeyPress = (e) => this.props.onKeyPress ? this.props.onKeyPress(e, this.props.user) : null;

    render() {
        return (
            <div id={WindowChat.createIdByKey(this.props.user.key)} className={'WindowChat'}>
                <div className={'WindowChat__header'}>
                    <div className={'WindowChat__header__username'}>{this.props.user.username}</div>
                    <div onClick={this.onClose} className={'WindowChat__header__close'}></div>
                </div>
                <div className={'WindowChat__messages'}>
                    {this.props.user.messages.map(mess => <div
                        className={'WindowChat__messages_message'}>{mess.message}</div>)}
                </div>
                <div className={'WindowChat__body'}>
                    <textarea onKeyUp={this.onKeyPress}>
                    </textarea>
                </div>
            </div>
        )
    }
}

export default WindowChat;