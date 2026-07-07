const WebSocket = require('ws');

class WebSocketClient {
    constructor(apiUrl, stationId, token, logger) {
        this.wsUrl = apiUrl.replace(/^http/, 'ws') + `/ws/studio/${stationId}`;
        this.token = token;
        this.logger = logger;
        this.ws = null;
        this.listeners = {};
        this.reconnectAttempts = 0;
        this.maxReconnects = 10;
        this.reconnectDelay = 3000;
    }

    connect() {
        try {
            this.ws = new WebSocket(this.wsUrl, {
                headers: {
                    Authorization: `Bearer ${this.token}`,
                },
            });

            this.ws.on('open', () => {
                this.logger.info('WebSocket connected');
                this.reconnectAttempts = 0;
                this.send('connector_hello', {
                    version: require('../package.json').version,
                    platform: process.platform,
                    nodeVersion: process.version,
                });
            });

            this.ws.on('message', (data) => {
                try {
                    const msg = JSON.parse(data.toString());
                    this.handleMessage(msg);
                } catch (e) {
                    this.logger.warn(`Invalid WebSocket message: ${e.message}`);
                }
            });

            this.ws.on('close', () => {
                this.logger.warn('WebSocket disconnected');
                this.reconnect();
            });

            this.ws.on('error', (err) => {
                this.logger.error(`WebSocket error: ${err.message}`);
            });
        } catch (err) {
            this.logger.error(`WebSocket connection failed: ${err.message}`);
            this.reconnect();
        }
    }

    handleMessage(msg) {
        if (msg.event && this.listeners[msg.event]) {
            this.listeners[msg.event].forEach((fn) => fn(msg.data));
        }
        if (this.listeners['*']) {
            this.listeners['*'].forEach((fn) => fn(msg));
        }
    }

    send(event, data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify({ event, data }));
        }
    }

    on(event, callback) {
        if (!this.listeners[event]) this.listeners[event] = [];
        this.listeners[event].push(callback);
    }

    reconnect() {
        if (this.reconnectAttempts >= this.maxReconnects) {
            this.logger.error('Max reconnection attempts reached');
            return;
        }

        this.reconnectAttempts++;
        const delay = this.reconnectDelay * Math.min(this.reconnectAttempts, 5);
        this.logger.info(`Reconnecting in ${delay / 1000}s (attempt ${this.reconnectAttempts}/${this.maxReconnects})`);

        setTimeout(() => this.connect(), delay);
    }

    disconnect() {
        if (this.ws) {
            this.ws.close();
            this.ws = null;
        }
    }

    isConnected() {
        return this.ws && this.ws.readyState === WebSocket.OPEN;
    }
}

module.exports = WebSocketClient;