export default class User {
    static #defaultAvatar = 'https://www.w3schools.com/howto/img_avatar.png';
    #key;
    #username;
    #avatar;
    #messages;

    constructor(user) {
        this.#key = user.key || '';
        this.#username = user.username || '';
        this.#avatar = user.avatar || User.#defaultAvatar;
        this.#messages = user.messages || [];
    }

    set key(key) {
        this.#key = key;
    }

    get key() {
        return this.#key;
    }

    set username(username) {
        this.#username = username;
    }

    get username() {
        return this.#username;
    }

    set avatar(avatar) {
        this.#avatar = avatar;
    }

    get avatar() {
        return this.#avatar;
    }

    get messages() {
        return this.#messages;
    }

    set messages(messages) {
        this.#messages = messages;
    }

    get lastMessage () {
        let message = this.messages.slice(-1);
        return message[0] ? message[0].message : '';
    }

    addMessage(message) {
        this.messages.push(message);
    }

}