#!/usr/bin/env node

const Config = require('./lib/config');
const Watcher = require('./lib/watcher');
const Metadata = require('./lib/metadata');
const Uploader = require('./lib/uploader');
const Encoder = require('./lib/encoder');
const WebSocketClient = require('./lib/websocket');
const Updater = require('./lib/updater');
const Logger = require('./lib/logger');

class DesktopConnector {
    constructor() {
        this.config = new Config();
        this.logger = new Logger(this.config.get('logLevel', 'info'));
        this.watcher = null;
        this.uploader = null;
        this.encoder = null;
        this.ws = null;
        this.updater = new Updater(this.config.get('updateUrl'));
        this.microphoneStream = null;
        this.running = false;
    }

    async start() {
        this.logger.info('Planet Hosts Desktop Connector starting...');
        this.logger.info(`Version: ${require('./package.json').version}`);
        this.logger.info(`Platform: ${process.platform} ${process.arch}`);

        const apiKey = this.config.get('apiKey');
        const apiUrl = this.config.get('apiUrl');
        const stationId = this.config.get('stationId');

        if (!apiKey || !apiUrl || !stationId) {
            this.logger.error('Missing required config: apiKey, apiUrl, stationId');
            this.logger.info('Run: ph-connector configure');
            return;
        }

        // Authenticate with panel
        this.uploader = new Uploader(apiUrl, apiKey, this.logger);
        const auth = await this.uploader.authenticate();
        if (!auth.success) {
            this.logger.error('Authentication failed. Check your API key.');
            return;
        }
        this.logger.info('Authenticated successfully');
        this.sessionToken = auth.data.token;

        // Start folder watcher
        const watchDirs = this.config.get('watchDirs', []);
        if (watchDirs.length > 0) {
            this.watcher = new Watcher(watchDirs, this.onFileChange.bind(this), this.logger);
            this.watcher.start();
            this.logger.info(`Watching ${watchDirs.length} directories for new music`);
        } else {
            this.logger.warn('No watch directories configured. Set watchDirs in config.');
        }

        // Connect WebSocket
        this.ws = new WebSocketClient(apiUrl, stationId, this.sessionToken, this.logger);
        this.ws.connect();
        this.ws.on('connector_status', (data) => {
            this.logger.info(`Station status: ${data.uptime || 'unknown'}`);
        });

        // Check for updates
        this.updater.check().then((update) => {
            if (update) {
                this.logger.info(`Update available: ${update.version}. Run 'ph-connector update'`);
            }
        });

        this.running = true;
        this.logger.info('Desktop Connector is running');

        // Keep process alive
        process.on('SIGINT', () => this.shutdown());
        process.on('SIGTERM', () => this.shutdown());
    }

    async onFileChange(filePath) {
        this.logger.info(`New file detected: ${filePath}`);

        // Read metadata
        const metadata = new Metadata(filePath, this.logger);
        const tags = await metadata.read();

        if (!tags) {
            this.logger.warn(`Could not read metadata from: ${filePath}`);
            return;
        }

        this.logger.info(`  Title: ${tags.title}`);
        this.logger.info(`  Artist: ${tags.artist}`);
        this.logger.info(`  Album: ${tags.album}`);

        // Upload file with metadata
        const result = await this.uploader.upload(filePath, tags);
        if (result.success) {
            this.logger.info(`Uploaded successfully: ${tags.title}`);
            this.ws.send('upload_complete', { file: filePath, title: tags.title });
        } else {
            this.logger.error(`Upload failed: ${result.error}`);
        }
    }

    async streamMicrophone(device, format, bitrate) {
        this.logger.info(`Starting microphone stream from: ${device}`);

        const stationId = this.config.get('stationId');
        const streamUrl = this.config.get('streamUrl');

        this.encoder = new Encoder(this.logger);
        this.encoder.startMicrophone(device, {
            format: format || 'mp3',
            bitrate: bitrate || 128,
            outputUrl: streamUrl,
            onData: (chunk) => {
                if (this.ws && this.ws.isConnected()) {
                    this.ws.send('audio_chunk', { stationId, chunk });
                }
            }
        });
    }

    stopMicrophone() {
        if (this.encoder) {
            this.encoder.stop();
            this.encoder = null;
            this.logger.info('Microphone stream stopped');
        }
    }

    shutdown() {
        this.logger.info('Shutting down...');
        this.running = false;

        if (this.watcher) this.watcher.stop();
        if (this.ws) this.ws.disconnect();
        if (this.encoder) this.encoder.stop();

        this.logger.info('Goodbye');
        process.exit(0);
    }
}

// CLI entry
const connector = new DesktopConnector();
const args = process.argv.slice(2);

if (args.includes('configure')) {
    const Config = require('./lib/config');
    const cfg = new Config();
    cfg.interactiveSetup();
} else if (args.includes('service-install')) {
    require('./bin/install-service')();
} else if (args.includes('update')) {
    const Updater = require('./lib/updater');
    const updater = new Updater();
    updater.run().then(() => process.exit(0));
} else {
    connector.start().catch((err) => {
        console.error('Fatal error:', err);
        process.exit(1);
    });
}

module.exports = DesktopConnector;