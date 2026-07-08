// ─── DECKS ───
let deckA = { song: null, playing: false, duration: 0, el: 'a', startTime: 0 };
let deckB = { song: null, playing: false, duration: 0, el: 'b', startTime: 0 };
let deckTimers = { a: null, b: null, autoAdvance: null };
let crossfadeDuration = 3;

const deck = {
    load(which, rawSong) {
        if (!rawSong) return;
        const d = which === 'a' ? deckA : deckB;
        const label = which.toUpperCase();
        
        // Ensure we have a proper song object
        const song = {
            path: rawSong.path || rawSong.file || '',
            title: rawSong.title || rawSong.name || 'Unknown',
            artist: rawSong.artist || 'Unknown',
            duration: rawSong.duration || 0,
            bpm: rawSong.bpm || 0
        };
        d.song = song;
        d.playing = false;
        d.duration = song.duration || 0;
        d.startTime = 0;
        
        document.getElementById(`title${label}`).textContent = song.title;
        document.getElementById(`artist${label}`).textContent = song.artist;
        document.getElementById(`duration${label}`).textContent = formatTime(d.duration);
        document.getElementById(`cover${label}`).textContent = '🎵';
        document.getElementById(`play${label}`).textContent = '▶';
        document.getElementById(`status${label}`).textContent = 'Loaded';
        document.getElementById(`status${label}`).className = 'deck-status cued';
        document.getElementById(`prog${label}`).style.width = '0%';
        document.getElementById(`curTime${label}`).textContent = '0:00';
        document.getElementById(`remTime${label}`).textContent = '-' + formatTime(d.duration);
        document.getElementById(`bpm${label}`).textContent = song.bpm ? `${song.bpm} BPM` : '';
        
        // Restore cues for this track
        this.restoreCues(which);
        
        initVU(which);
        initWaveform(which);
    },
    
    toggle(which) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song || !d.song.path) { console.warn('No song loaded'); return; }
        
        d.playing = !d.playing;
        const label = which.toUpperCase();
        const btn = document.getElementById(`play${label}`);
        const status = document.getElementById(`status${label}`);
        
        if (d.playing) {
            btn.textContent = '⏸';
            status.textContent = 'Playing';
            status.className = 'deck-status playing';
            d.startTime = Date.now();
            
            startProgress(which);
            startVU(which);
            startWaveform(which);
            
            updateNowPlaying(song);
            app.addHistory(d.song);
            
            // Start streaming if live
            if (app.isLive) {
                api.startStream(d.song.path, appConfig);
                api.incrementPlayCount(d.song.id || 0);
            }
            
            // Auto-start local monitor (mute only stops speakers, not encoder)
            this.monitors[which] = true;
            if (!this.masterMuted) {
                document.getElementById(`mon${label}`).style.color = '#3fb950';
                startMonitorPlayback(d.song.path, which);
            }
            
            // Auto-advance to next when track ends
            scheduleNext(which);
        } else {
            btn.textContent = '▶';
            status.textContent = 'Paused';
            status.className = 'deck-status';
            if (deckTimers[which]) clearInterval(deckTimers[which]);
            if (deckTimers.autoAdvance) clearTimeout(deckTimers.autoAdvance);
        }
    },
    
    stop(which) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song) return;
        d.playing = false;
        const label = which.toUpperCase();
        document.getElementById(`play${label}`).textContent = '▶';
        document.getElementById(`status${label}`).textContent = 'Stopped';
        document.getElementById(`status${label}`).className = 'deck-status';
        document.getElementById(`prog${label}`).style.width = '0%';
        document.getElementById(`curTime${label}`).textContent = '0:00';
        document.getElementById(`remTime${label}`).textContent = '-' + formatTime(d.duration);
        document.getElementById(`intro${label}`).textContent = 'I:--';
        document.getElementById(`outro${label}`).textContent = 'O:--';
        if (deckTimers[which]) clearInterval(deckTimers[which]);
        if (deckTimers.autoAdvance) clearTimeout(deckTimers.autoAdvance);
        if (app.isLive) api.stopStream();
        stopMonitorPlayback();
    },
    
    cue(which) { this.stop(which); },
    prev(which) { this.stop(which); },
    
    next(which) {
        const song = queue.shiftNext();
        if (song) { this.load(which, song); setTimeout(() => this.toggle(which), 150); }
    },
    
    loadFromLib(which) { this.next(which); },
    
    // ─── HOT CUES & INTRO/OUTRO ───
    cues: {}, // path -> { intro: secs, outro: secs, hot: [secs, secs, secs, secs] }
    
    getCuesFor(path) {
        if (!this.cues[path]) this.cues[path] = { intro: 0, outro: 0, hot: [null,null,null,null] };
        return this.cues[path];
    },
    
    setCue(which, idx) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song || !d.playing) return;
        const elapsed = (Date.now() - d.startTime) / 1000;
        const cues = this.getCuesFor(d.song.path);
        cues.hot[idx] = Math.round(elapsed);
        this.saveCues();
        this.renderCues(which);
    },
    
    jumpCue(which, idx) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song) return;
        const cues = this.getCuesFor(d.song.path);
        const pos = cues.hot[idx];
        if (pos === null) return;
        // Visual jump: update progress display to cue position
        const label = which.toUpperCase();
        const pct = d.duration ? (pos / d.duration) * 100 : 0;
        document.getElementById(`prog${label}`).style.width = pct + '%';
        document.getElementById(`curTime${label}`).textContent = formatTime(pos);
        document.getElementById(`remTime${label}`).textContent = '-' + formatTime((d.duration||0) - pos);
        // Restart monitor from cue position
        if (d.song && (which === 'a' ? deck.monitors.a : deck.monitors.b)) {
            startMonitorPlayback(d.song.path, which);
        }
        this.renderCues(which);
    },
    
    setIntro(which) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song) return;
        const elapsed = d.playing ? (Date.now() - d.startTime) / 1000 : 0;
        const cues = this.getCuesFor(d.song.path);
        cues.intro = Math.round(elapsed);
        this.saveCues();
        this.renderCues(which);
        document.getElementById(`intro${which.toUpperCase()}`).textContent = `I:${formatTime(elapsed)}`;
    },
    
    setOutro(which) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song) return;
        const elapsed = d.playing ? (Date.now() - d.startTime) / 1000 : d.duration || 0;
        const cues = this.getCuesFor(d.song.path);
        cues.outro = Math.round(elapsed);
        this.saveCues();
        this.renderCues(which);
        document.getElementById(`outro${which.toUpperCase()}`).textContent = `O:${formatTime(elapsed)}`;
    },
    
    renderCues(which) {
        const d = which === 'a' ? deckA : deckB;
        if (!d.song) return;
        const cues = this.getCuesFor(d.song.path);
        const el = document.getElementById(`hotcues${which.toUpperCase()}`);
        if (!el) return;
        const btns = el.querySelectorAll('.cue-btn');
        btns.forEach((btn, i) => {
            if (i < 4) {
                if (cues.hot[i] !== null) {
                    btn.classList.add('active-cue');
                    btn.textContent = formatTime(cues.hot[i]);
                } else {
                    btn.classList.remove('active-cue');
                    btn.textContent = `C${i+1}`;
                }
            }
        });
        document.getElementById(`intro${which.toUpperCase()}`).textContent = 
            cues.intro > 0 ? `I:${formatTime(cues.intro)}` : 'I:--';
        document.getElementById(`outro${which.toUpperCase()}`).textContent = 
            cues.outro > 0 ? `O:${formatTime(cues.outro)}` : 'O:--';
    },
    
    saveCues() {
        appConfig.cueData = this.cues;
        api.saveConfig(appConfig);
    },
    
    loadCues() {
        if (appConfig.cueData) this.cues = appConfig.cueData;
    },
    
    // Restore cues after loading a track
    restoreCues(which) {
        const d = which === 'a' ? deckA : deckB;
        if (d.song && d.song.path) {
            this.getCuesFor(d.song.path);
            this.renderCues(which);
        }
    },
    
    // ─── GLOBAL TOOLBAR CONTROLS ───
    play() { const d = deckA.playing ? 'a' : deckB.playing ? 'b' : (deckA.song ? 'a' : 'b'); this.toggle(d); },
    pauseToggle() { const d = deckA.playing ? 'a' : deckB.playing ? 'b' : null; if (d) this.toggle(d); },
    stopAll() { this.stop('a'); this.stop('b'); stopMonitorPlayback(); },
    nextTrack() { this.next(deckA.playing ? 'a' : 'b'); },
    prevTrack() { this.prev(deckA.playing ? 'a' : 'b'); },
    
    // ─── AUX DECKS ───
    auxFolders: {},
    async auxPlay(type) {
        const folder = this.auxFolders[type];
        if (!folder) { 
            const names = {jingle:'Jingles', advert:'Adverts', emergency:'Emergency', sweeper:'Sweepers'};
            const path = await api.pickFolder();
            if (path) {
                this.auxFolders[type] = path;
                appConfig.auxFolders = this.auxFolders;
                await api.saveConfig(appConfig);
                return this.auxPlay(type);
            } else {
                const manualPath = prompt(`Enter path to your ${names[type]} folder:`);
                if (manualPath) {
                    this.auxFolders[type] = manualPath;
                    appConfig.auxFolders = this.auxFolders;
                    await api.saveConfig(appConfig);
                    return this.auxPlay(type);
                }
            }
            return;
        }
        
        // Visual feedback
        const btn = event?.target;
        if (btn) { btn.style.filter = 'brightness(1.4)'; setTimeout(() => btn.style.filter = '', 200); }
        
        // Get a random audio file from the folder
        try {
            const items = await api.listFolder(folder);
            const audioFiles = items.filter(i => !i.isDir && /\.(mp3|wav|flac|ogg|aac|m4a|wma)$/i.test(i.name));
            if (audioFiles.length === 0) { alert(`No audio files found in ${folder}`); return; }
            const randomFile = audioFiles[Math.floor(Math.random() * audioFiles.length)];
            // Play locally via Web Audio API
            await playAuxFile(randomFile.path);
            // If streaming, send to stream too
            if (app.isLive) {
                api.startStream(randomFile.path, appConfig);
            }
        } catch(e) {
            console.warn('AUX error:', e);
        }
    },
    setAuxFolder(type, folder) {
        this.auxFolders[type] = folder;
    },
    
    // ─── MONITOR / MUTE ───
    monitors: { a: false, b: false },
    masterMuted: false,
    
    toggleMonitor(which) {
        this.monitors[which] = !this.monitors[which];
        const btn = document.getElementById(`mon${which.toUpperCase()}`);
        const d = which === 'a' ? deckA : deckB;
        
        if (this.monitors[which] && d.song && d.playing && !this.masterMuted) {
            btn.style.color = '#3fb950';
            btn.title = 'Monitoring';
            startMonitorPlayback(d.song.path, which);
        } else {
            btn.style.color = '#64748b';
            btn.title = 'Monitor off';
            stopMonitorPlayback();
        }
    },
    
    toggleMute() {
        this.masterMuted = !this.masterMuted;
        const btn = document.getElementById('muteBtn') || this.addMuteBtn();
        if (this.masterMuted) {
            btn.textContent = '🔇 Muted';
            btn.style.color = '#f85149';
            stopMonitorPlayback();
        } else {
            btn.textContent = '🔊 Monitor';
            btn.style.color = '#8b949e';
            if (this.monitors.a && deckA.playing) {
                document.getElementById('monA').style.color = '#3fb950';
                startMonitorPlayback(deckA.song.path, 'a');
            }
            if (this.monitors.b && deckB.playing) {
                document.getElementById('monB').style.color = '#3fb950';
                startMonitorPlayback(deckB.song.path, 'b');
            }
        }
    },
    
    addMuteBtn() {
        const tb = document.querySelector('.toolbar-left');
        const btn = document.createElement('button');
        btn.id = 'muteBtn';
        btn.textContent = '🔊 Monitor';
        btn.title = 'Toggle local speaker monitoring';
        btn.onclick = () => deck.toggleMute();
        btn.style.cssText = 'margin-left:4px';
        tb.appendChild(btn);
        return btn;
    }
};

// ─── HELPERS ───
function scheduleNext(which) {
    if (deckTimers.autoAdvance) clearTimeout(deckTimers.autoAdvance);
    const d = which === 'a' ? deckA : deckB;
    const other = which === 'a' ? 'b' : 'a';
    const dur = (d.duration || 180) * 1000;
    const fadeStart = Math.max(0, dur - crossfadeDuration * 1000);
    
    deckTimers.autoAdvance = setTimeout(() => {
        startCrossfade(which, other);
        
        const next = queue.shiftNext();
        if (next) {
            deck.load(other, next);
            setTimeout(() => {
                const od = other === 'a' ? deckA : deckB;
                od.playing = true;
                const ol = other.toUpperCase();
                document.getElementById(`play${ol}`).textContent = '⏸';
                document.getElementById(`status${ol}`).textContent = 'Playing';
                document.getElementById(`status${ol}`).className = 'deck-status playing';
                od.startTime = Date.now();
                startProgress(other);
                startVU(other);
                startWaveform(other);
                app.addHistory(od.song);
                if (app.isLive) {
                    api.startStream(od.song.path, appConfig);
                }
                scheduleNext(other);
            }, crossfadeDuration * 500);
        }
    }, fadeStart);
}

function startCrossfade(fromWhich, toWhich) {
    const prog = document.getElementById(`prog${fromWhich.toUpperCase()}`);
    let step = 100;
    const interval = setInterval(() => {
        step -= 100 / (crossfadeDuration * 10);
        if (step <= 0) { clearInterval(interval); prog.style.width = '0%'; }
        else prog.style.width = step + '%';
    }, 100);
}

function startProgress(which) {
    const d = which === 'a' ? deckA : deckB;
    const label = which.toUpperCase();
    if (deckTimers[which]) clearInterval(deckTimers[which]);
    
    deckTimers[which] = setInterval(() => {
        if (!d.playing) { clearInterval(deckTimers[which]); return; }
        const elapsed = (Date.now() - d.startTime) / 1000;
        const dur = d.duration || 180;
        if (elapsed >= dur) { clearInterval(deckTimers[which]); return; }
        const pct = (elapsed / dur) * 100;
        document.getElementById(`prog${label}`).style.width = pct + '%';
        document.getElementById(`curTime${label}`).textContent = formatTime(elapsed);
        document.getElementById(`remTime${label}`).textContent = '-' + formatTime(dur - elapsed);
    }, 200);
}

function initVU(which) {
    const el = document.getElementById(`vu${which.toUpperCase()}`); el.innerHTML = '';
    for (let i=0;i<16;i++) { const b=document.createElement('div');b.className='vu-bar';el.appendChild(b); }
}
function startVU(which) {
    const el = document.getElementById(`vu${which.toUpperCase()}`);
    setInterval(() => {
        if (!(which==='a'?deckA:deckB).playing) return;
        el.querySelectorAll('.vu-bar').forEach(b => {
            const h=Math.random()*16+1; b.style.height=h+'px';
            b.className = h>13?'vu-bar pk':'vu-bar';
        });
    }, 80);
}
function initWaveform(which) {
    const el = document.getElementById(`waveform${which.toUpperCase()}`); el.innerHTML = '';
    for (let i=0;i<60;i++) { const b=document.createElement('div');b.className='wf-bar';el.appendChild(b); }
}
function startWaveform(which) {
    const el = document.getElementById(`waveform${which.toUpperCase()}`);
    const c = which==='a'?'0,140,255':'168,85,247';
    setInterval(() => {
        el.querySelectorAll('.wf-bar').forEach(b => {
            b.style.height = (Math.random()*80+20)+'%';
            b.style.background = `rgba(${c},${0.04+Math.random()*0.2})`;
        });
    }, 200);
}

function updateNowPlaying(song) {
    document.getElementById('ssNowPlaying').textContent = song?.title || '—';
}

function formatTime(secs) {
    if (!secs||secs<=0) return '0:00';
    return Math.floor(secs/60)+':'+String(Math.floor(secs%60)).padStart(2,'0');
}

// ─── DECK UI INIT (runs after login when elements exist) ───
// Also wire up crossfader
document.addEventListener('DOMContentLoaded', () => {
    const cf = document.getElementById('crossfader');
    if (cf) {
        cf.addEventListener('input', function() {
            const val = this.value / 100; // 0-1
            // Crossfade gains: at 0 = A full, B silent; at 1 = B full, A silent
            const gainA = Math.cos(val * Math.PI / 2);
            const gainB = Math.sin(val * Math.PI / 2);
            if (monitorGains.a) monitorGains.a.gain.value = gainA;
            if (monitorGains.b) monitorGains.b.gain.value = gainB;
        });
    }
});
function initDeckUI() {
    const decks = document.querySelectorAll('.deck');
    if (!decks.length) return; // Login screen showing
    
    // Drag & Drop
    decks.forEach(el => {
        el.addEventListener('dragover', e => e.preventDefault());
        el.addEventListener('drop', async e => {
            e.preventDefault();
            const which = el.id==='deckA'?'a':'b';
            const data = e.dataTransfer.getData('text/plain');
            if (!data) return;
            try {
                const meta = await api.getFileMeta(data);
                deck.load(which, meta);
                setTimeout(() => deck.toggle(which), 150);
            } catch(ex) {
                deck.load(which, {path:data, title:data.split(/[\\/]/).pop(), artist:'Unknown'});
                setTimeout(() => deck.toggle(which), 150);
            }
        });
    });
    
    // Position sliders
    const posA = document.getElementById('posA');
    const posB = document.getElementById('posB');
    if (posA) {
        posA.addEventListener('input', function() {
            if (!deckA.song||!deckA.duration) return;
            const pct = this.value/100;
            const target = pct * deckA.duration;
            document.getElementById('curTimeA').textContent = formatTime(target);
            document.getElementById('progA').style.width = this.value+'%';
            document.getElementById('remTimeA').textContent = '-'+formatTime(deckA.duration-target);
        });
    }
    if (posB) {
        posB.addEventListener('input', function() {
            if (!deckB.song||!deckB.duration) return;
            const pct = this.value/100;
            const target = pct * deckB.duration;
            document.getElementById('curTimeB').textContent = formatTime(target);
            document.getElementById('progB').style.width = this.value+'%';
            document.getElementById('remTimeB').textContent = '-'+formatTime(deckB.duration-target);
        });
    }
    
    // Load saved cues
    deck.loadCues();
    deck.renderCues('a');
    deck.renderCues('b');
}

// ─── LOCAL MONITOR AUDIO ENGINE (per-deck gains) ───
let audioCtx = null;
const monitorGains = { a: null, b: null };
const monitorSources = { a: null, b: null };
let auxSource = null;
let auxGain = null;

async function getAudioCtx() {
    if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if (audioCtx.state === 'suspended') await audioCtx.resume();
    return audioCtx;
}

async function startMonitorPlayback(filePath, which) {
    try {
        stopMonitorPlayback(which);
        const ctx = await getAudioCtx();
        let buf;
        try {
            const base64 = await api.readAudioFile(filePath);
            if (base64) {
                const binary = atob(base64);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                buf = bytes.buffer;
            } else throw new Error('readAudioFile returned null');
        } catch(e) {
            const resp = await fetch('file://' + filePath.replace(/\\/g,'/'));
            buf = await resp.arrayBuffer();
        }
        const buffer = await ctx.decodeAudioData(buf);
        const source = ctx.createBufferSource();
        source.buffer = buffer;
        const gain = ctx.createGain();
        // Initial gain from volume slider and crossfader
        const vol = parseFloat(document.getElementById(`vol${which.toUpperCase()}`).value) / 100;
        const cf = document.getElementById('crossfader');
        const cfVal = cf ? parseFloat(cf.value) / 100 : 0.5;
        const crossGain = which === 'a' ? Math.cos(cfVal * Math.PI / 2) : Math.sin(cfVal * Math.PI / 2);
        gain.gain.value = vol * crossGain;
        
        source.connect(gain);
        gain.connect(ctx.destination);
        source.loop = false;
        source.start(0);
        
        monitorSources[which] = source;
        monitorGains[which] = gain;
        
        source.onended = () => { monitorSources[which] = null; monitorGains[which] = null; };
        return true;
    } catch(e) {
        console.warn('Monitor error:', e.message);
        return false;
    }
}

function stopMonitorPlayback(which) {
    if (which) {
        if (monitorSources[which]) { try { monitorSources[which].stop(); } catch(e) {} monitorSources[which] = null; }
        monitorGains[which] = null;
    } else {
        stopMonitorPlayback('a'); stopMonitorPlayback('b');
    }
}

function setMonitorVolume(which, val) {
    if (monitorGains[which]) monitorGains[which].gain.value = val / 100;
}

// ─── AUX PLAYBACK ───
async function playAuxFile(filePath) {
    try {
        if (auxSource) { try { auxSource.stop(); } catch(e) {} }
        const ctx = await getAudioCtx();
        let buf;
        try {
            const base64 = await api.readAudioFile(filePath);
            if (base64) {
                const binary = atob(base64);
                const bytes = new Uint8Array(binary.length);
                for (let i = 0; i < binary.length; i++) bytes[i] = binary.charCodeAt(i);
                buf = bytes.buffer;
            } else throw new Error('readAudioFile returned null');
        } catch(e) {
            const resp = await fetch('file://' + filePath.replace(/\\/g,'/'));
            buf = await resp.arrayBuffer();
        }
        const buffer = await ctx.decodeAudioData(buf);
        auxSource = ctx.createBufferSource();
        auxSource.buffer = buffer;
        auxGain = ctx.createGain();
        // Start at 70% volume (from aux slider)
        const auxVol = parseFloat(document.getElementById('auxVol')?.value || 70) / 100;
        auxGain.gain.value = auxVol;
        // Fade in
        auxGain.gain.setValueAtTime(0, ctx.currentTime);
        auxGain.gain.linearRampToValueAtTime(auxVol, ctx.currentTime + 0.1);
        // Duck main decks briefly
        const duckDuration = Math.min(buffer.duration + 0.5, 5);
        if (monitorGains.a) monitorGains.a.gain.linearRampToValueAtTime(0.2, ctx.currentTime + 0.05);
        if (monitorGains.b) monitorGains.b.gain.linearRampToValueAtTime(0.2, ctx.currentTime + 0.05);
        // Restore after aux ends
        const restoreTime = ctx.currentTime + duckDuration;
        const cf = document.getElementById('crossfader');
        const cfVal = cf ? parseFloat(cf.value) / 100 : 0.5;
        if (monitorGains.a) monitorGains.a.gain.linearRampToValueAtTime(
            parseFloat(document.getElementById('volA').value)/100 * Math.cos(cfVal * Math.PI / 2), restoreTime);
        if (monitorGains.b) monitorGains.b.gain.linearRampToValueAtTime(
            parseFloat(document.getElementById('volB').value)/100 * Math.sin(cfVal * Math.PI / 2), restoreTime);
        
        auxSource.connect(auxGain);
        auxGain.connect(ctx.destination);
        auxSource.start(0);
        auxSource.onended = () => { auxSource = null; auxGain = null; };
    } catch(e) {
        console.warn('AUX playback error:', e.message);
    }
}

// ─── WIRE VOLUME SLIDERS & CROSSFADER ───
function initVolumeUI() {
    const volA = document.getElementById('volA');
    const volB = document.getElementById('volB');
    const cf = document.getElementById('crossfader');
    
    if (volA) {
        volA.addEventListener('input', function() {
            if (monitorGains.a) monitorGains.a.gain.value = this.value / 100;
        });
    }
    if (volB) {
        volB.addEventListener('input', function() {
            if (monitorGains.b) monitorGains.b.gain.value = this.value / 100;
        });
    }
    if (cf) {
        cf.addEventListener('input', function() {
            const val = this.value / 100;
            const gA = Math.cos(val * Math.PI / 2);
            const gB = Math.sin(val * Math.PI / 2);
            if (monitorGains.a) monitorGains.a.gain.value = gA * (parseFloat(volA?.value || 80) / 100);
            if (monitorGains.b) monitorGains.b.gain.value = gB * (parseFloat(volB?.value || 80) / 100);
        });
    }
    
    // AUX deck context menus - set folders
    document.querySelectorAll('.aux-btn').forEach(btn => {
        btn.oncontextmenu = async (e) => {
            e.preventDefault();
            const type = btn.className.includes('jingle') ? 'jingle' :
                         btn.className.includes('advert') ? 'advert' :
                         btn.className.includes('emergency') ? 'emergency' : 'sweeper';
            const result = await api.selectFolder();
            // The selectFolder returns through IPC but we need a custom handler
            const folder = prompt(`Enter folder path for ${type} files:`);
            if (folder) {
                deck.auxFolders[type] = folder;
                appConfig.auxFolders = deck.auxFolders;
                api.saveConfig(appConfig);
            }
        };
    });
    
    // Restore saved aux folders
    if (appConfig.auxFolders) deck.auxFolders = appConfig.auxFolders;
    
    // Wire aux vol slider
    const auxVol = document.getElementById('auxVol');
    if (auxVol) {
        auxVol.addEventListener('input', function() {
            document.getElementById('auxVolLabel').textContent = this.value + '%';
        });
    }
}