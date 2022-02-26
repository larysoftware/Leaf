import React from "react";
import './WindowChat.less';
import classNames from "classnames";

class WindowChat extends React.Component {

    isDisabled() {
        return this.props.disabled === true;
    }

    createIdByKey(key) {
        return `${key}${this.props.user.key}`;
    }

    onClose = (e) => this.props.onClose ? this.props.onClose(e, this.props) : null;

    onKeyPress = (e) => {
        if (this.props.onSendMessage && e.code === 'Enter') {
            this.props.onSendMessage(document.getElementById(this.createIdByKey('WindowChat__body_tex')), this.props.user);
        }
    }

    onClickBtn = () => {
        this.props.onSendMessage(document.getElementById(this.createIdByKey('WindowChat__body_tex')), this.props.user);
    }

    render() {
        return (
            <div id={this.createIdByKey('WindowChat')}
                 className={classNames({'WindowChat': true}, {'WindowChat_disabled': this.isDisabled()})}>
                <div className={'WindowChat__header'}>
                    <div className={'WindowChat__header__username'}>{this.props.user.username}</div>
                    <div onClick={this.onClose} className={'WindowChat__header__close'}></div>
                </div>
                <div className={'WindowChat__messages'}>
                    {this.props.user.messages.map(mess => <div
                        key={mess.uniq}
                        className={classNames(
                            {'WindowChat__messages_message': true},
                            {'WindowChat__messages_message_disabled': !mess.isAccept},
                        )}>
                        <div className={'WindowChat__messages_message_header'}>
                            <div className={'WindowChat__messages_message_header_username'}>{mess.username}</div>
                            <div className={'WindowChat__messages_message_header_date'}>{mess.date}</div>
                        </div>
                        <div className={'WindowChat__messages_message_body'}>
                            {mess.message}
                        </div>
                    </div>)}
                </div>
                <div className={'WindowChat__body'}>
                    <textarea disabled={this.isDisabled()} placeholder={"Text here..."} id={this.createIdByKey('WindowChat__body_tex')}
                              onKeyUp={this.onKeyPress}>
                    </textarea>
                </div>
                <div className={'WindowChat__body_footer'}>
                    <button disabled={this.isDisabled()} onClick={this.onClickBtn}>Wyślij</button>
                </div>
            </div>
        )
    }
}

export default WindowChat;