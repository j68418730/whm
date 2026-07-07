<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planet Hosts Studio</title>
    <link rel="stylesheet" href="/plugins/Studio/Views/assets/studio.css">
</head>
<body>
    <div id="studio-app">
        <!-- Top Bar -->
        <header id="studio-header">
            <div class="header-left">
                <span class="studio-logo">Planet Hosts Studio</span>
            </div>
            <div class="header-center">
                <div id="now-playing-badge">
                    <span id="np-artist"></span> — <span id="np-title"></span>
                </div>
            </div>
            <div class="header-right">
                <select id="station-selector">
                    <option value="">Select Station</option>
                </select>
                <span id="listener-count">0 listeners</span>
                <span id="station-status" class="status-dot stopped"></span>
            </div>
        </header>

        <!-- Main Layout -->
        <div id="studio-body">
            <!-- Sidebar -->
            <nav id="studio-sidebar">
                <ul class="nav-list">
                    <li class="nav-item active" data-view="dashboard">
                        <span class="nav-icon">&#9632;</span> Dashboard
                    </li>
                    <li class="nav-item" data-view="library">
                        <span class="nav-icon">&#9835;</span> Music Library
                    </li>
                    <li class="nav-item" data-view="queue">
                        <span class="nav-icon">&#9654;</span> Queue
                    </li>
                    <li class="nav-item" data-view="history">
                        <span class="nav-icon">&#8635;</span> History
                    </li>
                    <li class="nav-item" data-view="playlists">
                        <span class="nav-icon">&#9776;</span> Playlists
                    </li>
                    <li class="nav-item" data-view="djs">
                        <span class="nav-icon">&#127897;</span> Live DJs
                    </li>
                    <li class="nav-item" data-view="requests">
                        <span class="nav-icon">&#9993;</span> Requests
                    </li>
                    <li class="nav-item" data-view="schedule">
                        <span class="nav-icon">&#128197;</span> Schedule
                    </li>
                    <li class="nav-item" data-view="stats">
                        <span class="nav-icon">&#128200;</span> Statistics
                    </li>
                    <li class="nav-item" data-view="analytics">
                        <span class="nav-icon">&#128202;</span> Analytics
                    </li>
                    <li class="nav-separator"></li>
                    <li class="nav-item" data-view="studioqueue">
                        <span class="nav-icon">&#9776;</span> Studio Queue
                    </li>
                    <li class="nav-item" data-view="voicetracking">
                        <span class="nav-icon">&#127908;</span> Voice Tracking
                    </li>
                    <li class="nav-item" data-view="voicefx">
                        <span class="nav-icon">&#127911;</span> Voice FX
                    </li>
                    <li class="nav-item" data-view="connector">
                        <span class="nav-icon">&#128187;</span> Desktop Connector
                    </li>
                </ul>
            </nav>

            <!-- Content -->
            <main id="studio-content">
                <!-- Dashboard View -->
                <div id="view-dashboard" class="studio-view active">
                    <div class="dashboard-grid">
                        <!-- Deck A -->
                        <div class="deck-card" id="deck-a">
                            <div class="deck-header">
                                <h3>Deck A</h3>
                                <span class="deck-status stopped">Stopped</span>
                            </div>
                            <div class="deck-body">
                                <div class="deck-artwork">&#9835;</div>
                                <div class="deck-info">
                                    <div class="deck-title">No Track Loaded</div>
                                    <div class="deck-artist"></div>
                                    <div class="deck-meta">
                                        <span class="meta-duration">0:00</span>
                                        <span class="meta-bitrate"></span>
                                    </div>
                                </div>
                                <div class="deck-controls" style="margin-top:10px;display:flex;gap:6px;justify-content:center">
                                    <button class="studio-btn small btn-deck-play" data-deck="a">&#9654; Play</button>
                                    <button class="studio-btn small btn-deck-stop" data-deck="a">&#9632; Stop</button>
                                </div>
                            </div>
                        </div>

                        <!-- Deck B -->
                        <div class="deck-card" id="deck-b">
                            <div class="deck-header">
                                <h3>Deck B</h3>
                                <span class="deck-status stopped">Stopped</span>
                            </div>
                            <div class="deck-body">
                                <div class="deck-artwork">&#9835;</div>
                                <div class="deck-info">
                                    <div class="deck-title">No Track Loaded</div>
                                    <div class="deck-artist"></div>
                                    <div class="deck-meta">
                                        <span class="meta-duration">0:00</span>
                                        <span class="meta-bitrate"></span>
                                    </div>
                                </div>
                                <div class="deck-controls" style="margin-top:10px;display:flex;gap:6px;justify-content:center">
                                    <button class="studio-btn small btn-deck-play" data-deck="b">&#9654; Play</button>
                                    <button class="studio-btn small btn-deck-stop" data-deck="b">&#9632; Stop</button>
                                </div>
                            </div>
                        </div>

                        <!-- Now Playing -->
                        <div class="info-card" id="now-playing-card">
                            <h3>Now Playing</h3>
                            <div class="now-playing-info">
                                <div class="np-title">---</div>
                                <div class="np-artist">---</div>
                            </div>
                            <div class="now-playing-meta">
                                <span id="np-listeners">0 listeners</span>
                                <span id="np-bitrate"></span>
                                <span id="np-format"></span>
                            </div>
                        </div>

                        <!-- Quick Queue -->
                        <div class="info-card" id="queue-preview">
                            <h3>Queue <span class="badge" id="queue-count">0</span></h3>
                            <div class="queue-list" id="queue-preview-list">
                                <div class="queue-empty">No items in queue</div>
                            </div>
                        </div>

                        <!-- Listeners -->
                        <div class="stat-card" id="listeners-card">
                            <div class="stat-value" id="stat-listeners">0</div>
                            <div class="stat-label">Listeners</div>
                        </div>

                        <!-- Peak Listeners -->
                        <div class="stat-card">
                            <div class="stat-value" id="stat-peak">0</div>
                            <div class="stat-label">Peak</div>
                        </div>

                        <!-- Stream Info -->
                        <div class="stat-card">
                            <div class="stat-value" id="stat-bitrate">0</div>
                            <div class="stat-label">Bitrate</div>
                        </div>

                        <!-- Status -->
                        <div class="stat-card">
                            <div class="stat-value" id="stat-status">---</div>
                            <div class="stat-label">Status</div>
                        </div>

                        <!-- Recent History -->
                        <div class="wide-card" id="recent-history">
                            <h3>Recent History</h3>
                            <table class="studio-table">
                                <thead>
                                    <tr><th>Time</th><th>Artist</th><th>Title</th></tr>
                                </thead>
                                <tbody id="history-tbody">
                                    <tr><td colspan="3" class="empty">No history</td></tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Requests -->
                        <div class="wide-card" id="requests-card">
                            <h3>Pending Requests <span class="badge" id="requests-count">0</span></h3>
                            <table class="studio-table">
                                <thead>
                                    <tr><th>Artist</th><th>Title</th><th>From</th><th>Time</th></tr>
                                </thead>
                                <tbody id="requests-tbody">
                                    <tr><td colspan="4" class="empty">No requests</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Library View -->
                <div id="view-library" class="studio-view">
                    <div class="view-header">
                        <h2>Music Library</h2>
                        <input type="text" id="library-search" placeholder="Search library..." class="search-input">
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr>
                                <th>Artist</th>
                                <th>Title</th>
                                <th>Album</th>
                                <th>Duration</th>
                                <th>Bitrate</th>
                            </tr>
                        </thead>
                        <tbody id="library-tbody">
                            <tr><td colspan="5" class="empty">Select a station to view library</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Queue View -->
                <div id="view-queue" class="studio-view">
                    <div class="view-header">
                        <h2>Full Queue</h2>
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr><th>#</th><th>Artist</th><th>Title</th><th>Duration</th><th>Playlist</th></tr>
                        </thead>
                        <tbody id="queue-tbody">
                            <tr><td colspan="5" class="empty">Select a station to view queue</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- History View -->
                <div id="view-history" class="studio-view">
                    <div class="view-header">
                        <h2>Song History</h2>
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr><th>Time</th><th>Artist</th><th>Title</th><th>DJ</th></tr>
                        </thead>
                        <tbody id="full-history-tbody">
                            <tr><td colspan="4" class="empty">Select a station to view history</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Playlists View -->
                <div id="view-playlists" class="studio-view">
                    <div class="view-header">
                        <h2>Playlists</h2>
                        <button id="btn-create-playlist" class="studio-btn primary">+ New Playlist</button>
                    </div>
                    <div id="playlists-container">
                        <p class="empty">Select a station to view playlists</p>
                    </div>
                </div>

                <!-- DJs View -->
                <div id="view-djs" class="studio-view">
                    <div class="view-header">
                        <h2>Live DJs</h2>
                    </div>
                    <div id="djs-container">
                        <p class="empty">Select a station to view DJs</p>
                    </div>
                </div>

                <!-- Requests View -->
                <div id="view-requests" class="studio-view">
                    <div class="view-header">
                        <h2>Song Requests</h2>
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr><th>Artist</th><th>Title</th><th>From</th><th>Status</th><th>Time</th></tr>
                        </thead>
                        <tbody id="full-requests-tbody">
                            <tr><td colspan="5" class="empty">Select a station to view requests</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Schedule View -->
                <div id="view-schedule" class="studio-view">
                    <div class="view-header">
                        <h2>Schedule</h2>
                    </div>
                    <div id="schedule-container">
                        <p class="empty">Select a station to view schedule</p>
                    </div>
                </div>

                <!-- Statistics View -->
                <div id="view-stats" class="studio-view">
                    <div class="view-header">
                        <h2>Station Statistics</h2>
                    </div>
                    <div id="stats-container">
                        <p class="empty">Select a station to view statistics</p>
                    </div>
                </div>

                <!-- Analytics View -->
                <div id="view-analytics" class="studio-view">
                    <div class="view-header">
                        <h2>Listener Analytics</h2>
                        <select id="analytics-days">
                            <option value="7">7 days</option>
                            <option value="14">14 days</option>
                            <option value="30">30 days</option>
                        </select>
                    </div>
                    <div id="analytics-container">
                        <p class="empty">Select a station to view analytics</p>
                    </div>
                </div>

                <!-- Studio Queue View (Phase 2 — drag-drop, editable) -->
                <div id="view-studioqueue" class="studio-view">
                    <div class="view-header">
                        <h2>Studio Queue</h2>
                        <div class="header-actions">
                            <button id="btn-clear-queue" class="studio-btn danger">Clear Queue</button>
                            <button id="btn-play-queue" class="studio-btn primary">Play All</button>
                        </div>
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr><th></th><th>#</th><th>Artist</th><th>Title</th><th>Duration</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="studio-queue-tbody">
                            <tr><td colspan="6" class="empty">Select a station to view queue</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Voice Tracking View (Phase 2) -->
                <div id="view-voicetracking" class="studio-view">
                    <div class="view-header">
                        <h2>Voice Tracking</h2>
                        <button id="btn-record-voice" class="studio-btn primary">&#9679; Record</button>
                    </div>
                    <table class="studio-table">
                        <thead>
                            <tr><th>Name</th><th>Duration</th><th>Created</th><th>Actions</th></tr>
                        </thead>
                        <tbody id="voice-tbody">
                            <tr><td colspan="4" class="empty">Select a station to view voice tracks</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Voice FX View -->
                <div id="view-voicefx" class="studio-view">
                    <div class="view-header">
                        <h2>Voice FX</h2>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:16px">
                            <h3 style="margin-bottom:12px">Apply Effect to Track</h3>
                            <div style="margin-bottom:10px">
                                <label style="display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px">Track</label>
                                <select id="fx-track-select" style="width:100%;background:var(--bg-hover);color:var(--text-primary);border:1px solid var(--border);padding:8px;border-radius:4px">
                                    <option value="">Select a track</option>
                                </select>
                            </div>
                            <div style="margin-bottom:10px">
                                <label style="display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px">Effect Preset</label>
                                <select id="fx-preset-select" style="width:100%;background:var(--bg-hover);color:var(--text-primary);border:1px solid var(--border);padding:8px;border-radius:4px">
                                    <option value="">Loading presets...</option>
                                </select>
                            </div>
                            <button id="btn-apply-fx" class="studio-btn primary" style="width:100%;margin-top:8px">Apply Effect</button>
                            <div id="fx-result" style="margin-top:10px;font-size:13px;color:var(--text-secondary)"></div>
                        </div>
                        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:16px">
                            <h3 style="margin-bottom:12px">Effect Presets</h3>
                            <div id="fx-presets-list" style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                                <p style="color:var(--text-muted)">Loading...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audio Player Modal -->
                <div id="audio-player-bar" style="display:none;position:fixed;bottom:0;left:0;right:0;background:var(--bg-secondary);border-top:2px solid var(--accent);padding:10px 20px;z-index:100;align-items:center;gap:16px">
                    <div style="display:flex;align-items:center;gap:12px;flex:1">
                        <div id="player-album-art" style="width:40px;height:40px;border-radius:4px;background:var(--bg-hover);flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--text-muted);overflow:hidden">
                            <img id="player-art-img" style="width:100%;height:100%;object-fit:cover;display:none">
                        </div>
                        <div style="min-width:0">
                            <div id="player-title" style="font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">No track selected</div>
                            <div id="player-artist" style="font-size:11px;color:var(--text-secondary)"></div>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <button id="btn-player-prev" class="studio-btn small">&#9664;&#9664;</button>
                        <button id="btn-player-play" class="studio-btn primary" style="font-size:16px;padding:4px 16px">&#9654;</button>
                        <button id="btn-player-next" class="studio-btn small">&#9654;&#9654;</button>
                        <button id="btn-player-stop" class="studio-btn small">&#9632;</button>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;min-width:200px">
                        <span id="player-current" style="font-size:11px;color:var(--text-secondary)">0:00</span>
                        <div style="flex:1;height:4px;background:var(--bg-hover);border-radius:2px;cursor:pointer" id="player-progress-bar">
                            <div id="player-progress" style="width:0%;height:100%;background:var(--accent);border-radius:2px;transition:width 0.3s"></div>
                        </div>
                        <span id="player-duration" style="font-size:11px;color:var(--text-secondary)">0:00</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px">
                        <button id="btn-player-close" class="studio-btn small danger">X</button>
                    </div>
                </div>

                <!-- Desktop Connector View (Phase 2) -->
                <div id="view-connector" class="studio-view">
                    <div class="view-header">
                        <h2>Desktop Connector</h2>
                    </div>
                    <div class="connector-info">
                        <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:20px;max-width:600px">
                            <h3 style="margin-bottom:12px">Connector API</h3>
                            <p style="color:var(--text-secondary);margin-bottom:16px">The Desktop Connector allows local audio applications to communicate with Planet Hosts Studio.</p>
                            <div style="margin-bottom:12px">
                                <label style="display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px">API Endpoint</label>
                                <code style="display:block;background:var(--bg-hover);padding:8px 12px;border-radius:4px;font-size:13px">/connector/auth</code>
                            </div>
                            <div style="margin-bottom:12px">
                                <label style="display:block;font-size:12px;color:var(--text-secondary);margin-bottom:4px">Authentication</label>
                                <code style="display:block;background:var(--bg-hover);padding:8px 12px;border-radius:4px;font-size:13px">X-API-Key header or api_key POST parameter</code>
                            </div>
                            <h4 style="margin:16px 0 8px">Available Endpoints</h4>
                            <table class="studio-table">
                                <thead><tr><th>Method</th><th>Path</th><th>Description</th></tr></thead>
                                <tbody>
                                    <tr><td>POST</td><td>/connector/auth</td><td>Authenticate and get session token</td></tr>
                                    <tr><td>GET</td><td>/connector/station/{id}/library</td><td>Get music library</td></tr>
                                    <tr><td>GET</td><td>/connector/station/{id}/queue</td><td>Get current queue</td></tr>
                                    <tr><td>GET</td><td>/connector/station/{id}/status</td><td>Get station status</td></tr>
                                    <tr><td>GET</td><td>/connector/station/{id}/history</td><td>Get song history</td></tr>
                                    <tr><td>POST</td><td>/connector/station/{id}/upload</td><td>Upload music file</td></tr>
                                    <tr><td>GET</td><td>/connector/devices</td><td>List connected devices</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        window.STUDIO_STATIONS = <?php echo json_encode(array_map(function($s) {
            return ['id' => $s->id, 'name' => $s->name, 'engine' => $s->engine ?? $s->server_type, 'port' => $s->port, 'status' => $s->status];
        }, $stations)); ?>;
    </script>
    <script src="/plugins/Studio/Views/assets/studio.js"></script>
</body>
</html>