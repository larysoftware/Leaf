export default class User {
    key = '';
    username = '';
    avatar = 'https://www.w3schools.com/howto/img_avatar.png';
    messages = [];

    constructor(user) {
        Object.assign(this, user);
    }

    addMessage(message) {
        this.messages.push(message);
    }

    getMessages() {
        return this.messages;
    }

    getLastMessage() {
        let message = this.messages.slice(-1);
        return message[0] ? message[0].message : '';
    }
}