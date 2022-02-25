export default class Message {
    message = '';
    isAccept = false;
    uniq = null;
    constructor(message) {
        Object.assign(this, message);
    }

    getMessage() {
        return this.message;
    }
}