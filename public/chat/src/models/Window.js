export default class Window {
    #user = null;
    #disabled = false;

    constructor(user) {
        this.#user = user;
    }

    get user() {
        return this.#user;
    }

    get disabled() {
        return this.#disabled;
    }

    set disabled(value) {
        this.#disabled = value;
    }
}