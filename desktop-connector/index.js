#!/usr/bin/env node
/**
 * Planet Hosts Studio Streamer
 * 
 * Plays local audio files and streams to SHOUTcast.
 * Music stays on your computer — nothing is uploaded.
 * Like SAM Broadcaster, but free and lightweight.
 */

const http = require('http');
const fs = require('fs');
const path = require('path');
const readline = require('readline');
const os = require('os');
const { spawn, exec } = require('child_process');

const configDir = path.join(os.homedir(), '.planethosts');
if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
const cfgPath = path.join(configDir, 'streamer-config.json');

// ─── CONFIG ───
function loadConfig() {
    if (fs.existsSync(cfgPath)) return JSON.parse(fs.readFileSync(cfgPath, 'utf8'));
    return null;
}
function saveConfig(cfg) {
    fs.writeFileSync(cfgPath, JSON.stringify(cfg, null, 2));
}

// ─── SETUP WIZARD ───
async function runSetup() {
    console.log('');
    console.log('  ╔══════════════════════════════════════════╗');
    console.log('  ║    Planet Hosts Studio Streamer         ║');
    console.log('  ║            Setup                         ║');
    console.log('  ╚══════════════════════════════════════════╝');
    console.log('');
    
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    const ask = (q, d) => new Promise(r => rl.question(q + (d ? ' ['+d+']' : '') + ': ', a => r(a || d || '')));
    
    const cfg = {
        server: await ask('SHOUTcast Server', '45.61.59.55'),
        port: parseInt(await ask('Port', '9000')),
        password: await ask('Source Password'),
        relayPort: parseInt(await ask('Studio Relay Port (use 9006)', '9006')),
        stationId: await ask('Station ID (for status)'),
        musicDir: '',
        bitrate: parseInt(await ask('Bitrate', '128')),
    };
    
    saveConfig(cfg);
    console.log('\n✅ Configured! Music stays on your computer.');
    console.log('   Files are streamed live — never uploaded.\n');
    rl.close();
    return cfg;
}

// ─── MAIN ───
async function main() {
    let cfg = loadConfig();
    if (!cfg) {
        cfg = await runSetup();
        console.log('Restart the app to start streaming.\n');
        process.exit(0);
    }
    
    console.log('Planet Hosts Studio Streamer');
    console.log(`Streaming to ${cfg.server}:${cfg.port}`);
    console.log('Music stays local — nothing is uploaded.\n');
    console.log('Enter a file path to stream, or type "help":\n');
    
    let currentProcess = null;
    let isStreaming = false;
    
    function streamFile(filePath) {
        if (!fs.existsSync(filePath)) {
            console.log(' File not found: ' + filePath);
            return;
        }
        
        if (currentProcess) {
            currentProcess.kill();
            currentProcess = null;
        }
        
        const ext = path.extname(filePath).toLowerCase();
        const supported = ['.mp3', '.wav', '.flac', '.ogg', '.aac', '.m4a', '.wma'];
        
        if (!supported.includes(ext)) {
            console.log(' Unsupported format. Supported: ' + supported.join(', '));
            return;
        }
        
        isStreaming = true;
        console.log(` ▶ Streaming: ${path.basename(filePath)}`);
        
        // Use ffmpeg to stream to SHOUTcast
        const url = `http://source:${cfg.password}@${cfg.server}:${cfg.port}/`;
        currentProcess = spawn('ffmpeg', [
            '-re', '-i', filePath,
            '-c:a', 'libmp3lame', '-b:a', cfg.bitrate + 'k',
            '-f', 'mp3', '-vn',
            url
        ], { stdio: ['pipe', 'pipe', 'pipe'] });
        
        currentProcess.stderr.on('data', (d) => {
            const line = d.toString();
            if (line.includes('error') || line.includes('Error')) {
                console.log(' ⚠ ' + line.trim());
            }
        });
        
        currentProcess.on('close', (code) => {
            isStreaming = false;
            if (code === 0) console.log(' ⏹ Streaming ended');
            else console.log(' ⏹ Stream stopped (code ' + code + ')');
        });
    }
    
    function stopStreaming() {
        if (currentProcess) {
            currentProcess.kill();
            currentProcess = null;
            isStreaming = false;
            console.log(' ⏹ Streaming stopped');
        }
    }
    
    // CLI loop
    const rl = readline.createInterface({ input: process.stdin, output: process.stdout });
    
    rl.on('line', (line) => {
        const cmd = line.trim();
        
        if (!cmd) return;
        
        if (cmd === 'help') {
            console.log('');
            console.log('  Commands:');
            console.log('  /path/to/file.mp3   Stream a local file');
            console.log('  stop                Stop streaming');
            console.log('  status              Show connection info');
            console.log('  exit                Quit');
            console.log('');
        } else if (cmd === 'stop') {
            stopStreaming();
        } else if (cmd === 'status') {
            console.log(`  Server: ${cfg.server}:${cfg.port}`);
            console.log(`  Streaming: ${isStreaming ? 'Yes' : 'No'}`);
            console.log(`  Bitrate: ${cfg.bitrate}kbps`);
        } else if (cmd === 'exit') {
            stopStreaming();
            process.exit(0);
        } else {
            streamFile(cmd);
        }
    });
    
    console.log('Drag an audio file onto this window, or type a path.');
}

main().catch(console.error);