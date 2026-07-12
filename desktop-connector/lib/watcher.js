const fs = require('fs');
const path = require('path');

class Watcher {
    constructor(dirs, onChange, logger) {
        this.dirs = dirs || [];
        this.onChange = onChange;
        this.logger = logger;
        this.watchers = [];
        this.supportedFormats = ['.mp3', '.aac', '.ogg', '.flac', '.wav', '.m4a', '.wma', '.opus'];
        this.debounceTimers = {};
    }

    start() {
        this.logger.info('File watcher starting...');

        // Scan existing files first
        this.dirs.forEach((dir) => {
            if (fs.existsSync(dir)) {
                this.scanExisting(dir);
            } else {
                this.logger.warn(`Watch directory does not exist: ${dir}`);
            }
        });

        // Use fs.watch for real-time monitoring
        this.dirs.forEach((dir) => {
            if (!fs.existsSync(dir)) return;

            try {
                const watcher = fs.watch(dir, { recursive: true }, (eventType, filename) => {
                    if (!filename) return;

                    const ext = path.extname(filename).toLowerCase();
                    if (!this.supportedFormats.includes(ext)) return;
                    if (eventType !== 'change' && eventType !== 'rename') return;

                    const fullPath = path.join(dir, filename);
                    this.debounce(fullPath, () => {
                        if (fs.existsSync(fullPath)) {
                            this.onChange(fullPath);
                        }
                    });
                });

                this.watchers.push(watcher);
                this.logger.info(`Watching: ${dir}`);
            } catch (err) {
                this.logger.error(`Cannot watch ${dir}: ${err.message}`);
            }
        });
    }

    scanExisting(dir) {
        try {
            const entries = fs.readdirSync(dir, { withFileTypes: true });
            entries.forEach((entry) => {
                const fullPath = path.join(dir, entry.name);
                if (entry.isDirectory()) {
                    this.scanExisting(fullPath);
                } else {
                    const ext = path.extname(entry.name).toLowerCase();
                    if (this.supportedFormats.includes(ext)) {
                        this.onChange(fullPath);
                    }
                }
            });
        } catch (e) {
            this.logger.warn(`Scan error in ${dir}: ${e.message}`);
        }
    }

    debounce(key, fn, delay) {
        if (this.debounceTimers[key]) {
            clearTimeout(this.debounceTimers[key]);
        }
        this.debounceTimers[key] = setTimeout(() => {
            delete this.debounceTimers[key];
            fn();
        }, delay || 1000);
    }

    stop() {
        this.watchers.forEach((w) => w.close());
        this.watchers = [];
        Object.keys(this.debounceTimers).forEach((k) => {
            clearTimeout(this.debounceTimers[k]);
        });
        this.debounceTimers = {};
        this.logger.info('File watcher stopped');
    }
}

module.exports = Watcher;