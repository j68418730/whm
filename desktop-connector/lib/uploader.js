const fs = require('fs');
const path = require('path');
const fetch = require('node-fetch');
const FormData = require('form-data');

class Uploader {
    constructor(apiUrl, apiKey, logger) {
        this.apiUrl = apiUrl.replace(/\/+$/, '');
        this.apiKey = apiKey;
        this.logger = logger;
        this.token = null;
    }

    async authenticate() {
        try {
            const form = new FormData();
            form.append('api_key', this.apiKey);
            form.append('device_name', `Connector-${process.platform}`);

            const res = await fetch(`${this.apiUrl}/connector/auth`, {
                method: 'POST',
                body: form,
            });

            const data = await res.json();
            if (data.success) {
                this.token = data.data.token;
            }
            return data;
        } catch (err) {
            this.logger.error(`Auth error: ${err.message}`);
            return { success: false, error: err.message };
        }
    }

    async upload(filePath, tags) {
        try {
            const stationId = this.getStationId();
            if (!stationId) {
                return { success: false, error: 'Station ID not configured' };
            }

            const form = new FormData();
            form.append('file', fs.createReadStream(filePath), path.basename(filePath));
            form.append('title', tags.title || path.basename(filePath));
            form.append('artist', tags.artist || '');
            form.append('album', tags.album || '');
            form.append('genre', tags.genre || '');
            form.append('duration', String(tags.duration || 0));
            form.append('bitrate', String(tags.bitrate || 0));
            form.append('sample_rate', String(tags.sampleRate || 0));
            form.append('year', String(tags.year || ''));
            form.append('track', String(tags.track || ''));

            // Include album art if present
            if (tags.albumArt) {
                form.append('album_art', tags.albumArt, 'cover.jpg');
            }

            const res = await fetch(`${this.apiUrl}/connector/station/${stationId}/upload`, {
                method: 'POST',
                headers: {
                    'X-API-Key': this.apiKey,
                },
                body: form,
            });

            return await res.json();
        } catch (err) {
            this.logger.error(`Upload error for ${filePath}: ${err.message}`);
            return { success: false, error: err.message };
        }
    }

    async fetchLibrary(stationId) {
        try {
            const res = await fetch(`${this.apiUrl}/connector/station/${stationId}/library`, {
                headers: { 'X-API-Key': this.apiKey },
            });
            return await res.json();
        } catch (err) {
            this.logger.error(`Library fetch error: ${err.message}`);
            return { success: false, error: err.message };
        }
    }

    async fetchQueue(stationId) {
        try {
            const res = await fetch(`${this.apiUrl}/connector/station/${stationId}/queue`, {
                headers: { 'X-API-Key': this.apiKey },
            });
            return await res.json();
        } catch (err) {
            return { success: false, error: err.message };
        }
    }

    async fetchStatus(stationId) {
        try {
            const res = await fetch(`${this.apiUrl}/connector/station/${stationId}/status`, {
                headers: { 'X-API-Key': this.apiKey },
            });
            return await res.json();
        } catch (err) {
            return { success: false, error: err.message };
        }
    }

    getStationId() {
        try {
            const config = require('./config');
            const cfg = new config();
            return cfg.get('stationId');
        } catch (e) {
            return null;
        }
    }
}

module.exports = Uploader;