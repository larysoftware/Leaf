export default class Message {
    message = '';
    constructor(message) {
        Object.assign(this, message);
    }

    getMessage() {
        return this.message;
    }
}