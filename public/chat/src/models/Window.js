export default class Window {
    user = null;
    messages = [];
    disabled = false;

    constructor(user) {
        this.user = user;
    }

    setMessage(message) {
        this.messages.push(message)
        return this;
    }

    getAllMessages() {
        return this.messages;
    }
}