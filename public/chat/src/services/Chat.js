import Window from "../models/Window";
import Message from "../models/Message";
import {v4 as uuid} from 'uuid';
import Ws from "./Ws";

export default class Chat {
    #userList = [];
    #windows = [];
    #status = 0;
    #username;
    #ws;
    #connectionString;

    constructor(connectionString) {
        this.#connectionString = connectionString;
    }

    /**
     * run event if message is incoming
     * @param e
     * @param data
     */
    #onMessage = (e, data) => {
    };

    /**
     * on open
     * @param e
     */
    #onOpen = (e) => {
    };


    /**
     * on error
     * @param e
     */
    #onError = (e) => {
    };

    /**
     *
     * @param e
     */
    #onClose = (e) => {
    };

    /**
     * find user by key
     * @param key
     * @returns User
     */
    #findUserByKey = (key) => this.#userList.find((u) => u.key == key);

    /**
     * find window by key
     * @param key
     * @returns Window
     */
    #findWindowByKey = (key) => this.#windows.find(w => w.user.key == key)

    /**
     * get status struct
     * @returns {{userList: *[], windows: *[], status: number}}
     */
    get status() {
        return {
            userList: this.#userList,
            windows: this.#windows,
            status: this.#status,
        };
    }


    logIn(username) {
        if (username.length) {
            return;
        }
        this.#ws = new Ws(this.#connectionString, username);
        this.#ws
            .open(this.#onOpen)
            .onMessage(this.onMessage)
            .onError(this.#onError)
            .onClose(this.#onClose)
    }

    createWindow(user) {
        let window = new Window(user);
        if (this.#findWindowByKey(window.user.key)) {
            return;
        }
        /*add window */
        this.window = window;
    }

    closeChatWindow(user) {
        this.#windows = this.#windows.filter(window => window.user.key !== user.key);
    }

    sendMessage(value, user) {
        if (value.length === 0) {
            return;
        }
        let uniqKey = uuid();
        user.addMessage(new Message({message: value, uniq: uniqKey, isAccept: false, username: this.#ws.username}));
        this.#ws.send(JSON.stringify({'uniq': uniqKey, 'value': value, type: 'text', to: [user.key]}))
    }

    set username(username) {
        this.#username = username;
    }

    get username() {
        return this.#username;
    }

    set window(window) {
        this.#windows.push(window);
    }

    set onMessage(callable) {
        this.#onMessage = callable;
    }

    set onOpen(callable) {
        this.#onOpen = callable;
    }

    set onError(callable) {
        this.#onError = callable;
    }

    set onClose(callable) {
        this.#onMessage = callable;
    }
}