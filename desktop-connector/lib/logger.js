class Logger {
    constructor(level) {
        this.level = level || 'info';
        this.levels = { error: 0, warn: 1, info: 2, debug: 3 };
    }

    log(level, message) {
        if (this.levels[level] > this.levels[this.level]) return;
        const ts = new Date().toISOString();
        const prefix = level.toUpperCase();
        console.log(`[${ts}] [${prefix}] ${message}`);
    }

    error(message) { this.log('error', message); }
    warn(message) { this.log('warn', message); }
    info(message) { this.log('info', message); }
    debug(message) { this.log('debug', message); }
}

module.exports = Logger;