export default class Ws {
    socket;
    username;

    constructor(host, username) {
        this.socket = new WebSocket(host);
        this.username = username;
    }

     open(fn) {
        this.socket.onopen = (e) => {
            if(this.socket.readyState == 1) {
                this.socket.send(JSON.stringify({
                    'username': this.username
                }));
                fn(e);
            }
        }
        return this;
    }

     onMessage(fn) {
        this.socket.onmessage = (e) => {
            if(this.socket.readyState) {
                fn(e, JSON.parse(e.data));
            }
        }
        return this;
    }

     onClose(fn) {
        this.socket.onclose = (e) => {
            fn(e);
        };
        return this;
    }

     onError(fn) {
        this.socket.on('error', fn);
        return this;
    }

    send(message) {
        this.socket.send(message);
    }

    status() {
        return this.socket.readyState;
    }

    close()  {
        return this.socket.close();
    }
}