// ─── STATIONS / MULTI-ENCODER MANAGER ───
const stations = {
    list: [],
    editingIndex: -1,
    
    init() {
        if (appConfig.stations) this.list = appConfig.stations;
        else if (appConfig.username) {
            // Auto-import from login
            this.list = [{
                name: appConfig.stationName || 'My Station',
                host: appConfig.server || '45.61.59.55',
                port: appConfig.port || 9002,
                username: appConfig.username,
                password: appConfig.password,
                bitrate: appConfig.bitrate || 128
            }];
            this.save();
        }
        this.updateCount();
    },
    
    open() {
        this.render();
        document.getElementById('stationsManager').style.display = 'flex';
    },
    
    showForm(index) {
        this.editingIndex = index !== undefined ? index : -1;
        document.getElementById('sfTitle').textContent = index >= 0 ? 'Edit Station' : 'New Station';
        if (index >= 0 && this.list[index]) {
            const s = this.list[index];
            document.getElementById('sfName').value = s.name || '';
            document.getElementById('sfHost').value = s.host || '';
            document.getElementById('sfPort').value = s.port || 9002;
            document.getElementById('sfUser').value = s.username || '';
            document.getElementById('sfPass').value = s.password || '';
            document.getElementById('sfBitrate').value = s.bitrate || 128;
        } else {
            document.getElementById('sfName').value = '';
            document.getElementById('sfHost').value = appConfig.server || '45.61.59.55';
            document.getElementById('sfPort').value = appConfig.port || 9002;
            document.getElementById('sfUser').value = appConfig.username || '';
            document.getElementById('sfPass').value = '';
            document.getElementById('sfBitrate').value = appConfig.bitrate || 128;
        }
        document.getElementById('stationForm').style.display = 'block';
    },
    
    hideForm() {
        document.getElementById('stationForm').style.display = 'none';
        this.editingIndex = -1;
    },
    
    saveStation() {
        const s = {
            name: document.getElementById('sfName').value || 'Unnamed Station',
            host: document.getElementById('sfHost').value,
            port: parseInt(document.getElementById('sfPort').value) || 9002,
            username: document.getElementById('sfUser').value,
            password: document.getElementById('sfPass').value,
            bitrate: parseInt(document.getElementById('sfBitrate').value) || 128
        };
        if (!s.host || !s.username) { alert('Host and username required'); return; }
        
        if (this.editingIndex >= 0) this.list[this.editingIndex] = s;
        else this.list.push(s);
        
        this.hideForm();
        this.save();
        this.render();
    },
    
    deleteStation(idx) {
        if (confirm(`Remove "${this.list[idx]?.name}"?`)) {
            this.list.splice(idx, 1);
            this.save();
            this.render();
        }
    },
    
    render() {
        const el = document.getElementById('stationList');
        if (this.list.length === 0) {
            el.innerHTML = '<div class="empty-msg">No stations configured. Click "+ Add Station" to add your first stream destination.</div>';
            return;
        }
        el.innerHTML = this.list.map((s, i) =>
            `<div style="background:rgba(13,17,23,.4);border:1px solid rgba(48,54,61,.2);border-radius:6px;padding:8px;display:flex;align-items:center;gap:6px">
                <div style="flex:1">
                    <div style="font-size:12px;font-weight:600;color:#c9d1d9">${s.name || 'Unnamed'}</div>
                    <div style="font-size:10px;color:#64748b">${s.host}:${s.port} — ${s.bitrate}kbps</div>
                </div>
                <button onclick="stations.showForm(${i})" style="padding:3px 7px;border-radius:3px;border:none;font-size:10px;cursor:pointer;background:rgba(255,255,255,.04);color:#8b949e">✏️</button>
                <button onclick="stations.deleteStation(${i})" style="padding:3px 7px;border-radius:3px;border:none;font-size:10px;cursor:pointer;background:rgba(255,255,255,.04);color:#f85149">🗑</button>
            </div>`
        ).join('');
    },
    
    save() {
        appConfig.stations = this.list;
        api.saveConfig(appConfig);
        this.updateCount();
    },
    
    updateCount() {
        const el = document.getElementById('encCount');
        if (el) el.textContent = this.list.length;
    },
    
    getEnabled() {
        // All stations are active (no enable/disable toggle)
        return this.list;
    }
};

// Init on login
setTimeout(() => stations.init(), 1500);