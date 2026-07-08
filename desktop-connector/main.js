const { app, BrowserWindow, ipcMain, dialog, Menu, globalShortcut } = require('electron');
const path = require('path');
const fs = require('fs');
const { spawn } = require('child_process');
const os = require('os');
const http = require('http');
const { initDb, close, addSong, searchSongs, getAllSongs, getSongCount, getStats, getArtists, getAlbums, getGenres, getSong, getRecentSongs, incrementPlayCount, updateRating, createPlaylist, getPlaylists, addToPlaylist, getPlaylistSongs, removeFromPlaylist, deletePlaylist } = require('./database');

let mainWindow = null;
let streamProcesses = []; // Multiple encoders

// ─── CONFIG ───
const configDir = path.join(os.homedir(), '.planethosts-studio');
if (!fs.existsSync(configDir)) fs.mkdirSync(configDir, { recursive: true });
const cfgPath = path.join(configDir, 'config.json');

function loadConfig() {
    try { return JSON.parse(fs.readFileSync(cfgPath, 'utf8')); } catch(e) { return {}; }
}
function saveConfig(cfg) {
    fs.writeFileSync(cfgPath, JSON.stringify(cfg, null, 2));
}

function getFfmpegPath() {
    const base = app.isPackaged
        ? path.join(process.resourcesPath, 'ffmpeg')
        : path.join(__dirname, 'ffmpeg');
    const exe = path.join(base, 'ffmpeg.exe');
    if (fs.existsSync(exe)) return exe;
    return 'ffmpeg';
}

// ─── SCAN & IMPORT ───
async function scanAndImport(dir) {
    const exts = ['.mp3','.wav','.flac','.ogg','.aac','.m4a','.wma','.mp4'];
    const files = [];
    function walk(d) {
        try {
            for (const item of fs.readdirSync(d)) {
                const fp = path.join(d, item);
                const st = fs.statSync(fp);
                if (st.isDirectory()) walk(fp);
                else if (exts.includes(path.extname(fp).toLowerCase())) files.push({ path: fp, size: st.size, modified: st.mtimeMs });
            }
        } catch(e) {}
    }
    walk(dir);
    let imported = 0;
    for (const f of files) {
        try {
            const mm = require('music-metadata');
            const m = await mm.parseFile(f.path, { duration: true });
            if (addSong({
                path: f.path,
                title: m.common.title || path.basename(f.path, path.extname(f.path)),
                artist: m.common.artist || 'Unknown Artist',
                album: m.common.album || 'Unknown Album',
                genre: (m.common.genre || []).join(', '),
                year: m.common.year || 0,
                bpm: m.common.bpm || 0,
                track: m.common.track?.no || 0,
                duration: m.format.duration || 0,
                bitrate: m.format.bitrate ? Math.round(m.format.bitrate/1000) : 0,
                format: path.extname(f.path).toLowerCase().replace('.',''),
                file_size: f.size,
                file_modified: Math.floor(f.modified)
            })) imported++;
        } catch(e) {
            addSong({ path: f.path, title: path.basename(f.path, path.extname(f.path)), artist: 'Unknown', album: 'Unknown', duration: 0, format: path.extname(f.path).toLowerCase().replace('.',''), file_size: f.size, file_modified: Math.floor(f.modified) });
            imported++;
        }
    }
    return { imported, total: files.length };
}

// ─── STREAMING ───
function startStream(songPath, encoderConfigs) {
    stopStream();
    const ff = getFfmpegPath();
    if (!Array.isArray(encoderConfigs)) encoderConfigs = [encoderConfigs];
    
    encoderConfigs.forEach((cfg, idx) => {
        if (!cfg || !cfg.server || !cfg.username) return;
        const url = `icecast://${cfg.username}:${cfg.password}@${cfg.server}:${cfg.port}/stream`;
        const proc = spawn(ff, [
            '-re','-i',songPath,
            '-c:a','libmp3lame','-b:a',(cfg.bitrate||128)+'k',
            '-f','mp3','-content_type','audio/mpeg',
            '-vn', url
        ]);
        proc._encoderIndex = idx;
        proc._encoderName = cfg.name || `Encoder ${idx+1}`;
        proc.stderr.on('data', d => {
            if (mainWindow) mainWindow.webContents.send('stream-log', `[${proc._encoderName}] ${d.toString()}`);
        });
        proc.on('close', code => {
            const i = streamProcesses.indexOf(proc);
            if (i > -1) streamProcesses.splice(i, 1);
            if (mainWindow) mainWindow.webContents.send('stream-stopped', { name: proc._encoderName, code });
        });
        streamProcesses.push(proc);
    });
    
    if (mainWindow) mainWindow.webContents.send('streams-started', streamProcesses.length);
    return streamProcesses.length > 0;
}

function stopStream() {
    streamProcesses.forEach(p => { try { p.kill('SIGTERM'); } catch(e) {} });
    streamProcesses = [];
}

function getActiveStreamCount() { return streamProcesses.length; }

// ─── WINDOW ───
function createWindow() {
    mainWindow = new BrowserWindow({
        width: 1600, height: 1000, minWidth: 1200, minHeight: 800,
        title: 'Planet Hosts Studio',
        backgroundColor: '#0d1117',
        webPreferences: { preload: path.join(__dirname, 'preload.js'), contextIsolation: true, nodeIntegration: false, webSecurity: false },
        show: false
    });
    mainWindow.loadFile(path.join(__dirname, 'renderer', 'index.html'));
    mainWindow.once('ready-to-show', () => mainWindow.show());
    mainWindow.on('closed', () => { mainWindow = null; });
    
    // Accept file drops on the window
    mainWindow.webContents.on('will-navigate', e => e.preventDefault());
    mainWindow.webContents.setWindowOpenHandler(() => ({ action: 'deny' }));
    
    // Handle drag-drop of folders onto the window
    mainWindow.webContents.on('will-navigate', (e) => e.preventDefault());
    
    setMenu(false); // Login menu on start
}

function setMenu(full) {
    const helpSubmenu = [
        { label: 'Keyboard Shortcuts', accelerator: '?', click: () => mainWindow.webContents.send('show-shortcuts') },
        { type: 'separator' },
        { label: 'About Planet Hosts Studio', click: () => dialog.showMessageBox(mainWindow, {
            type: 'info', title: 'About',
            message: 'Planet Hosts Studio v1.1.0',
            detail: 'Professional broadcast software.\nMusic stays on your computer.\nStreams to Planet Hosts servers.'
        })}
    ];
    
    if (!full) {
        Menu.setApplicationMenu(Menu.buildFromTemplate([
            { label: 'Help', submenu: helpSubmenu }
        ]));
        return;
    }
    
    // Build window panel toggle submenu
    const panelList = [
        'Deck A','Deck B','Aux 1','Aux 2','Playlist','Queue','History',
        'Requests','Voice FX','Volume','Sound FX','Fade Control','Voice Tracking',
        'StreamAds','Encoders','Statistics','Automation Rules','Audio Devices',
        'Scheduler','Event Log','FTP Log','Clock'
    ];
    const windowSubmenu = [
        { label: 'Close All', click: () => mainWindow.webContents.send('window-close-all') },
        { label: 'Restore Default', click: () => mainWindow.webContents.send('window-restore') },
        { type: 'separator' },
        ...panelList.map(p => ({
            label: `✓ ${p}`, click: () => mainWindow.webContents.send('window-toggle', p.toLowerCase().replace(/ /g,'-'))
        }))
    ];
    
    const layoutSubmenu = [
        { label: 'Dashboard A', accelerator: 'CmdOrCtrl+1', click: () => mainWindow.webContents.send('switch-dashboard','a') },
        { label: 'Dashboard B', accelerator: 'CmdOrCtrl+2', click: () => mainWindow.webContents.send('switch-dashboard','b') },
        { label: 'Dashboard C', accelerator: 'CmdOrCtrl+3', click: () => mainWindow.webContents.send('switch-dashboard','c') },
        { type: 'separator' },
        { label: 'Save Workspace', click: () => mainWindow.webContents.send('workspace-save') },
        { label: 'Load Workspace', click: () => mainWindow.webContents.send('workspace-load') }
    ];
    
    Menu.setApplicationMenu(Menu.buildFromTemplate([
        { label: 'File', submenu: [
            { label: 'Config', click: () => mainWindow.webContents.send('navigate','settings') },
            { label: 'Save Config', accelerator: 'CmdOrCtrl+S', click: () => mainWindow.webContents.send('menu-save') },
            { type: 'separator' },
            { label: 'Exit', accelerator: 'Alt+F4', role: 'quit' }
        ]},
        { label: 'Player', submenu: [
            { label: 'Play', accelerator: 'F1', click: () => mainWindow.webContents.send('player-play') },
            { label: 'Pause', accelerator: 'F2', click: () => mainWindow.webContents.send('player-pause') },
            { label: 'Stop', accelerator: 'F3', click: () => mainWindow.webContents.send('player-stop') },
            { label: 'Play Next', accelerator: 'F4', click: () => mainWindow.webContents.send('player-next') }
        ]},
        { label: 'Window', submenu: windowSubmenu },
        { label: 'Layout', submenu: layoutSubmenu },
        { label: 'Tools', submenu: [
            { label: 'Record Show', click: () => mainWindow.webContents.send('tool-record') },
            { label: 'Audio Settings', click: () => mainWindow.webContents.send('navigate','audio') },
            { label: 'Import Music', accelerator: 'CmdOrCtrl+I', click: () => selectImportFolder() }
        ]},
        { label: 'Help', submenu: helpSubmenu }
    ]));
}

// ─── IPC ───
async function selectImportFolder() {
    const r = await dialog.showOpenDialog(mainWindow, { properties: ['openDirectory'], title: 'Select Music Folder' });
    if (!r.canceled && r.filePaths[0]) {
        mainWindow.webContents.send('scan-start', r.filePaths[0]);
        // Save folder to config FIRST so lib.init() finds it
        const cfg = loadConfig();
        if (!cfg.musicFolders) cfg.musicFolders = [];
        if (!cfg.musicFolders.includes(r.filePaths[0])) cfg.musicFolders.push(r.filePaths[0]);
        saveConfig(cfg);
        // Then scan files async
        const result = await scanAndImport(r.filePaths[0]);
        // Then notify renderer
        if (mainWindow) mainWindow.webContents.send('scan-complete', { dir: r.filePaths[0], imported: result.imported, total: result.total });
    }
}

// ─── REGISTER IPC ───
function registerIpc() {
    ipcMain.handle('get-config', () => loadConfig());
    ipcMain.handle('save-config', (e,c) => { saveConfig(c); return true; });
    ipcMain.handle('import-folder', () => selectImportFolder());
    ipcMain.handle('import-folder-path', async (e, dirPath) => {
        if (!dirPath || !fs.existsSync(dirPath)) return {imported: 0};
        mainWindow.webContents.send('scan-start', dirPath);
        const cfg = loadConfig();
        if (!cfg.musicFolders) cfg.musicFolders = [];
        if (!cfg.musicFolders.includes(dirPath)) cfg.musicFolders.push(dirPath);
        saveConfig(cfg);
        const result = await scanAndImport(dirPath);
        if (mainWindow) mainWindow.webContents.send('scan-complete', { dir: dirPath, imported: result.imported, total: result.total });
        return result;
    });
    ipcMain.handle('pick-folder', async () => {
        const r = await dialog.showOpenDialog(mainWindow, { properties: ['openDirectory'], title: 'Select Folder' });
        return r.canceled ? null : r.filePaths[0];
    });
    ipcMain.handle('search-songs', (e,q) => searchSongs(q));
    ipcMain.handle('get-all-songs', (e,l) => getAllSongs(l));
    ipcMain.handle('get-song', (e,i) => getSong(i));
    ipcMain.handle('get-recent-songs', (e,l) => getRecentSongs(l));
    ipcMain.handle('get-song-count', () => getSongCount());
    ipcMain.handle('get-stats', () => getStats());
    ipcMain.handle('get-artists', () => getArtists());
    ipcMain.handle('get-albums', (e,a) => getAlbums(a));
    ipcMain.handle('get-genres', () => getGenres());
    ipcMain.handle('increment-play-count', (e,i) => incrementPlayCount(i));
    ipcMain.handle('update-rating', (e,i,r) => updateRating(i, r));
    ipcMain.handle('create-playlist', (e,n) => createPlaylist(n));
    ipcMain.handle('get-playlists', () => getPlaylists());
    ipcMain.handle('add-to-playlist', (e,p,s) => addToPlaylist(p, s));
    ipcMain.handle('get-playlist-songs', (e,p) => getPlaylistSongs(p));
    ipcMain.handle('remove-from-playlist', (e,p,s) => removeFromPlaylist(p, s));
    ipcMain.handle('delete-playlist', (e,i) => deletePlaylist(i));
    // ─── FILE SYSTEM ───
    ipcMain.handle('list-folder', (e, dir) => {
        if (!dir || !fs.existsSync(dir) || !fs.statSync(dir).isDirectory()) return [];
        const items = [];
        for (const item of fs.readdirSync(dir)) {
            if (item.startsWith('.')) continue;
            const fp = path.join(dir, item);
            const stat = fs.statSync(fp);
            items.push({
                name: item,
                path: fp,
                isDir: stat.isDirectory(),
                size: stat.isDirectory() ? 0 : stat.size,
                modified: stat.mtimeMs
            });
        }
        items.sort((a, b) => {
            if (a.isDir && !b.isDir) return -1;
            if (!a.isDir && b.isDir) return 1;
            return a.name.localeCompare(b.name);
        });
        return items;
    });
    ipcMain.handle('create-folder', (e, parent, name) => {
        const fp = path.join(parent, name);
        if (!fs.existsSync(fp)) { fs.mkdirSync(fp, { recursive: true }); return true; }
        return false;
    });
    ipcMain.handle('rename-folder', (e, oldPath, newName) => {
        const parent = path.dirname(oldPath);
        const newPath = path.join(parent, newName);
        if (!fs.existsSync(newPath)) { fs.renameSync(oldPath, newPath); return true; }
        return false;
    });
    ipcMain.handle('delete-folder', (e, dir) => {
        if (fs.existsSync(dir)) { fs.rmSync(dir, { recursive: true, force: true }); return true; }
        return false;
    });
    const audioExts = ['.mp3','.wav','.flac','.ogg','.aac','.m4a','.wma','.mp4'];
    ipcMain.handle('get-file-meta', async (e, fp) => {
        try {
            const mm = require('music-metadata');
            const m = await mm.parseFile(fp, { duration: true });
            return {
                path: fp,
                title: m.common.title || path.basename(fp, path.extname(fp)),
                artist: m.common.artist || 'Unknown',
                album: m.common.album || '',
                genre: (m.common.genre || []).join(', '),
                year: m.common.year || 0,
                bpm: m.common.bpm || 0,
                track: m.common.track?.no || 0,
                duration: m.format.duration || 0,
                bitrate: m.format.bitrate ? Math.round(m.format.bitrate/1000) : 0,
                format: path.extname(fp).toLowerCase().replace('.',''),
                picture: m.common.picture ? { data: m.common.picture[0].data.toString('base64'), format: m.common.picture[0].format } : null
            };
        } catch(e) {
            return {
                path: fp,
                title: path.basename(fp, path.extname(fp)),
                artist: 'Unknown',
                album: '',
                duration: 0,
                format: path.extname(fp).toLowerCase().replace('.','')
            };
        }
    });
    ipcMain.handle('delete-file', (e, fp) => {
        if (fs.existsSync(fp)) { fs.unlinkSync(fp); return true; }
        return false;
    });
    // ─── WRITE METADATA TAGS ───
    ipcMain.handle('write-tags', async (e, fp, tags) => {
        try {
            const NodeId3 = require('node-id3');
            const result = NodeId3.write({
                title: tags.title,
                artist: tags.artist,
                album: tags.album,
                genre: tags.genre,
                year: tags.year,
                trackNumber: tags.track
            }, fp);
            if (result) {
                // Also update our DB
                try { const mm = require('music-metadata'); const m = await mm.parseFile(fp, {duration:true});
                    addSong({ path: fp, title: m.common.title||tags.title, artist: m.common.artist||tags.artist, album: m.common.album||tags.album, genre: (m.common.genre||[]).join(', '), year: m.common.year||0, bpm: m.common.bpm||0, track: m.common.track?.no||0, duration: m.format.duration||0, bitrate: m.format.bitrate?Math.round(m.format.bitrate/1000):0, format: path.extname(fp).toLowerCase().replace('.',''), file_size: fs.statSync(fp).size, file_modified: Math.floor(fs.statSync(fp).mtimeMs) }); } catch(e) {}
                return true;
            }
            return false;
        } catch(e) { return false; }
    });
    // ─── LOCAL MONITOR PLAYBACK ───
    let monitorProcess = null;
    function startMonitor(filePath) {
        stopMonitor();
        const ff = getFfmpegPath();
        // Use ffplay in background with -nodisp for audio-only, -autoexit to stop on file end
        monitorProcess = spawn(ff.replace('ffmpeg','ffplay'), [
            '-nodisp', '-autoexit', '-volume', '50',
            filePath
        ], { stdio: 'ignore', detached: true });
        return true;
    }
    function stopMonitor() {
        if (monitorProcess) { try { monitorProcess.kill(); } catch(e) {} monitorProcess = null; }
    }
    ipcMain.handle('read-audio-file', async (e, filePath) => {
        try {
            const buffer = require('fs').readFileSync(filePath);
            return buffer.toString('base64');
        } catch(err) { return null; }
    });
    ipcMain.handle('monitor-start', (e, path) => startMonitor(path));
    ipcMain.handle('monitor-stop', () => { stopMonitor(); return true; });
    
    // ─── MIC RELAY / DUCKING ───
    let micProcess = null;
    let isPttActive = false;
    
    function getMicDevices() {
        try {
            const ff = getFfmpegPath();
            const result = require('child_process').execSync(`"${ff}" -list_devices true -f dshow -i dummy 2>&1`).toString();
            const mics = [];
            const lines = result.split('\n');
            let inAudio = false;
            for (const line of lines) {
                if (line.includes('DirectShow audio devices')) inAudio = true;
                else if (line.includes('DirectShow video devices')) inAudio = false;
                else if (inAudio && line.includes('"')) {
                    const match = line.match(/"(.+?)"/);
                    if (match) mics.push(match[1]);
                }
            }
            return mics;
        } catch(e) { return []; }
    }
    
    function startMicCapture(musicPath, djConfig) {
        if (micProcess) stopMicCapture();
        const ff = getFfmpegPath();
        const micDevice = djConfig.micDevice || 'Microphone';
        
        // ffmpeg: read music file + mic, mix with ducking (mic takes priority)
        const url = `icecast://${djConfig.username}:${djConfig.password}@${djConfig.server}:${djConfig.port}/stream`;
        
        // Use amix filter: music on channel 0, mic on channel 1
        // When PTT is active, mic volume=1.0 and music ducks to 0.3
        micProcess = spawn(ff, [
            '-re', '-i', musicPath,
            '-f', 'dshow', '-i', `audio=${micDevice}`,
            '-filter_complex', `[0:a]volume=0.8[music];[1:a]volume=0.0[mic];[music][mic]amix=inputs=2:duration=first:weights=1 0[out]`,
            '-map', '[out]',
            '-c:a', 'libmp3lame', '-b:a', (djConfig.bitrate||128)+'k',
            '-f', 'mp3', '-content_type', 'audio/mpeg',
            '-vn', url
        ]);
        
        micProcess.stderr.on('data', d => {
            if (mainWindow) mainWindow.webContents.send('stream-log', '[MIC] '+d.toString());
        });
        micProcess.on('close', () => { micProcess = null; });
        return true;
    }
    
    function stopMicCapture() { if (micProcess) { micProcess.kill('SIGTERM'); micProcess = null; } }
    
    function setPttState(active) {
        isPttActive = active;
        // Notify renderer for UI update
        if (mainWindow) mainWindow.webContents.send('ptt-state', active);
    }
    
    ipcMain.handle('get-mic-devices', () => getMicDevices());
    ipcMain.handle('start-mic-stream', (e, path, cfg) => startMicCapture(path, cfg));
    ipcMain.handle('stop-mic-stream', () => { stopMicCapture(); return true; });
    ipcMain.handle('set-ptt', (e, active) => { setPttState(active); return true; });
    
    // ─── RECORD SHOW ───
    let recordProcess = null;
    ipcMain.handle('start-record', (e, streamUrl, outputPath) => {
        if (recordProcess) stopRecord();
        const ff = getFfmpegPath();
        const dir = path.dirname(outputPath);
        if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
        recordProcess = spawn(ff, ['-i', streamUrl, '-c', 'copy', '-f', 'mp3', outputPath]);
        recordProcess.stderr.on('data', d => { if (mainWindow) mainWindow.webContents.send('stream-log', '[REC] '+d.toString()); });
        recordProcess.on('close', () => { recordProcess = null; if (mainWindow) mainWindow.webContents.send('record-stopped'); });
        return true;
    });
    ipcMain.handle('stop-record', () => { if (recordProcess) { recordProcess.kill('SIGTERM'); recordProcess = null; } return true; });
    function stopRecord() { if (recordProcess) { recordProcess.kill(); recordProcess = null; } }
    ipcMain.handle('move-file', (e, src, destDir) => {
        const name = path.basename(src);
        const dest = path.join(destDir, name);
        if (!fs.existsSync(dest)) { fs.renameSync(src, dest); return true; }
        return false;
    });
    ipcMain.handle('start-stream', (e,p,c) => startStream(p, c));
    ipcMain.handle('stop-stream', () => { stopStream(); return true; });
    ipcMain.handle('get-stream-status', () => ({ active: streamProcesses.length > 0 }));
    ipcMain.handle('set-menu-full', () => { setMenu(true); return true; });
    
    // ─── API PROXY (handles self-signed certs) ───
    ipcMain.handle('api-request', async (e, opts) => {
        const https = require('https');
        return new Promise((resolve) => {
            const url = new URL(opts.url);
            const req = https.request({
                hostname: url.hostname, port: url.port, path: url.pathname + url.search,
                method: opts.method || 'GET',
                headers: opts.headers || {},
                rejectUnauthorized: false
            }, (res) => {
                let data = '';
                res.on('data', chunk => data += chunk);
                res.on('end', () => {
                    try { resolve(JSON.parse(data)); }
                    catch(e) { resolve({success:false, error:data.substring(0,200)}); }
                });
            });
            req.on('error', (e) => resolve({success:false, error:e.message}));
            if (opts.body) req.write(JSON.stringify(opts.body));
            req.end();
        });
    });
}

// Allow self-signed certs for API calls
process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';
app.commandLine.appendSwitch('ignore-certificate-errors');

app.whenReady().then(async () => {
    await initDb();
    registerIpc();
    createWindow();
});
app.on('window-all-closed', () => {
    stopStream();
    // Clear login credentials on close (logout)
    try {
        const c = loadConfig();
        if (c.username) {
            delete c.username; delete c.password; delete c.stationName; delete c.stationId; delete c.encoders;
            saveConfig(c);
        }
    } catch(e) {}
    close();
    app.quit();
});
app.on('before-quit', () => {
    stopStream();
    try {
        const c = loadConfig();
        if (c.username) {
            delete c.username; delete c.password; delete c.stationName; delete c.stationId; delete c.encoders;
            saveConfig(c);
        }
    } catch(e) {}
});
app.on('activate', () => { if (!mainWindow) createWindow(); });