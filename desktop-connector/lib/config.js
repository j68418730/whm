const fs = require('fs');
const path = require('path');
const readline = require('readline');

class Config {
    constructor() {
        this.configPath = this.getConfigPath();
        this.data = this.load();
    }

    getConfigPath() {
        const home = process.env.HOME || process.env.USERPROFILE || '.';
        const dir = path.join(home, '.planethosts');
        if (!fs.existsSync(dir)) {
            fs.mkdirSync(dir, { recursive: true });
        }
        return path.join(dir, 'connector-config.json');
    }

    load() {
        try {
            if (fs.existsSync(this.configPath)) {
                return JSON.parse(fs.readFileSync(this.configPath, 'utf8'));
            }
        } catch (e) {
            console.warn('Config load error:', e.message);
        }
        return this.defaults();
    }

    defaults() {
        return {
            apiUrl: 'https://planet-hosts.com',
            apiKey: '',
            stationId: null,
            watchDirs: [],
            logLevel: 'info',
            updateUrl: 'https://planet-hosts.com/api/connector/update',
            streamUrl: null,
            autoUpload: true,
            maxFileSize: 500 * 1024 * 1024,
            supportedFormats: ['.mp3', '.aac', '.ogg', '.flac', '.wav', '.m4a', '.wma', '.opus'],
        };
    }

    get(key, defaultValue) {
        return this.data[key] !== undefined ? this.data[key] : defaultValue;
    }

    set(key, value) {
        this.data[key] = value;
        this.save();
    }

    save() {
        try {
            fs.writeFileSync(this.configPath, JSON.stringify(this.data, null, 2));
        } catch (e) {
            console.error('Config save error:', e.message);
        }
    }

    interactiveSetup() {
        const rl = readline.createInterface({
            input: process.stdin,
            output: process.stdout,
        });

        console.log('\n=== Planet Hosts Desktop Connector Setup ===\n');

        const ask = (question, key, defaultVal) => {
            return new Promise((resolve) => {
                rl.question(`${question} [${defaultVal}]: `, (answer) => {
                    this.data[key] = answer.trim() || defaultVal;
                    resolve();
                });
            });
        };

        (async () => {
            await ask('Panel API URL', 'apiUrl', this.get('apiUrl'));
            await ask('API Key', 'apiKey', this.get('apiKey'));
            await ask('Station ID', 'stationId', this.get('stationId') || '');
            await ask('Watch directories (comma-separated)', 'watchDirsStr', '');
            await ask('Stream URL (for live broadcasting)', 'streamUrl', this.get('streamUrl') || '');

            const dirsStr = this.get('watchDirsStr', '');
            if (dirsStr) {
                this.data.watchDirs = dirsStr.split(',').map((d) => d.trim()).filter(Boolean);
            }
            delete this.data.watchDirsStr;

            this.save();
            console.log('\nConfiguration saved to:', this.configPath);
            console.log('Starting connector...\n');
        })();
    }
}

module.exports = Config;