import React from "react";
import './WindowChat.less';
import classNames from "classnames";

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
                        key={mess.uniq}
                        className={ classNames(
                            {'WindowChat__messages_message': true},
                            {'WindowChat__messages_message_disabled': !mess.isAccept},
                        )}>{mess.message}</div>)}
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