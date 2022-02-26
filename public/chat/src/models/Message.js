export default class Message {
    #message;
    #isAccept;
    #uniq;
    #timestamp;
    #username;

    constructor(message) {
        this.#message = message.message || '';
        this.#isAccept = message.isAccept || false;
        this.#uniq = message.uniq || null;
        this.#timestamp = message.timestamp || null;
        this.#username = message.username || '';
    }

    set message(message) {
        this.#message = message;
    }

    get message() {
        return this.#message;
    }

    set isAccept(isAccept) {
        this.#isAccept = isAccept;
    }

    get isAccept() {
        return this.#isAccept;
    }

    set uniq(uniq) {
        this.#uniq = uniq;
    }

    get uniq() {
        return this.#uniq;
    }

    set username(username) {
        this.#username = username;
    }

    get username() {
        return this.#username;
    }

    set timestamp(timestamp) {
        this.#timestamp = timestamp;
    }

    get date() {
        if (this.#timestamp) {
            let date = new Date(this.#timestamp * 1000);
            return  date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
        }
        return '';
    }
}