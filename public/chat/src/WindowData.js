export default class WindowData {
    user = null;
    messages = [];
    constructor(user) {
        this.user = user;
    }
    setMessage(message) {
        this.messages.push(message)
        return this;
    }
}