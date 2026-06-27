<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:900px">
<h3 style="color:var(--accent);margin-bottom:20px"><i class="bi bi-ui-checks"></i> Create Feature List</h3>
<form method="POST" action="/admin/feature-lists/store">
<div class="form-group"><label>Name *</label><input name="name" required placeholder="e.g. Basic Feature Set" style="width:100%"></div>

<!-- Limits -->
<div style="margin-top:16px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Resource Limits</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="-1" style="width:100%"><small>-1 unlimited</small></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="-1" style="width:100%"></div>
<div class="form-group"><label>Databases</label><input name="databases" type="number" value="-1" style="width:100%"></div>
<div class="form-group"><label>Database Users</label><input name="database_users" type="number" value="-1" style="width:100%"></div>
<div class="form-group"><label>Subdomains</label><input name="subdomains" type="number" value="-1" style="width:100%"></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="-1" style="width:100%"></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="-1" style="width:100%"></div>
</div>
</div>

<!-- General Toggles -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">General Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="cron_jobs" value="1" checked> Cron Jobs</label>
<label class="fl-toggle"><input type="checkbox" name="ssh_access" value="1"> SSH Access</label>
<label class="fl-toggle"><input type="checkbox" name="ssl_allowed" value="1" checked> SSL Allowed</label>
<label class="fl-toggle"><input type="checkbox" name="git_access" value="1" checked> Git Access</label>
<label class="fl-toggle"><input type="checkbox" name="nodejs" value="1"> Node.js</label>
<label class="fl-toggle"><input type="checkbox" name="python" value="1"> Python</label>
<label class="fl-toggle"><input type="checkbox" name="ruby" value="1"> Ruby</label>
<label class="fl-toggle"><input type="checkbox" name="terminal" value="1"> Terminal</label>
<label class="fl-toggle"><input type="checkbox" name="backups" value="1" checked> Backups</label>
</div>
</div>

<!-- Website Builder Section -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Website Builder</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="builder" value="1"> Website Builder</label>
<label class="fl-toggle"><input type="checkbox" name="ai_website_builder" value="1"> AI Website Builder</label>
<label class="fl-toggle"><input type="checkbox" name="ai_assistant" value="1"> AI Assistant</label>
</div>
</div>

<!-- Developer -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Developer Tools</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="plugin_marketplace" value="1"> Plugin Marketplace</label>
<label class="fl-toggle"><input type="checkbox" name="api_access" value="1"> API Access</label>
<label class="fl-toggle"><input type="checkbox" name="webhooks" value="1"> Webhooks</label>
<label class="fl-toggle"><input type="checkbox" name="installer" value="1" checked> One Click Installer</label>
</div>
</div>

<!-- Chat -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Chat Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="chatbox" value="1"> Chatbox (Text)</label>
<label class="fl-toggle"><input type="checkbox" name="chatbox_voice" value="1"> Chatbox (Voice)</label>
<label class="fl-toggle"><input type="checkbox" name="chatbox_video" value="1"> Chatbox (Voice + Video)</label>
<label class="fl-toggle"><input type="checkbox" name="dj_panel" value="1"> DJ Panel</label>
</div>
</div>

<!-- Streaming Section -->
<div style="margin-top:20px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="streaming_enabled" value="1" onchange="toggleGroup(this, 'streaming-group')"> Streaming (If Assigned)</label>
</h4>
<div id="streaming-group" style="display:none;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="shoutcast_v1" value="1"> SHOUTcast v1</label>
<label class="fl-toggle"><input type="checkbox" name="shoutcast_v2" value="1"> SHOUTcast v2</label>
<label class="fl-toggle"><input type="checkbox" name="icecast_enabled" value="1"> Icecast</label>
<label class="fl-toggle"><input type="checkbox" name="autodj" value="1"> AutoDJ</label>
<label class="fl-toggle"><input type="checkbox" name="ssl_streaming" value="1"> SSL Streaming</label>
<label class="fl-toggle"><input type="checkbox" name="statistics" value="1"> Statistics</label>
<label class="fl-toggle"><input type="checkbox" name="recording" value="1"> Recording</label>
<label class="fl-toggle"><input type="checkbox" name="song_requests" value="1"> Song Requests</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>Max Stations</label><input name="max_stations" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Max DJs</label><input name="max_djs" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Max Listeners</label><input name="max_listeners" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Maximum Bitrate</label><input name="max_bitrate" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Playlist Storage (MB)</label><input name="playlist_storage" type="number" value="0" style="width:100%"></div>
</div>
</div>
</div>

<!-- Game Servers Section -->
<div style="margin-top:16px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="game_servers_enabled" value="1" onchange="toggleGroup(this, 'game-group')"> Game Servers (If Assigned)</label>
</h4>
<div id="game-group" style="display:none;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="steamcmd" value="1"> SteamCMD</label>
<label class="fl-toggle"><input type="checkbox" name="workshop" value="1"> Workshop</label>
<label class="fl-toggle"><input type="checkbox" name="mod_support" value="1"> Mod Support</label>
<label class="fl-toggle"><input type="checkbox" name="scheduled_restarts" value="1"> Scheduled Restarts</label>
<label class="fl-toggle"><input type="checkbox" name="automatic_updates" value="1"> Automatic Updates</label>
<label class="fl-toggle"><input type="checkbox" name="game_backups" value="1"> Backups</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>Max Game Servers</label><input name="max_game_servers" type="number" value="0" style="width:100%"></div>
</div>
</div>
</div>

<!-- VPS Section -->
<div style="margin-top:16px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="vps_enabled" value="1" onchange="toggleGroup(this, 'vps-group')"> VPS (If Assigned)</label>
</h4>
<div id="vps-group" style="display:none;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="iso_mount" value="1"> ISO Mount</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>vCPU</label><input name="vcpu" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>RAM (GB)</label><input name="ram" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Storage (GB)</label><input name="vps_storage" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Bandwidth (TB)</label><input name="vps_bandwidth" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Snapshots</label><input name="snapshots" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>Backup Slots</label><input name="vps_backups" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>IPv4 Count</label><input name="ipv4" type="number" value="0" style="width:100%"></div>
<div class="form-group"><label>IPv6 Count</label><input name="ipv6" type="number" value="0" style="width:100%"></div>
</div>
</div>
</div>

<!-- Legacy toggles (hidden, kept for backward compat) -->
<div style="display:none">
<label><input type="checkbox" name="radio" value="1"></label>
<label><input type="checkbox" name="shoutcast" value="1"></label>
<label><input type="checkbox" name="game" value="1"></label>
</div>

<div style="margin-top:24px;display:flex;gap:12px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Create</button>
<a href="/admin/feature-lists" class="btn secondary">Cancel</a>
</div>
</form>
</div>

<style>
.fl-toggle { display:flex;align-items:center;gap:6px;font-size:13px;cursor:pointer;padding:4px 6px;border-radius:4px;transition:background .15s }
.fl-toggle:hover { background:rgba(0,140,255,.06) }
</style>

<script>
function toggleGroup(cb, id) {
    document.getElementById(id).style.display = cb.checked ? 'block' : 'none';
}
// Auto-show groups on page load (for edit page - but this is create, so default hidden)
</script>