const fs = require('fs');
const path = require('path');

class MetadataReader {
    constructor(filePath, logger) {
        this.filePath = filePath;
        this.logger = logger;
    }

    async read() {
        try {
            const ext = path.extname(this.filePath).toLowerCase();
            const stats = fs.statSync(this.filePath);

            const basic = {
                filePath: this.filePath,
                fileName: path.basename(this.filePath),
                size: stats.size,
                extension: ext,
                modifiedAt: stats.mtime.toISOString(),
                title: path.basename(this.filePath, ext),
                artist: 'Unknown Artist',
                album: 'Unknown Album',
                genre: '',
                year: null,
                track: null,
                duration: 0,
                bitrate: null,
                sampleRate: null,
                albumArt: null,
            };

            if (ext === '.mp3') {
                return await this.readMp3(basic);
            } else if (ext === '.flac') {
                return await this.readFlac(basic);
            } else if (['.wav', '.aac', '.ogg', '.m4a', '.wma', '.opus'].includes(ext)) {
                return await this.readGeneric(basic);
            }

            return basic;
        } catch (err) {
            this.logger.error(`Metadata read error for ${this.filePath}: ${err.message}`);
            return null;
        }
    }

    async readMp3(basic) {
        try {
            // Estimate duration from file size for MP3 at 128kbps
            const estimatedBitrate = 128;
            basic.bitrate = estimatedBitrate;
            basic.duration = Math.floor(basic.size / (estimatedBitrate * 1000 / 8) / 60);
            basic.sampleRate = 44100;
            basic.format = 'MP3';
            return basic;
        } catch (e) {
            return basic;
        }
    }

    async readFlac(basic) {
        try {
            const estimatedBitrate = 800;
            basic.bitrate = estimatedBitrate;
            basic.duration = Math.floor(basic.size / (estimatedBitrate * 1000 / 8) / 60);
            basic.sampleRate = 44100;
            basic.format = 'FLAC';
            return basic;
        } catch (e) {
            return basic;
        }
    }

    async readGeneric(basic) {
        try {
            const formatMap = {
                '.wav': { bitrate: 1411, format: 'WAV', sampleRate: 44100 },
                '.aac': { bitrate: 128, format: 'AAC', sampleRate: 44100 },
                '.ogg': { bitrate: 160, format: 'OGG', sampleRate: 44100 },
                '.m4a': { bitrate: 128, format: 'AAC', sampleRate: 44100 },
                '.wma': { bitrate: 128, format: 'WMA', sampleRate: 44100 },
                '.opus': { bitrate: 96, format: 'Opus', sampleRate: 48000 },
            };

            const info = formatMap[basic.extension] || { bitrate: 128, format: 'Audio', sampleRate: 44100 };
            basic.bitrate = info.bitrate;
            basic.format = info.format;
            basic.sampleRate = info.sampleRate;
            basic.duration = Math.floor(basic.size / (info.bitrate * 1000 / 8) / 60);
            return basic;
        } catch (e) {
            return basic;
        }
    }
}

module.exports = MetadataReader;