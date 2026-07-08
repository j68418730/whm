// ─── TAB SYSTEM ───
function initTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tab = btn.dataset.tab;
            if (['dashboarda','dashboardb','dashboardc','crossfade','encoders','schedule','events','requests','ai'].includes(tab)) {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                document.querySelectorAll('.dashboard').forEach(d => d.classList.remove('active'));
                const dashMap = {
                    dashboarda:'dashboardA', dashboardb:'dashboardB', dashboardc:'dashboardC',
                    crossfade:'dashboardA', encoders:'dashboardA', schedule:'dashboardB',
                    events:'dashboardB', requests:'dashboardB', ai:'dashboardB'
                };
                const target = document.getElementById(dashMap[tab]);
                if (target) target.classList.add('active');
                if (tab === 'crossfade') { crossfade.showSettings(); }
                if (tab === 'encoders') { stations.open(); }
            }
        });
    });
}

// ─── PANEL DRAG (grip handles) ───
function initPanelDrag() {
    document.querySelectorAll('.panel-hdr').forEach(hdr => {
        hdr.addEventListener('mousedown', e => {
            if (e.target.closest('.win-btns')) return;
            e.preventDefault();
            const panel = hdr.closest('.panel');
            if (!panel) return;
            const grid = panel.closest('.dash-a-grid, .dashb-grid');
            if (!grid) return;
            const ghost = panel.cloneNode(true);
            ghost.style.cssText = 'position:fixed;pointer-events:none;opacity:0.5;z-index:1000;width:'+panel.offsetWidth+'px';
            document.body.appendChild(ghost);
            const shiftX = e.clientX - panel.getBoundingClientRect().left;
            const shiftY = e.clientY - panel.getBoundingClientRect().top;
            
            function onMouseMove(ev) {
                ghost.style.left = (ev.clientX - shiftX) + 'px';
                ghost.style.top = (ev.clientY - shiftY) + 'px';
                grid.querySelectorAll('.panel').forEach(p => p.style.borderColor = '');
                const target = document.elementFromPoint(ev.clientX, ev.clientY);
                if (target) {
                    const targetPanel = target.closest('.panel');
                    if (targetPanel && targetPanel !== panel && !targetPanel.classList.contains('full')) {
                        targetPanel.style.borderColor = '#58a6ff';
                    }
                }
            }
            function onMouseUp(ev) {
                ghost.remove();
                grid.querySelectorAll('.panel').forEach(p => p.style.borderColor = '');
                const target = document.elementFromPoint(ev.clientX, ev.clientY);
                if (target) {
                    const targetPanel = target.closest('.panel');
                    if (targetPanel && targetPanel !== panel && !targetPanel.classList.contains('full')) {
                        const parent = targetPanel.parentNode;
                        const panelsArr = Array.from(parent.querySelectorAll(':scope > .panel'));
                        const fromIdx = panelsArr.indexOf(panel);
                        const toIdx = panelsArr.indexOf(targetPanel);
                        if (fromIdx >= 0 && toIdx >= 0) {
                            if (fromIdx < toIdx) {
                                targetPanel.after(panel);
                            } else {
                                targetPanel.before(panel);
                            }
                            grid.dispatchEvent(new Event('panel-reordered'));
                        }
                    }
                }
                document.removeEventListener('mousemove', onMouseMove);
                document.removeEventListener('mouseup', onMouseUp);
            }
            document.addEventListener('mousemove', onMouseMove);
            document.addEventListener('mouseup', onMouseUp);
        });
    });
}

// ─── WIN BUTTONS ───
function initWinBtns() {
    document.querySelectorAll('.win-min').forEach(btn => {
        btn.addEventListener('click', function() {
            const panel = this.closest('.panel');
            const body = panel.querySelector('.panel-body');
            if (body) {
                body.style.display = body.style.display === 'none' ? '' : 'none';
                this.textContent = body.style.display === 'none' ? '▢' : '─';
            }
        });
    });
    document.querySelectorAll('.win-max').forEach(btn => {
        btn.addEventListener('click', function() {
            const panel = this.closest('.panel');
            if (panel.classList.contains('maximized')) {
                panel.classList.remove('maximized');
                panel.style.position = '';
                panel.style.inset = '';
                panel.style.zIndex = '';
                this.textContent = '□';
            } else {
                panel.classList.add('maximized');
                panel.style.position = 'fixed';
                panel.style.inset = '60px 12px 32px 12px';
                panel.style.zIndex = '300';
                this.textContent = '▣';
            }
        });
    });
    document.querySelectorAll('.win-close').forEach(btn => {
        btn.addEventListener('click', function() {
            const panel = this.closest('.panel');
            panel.dataset.hidden = 'true';
            panel.style.display = 'none';
            if (typeof widgetManager !== 'undefined') widgetManager.renderPalette();
        });
    });
}

// ─── CLOCK BAR ───
function initClockBar() {
    updateClockBar();
    setInterval(updateClockBar, 1000);
}
function updateClockBar() {
    const now = new Date();
    document.getElementById('clockLocal').textContent = now.toLocaleTimeString('en-US', { hour12: false });
    document.getElementById('clockUtc').textContent = now.toUTCString().slice(17,25);
    // Panel clocks
    const pc = document.getElementById('panelClockLocal');
    if (pc) pc.textContent = now.toLocaleTimeString('en-US', { hour12: false });
    const pu = document.getElementById('panelClockUtc');
    if (pu) pu.textContent = now.toUTCString().slice(17,25);
}

// ─── WIDGET MANAGER ───
const widgetManager = {
    init() {
        document.querySelectorAll('.panel[id]').forEach(p => {
            const id = p.id;
            const label = p.querySelector('.panel-hdr span')?.textContent?.trim() || id;
            this.widgets[id] = { id, label: label.replace('⠿','').trim(), visible: true };
        });
    },
    widgets: {},
    toggle(id) {
        if (this.widgets[id]) {
            this.widgets[id].visible = !this.widgets[id].visible;
            const el = document.getElementById(id);
            if (el) el.style.display = this.widgets[id].visible ? '' : 'none';
            this.renderPalette();
        }
    },
    renderPalette() {
        const el = document.getElementById('widgetList');
        if (!el) return;
        el.innerHTML = Object.values(this.widgets).map(w =>
            `<div class="widget-item" onclick="widgetManager.toggle('${w.id}')">
                <div class="wi-toggle ${w.visible?'on':''}"></div>
                <span>${w.label}</span>
            </div>`
        ).join('');
    },
    closeAll() {
        Object.keys(this.widgets).forEach(id => {
            this.widgets[id].visible = false;
            const el = document.getElementById(id);
            if (el) el.style.display = 'none';
        });
        this.renderPalette();
    },
    restoreDefault() {
        Object.keys(this.widgets).forEach(id => {
            this.widgets[id].visible = true;
            const el = document.getElementById(id);
            if (el) el.style.display = '';
        });
        this.renderPalette();
    }
};

// ─── APP CONTROLLER ───
const app = {
    isLive: false,
    isRecording: false,
    streamStartTime: null,
    history: [],
    requests: [],
    notInLibrary: [],
    requestTimerMin: 5,
    unavailableAlerted: false,
    
    init() {
        initTabs();
        initPanelDrag();
        initWinBtns();
        initClockBar();
        clock.start();
        lib.init();
        queue.startAutoSync();
        this.loadStats();
        this.pollStatus();
        this.pollAutoDj();
        this.pollSchedule();
        this.pollRequests();
        this.bindShortcuts();
        this.bindCrossfade();
        this.restoreSession();
        
        // Init widgets after DOM is ready
        setTimeout(() => widgetManager.init(), 500);
        
        api.onNavigate(page => {
            if (page === 'settings') document.getElementById('settingsOverlay').style.display = 'flex';
        });
        api.onBroadcastLive(() => this.toggleStream());
        api.onBroadcastStop(() => { if (this.isLive) this.toggleStream(); });
        api.onBroadcastMic(() => this.toggleMic());
        api.onMenuSave(() => app.saveQueue());
        api.onMenuLoad(() => app.loadQueue());
        api.onShowShortcuts(() => document.getElementById('shortcutsOverlay').style.display = 'flex');
        api.onManageAux(() => app.showAuxManager());
        api.onTogglePipeline(() => { if (typeof pipeline !== 'undefined') pipeline.toggle(); });
        api.onWindowCloseAll(() => widgetManager.closeAll());
        api.onWindowRestore(() => widgetManager.restoreDefault());
        api.onWindowToggle(p => {
            const map = {
                'deck-a':'panelDeckA','deck-b':'panelDeckB','playlist':'panelPlaylist',
                'requests':'panelRequests','voice-fx':'panelVoice','volume':'panelVolume',
                'fade-control':'panelFadeControl','encoders':'panelEncoders',
                'statistics':'panelStats','scheduler':'panelScheduler',
                'event-log':'panelEvents','ftp-log':'panelFtp','clock':'panelClock'
            };
            widgetManager.toggle(map[p] || p);
        });
        api.onSwitchDashboard(d => app.switchDashboard(d));
        api.onWorkspaceSave(() => app.saveWorkspace());
        api.onWorkspaceLoad(() => app.loadWorkspace());
        api.onPlayerPlay(() => { if (typeof deck !== 'undefined') deck.play(); });
        api.onPlayerPause(() => { if (typeof deck !== 'undefined') deck.toggle(deckA.playing ? 'a' : deckB.playing ? 'b' : 'a'); });
        api.onPlayerStop(() => { if (typeof deck !== 'undefined') { deck.stop('a'); deck.stop('b'); } });
        api.onPlayerNext(() => { if (typeof deck !== 'undefined') { if (deckA.playing) deck.next('a'); else deck.next('b'); } });
        api.onToolRecord(() => app.toggleRecording());
        
        if (typeof initDeckUI === 'function') initDeckUI();
        if (typeof initVolumeUI === 'function') initVolumeUI();
        if (typeof initLibSearch === 'function') initLibSearch();
        
        api.onScanStart(() => {});
        api.onScanComplete(d => { lib.init(); this.loadStats(); });
        
        document.getElementById('shortcutsOverlay').onclick = e => {
            if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
        };
        document.getElementById('metaDialog').onclick = e => {
            if (e.target === e.currentTarget) e.currentTarget.style.display = 'none';
        };
        
        eventLog.log('🔌', 'Session started — Planet Hosts Studio');
    },
    
    toggleWidgetPalette() {
        const el = document.getElementById('widgetPalette');
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
        if (el.style.display === 'block') widgetManager.renderPalette();
    },
    
    addTimezone() {
        const tz = prompt('Enter timezone (e.g. America/New_York):');
        if (tz) {
            const bar = document.getElementById('clockBar');
            const cluster = bar.querySelector('.clock-cluster');
            const div = document.createElement('div');
            div.className = 'clock-item extra';
            div.style.color = '#a855f7';
            div.innerHTML = `<span class="clock-val">??:??</span><span class="clock-tz">${tz.split('/').pop()}</span>`;
            cluster.appendChild(div);
        }
    },
    
    bindCrossfade() {
        const slider = document.getElementById('crossfadeSlider');
        const label = document.getElementById('crossfadeLabel');
        const saved = appConfig.crossfadeDuration || 3;
        if (slider) { slider.value = saved; label.textContent = saved + 's'; }
        crossfadeDuration = saved;
        if (slider) {
            slider.oninput = () => {
                const val = parseFloat(slider.value);
                label.textContent = val + 's';
                crossfadeDuration = val;
                appConfig.crossfadeDuration = val;
                api.saveConfig(appConfig);
            };
        }
    },
    
    peakListeners: 0,
    
    showAuxManager() {
        const el = document.getElementById('auxFolderList');
        const types = [
            {type:'jingle', label:'Jingle Deck', icon:'🔔'},
            {type:'advert', label:'Advert Deck', icon:'📢'},
            {type:'emergency', label:'Emergency Deck', icon:'🚨'},
            {type:'sweeper', label:'Sweeper Deck', icon:'🔊'}
        ];
        el.innerHTML = types.map(t => {
            const folder = deck.auxFolders[t.type];
            return `<div style="display:flex;align-items:center;gap:6px;background:rgba(13,17,23,.4);border:1px solid rgba(48,54,61,.2);border-radius:6px;padding:8px">
                <span style="font-size:16px">${t.icon}</span>
                <div style="flex:1"><div style="font-size:12px;font-weight:600;color:#c9d1d9">${t.label}</div>
                <div style="font-size:10px;color:#64748b">${folder || 'No folder set'}</div></div>
                ${folder ? `<button onclick="app.clearAuxFolder('${t.type}')" style="padding:3px 8px;border-radius:3px;border:none;background:rgba(248,81,73,.1);color:#f85149;cursor:pointer;font-size:10px">Clear</button>` : ''}
                <button onclick="app.setAuxFolder('${t.type}')" style="padding:3px 8px;border-radius:3px;border:none;background:rgba(0,140,255,.1);color:#008cff;cursor:pointer;font-size:10px">${folder ? 'Change' : 'Set'}</button>
            </div>`;
        }).join('');
        document.getElementById('auxManager').style.display = 'flex';
    },
    
    async setAuxFolder(type) {
        const path = await api.pickFolder();
        if (path) {
            deck.auxFolders[type] = path;
            appConfig.auxFolders = deck.auxFolders;
            await api.saveConfig(appConfig);
            this.showAuxManager();
        }
    },
    
    clearAuxFolder(type) {
        delete deck.auxFolders[type];
        appConfig.auxFolders = deck.auxFolders;
        api.saveConfig(appConfig);
        this.showAuxManager();
    },
    
    logout() {
        if (confirm('Log out and switch accounts?')) {
            api.saveConfig({});
            document.getElementById('appMain').style.display = 'none';
            document.getElementById('loginScreen').style.display = 'flex';
            document.getElementById('lgEmail').value = '';
            document.getElementById('lgPass').value = '';
            this.isLive = false;
            if (this.isLive) api.stopStream();
            queue.clear();
        }
    },
    
    toggleMic() {
        const btn = document.getElementById('micBtn');
        if (this.micActive) {
            this.micActive = false;
            btn.style.color = '';
            document.getElementById('micStatus').textContent = 'Off';
            document.getElementById('pttBtn').className = 'vx-btn';
            api.stopMicStream();
            eventLog.log('🎤', 'Microphone deactivated');
        } else {
            this.micActive = true;
            btn.style.color = '#3fb950';
            document.getElementById('micStatus').textContent = '🎤 On';
            eventLog.log('🎤', 'Microphone activated');
        }
    },
    
    toggleAutoDj() {
        alert('AutoDJ status: Active. Use the server panel to configure AutoDJ rotation.');
    },
    
    toggleRecording() {
        if (!this.isRecording) {
            api.startRecord(appConfig.recordPath || appConfig.musicFolder, appConfig);
            this.isRecording = true;
            eventLog.log('●', 'Recording started');
            document.querySelectorAll('[id*="recBtn"],[id*="record"]').forEach(b => {
                if (b.tagName === 'BUTTON') b.textContent = '⏹ Stop Rec';
            });
        } else {
            api.stopRecord();
            this.isRecording = false;
            eventLog.log('●', 'Recording stopped');
            document.querySelectorAll('[id*="recBtn"],[id*="record"]').forEach(b => {
                if (b.tagName === 'BUTTON') b.textContent = '⏺ Record';
            });
        }
    },
    
    switchDashboard(d) {
        const tab = document.querySelector(`.tab-btn[data-tab="dashboard${d}"]`);
        if (tab) tab.click();
    },
    
    saveWorkspace() {
        const state = {};
        document.querySelectorAll('.panel[id]').forEach(p => {
            state[p.id] = { display: p.style.display || '' };
        });
        localStorage.setItem('workspace', JSON.stringify(state));
        eventLog.log('💾', 'Workspace saved');
    },
    
    loadWorkspace() {
        const raw = localStorage.getItem('workspace');
        if (!raw) { eventLog.log('⚠', 'No saved workspace found'); return; }
        try {
            const state = JSON.parse(raw);
            Object.entries(state).forEach(([id, s]) => {
                const el = document.getElementById(id);
                if (el) el.style.display = s.display || '';
                if (widgetManager.widgets[id]) widgetManager.widgets[id].visible = !s.display || s.display !== 'none';
            });
            widgetManager.renderPalette();
            eventLog.log('📂', 'Workspace loaded');
        } catch(e) { eventLog.log('⚠', 'Failed to load workspace'); }
    },
    
    bindShortcuts() {
        document.addEventListener('keydown', e => {
            if (e.key === '?' && !e.ctrlKey && !e.metaKey) {
                e.preventDefault();
                const el = document.getElementById('shortcutsOverlay');
                el.style.display = el.style.display === 'none' ? 'flex' : 'none';
            }
            if (e.key === '`' || e.key === '~') {
                e.preventDefault();
                this.activatePtt(true);
            }
        });
        document.addEventListener('keyup', e => {
            if (e.key === '`' || e.key === '~') {
                e.preventDefault();
                this.activatePtt(false);
            }
        });
        const pttBtn = document.getElementById('pttBtn');
        if (pttBtn) {
            pttBtn.addEventListener('mousedown', () => this.activatePtt(true));
            pttBtn.addEventListener('mouseup', () => this.activatePtt(false));
            pttBtn.addEventListener('mouseleave', () => this.activatePtt(false));
        }
        this.loadMicDevices();
    },
    
    async loadMicDevices() {
        try {
            const devices = await api.getMicDevices();
            const sel = document.getElementById('micDevice');
            if (!sel) return;
            sel.innerHTML = '';
            if (devices.length === 0) {
                sel.innerHTML = '<option value="">No mic detected</option>';
            } else {
                devices.forEach(d => {
                    const opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    sel.appendChild(opt);
                });
                if (appConfig.micDevice) sel.value = appConfig.micDevice;
            }
        } catch(e) {
            const sel = document.getElementById('micDevice');
            if (sel) sel.innerHTML = '<option value="">Mic unavailable</option>';
        }
        const gain = document.getElementById('micGain');
        if (gain) {
            gain.oninput = function() {
                this.nextElementSibling.textContent = this.value + '%';
                appConfig.micVolume = parseInt(this.value);
                api.saveConfig(appConfig);
            };
        }
        const duck = document.getElementById('duckLevel');
        if (duck) {
            duck.oninput = function() {
                this.nextElementSibling.textContent = this.value + '%';
                appConfig.duckLevel = parseInt(this.value);
                api.saveConfig(appConfig);
            };
        }
        if (appConfig.micVolume && gain) gain.value = appConfig.micVolume;
        if (appConfig.duckLevel && duck) duck.value = appConfig.duckLevel;
    },
    
    pttActive: false,
    async activatePtt(active) {
        if (active === this.pttActive) return;
        this.pttActive = active;
        const activeDeck = deckA.playing ? 'a' : deckB.playing ? 'b' : null;
        if (!activeDeck || !this.isLive) return;
        const d = activeDeck === 'a' ? deckA : deckB;
        const micDev = document.getElementById('micDevice')?.value;
        if (active && micDev) {
            document.getElementById('micStatus').textContent = '🎤 ON AIR';
            document.getElementById('micStatus').style.color = '#3fb950';
            const ptt = document.getElementById('pttBtn');
            if (ptt) { ptt.className = 'vx-btn ptt-active'; ptt.textContent = '🎤 Mic Live (Release)'; }
            appConfig.micDevice = micDev;
            await api.saveConfig(appConfig);
            await api.stopStream();
            await api.startMicStream(d.song.path, appConfig);
            await api.setPtt(true);
        } else {
            document.getElementById('micStatus').textContent = 'off';
            document.getElementById('micStatus').style.color = '#64748b';
            const ptt = document.getElementById('pttBtn');
            if (ptt) { ptt.className = 'vx-btn'; ptt.textContent = '🎤 Push To Talk (Hold ~ key)'; }
            await api.stopMicStream();
            await api.setPtt(false);
            if (this.isLive && d.song) await api.startStream(d.song.path, appConfig);
        }
    },
    
    async toggleRecord() {
        const btn = document.getElementById('recordBtn');
        if (!this.isRecording) {
            this.isRecording = true;
            btn.textContent = '⏹ Stop Rec';
            btn.style.background = 'rgba(63,185,80,.1)';
            btn.style.color = '#3fb950';
            const dir = require('path').join(require('os').homedir(), '.planethosts-studio', 'recordings');
            const name = `show_${new Date().toISOString().slice(0,19).replace(/[T:]/g,'-')}.mp3`;
            const out = require('path').join(dir, name);
            await api.startRecord('http://source:OVc3FNg8BgBcfnLX@localhost:9000/', out);
            eventLog.log('🔴', 'Recording started');
        } else {
            this.isRecording = false;
            btn.textContent = '🔴 Record';
            btn.style.background = 'rgba(248,81,73,.1)';
            btn.style.color = '#f85149';
            await api.stopRecord();
            eventLog.log('⏹', 'Recording stopped');
        }
    },
    
    saveSession() {
        const session = {
            queue: queue.items,
            deckA: deckA.song,
            deckB: deckB.song,
            crossfade: crossfadeDuration,
            timestamp: Date.now()
        };
        localStorage.setItem('studio_session', JSON.stringify(session));
    },
    
    restoreSession() {
        try {
            const data = localStorage.getItem('studio_session');
            if (data) {
                const session = JSON.parse(data);
                if (session.queue && session.queue.length > 0) {
                    session.queue.forEach(s => queue.items.push(s));
                    queue.render();
                }
                if (session.deckA) deck.load('a', session.deckA);
                if (session.deckB) deck.load('b', session.deckB);
            }
        } catch(e) {}
    },
    
    async loadStats() {
        const stats = await api.getStats();
        const cfg = await api.getConfig();
        const songs = document.getElementById('statusSongs');
        const folders = document.getElementById('statusFolders');
        if (songs) songs.textContent = (stats.songs || 0) + ' songs';
        if (folders) folders.textContent = ((cfg.musicFolders||[]).length) + ' folders';
    },
    
    async toggleStream() {
        const btn = document.getElementById('liveBtn');
        if (!this.isLive) {
            const active = deckA.playing ? 'a' : deckB.playing ? 'b' : null;
            if (!active) { alert('Start a deck first'); return; }
            const d = active === 'a' ? deckA : deckB;
            const encoders = stations.getEnabled();
            if (encoders.length === 0) {
                if (!confirm('No stations configured. Add one now?')) return;
                stations.open();
                return;
            }
            this.isLive = true;
            btn.classList.add('on');
            btn.innerHTML = `⏹ <span>Stop (${encoders.length})</span>`;
            this.streamStartTime = Date.now();
            document.getElementById('ssStatus').textContent = `On Air (${encoders.length})`;
            document.getElementById('encCount').textContent = encoders.length;
            await api.startStream(d.song.path, encoders);
            document.getElementById('ssNowPlaying').textContent = d.song.title || 'Unknown';
            this.addHistory(d.song);
            eventLog.log('🔴', `Stream started — ${encoders.length} encoder(s) active`);
        } else {
            await api.stopStream();
            this.isLive = false;
            btn.classList.remove('on');
            btn.innerHTML = '🔴 <span>Go Live</span>';
            document.getElementById('ssStatus').textContent = 'Offline';
            eventLog.log('⏹', 'Stream stopped');
        }
    },
    
    addHistory(song) {
        if (!song) return;
        this.history.unshift({ title: song.title || 'Unknown', artist: song.artist || '', time: new Date() });
        this.renderHistory();
    },
    
    renderHistory() {
        const el = document.getElementById('histBody');
        if (!el) return;
        if (this.history.length === 0) {
            el.innerHTML = '<div class="empty-msg">No tracks played yet</div>';
        } else {
            el.innerHTML = this.history.slice(0, 20).map(h =>
                `<div class="hist-item"><span class="t">${h.title} — ${h.artist}</span><span class="time">${h.time.toLocaleTimeString()}</span></div>`
            ).join('');
        }
        const hc = document.getElementById('histBodyC');
        if (hc) {
            hc.innerHTML = this.history.slice(0, 10).map(h =>
                `<div style="padding:2px 0;border-bottom:1px solid rgba(48,54,61,.06)">${h.title}</div>`
            ).join('');
        }
    },
    
    clearQueue() { queue.clear(); },
    shuffleQueue() { queue.shuffle(); },
    saveQueue() { queue.save(); },
    loadQueue() { queue.load(); },
    
    pollAutoDj() {
        this._fetchAutoDj();
        setInterval(() => this._fetchAutoDj(), 8000);
    },
    
    async _fetchAutoDj() {
        try {
            const d = await apiGet('/connector/station/4/autodj');
            if (d.success) {
                const el = document.getElementById('autodjBody');
                const s = d.data;
                const isDjLive = app.isLive;
                if (isDjLive) {
                    document.getElementById('autodjStatus').textContent = '🎤 DJ On Air';
                    if (el) el.innerHTML = `<div class="now-playing"><div class="np-label">DJ Broadcasting</div><div class="np-title">AutoDJ paused — will resume when you disconnect</div></div>`;
                } else {
                    document.getElementById('autodjStatus').textContent = s.enabled ? '🟢 AutoDJ Active' : '⚫ AutoDJ Paused';
                    let html = '';
                    if (s.current_song) html += `<div class="now-playing"><div class="np-label">Now Playing</div><div class="np-title">${s.current_song}</div></div>`;
                    if (s.playlist) html += `<div style="font-size:10px;color:#64748b;padding:2px 6px">Playlist: ${s.playlist}</div>`;
                    if (s.next_songs && s.next_songs.length > 0) {
                        html += `<div style="font-size:10px;color:#8b949e;padding:4px 6px 2px;font-weight:600">Up Next:</div>`;
                        s.next_songs.forEach(sng => {
                            html += `<div class="next-song">▶ ${sng.title || '?'}${sng.artist ? ' — '+sng.artist : ''}</div>`;
                        });
                    }
                    if (el) el.innerHTML = html || '<div class="empty-msg">No AutoDJ data</div>';
                }
            } else {
                const el = document.getElementById('autodjBody');
                if (el) el.innerHTML = '<div class="empty-msg">No AutoDJ data</div>';
            }
        } catch(e) {
            const el = document.getElementById('autodjBody');
            if (el) el.innerHTML = '<div class="empty-msg">Could not reach server</div>';
        }
    },
    
    pollSchedule() {
        this._fetchSchedule();
        setInterval(() => this._fetchSchedule(), 15000);
    },
    
    async _fetchSchedule() {
        try {
            const d = await apiGet('/connector/station/4/schedule');
            if (d.success && d.data && d.data.length > 0) {
                const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                const html = d.data.slice(0, 15).map(e =>
                    `<div class="sched-event"><span class="day">${days[e.day_of_week] || e.day_of_week}</span><span class="time">${e.start_time?.substring(0,5)}-${e.end_time?.substring(0,5)}</span><span class="show">${e.show_name || e.dj_name || 'Show'}${e.dj_name ? ' — '+e.dj_name : ''}</span></div>`
                ).join('');
                document.getElementById('schedBody').innerHTML = html;
            } else {
                document.getElementById('schedBody').innerHTML = '<div class="empty-msg">No shows scheduled</div>';
            }
        } catch(e) {
            document.getElementById('schedBody').innerHTML = '<div class="empty-msg">Could not reach server</div>';
        }
    },
    
    pollRequests() {
        setInterval(async () => {
            try {
                const d = await apiGet('/connector/station/4/requests');
                const el = document.getElementById('reqBody');
                const unavailEl = document.getElementById('reqUnavailBody');
                if (d.success && d.data && d.data.length > 0) {
                    this.requests = d.data;
                    const pending = d.data.filter(r => r.status === 'pending');
                    const now = Date.now();
                    document.getElementById('reqCount').textContent = pending.length;
                    
                    if (el) {
                        el.innerHTML = pending.slice(0, 20).map(r => {
                            const elapsed = now - new Date(r.created_at).getTime();
                            const elapsedMin = elapsed / 60000;
                            const remaining = Math.max(0, this.requestTimerMin - elapsedMin);
                            const timerColor = remaining < 1 ? '#f85149' : remaining < 2 ? '#d29922' : '#8b949e';
                            const timerText = remaining <= 0 ? 'Auto...' : Math.ceil(remaining) + 'm';
                            return `<div class="hist-item">
                                <span class="t">🎵 ${r.title || '?'} — ${r.artist || ''}${r.guest_name ? ' (from '+r.guest_name+')' : ''}</span>
                                <span style="color:${timerColor};font-size:8px;font-weight:600;min-width:32px;text-align:right">${timerText}</span>
                                <div style="display:flex;gap:3px;flex-shrink:0">
                                    <button onclick="app.approveRequest(${r.id},'${(r.title||'').replace(/'/g,"\\'")}','${(r.artist||'').replace(/'/g,"\\'")}')" style="background:rgba(63,185,80,.1);border:none;color:#3fb950;cursor:pointer;font-size:9px;padding:1px 5px;border-radius:3px">✓</button>
                                    <button onclick="app.denyRequest(${r.id})" style="background:rgba(248,81,73,.1);border:none;color:#f85149;cursor:pointer;font-size:9px;padding:1px 5px;border-radius:3px">✕</button>
                                </div>
                            </div>`;
                        }).join('');
                    }
                    
                    // Auto-process expired requests
                    pending.forEach(r => {
                        const elapsed = now - new Date(r.created_at).getTime();
                        const elapsedMin = elapsed / 60000;
                        if (elapsedMin >= this.requestTimerMin && this.requestTimerMin > 0) {
                            this.processRequestTimer(r);
                        }
                    });
                    
                    // Show unavailable count
                    this.renderUnavailableRequests();
                } else {
                    if (el) el.innerHTML = '<div class="empty-msg">No pending requests</div>';
                    document.getElementById('reqCount').textContent = '0';
                }
            } catch(e) {}
        }, 10000);
    },
    
    async processRequestTimer(r) {
        if (r._processed) return;
        r._processed = true;
        const q = (r.title + ' ' + (r.artist || '')).trim();
        if (!q) return;
        try {
            const results = await api.searchSongs(q);
            const inLibrary = results && results.length > 0;
            if (inLibrary) {
                await apiPost('/connector/station/4/requests', {request_id: r.id, action: 'approve'});
                queue.add({title: r.title, artist: r.artist});
                eventLog.log('✓', `Auto-queued: ${r.title}`);
            } else {
                if (!this.notInLibrary.find(x => x.id === r.id)) {
                    this.notInLibrary.push(r);
                    this.renderUnavailableRequests();
                    if (!this.unavailableAlerted) {
                        this.unavailableAlerted = true;
                        eventLog.log('🔴', `Unavailable request: ${r.title}`);
                        const flash = document.getElementById('reqUnavailBadge');
                        if (flash) { flash.style.display = 'inline'; flash.textContent = this.notInLibrary.length; }
                    }
                }
            }
        } catch(e) {}
    },
    
    renderUnavailableRequests() {
        const el = document.getElementById('reqUnavailBody');
        const badge = document.getElementById('reqUnavailBadge');
        if (!el) return;
        if (this.notInLibrary.length === 0) {
            el.innerHTML = '<div class="empty-msg" style="color:#64748b">No unavailable requests</div>';
            if (badge) badge.style.display = 'none';
            this.unavailableAlerted = false;
            return;
        }
        if (badge) { badge.style.display = 'inline'; badge.textContent = this.notInLibrary.length; }
        el.innerHTML = this.notInLibrary.slice(-20).map(r =>
            `<div class="unavail-item" style="display:flex;align-items:center;gap:4px;padding:3px 4px;border-bottom:1px solid rgba(248,81,73,.08)">
                <span style="color:#f85149;font-size:8px">●</span>
                <span style="flex:1;color:#f85149;font-size:10px">🎵 ${r.title || '?'}${r.artist ? ' — '+r.artist : ''}${r.guest_name ? ' ('+r.guest_name+')' : ''}</span>
                <button onclick="app.approveRequest(${r.id},'${(r.title||'').replace(/'/g,"\\'")}','${(r.artist||'').replace(/'/g,"\\'")}')" style="background:rgba(63,185,80,.1);border:none;color:#3fb950;cursor:pointer;font-size:8px;padding:1px 4px;border-radius:3px">✓</button>
                <button onclick="app.denyRequest(${r.id})" style="background:rgba(248,81,73,.1);border:none;color:#f85149;cursor:pointer;font-size:8px;padding:1px 4px;border-radius:3px">✕</button>
            </div>`
        ).join('');
    },
    
    async approveRequest(id, title, artist) {
        try {
            const d = await apiPost('/connector/station/4/requests', {request_id: id, action: 'approve'});
            if (d.success) { queue.add({title, artist}); eventLog.log('✓', `Request approved: ${title}`); }
        } catch(e) {}
    },
    
    async denyRequest(id) {
        try {
            const d = await apiPost('/connector/station/4/requests', {request_id: id, action: 'deny'});
            if (d.success) eventLog.log('✕', `Request denied`);
        } catch(e) {}
    },
    
    pollStatus() {
        setInterval(async () => {
            try {
                const d = await apiGet('/connector/station/4/status');
                if (d.success) {
                    const n = d.data.listeners || 0;
                    if (n > this.peakListeners) this.peakListeners = n;
                    document.getElementById('tbListeners').textContent = `👥 ${n}`;
                    document.getElementById('ssListeners').textContent = n;
                    document.getElementById('ssPeak').textContent = this.peakListeners;
                    document.getElementById('statCurrent').textContent = n;
                    document.getElementById('statPeak').textContent = this.peakListeners;
                    document.getElementById('statNowPlaying').textContent = d.data.current_song || '—';
                    document.getElementById('dbListeners').textContent = n;
                    document.getElementById('dbPeak').textContent = this.peakListeners;
                    this.renderEncoderList();
                }
            } catch(e) {}
        }, 5000);
    },
    
    renderEncoderList() {
        const el = document.getElementById('dbEncoders');
        if (!el) return;
        el.innerHTML = stations.list.length === 0
            ? '<div class="empty-msg">No encoders configured</div>'
            : stations.list.map(s =>
                `<div style="display:flex;justify-content:space-between;padding:2px 4px;border-bottom:1px solid rgba(48,54,61,.04)">
                    <span>${s.name || 'Unnamed'}</span>
                    <span style="color:${this.isLive ? '#3fb950' : '#64748b'}">${this.isLive ? '✓ Connected' : '○ Standby'}</span>
                    <span style="color:#475569">${s.bitrate}k</span>
                </div>`
            ).join('');
    }
};

// ─── CLOCK ───
const clock = {
    use24h: true,
    start() {
        if (appConfig.use24h !== undefined) this.use24h = appConfig.use24h;
        this.update();
        setInterval(() => this.update(), 1000);
        const el = document.getElementById('tbClock');
        if (el) {
            el.onclick = () => {
                this.use24h = !this.use24h;
                appConfig.use24h = this.use24h;
                api.saveConfig(appConfig);
                this.update();
            };
            el.title = 'Click to toggle 12/24h';
            el.style.cursor = 'pointer';
        }
    },
    update() {
        const now = new Date();
        const el = document.getElementById('tbClock');
        if (el) el.textContent = now.toLocaleTimeString('en-US', { hour12: !this.use24h });
    }
};

// ─── UPTIME ───
const perfStart = Date.now();
setInterval(() => {
    const ssEl = document.getElementById('ssUptime');
    const statusEl = document.getElementById('statusUptime');
    const clockUp = document.getElementById('clockUptime');
    if (!ssEl || !statusEl) return;
    if (app.isLive && app.streamStartTime) {
        const s = Math.floor((Date.now() - app.streamStartTime) / 1000);
        const fmt = String(Math.floor(s/3600)).padStart(2,'0')+':'+String(Math.floor((s%3600)/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0');
        ssEl.textContent = fmt;
        if (clockUp) clockUp.textContent = 'Stream: '+fmt;
    } else {
        if (clockUp) clockUp.textContent = 'Stream: --:--:--';
    }
    const session = Math.floor((Date.now() - perfStart) / 1000);
    statusEl.textContent = 'Session: '+String(Math.floor(session/60)).padStart(2,'0')+':'+String(session%60).padStart(2,'0');
}, 1000);

setInterval(() => app.saveSession(), 30000);
