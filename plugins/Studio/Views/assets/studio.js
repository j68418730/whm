(function () {
    'use strict';

    let currentStationId = null;
    let studioQueue = [];
    let libraryTracks = [];
    let voiceFxPresets = [];
    let sseSource = null;
    let audioPlayer = null;
    let playerTrackList = [];
    let playerTrackIndex = -1;

    const stationSelector = document.getElementById('station-selector');
    const stations = window.STUDIO_STATIONS || [];

    // ── Station Selector ──

    function populateStationSelector() {
        stations.forEach(function (s) {
            var opt = document.createElement('option');
            opt.value = s.id;
            opt.textContent = s.name + ' (' + s.engine + ':' + s.port + ')';
            stationSelector.appendChild(opt);
        });
    }

    stationSelector.addEventListener('change', function () {
        var prevId = currentStationId;
        currentStationId = this.value || null;
        if (currentStationId) {
            connectSSE(currentStationId);
            loadAllData(currentStationId);
            loadStudioQueue(currentStationId);
            loadFxTracks(currentStationId);
        } else {
            disconnectSSE();
            clearAllViews();
        }
    });

    // ── SSE Real-time (replaces polling) ──

    function connectSSE(stationId) {
        disconnectSSE();
        sseSource = new EventSource('/admin/studio/station/' + stationId + '/sse');

        sseSource.addEventListener('song_change', function (e) {
            var data = JSON.parse(e.data);
            updateNowPlaying(data);
            loadAllData(stationId);
        });

        sseSource.addEventListener('listener_change', function (e) {
            var data = JSON.parse(e.data);
            document.getElementById('listener-count').textContent = (data.listeners || 0) + ' listeners';
            document.getElementById('stat-listeners').textContent = data.listeners || 0;
            document.getElementById('np-listeners').textContent = (data.listeners || 0) + ' listeners';
        });

        sseSource.addEventListener('queue_change', function () {
            loadStudioQueue(stationId);
        });

        sseSource.addEventListener('connector_status', function () {
            if (currentStationId) loadAllData(currentStationId);
        });

        sseSource.addEventListener('error', function () {
            // SSE will auto-reconnect
        });
    }

    function disconnectSSE() {
        if (sseSource) {
            sseSource.close();
            sseSource = null;
        }
    }

    // ── Navigation ──

    document.querySelectorAll('.nav-item').forEach(function (item) {
        item.addEventListener('click', function () {
            document.querySelectorAll('.nav-item').forEach(function (n) { n.classList.remove('active'); });
            this.classList.add('active');
            document.querySelectorAll('.studio-view').forEach(function (v) { v.classList.remove('active'); });
            var view = document.getElementById('view-' + this.dataset.view);
            if (view) view.classList.add('active');
            if (currentStationId) {
                if (this.dataset.view === 'queue') loadStudioQueue(currentStationId);
                if (this.dataset.view === 'voicetracking') loadVoiceTracks(currentStationId);
                if (this.dataset.view === 'voicefx') loadFxPresets();
            }
        });
    });

    // ── API Helpers ──

    function apiGet(url) {
        return fetch(url).then(function (r) { return r.json(); });
    }

    function apiPost(url, data) {
        var formData = new FormData();
        if (data) {
            Object.keys(data).forEach(function (k) { formData.append(k, data[k]); });
        }
        return fetch(url, { method: 'POST', body: formData }).then(function (r) { return r.json(); });
    }

    function updateHeader(station) {
        var statusDot = document.getElementById('station-status');
        statusDot.className = 'status-dot ' + (station.status || 'stopped');
        document.getElementById('listener-count').textContent = (station.listeners || 0) + ' listeners';
    }

    function updateNowPlaying(song) {
        if (!song) {
            document.getElementById('np-artist').textContent = '';
            document.getElementById('np-title').textContent = 'No song playing';
            return;
        }
        document.getElementById('np-artist').textContent = song.artist || 'Unknown';
        document.getElementById('np-title').textContent = song.title || 'Unknown';
    }

    // ── Dashboard ──

    function renderDashboard(data) {
        var station = data.station || {};
        var currentSong = data.current_song || {};
        var queue = data.queue || [];
        var history = data.history || [];
        var requests = data.requests || [];

        updateHeader(station);
        updateNowPlaying(currentSong);

        document.querySelector('#now-playing-card .np-title').textContent = currentSong.title || '---';
        document.querySelector('#now-playing-card .np-artist').textContent = currentSong.artist || '---';
        document.getElementById('np-listeners').textContent = (currentSong.listeners || 0) + ' listeners';
        document.getElementById('np-bitrate').textContent = station.bitrate ? station.bitrate + ' kbps' : '';
        document.getElementById('np-format').textContent = station.format || '';

        document.getElementById('stat-listeners').textContent = station.listeners || 0;
        document.getElementById('stat-peak').textContent = station.listener_peak || 0;
        document.getElementById('stat-bitrate').textContent = station.bitrate || 0;
        document.getElementById('stat-status').textContent = station.status || '---';

        // Queue preview
        var qlist = document.getElementById('queue-preview-list');
        document.getElementById('queue-count').textContent = queue.length;
        if (queue.length === 0) {
            qlist.innerHTML = '<div class="queue-empty">No items in queue</div>';
        } else {
            qlist.innerHTML = queue.slice(0, 8).map(function (item) {
                return '<div class="queue-item" draggable="true" data-id="' + item.id + '">'
                    + '<span>' + esc(item.artist || 'Unknown') + ' - ' + esc(item.title || 'Unknown') + '</span>'
                    + '<span>' + formatDuration(item.duration) + '</span></div>';
            }).join('');
        }

        // History
        var htbody = document.getElementById('history-tbody');
        if (history.length === 0) {
            htbody.innerHTML = '<tr><td colspan="3" class="empty">No history</td></tr>';
        } else {
            htbody.innerHTML = history.map(function (h) {
                return '<tr><td>' + (h.played_at || '') + '</td><td>' + esc(h.artist) + '</td><td>' + esc(h.title) + '</td></tr>';
            }).join('');
        }

        // Requests
        document.getElementById('requests-count').textContent = requests.length;
        var rtbody = document.getElementById('requests-tbody');
        if (requests.length === 0) {
            rtbody.innerHTML = '<tr><td colspan="4" class="empty">No requests</td></tr>';
        } else {
            rtbody.innerHTML = requests.map(function (r) {
                return '<tr><td>' + esc(r.artist) + '</td><td>' + esc(r.title) + '</td><td>' + esc(r.guest_name || 'Anonymous') + '</td><td>' + (r.created_at || '') + '</td></tr>';
            }).join('');
        }
    }

    // ── Library (with album art and audio preview) ──

    function renderLibrary(items) {
        libraryTracks = items || [];
        var tbody = document.getElementById('library-tbody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="empty">Library is empty</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (item, i) {
            var artUrl = currentStationId ? '/admin/studio/station/' + currentStationId + '/album-art/' + item.id : '';
            return '<tr draggable="true" data-id="' + item.id + '" data-artist="' + esc(item.artist) + '" data-title="' + esc(item.title) + '" data-duration="' + (item.duration || 0) + '">'
                + '<td class="lib-art"><img src="' + artUrl + '" alt="" style="width:32px;height:32px;border-radius:3px;object-fit:cover" onerror="this.style.display=\'none\'"></td>'
                + '<td>' + esc(item.artist) + '</td>'
                + '<td>' + esc(item.title) + '</td>'
                + '<td>' + esc(item.album) + '</td>'
                + '<td>' + formatDuration(item.duration) + '</td>'
                + '<td>' + (item.bitrate || '') + '</td>'
                + '<td class="actions">'
                + '<button class="studio-btn small btn-audio-preview" data-index="' + i + '">&#9654;</button> '
                + '<button class="studio-btn small btn-add-queue" data-id="' + item.id + '">+Q</button></td></tr>';
        }).join('');

        tbody.querySelectorAll('.btn-add-queue').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (currentStationId) {
                    apiPost('/admin/studio/station/' + currentStationId + '/queue/add', { playlist_item_id: this.dataset.id }).then(function () {
                        loadStudioQueue(currentStationId);
                    });
                }
            });
        });

        tbody.querySelectorAll('.btn-audio-preview').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var idx = parseInt(this.dataset.index);
                playTrackList(items, idx);
            });
        });
    }

    document.getElementById('library-search').addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#library-tbody tr').forEach(function (row) {
            var text = row.textContent.toLowerCase();
            row.style.display = text.indexOf(q) === -1 ? 'none' : '';
        });
    });

    // ── Browser Audio Player ──

    function playTrackList(tracks, startIndex) {
        playerTrackList = tracks;
        playerTrackIndex = startIndex;
        showAudioPlayer();
        playCurrentTrack();
    }

    function showAudioPlayer() {
        document.getElementById('audio-player-bar').style.display = 'flex';
    }

    function hideAudioPlayer() {
        document.getElementById('audio-player-bar').style.display = 'none';
        if (audioPlayer) {
            audioPlayer.pause();
            audioPlayer = null;
        }
    }

    function playCurrentTrack() {
        if (playerTrackIndex < 0 || playerTrackIndex >= playerTrackList.length) {
            hideAudioPlayer();
            return;
        }

        var track = playerTrackList[playerTrackIndex];
        document.getElementById('player-title').textContent = track.title || 'Unknown';
        document.getElementById('player-artist').textContent = track.artist || '';
        document.getElementById('player-duration').textContent = formatDuration(track.duration);

        // Album art
        var artUrl = currentStationId ? '/admin/studio/station/' + currentStationId + '/album-art/' + track.id : '';
        var artImg = document.getElementById('player-art-img');
        if (artUrl) {
            artImg.src = artUrl;
            artImg.style.display = 'block';
            artImg.onerror = function () { artImg.style.display = 'none'; };
        } else {
            artImg.style.display = 'none';
        }

        // Audio element
        if (audioPlayer) { audioPlayer.pause(); }
        audioPlayer = new Audio('/admin/studio/station/' + currentStationId + '/audio-preview/' + track.id);
        audioPlayer.addEventListener('timeupdate', function () {
            document.getElementById('player-current').textContent = formatDuration(audioPlayer.currentTime);
            var pct = (audioPlayer.currentTime / (audioPlayer.duration || 1)) * 100;
            document.getElementById('player-progress').style.width = pct + '%';
        });
        audioPlayer.addEventListener('ended', function () {
            nextTrack();
        });
        audioPlayer.play();
        document.getElementById('btn-player-play').textContent = '\u2759\u2759';
    }

    function prevTrack() {
        if (playerTrackIndex > 0) {
            playerTrackIndex--;
            playCurrentTrack();
        }
    }

    function nextTrack() {
        if (playerTrackIndex < playerTrackList.length - 1) {
            playerTrackIndex++;
            playCurrentTrack();
        } else {
            hideAudioPlayer();
        }
    }

    function togglePlayPause() {
        if (!audioPlayer) return;
        if (audioPlayer.paused) {
            audioPlayer.play();
            document.getElementById('btn-player-play').textContent = '\u2759\u2759';
        } else {
            audioPlayer.pause();
            document.getElementById('btn-player-play').textContent = '\u25B6';
        }
    }

    document.getElementById('btn-player-play').addEventListener('click', togglePlayPause);
    document.getElementById('btn-player-next').addEventListener('click', nextTrack);
    document.getElementById('btn-player-prev').addEventListener('click', prevTrack);
    document.getElementById('btn-player-stop').addEventListener('click', function () { hideAudioPlayer(); });
    document.getElementById('btn-player-close').addEventListener('click', function () { hideAudioPlayer(); });

    document.getElementById('player-progress-bar').addEventListener('click', function (e) {
        if (!audioPlayer) return;
        var rect = this.getBoundingClientRect();
        var pct = (e.clientX - rect.left) / rect.width;
        audioPlayer.currentTime = pct * audioPlayer.duration;
    });

    // ── Studio Queue View ──

    function loadStudioQueue(stationId) {
        apiGet('/admin/studio/station/' + stationId + '/studio-queue').then(function (res) {
            if (res.success) {
                studioQueue = res.data || [];
                renderStudioQueue(studioQueue);
            }
        });
    }

    function renderStudioQueue(items) {
        var tbody = document.getElementById('studio-queue-tbody');
        if (!tbody) return;
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty">Queue is empty.</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (item, i) {
            return '<tr draggable="true" data-queue-id="' + item.id + '" data-sort="' + (item.sort_order || i) + '">'
                + '<td class="drag-handle">&#9776;</td>'
                + '<td>' + (i + 1) + '</td>'
                + '<td>' + esc(item.artist) + '</td>'
                + '<td>' + esc(item.title) + '</td>'
                + '<td>' + formatDuration(item.duration) + '</td>'
                + '<td class="actions">'
                + '<button class="studio-btn small btn-cue" data-id="' + item.id + '">Cue</button> '
                + '<button class="studio-btn small btn-play-queue" data-id="' + item.id + '">Play</button> '
                + '<button class="studio-btn small danger btn-remove" data-id="' + item.id + '">X</button>'
                + '</td></tr>';
        }).join('');

        tbody.querySelectorAll('.btn-cue').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/cue', { queue_id: this.dataset.id });
            });
        });
        tbody.querySelectorAll('.btn-play-queue').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/play', { queue_id: this.dataset.id }).then(function () {
                    loadStudioQueue(currentStationId);
                });
            });
        });
        tbody.querySelectorAll('.btn-remove').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/queue/remove', { queue_id: this.dataset.id }).then(function () {
                    loadStudioQueue(currentStationId);
                });
            });
        });
    }

    // ── Queue View ──

    function renderQueue(items) {
        var tbody = document.getElementById('queue-tbody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="empty">Queue is empty</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (item, i) {
            return '<tr><td>' + (i + 1) + '</td><td>' + esc(item.artist) + '</td><td>' + esc(item.title) + '</td><td>' + formatDuration(item.duration) + '</td><td>' + esc(item.name || '') + '</td></tr>';
        }).join('');
    }

    // ── History View ──

    function renderFullHistory(items) {
        var tbody = document.getElementById('full-history-tbody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="empty">No history</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (h) {
            return '<tr><td>' + (h.played_at || '') + '</td><td>' + esc(h.artist) + '</td><td>' + esc(h.title) + '</td><td>' + esc(h.dj_name || 'AutoDJ') + '</td></tr>';
        }).join('');
    }

    // ── Playlists ──

    function renderPlaylists(items) {
        var container = document.getElementById('playlists-container');
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="empty">No playlists.</p>';
            return;
        }
        container.innerHTML = items.map(function (pl) {
            return '<div class="playlist-card">'
                + '<h4>' + esc(pl.name) + '</h4>'
                + '<p>' + esc(pl.description || '') + '</p>'
                + '<div class="playlist-count">Default: ' + (pl.is_default ? 'Yes' : 'No') + '</div>'
                + '<div class="playlist-actions" style="margin-top:8px">'
                + '<button class="studio-btn small danger btn-del-playlist" data-id="' + pl.id + '">Delete</button>'
                + '</div></div>';
        }).join('');

        container.querySelectorAll('.btn-del-playlist').forEach(function (b) {
            b.addEventListener('click', function () {
                if (confirm('Delete this playlist?')) {
                    apiPost('/admin/studio/station/' + currentStationId + '/playlist/' + this.dataset.id + '/delete').then(function () {
                        if (currentStationId) loadAllData(currentStationId);
                    });
                }
            });
        });
    }

    // ── DJs ──

    function renderDjs(data) {
        var container = document.getElementById('djs-container');
        var djs = data.djs || [];
        var connected = data.connected_dj;
        if (djs.length === 0 && !connected) {
            container.innerHTML = '<p class="empty">No DJs configured</p>';
            return;
        }
        var html = '';
        if (connected) {
            html += '<div class="dj-card" style="margin-bottom:12px;border-color:var(--green);"><div class="dj-avatar">&#127897;</div><div class="dj-info"><h4>' + esc(connected.name || connected.username) + ' <span style="color:var(--green)">&#9679; Connected</span></h4><p>' + esc(connected.email || '') + '</p></div></div>';
        }
        djs.forEach(function (dj) {
            html += '<div class="dj-card"><div class="dj-avatar">&#127897;</div><div class="dj-info"><h4>' + esc(dj.name || dj.username) + '</h4><p>' + esc(dj.email || '') + '</p><div class="dj-status">' + dj.status + '</div></div></div>';
        });
        container.innerHTML = html;
    }

    // ── Requests View ──

    function renderFullRequests(items) {
        var tbody = document.getElementById('full-requests-tbody');
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="empty">No requests</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (r) {
            return '<tr>'
                + '<td>' + esc(r.artist) + '</td>'
                + '<td>' + esc(r.title) + '</td>'
                + '<td>' + esc(r.guest_name || 'Anonymous') + '</td>'
                + '<td>' + (r.status || 'pending') + '</td>'
                + '<td>' + (r.created_at || '') + '</td>'
                + '<td class="actions">'
                + '<button class="studio-btn small btn-appr" data-id="' + r.id + '">Approve</button> '
                + '<button class="studio-btn small danger btn-rej" data-id="' + r.id + '">Reject</button>'
                + '</td></tr>';
        }).join('');

        tbody.querySelectorAll('.btn-appr').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/request/' + this.dataset.id + '/approve').then(function () {
                    if (currentStationId) loadAllData(currentStationId);
                });
            });
        });
        tbody.querySelectorAll('.btn-rej').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/request/' + this.dataset.id + '/reject').then(function () {
                    if (currentStationId) loadAllData(currentStationId);
                });
            });
        });
    }

    // ── Schedule ──

    var DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    function renderSchedule(items) {
        var container = document.getElementById('schedule-container');
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="empty">No schedule configured</p>';
            return;
        }
        var byDay = {};
        items.forEach(function (s) {
            var day = s.day_of_week;
            if (!byDay[day]) byDay[day] = [];
            byDay[day].push(s);
        });
        var html = '<div class="schedule-grid">';
        DAY_NAMES.forEach(function (name, i) {
            var shows = byDay[i] || [];
            html += '<div class="schedule-day"><h4>' + name + '</h4>';
            if (shows.length === 0) {
                html += '<div class="schedule-show" style="color:var(--text-muted)">No shows</div>';
            } else {
                shows.forEach(function (s) {
                    html += '<div class="schedule-show"><strong>' + esc(s.show_name) + '</strong> ' + (s.start_time || '') + ' - ' + (s.end_time || '') + '</div>';
                });
            }
            html += '</div>';
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // ── Statistics ──

    function renderStats(data) {
        var container = document.getElementById('stats-container');
        var info = data.info || {};
        container.innerHTML =
            '<div class="stat-card"><div class="stat-value">' + (info.listeners || 0) + '</div><div class="stat-label">Listeners</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.listener_peak || 0) + '</div><div class="stat-label">Peak</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.bitrate || 0) + '</div><div class="stat-label">Bitrate (kbps)</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.format || '---').toUpperCase() + '</div><div class="stat-label">Format</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.max_listeners || 0) + '</div><div class="stat-label">Max Listeners</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.port || '---') + '</div><div class="stat-label">Port</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.engine || '---') + '</div><div class="stat-label">Engine</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.status || '---') + '</div><div class="stat-label">Status</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.ssl_enabled ? 'Yes' : 'No') + '</div><div class="stat-label">SSL</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + (info.autodj_enabled ? 'Yes' : 'No') + '</div><div class="stat-label">AutoDJ</div></div>';
    }

    // ── Analytics ──

    document.getElementById('analytics-days').addEventListener('change', function () {
        if (currentStationId) loadAnalytics(currentStationId, this.value);
    });

    function renderAnalytics(items) {
        var container = document.getElementById('analytics-container');
        if (!items || items.length === 0) {
            container.innerHTML = '<p class="empty">No analytics data available</p>';
            return;
        }
        var totalListeners = 0;
        var peak = 0;
        var totalBandwidth = 0;
        items.forEach(function (a) {
            totalListeners += (a.average_listeners || 0);
            if ((a.peak_listeners || 0) > peak) peak = a.peak_listeners;
            totalBandwidth += (a.bandwidth_used || 0);
        });
        var avg = (totalListeners / items.length).toFixed(1);
        var bandwidthGb = (totalBandwidth / 1073741824).toFixed(2);
        container.innerHTML =
            '<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px">' +
            '<div class="stat-card"><div class="stat-value">' + avg + '</div><div class="stat-label">Avg Listeners</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + peak + '</div><div class="stat-label">Peak</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + items.length + '</div><div class="stat-label">Data Points</div></div>' +
            '<div class="stat-card"><div class="stat-value">' + bandwidthGb + ' GB</div><div class="stat-label">Bandwidth</div></div>' +
            '</div>' +
            '<table class="studio-table"><thead><tr><th>Date</th><th>Hour</th><th>Peak</th><th>Avg</th><th>Minutes</th><th>Bandwidth</th></tr></thead><tbody>' +
            items.map(function (a) {
                return '<tr><td>' + (a.date || '') + '</td><td>' + (a.hour || 0) + ':00</td><td>' + (a.peak_listeners || 0) + '</td><td>' + (a.average_listeners || 0) + '</td><td>' + (a.total_minutes_listened || 0) + '</td><td>' + formatBytes(a.bandwidth_used || 0) + '</td></tr>';
            }).join('') +
            '</tbody></table>';
    }

    // ── Voice Tracking ──

    function loadVoiceTracks(stationId) {
        apiGet('/admin/studio/station/' + stationId + '/voice-tracks').then(function (res) {
            if (res.success) renderVoiceTracks(res.data);
        });
    }

    function renderVoiceTracks(items) {
        var tbody = document.getElementById('voice-tbody');
        if (!tbody) return;
        if (!items || items.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="empty">No voice tracks recorded</td></tr>';
            return;
        }
        tbody.innerHTML = items.map(function (t) {
            return '<tr><td>' + esc(t.name) + '</td><td>' + formatDuration(t.duration) + '</td><td>' + (t.created_at || '') + '</td><td><button class="studio-btn small danger btn-del-voice" data-id="' + t.id + '">Delete</button></td></tr>';
        }).join('');
        tbody.querySelectorAll('.btn-del-voice').forEach(function (b) {
            b.addEventListener('click', function () {
                apiPost('/admin/studio/station/' + currentStationId + '/voice/' + this.dataset.id + '/delete').then(function () {
                    loadVoiceTracks(currentStationId);
                });
            });
        });
    }

    // ── Voice FX ──

    function loadFxPresets() {
        apiGet('/admin/studio/voice-fx-presets').then(function (res) {
            if (!res.success) return;
            voiceFxPresets = res.data || [];
            var select = document.getElementById('fx-preset-select');
            var list = document.getElementById('fx-presets-list');
            select.innerHTML = '<option value="">Select effect...</option>';
            list.innerHTML = '';
            voiceFxPresets.forEach(function (p) {
                select.innerHTML += '<option value="' + p.id + '">' + esc(p.name) + '</option>';
                if (p.id !== 'none') {
                    var params = p.params ? Object.keys(p.params).map(function (k) { return k + ': ' + p.params[k]; }).join(', ') : '';
                    list.innerHTML += '<div style="background:var(--bg-hover);padding:8px 10px;border-radius:4px;font-size:12px"><strong>' + esc(p.name) + '</strong><br><span style="color:var(--text-muted)">' + esc(params) + '</span></div>';
                }
            });
        });
    }

    function loadFxTracks(stationId) {
        apiGet('/admin/studio/station/' + stationId + '/library').then(function (res) {
            if (!res.success) return;
            var select = document.getElementById('fx-track-select');
            select.innerHTML = '<option value="">Select a track...</option>';
            (res.data || []).forEach(function (t) {
                select.innerHTML += '<option value="' + t.id + '">' + esc(t.artist) + ' - ' + esc(t.title) + '</option>';
            });
        });
    }

    document.getElementById('btn-apply-fx').addEventListener('click', function () {
        var trackId = document.getElementById('fx-track-select').value;
        var presetId = document.getElementById('fx-preset-select').value;
        var resultDiv = document.getElementById('fx-result');

        if (!trackId || !presetId) {
            resultDiv.textContent = 'Please select a track and an effect.';
            resultDiv.style.color = 'var(--yellow)';
            return;
        }

        resultDiv.textContent = 'Applying effect...';
        resultDiv.style.color = 'var(--text-secondary)';

        apiPost('/admin/studio/station/' + currentStationId + '/voice-fx/' + trackId, { preset: presetId }).then(function (res) {
            if (res.success) {
                if (res.output_path) {
                    resultDiv.innerHTML = 'Effect applied! Output: ' + esc(res.output_path);
                    resultDiv.style.color = 'var(--green)';
                } else {
                    resultDiv.textContent = res.message || 'Done (no change)';
                    resultDiv.style.color = 'var(--text-secondary)';
                }
            } else {
                resultDiv.textContent = 'Error: ' + (res.error || 'Unknown');
                resultDiv.style.color = 'var(--red)';
            }
        });
    });

    // ─── Deck Controls ───

    document.querySelectorAll('.btn-deck-play').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (currentStationId) {
                apiPost('/admin/studio/station/' + currentStationId + '/play', {}).then(function () {
                    loadStudioQueue(currentStationId);
                });
            }
        });
    });

    document.querySelectorAll('.btn-deck-stop').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (currentStationId) apiPost('/admin/studio/station/' + currentStationId + '/stop', {});
        });
    });

    // ── Data Loading ──

    function loadAllData(stationId) {
        apiGet('/admin/studio/dashboard/' + stationId).then(function (res) {
            if (res.success) renderDashboard(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/library').then(function (res) {
            if (res.success) renderLibrary(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/queue').then(function (res) {
            if (res.success) renderQueue(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/history').then(function (res) {
            if (res.success) renderFullHistory(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/playlists').then(function (res) {
            if (res.success) renderPlaylists(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/djs').then(function (res) {
            if (res.success) renderDjs(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/requests').then(function (res) {
            if (res.success) renderFullRequests(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/schedule').then(function (res) {
            if (res.success) renderSchedule(res.data);
        });
        apiGet('/admin/studio/station/' + stationId + '/stats').then(function (res) {
            if (res.success) renderStats(res.data);
        });
        var days = document.getElementById('analytics-days').value;
        loadAnalytics(stationId, days);
    }

    function loadAnalytics(stationId, days) {
        apiGet('/admin/studio/station/' + stationId + '/analytics?days=' + days).then(function (res) {
            if (res.success) renderAnalytics(res.data);
        });
    }

    function clearAllViews() {
        document.getElementById('station-status').className = 'status-dot stopped';
        document.getElementById('listener-count').textContent = '0 listeners';
        document.getElementById('np-artist').textContent = '';
        document.getElementById('np-title').textContent = 'No station selected';
        document.querySelector('#now-playing-card .np-title').textContent = '---';
        document.querySelector('#now-playing-card .np-artist').textContent = '---';
        document.getElementById('stat-listeners').textContent = '0';
        document.getElementById('stat-peak').textContent = '0';
        document.getElementById('stat-bitrate').textContent = '0';
        document.getElementById('stat-status').textContent = '---';
    }

    // ── Playlist Create ──

    document.getElementById('btn-create-playlist').addEventListener('click', function () {
        var name = prompt('New playlist name:');
        if (name && currentStationId) {
            apiPost('/admin/studio/station/' + currentStationId + '/playlist/create', { name: name }).then(function () {
                loadAllData(currentStationId);
            });
        }
    });

    // ── Queue Clear / Play All ──

    document.getElementById('btn-clear-queue').addEventListener('click', function () {
        if (currentStationId && confirm('Clear entire queue?')) {
            apiPost('/admin/studio/station/' + currentStationId + '/queue/clear', {}).then(function () {
                loadStudioQueue(currentStationId);
            });
        }
    });

    document.getElementById('btn-play-queue').addEventListener('click', function () {
        if (currentStationId) {
            apiPost('/admin/studio/station/' + currentStationId + '/play', {}).then(function () {
                loadStudioQueue(currentStationId);
            });
        }
    });

    // ── Voice Record ──

    document.getElementById('btn-record-voice').addEventListener('click', function () {
        var name = prompt('Voice track name:');
        if (name && currentStationId) {
            apiPost('/admin/studio/station/' + currentStationId + '/voice/save', { name: name }).then(function () {
                loadVoiceTracks(currentStationId);
            });
        }
    });

    // ── Utilities ──

    function formatDuration(seconds) {
        if (!seconds || seconds <= 0) return '--:--';
        var m = Math.floor(seconds / 60);
        var s = Math.floor(seconds % 60);
        return m + ':' + (s < 10 ? '0' : '') + s;
    }

    function formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        var units = ['B', 'KB', 'MB', 'GB', 'TB'];
        var i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }

    function esc(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    // ── Init ──

    populateStationSelector();
    if (stations.length === 1) {
        stationSelector.value = stations[0].id;
        currentStationId = stations[0].id;
        connectSSE(currentStationId);
        loadAllData(currentStationId);
        loadStudioQueue(currentStationId);
        loadFxTracks(currentStationId);
    }
})();