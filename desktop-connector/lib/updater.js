const fs = require('fs');
const path = require('path');
const fetch = require('node-fetch');
const { execSync } = require('child_process');

class Updater {
    constructor(updateUrl) {
        this.updateUrl = updateUrl || 'https://planet-hosts.com/api/connector/update';
        this.currentVersion = require('../package.json').version;
        this.tempDir = path.join(require('os').tmpdir(), 'ph-connector-update');
    }

    async check() {
        try {
            const res = await fetch(this.updateUrl, {
                headers: { 'User-Agent': `PH-Connector/${this.currentVersion}` },
                timeout: 10000,
            });

            if (!res.ok) return null;
            const data = await res.json();

            if (this.compareVersions(data.version, this.currentVersion) > 0) {
                return data;
            }
            return null;
        } catch (err) {
            console.warn('Update check failed:', err.message);
            return null;
        }
    }

    async run() {
        const update = await this.check();
        if (!update) {
            console.log(`Already up to date (v${this.currentVersion})`);
            return;
        }

        console.log(`Update available: v${this.currentVersion} -> v${update.version}`);

        if (!fs.existsSync(this.tempDir)) {
            fs.mkdirSync(this.tempDir, { recursive: true });
        }

        try {
            console.log('Downloading update...');
            const res = await fetch(update.downloadUrl);
            const buffer = await res.buffer();

            const packagePath = path.join(this.tempDir, 'update.tar.gz');
            fs.writeFileSync(packagePath, buffer);

            console.log('Installing update...');
            const connectorDir = path.dirname(__dirname);

            if (process.platform === 'win32') {
                execSync(`tar -xzf "${packagePath}" -C "${connectorDir}" --strip-components=1`, { stdio: 'inherit' });
            } else {
                execSync(`tar -xzf "${packagePath}" -C "${connectorDir}" --strip-components=1`, { stdio: 'inherit' });
            }

            // Run npm install for any new dependencies
            try {
                execSync('npm install --production', { cwd: connectorDir, stdio: 'inherit' });
            } catch (e) {
                console.warn('npm install warning:', e.message);
            }

            console.log(`Updated to v${update.version}. Restart the connector.`);
        } catch (err) {
            console.error('Update failed:', err.message);
        }
    }

    compareVersions(a, b) {
        const pa = a.split('.').map(Number);
        const pb = b.split('.').map(Number);
        for (let i = 0; i < Math.max(pa.length, pb.length); i++) {
            const na = pa[i] || 0;
            const nb = pb[i] || 0;
            if (na > nb) return 1;
            if (na < nb) return -1;
        }
        return 0;
    }
}

module.exports = Updater;