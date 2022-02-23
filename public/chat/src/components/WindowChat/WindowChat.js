import React from "react";
import './WindowChat.less';

class WindowChat extends React.Component {
    static createIdByKey(key) {
        return `WindowChat${key}`;
    }

    onClose = (e) =>this.props.onClose ? this.props.onClose(e, this.props): null;

    render() {
        return (
            <div id={WindowChat.createIdByKey(this.props.user.key)} className={'WindowChat'}>
                <div className={'WindowChat__header'}>
                    <div className={'WindowChat__header__username'}>{this.props.user.username}</div>
                    <div onClick={this.onClose } className={'WindowChat__header__close'}></div>
                </div>
            </div>
        )
    }
}

export default WindowChat;