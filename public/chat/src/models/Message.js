export default class Message {
    message = '';
    isAccept = false;
    uniq = null;
    timestamp = null;
    username = '';

    constructor(message) {
        Object.assign(this, message);
    }

    getMessage() {
        return this.message;
    }

    getDate() {
        if (this.timestamp) {
            let date = new Date(this.timestamp * 1000);
            return  date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
        return '';
    }
}