const { spawn } = require('child_process');
const fs = require('fs');
const path = require('path');

class Encoder {
    constructor(logger) {
        this.logger = logger;
        this.process = null;
        this.ffmpegPath = this.detectFfmpeg();
    }

    detectFfmpeg() {
        const candidates = ['ffmpeg', 'ffmpeg.exe', '/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg'];
        for (const cmd of candidates) {
            try {
                const result = require('child_process').spawnSync(cmd, ['-version'], { timeout: 3000 });
                if (result.status === 0) return cmd;
            } catch (e) {}
        }
        return 'ffmpeg';
    }

    isAvailable() {
        try {
            const result = require('child_process').spawnSync(this.ffmpegPath, ['-version'], { timeout: 3000 });
            return result.status === 0;
        } catch (e) {
            return false;
        }
    }

    startMicrophone(device, opts) {
        if (!this.isAvailable()) {
            this.logger.error('FFmpeg not found. Install FFmpeg for microphone streaming.');
            return;
        }

        const format = opts.format || 'mp3';
        const bitrate = opts.bitrate || 128;
        const sampleRate = opts.sampleRate || 44100;
        const channels = opts.channels || 2;
        const outputUrl = opts.outputUrl;
        const onData = opts.onData;

        this.logger.info(`Starting microphone encoder: ${format} @ ${bitrate}kbps`);

        const platform = process.platform;
        let inputOpts = [];

        if (platform === 'win32') {
            inputOpts = ['-f', 'dshow', '-i', `audio=${device || 'Microphone'}`];
        } else if (platform === 'linux') {
            inputOpts = ['-f', 'alsa', '-i', device || 'default'];
        } else if (platform === 'darwin') {
            inputOpts = ['-f', 'avfoundation', '-i', `:${device || '0'}`];
        }

        const args = [
            '-re',
            ...inputOpts,
            '-acodec', format === 'mp3' ? 'libmp3lame' : format,
            '-b:a', `${bitrate}k`,
            '-ar', String(sampleRate),
            '-ac', String(channels),
            '-f', format,
        ];

        if (outputUrl) {
            args.push(outputUrl);
        } else {
            args.push('pipe:1');
        }

        this.process = spawn(this.ffmpegPath, args, { stdio: ['ignore', 'pipe', 'pipe'] });

        this.process.stdout.on('data', (chunk) => {
            if (onData) onData(chunk);
        });

        this.process.stderr.on('data', (data) => {
            this.logger.debug(`FFmpeg: ${data.toString().trim()}`);
        });

        this.process.on('close', (code) => {
            this.logger.info(`Encoder exited with code ${code}`);
            this.process = null;
        });
    }

    transcodeFile(inputPath, outputFormat, outputBitrate) {
        if (!this.isAvailable()) {
            this.logger.error('FFmpeg not found for transcoding');
            return null;
        }

        const ext = path.extname(inputPath);
        const base = path.basename(inputPath, ext);
        const dir = path.dirname(inputPath);
        const outputPath = path.join(dir, `${base}_${outputBitrate}.${outputFormat}`);

        const args = [
            '-i', inputPath,
            '-b:a', `${outputBitrate}k`,
            '-y',
            outputPath,
        ];

        this.logger.info(`Transcoding: ${inputPath} -> ${outputPath}`);

        return new Promise((resolve, reject) => {
            const proc = spawn(this.ffmpegPath, args);
            proc.on('close', (code) => {
                if (code === 0) {
                    this.logger.info('Transcoding complete');
                    resolve(outputPath);
                } else {
                    reject(new Error(`FFmpeg exited with code ${code}`));
                }
            });
            proc.on('error', reject);
        });
    }

    stop() {
        if (this.process) {
            this.process.kill('SIGTERM');
            setTimeout(() => {
                if (this.process) this.process.kill('SIGKILL');
            }, 3000);
        }
    }
}

module.exports = Encoder;