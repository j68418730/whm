// ─── SETTINGS / ENCODER MANAGER ───
const settings = {
    encoders: [],
    editingIndex: -1,
    
    init() {
        // Try to restore saved encoders
        if (appConfig.encoders) {
            this.encoders = appConfig.encoders;
        } else {
            // Import from login config if no encoders exist
            this.importFromLogin();
        }
        this.renderList();
    },
    
    importFromLogin() {
        if (appConfig.username && appConfig.password) {
            this.encoders = [{
                name: 'Main Stream',
                host: appConfig.server || '45.61.59.55',
                port: appConfig.port || 9002,
                username: appConfig.username,
                password: appConfig.password,
                bitrate: appConfig.bitrate || 128,
                enabled: false
            }];
        }
    },
    
    open() {
        this.init();
        document.getElementById('settingsOverlay').style.display = 'flex';
    },
    
    showAddForm(index) {
        if (index !== undefined) {
            this.editingIndex = index;
            const e = this.encoders[index];
            document.getElementById('formTitle').textContent = 'Edit Encoder';
            document.getElementById('encName').value = e.name || '';
            document.getElementById('encHost').value = e.host || '';
            document.getElementById('encPort').value = e.port || 9002;
            document.getElementById('encBitrate').value = e.bitrate || 128;
            document.getElementById('encUser').value = e.username || '';
            document.getElementById('encPass').value = e.password || '';
        } else {
            this.editingIndex = -1;
            document.getElementById('formTitle').textContent = 'New Encoder';
            document.getElementById('encName').value = '';
            document.getElementById('encHost').value = appConfig.server || '45.61.59.55';
            document.getElementById('encPort').value = appConfig.port || 9002;
            document.getElementById('encBitrate').value = appConfig.bitrate || 128;
            document.getElementById('encUser').value = appConfig.username || '';
            document.getElementById('encPass').value = '';
        }
        document.getElementById('encoderForm').style.display = 'block';
    },
    
    hideForm() {
        document.getElementById('encoderForm').style.display = 'none';
        this.editingIndex = -1;
    },
    
    saveEncoder() {
        const enc = {
            name: document.getElementById('encName').value || 'Unnamed',
            host: document.getElementById('encHost').value,
            port: parseInt(document.getElementById('encPort').value) || 9002,
            username: document.getElementById('encUser').value,
            password: document.getElementById('encPass').value,
            bitrate: parseInt(document.getElementById('encBitrate').value) || 128,
            enabled: false
        };
        
        if (!enc.host || !enc.username || !enc.password) {
            alert('Host, username and password are required');
            return;
        }
        
        if (this.editingIndex >= 0) {
            this.encoders[this.editingIndex] = enc;
        } else {
            this.encoders.push(enc);
        }
        
        this.hideForm();
        this.save();
        this.renderList();
    },
    
    deleteEncoder(index) {
        if (confirm('Delete encoder "' + (this.encoders[index]?.name || '') + '"?')) {
            this.encoders.splice(index, 1);
            this.save();
            this.renderList();
        }
    },
    
    toggleEncoder(index) {
        const enc = this.encoders[index];
        if (!enc) return;
        
        if (enc.enabled) {
            // Stop this encoder
            enc.enabled = false;
            if (this._currentSong) {
                // Would need per-encoder stream tracking
            }
            document.getElementById('liveBtn').classList.remove('on');
            document.getElementById('liveBtn').innerHTML = '🔴 <span>Go Live</span>';
            document.getElementById('ssStatus').textContent = 'Offline';
            document.getElementById('st_dot').className = 'dot r';
            document.getElementById('st_text').textContent = 'Offline';
            app.isLive = false;
            api.stopStream();
        } else {
            // Start this encoder
            const active = deckA.playing ? 'a' : deckB.playing ? 'b' : null;
            if (!active) { alert('Start a deck first'); return; }
            const d = active === 'a' ? deckA : deckB;
            
            enc.enabled = true;
            this._currentSong = d.song;
            appConfig.encoderIndex = index;
            
            document.getElementById('liveBtn').classList.add('on');
            document.getElementById('liveBtn').innerHTML = '⏹ <span>Stop Stream</span>';
            document.getElementById('ssStatus').textContent = 'On Air';
            app.isLive = true;
            app.streamStartTime = Date.now();
            
            api.startStream(d.song.path, enc);
            document.getElementById('ssNowPlaying').textContent = d.song.title || 'Unknown';
            app.addHistory(d.song);
        }
        this.save();
        this.renderList();
    },
    
    renderList() {
        const el = document.getElementById('encoderList');
        if (this.encoders.length === 0) {
            el.innerHTML = '<div class="empty-msg">No encoders configured. Click "+ Add Encoder" to create your first stream connection.</div>';
            return;
        }
        el.innerHTML = this.encoders.map((e, i) =>
            `<div style="background:rgba(13,17,23,.4);border:1px solid rgba(48,54,61,.2);border-radius:6px;padding:8px;display:flex;align-items:center;gap:8px">
                <div style="flex:1">
                    <div style="font-size:13px;font-weight:600;color:#c9d1d9">${e.name || 'Unnamed'}</div>
                    <div style="font-size:11px;color:#64748b">${e.host}:${e.port} — ${e.bitrate} kbps</div>
                </div>
                <button onclick="settings.toggleEncoder(${i})" style="padding:4px 10px;border-radius:4px;border:none;font-size:11px;cursor:pointer;font-weight:600;${e.enabled ? 'background:rgba(63,185,80,.12);color:#3fb950' : 'background:rgba(0,140,255,.1);color:#008cff'}">${e.enabled ? '⏹ Stop' : '▶ Start'}</button>
                <button onclick="settings.showAddForm(${i})" style="padding:4px 8px;border-radius:4px;border:none;font-size:11px;cursor:pointer;background:rgba(255,255,255,.04);color:#8b949e">✏️</button>
                <button onclick="settings.deleteEncoder(${i})" style="padding:4px 8px;border-radius:4px;border:none;font-size:11px;cursor:pointer;background:rgba(255,255,255,.04);color:#f85149">🗑</button>
            </div>`
        ).join('');
    },
    
    save() {
        appConfig.encoders = this.encoders;
        api.saveConfig(appConfig);
    }
};

// Wire navigation event from File menu
api.onNavigate(page => {
    if (page === 'settings') settings.open();
});

// Wire settings button in toolbar
document.querySelector('.toolbar-left')?.insertAdjacentHTML('beforeend',
    `<button onclick="settings.open()" title="Settings" style="margin-left:4px">⚙ <span>Settings</span></button>`
);