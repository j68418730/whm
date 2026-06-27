<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="max-width:900px">
<h3 style="color:var(--accent);margin-bottom:20px"><i class="bi bi-ui-checks"></i> Edit Feature List</h3>
<form method="POST" action="/admin/feature-lists/update/<?php echo $list->id; ?>">
<div class="form-group"><label>Name *</label><input name="name" required value="<?php echo htmlspecialchars($list->name); ?>" style="width:100%"></div>

<!-- Limits -->
<div style="margin-top:16px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Resource Limits</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px">
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="<?php echo $list->email_accounts; ?>" style="width:100%"><small>-1 unlimited</small></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="<?php echo $list->ftp_accounts; ?>" style="width:100%"></div>
<div class="form-group"><label>Databases</label><input name="databases" type="number" value="<?php echo $list->databases; ?>" style="width:100%"></div>
<div class="form-group"><label>Database Users</label><input name="database_users" type="number" value="<?php echo $list->database_users; ?>" style="width:100%"></div>
<div class="form-group"><label>Subdomains</label><input name="subdomains" type="number" value="<?php echo $list->subdomains; ?>" style="width:100%"></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="<?php echo $list->parked_domains; ?>" style="width:100%"></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="<?php echo $list->addon_domains; ?>" style="width:100%"></div>
</div>
</div>

<!-- General Toggles -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">General Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="cron_jobs" value="1" <?php echo $list->cron_jobs ? 'checked' : ''; ?>> Cron Jobs</label>
<label class="fl-toggle"><input type="checkbox" name="ssh_access" value="1" <?php echo $list->ssh_access ? 'checked' : ''; ?>> SSH Access</label>
<label class="fl-toggle"><input type="checkbox" name="ssl_allowed" value="1" <?php echo $list->ssl_allowed ? 'checked' : ''; ?>> SSL Allowed</label>
<label class="fl-toggle"><input type="checkbox" name="git_access" value="1" <?php echo $list->git_access ? 'checked' : ''; ?>> Git Access</label>
<label class="fl-toggle"><input type="checkbox" name="nodejs" value="1" <?php echo $list->nodejs ? 'checked' : ''; ?>> Node.js</label>
<label class="fl-toggle"><input type="checkbox" name="python" value="1" <?php echo $list->python ? 'checked' : ''; ?>> Python</label>
<label class="fl-toggle"><input type="checkbox" name="ruby" value="1" <?php echo $list->ruby ? 'checked' : ''; ?>> Ruby</label>
<label class="fl-toggle"><input type="checkbox" name="terminal" value="1" <?php echo $list->terminal ? 'checked' : ''; ?>> Terminal</label>
<label class="fl-toggle"><input type="checkbox" name="backups" value="1" <?php echo $list->backups ? 'checked' : ''; ?>> Backups</label>
</div>
</div>

<!-- Website Builder Section -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Website Builder</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="builder" value="1" <?php echo ($list->builder ?? 0) ? 'checked' : ''; ?>> Website Builder</label>
<label class="fl-toggle"><input type="checkbox" name="ai_website_builder" value="1" <?php echo ($list->ai_website_builder ?? 0) ? 'checked' : ''; ?>> AI Website Builder</label>
<label class="fl-toggle"><input type="checkbox" name="ai_assistant" value="1" <?php echo ($list->ai_assistant ?? 0) ? 'checked' : ''; ?>> AI Assistant</label>
</div>
</div>

<!-- Developer -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Developer Tools</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="plugin_marketplace" value="1" <?php echo ($list->plugin_marketplace ?? 0) ? 'checked' : ''; ?>> Plugin Marketplace</label>
<label class="fl-toggle"><input type="checkbox" name="api_access" value="1" <?php echo ($list->api_access ?? 0) ? 'checked' : ''; ?>> API Access</label>
<label class="fl-toggle"><input type="checkbox" name="webhooks" value="1" <?php echo ($list->webhooks ?? 0) ? 'checked' : ''; ?>> Webhooks</label>
<label class="fl-toggle"><input type="checkbox" name="installer" value="1" <?php echo ($list->installer ?? 1) ? 'checked' : ''; ?>> One Click Installer</label>
</div>
</div>

<!-- Chat -->
<div style="margin-top:20px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:14px">Chat Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="chatbox" value="1" <?php echo ($list->chatbox ?? 0) ? 'checked' : ''; ?>> Chatbox (Text)</label>
<label class="fl-toggle"><input type="checkbox" name="chatbox_voice" value="1" <?php echo ($list->chatbox_voice ?? 0) ? 'checked' : ''; ?>> Chatbox (Voice)</label>
<label class="fl-toggle"><input type="checkbox" name="chatbox_video" value="1" <?php echo ($list->chatbox_video ?? 0) ? 'checked' : ''; ?>> Chatbox (Voice + Video)</label>
<label class="fl-toggle"><input type="checkbox" name="dj_panel" value="1" <?php echo ($list->dj_panel ?? 0) ? 'checked' : ''; ?>> DJ Panel</label>
</div>
</div>

<!-- Streaming Section -->
<div style="margin-top:20px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="streaming_enabled" value="1" onchange="toggleGroup(this, 'streaming-group')" <?php echo ($list->streaming_enabled ?? 0) ? 'checked' : ''; ?>> Streaming (If Assigned)</label>
</h4>
<div id="streaming-group" style="display:<?php echo ($list->streaming_enabled ?? 0) ? 'block' : 'none'; ?>;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="shoutcast_v1" value="1" <?php echo ($list->shoutcast_v1 ?? 0) ? 'checked' : ''; ?>> SHOUTcast v1</label>
<label class="fl-toggle"><input type="checkbox" name="shoutcast_v2" value="1" <?php echo ($list->shoutcast_v2 ?? 0) ? 'checked' : ''; ?>> SHOUTcast v2</label>
<label class="fl-toggle"><input type="checkbox" name="icecast_enabled" value="1" <?php echo ($list->icecast_enabled ?? 0) ? 'checked' : ''; ?>> Icecast</label>
<label class="fl-toggle"><input type="checkbox" name="autodj" value="1" <?php echo ($list->autodj ?? 0) ? 'checked' : ''; ?>> AutoDJ</label>
<label class="fl-toggle"><input type="checkbox" name="ssl_streaming" value="1" <?php echo ($list->ssl_streaming ?? 0) ? 'checked' : ''; ?>> SSL Streaming</label>
<label class="fl-toggle"><input type="checkbox" name="statistics" value="1" <?php echo ($list->statistics ?? 0) ? 'checked' : ''; ?>> Statistics</label>
<label class="fl-toggle"><input type="checkbox" name="recording" value="1" <?php echo ($list->recording ?? 0) ? 'checked' : ''; ?>> Recording</label>
<label class="fl-toggle"><input type="checkbox" name="song_requests" value="1" <?php echo ($list->song_requests ?? 0) ? 'checked' : ''; ?>> Song Requests</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>Max Stations</label><input name="max_stations" type="number" value="<?php echo $list->max_stations ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Max DJs</label><input name="max_djs" type="number" value="<?php echo $list->max_djs ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Max Listeners</label><input name="max_listeners" type="number" value="<?php echo $list->max_listeners ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Maximum Bitrate</label><input name="max_bitrate" type="number" value="<?php echo $list->max_bitrate ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Playlist Storage (MB)</label><input name="playlist_storage" type="number" value="<?php echo $list->playlist_storage ?? 0; ?>" style="width:100%"></div>
</div>
</div>
</div>

<!-- Game Servers Section -->
<div style="margin-top:16px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="game_servers_enabled" value="1" onchange="toggleGroup(this, 'game-group')" <?php echo ($list->game_servers_enabled ?? 0) ? 'checked' : ''; ?>> Game Servers (If Assigned)</label>
</h4>
<div id="game-group" style="display:<?php echo ($list->game_servers_enabled ?? 0) ? 'block' : 'none'; ?>;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="steamcmd" value="1" <?php echo ($list->steamcmd ?? 0) ? 'checked' : ''; ?>> SteamCMD</label>
<label class="fl-toggle"><input type="checkbox" name="workshop" value="1" <?php echo ($list->workshop ?? 0) ? 'checked' : ''; ?>> Workshop</label>
<label class="fl-toggle"><input type="checkbox" name="mod_support" value="1" <?php echo ($list->mod_support ?? 0) ? 'checked' : ''; ?>> Mod Support</label>
<label class="fl-toggle"><input type="checkbox" name="scheduled_restarts" value="1" <?php echo ($list->scheduled_restarts ?? 0) ? 'checked' : ''; ?>> Scheduled Restarts</label>
<label class="fl-toggle"><input type="checkbox" name="automatic_updates" value="1" <?php echo ($list->automatic_updates ?? 0) ? 'checked' : ''; ?>> Automatic Updates</label>
<label class="fl-toggle"><input type="checkbox" name="game_backups" value="1" <?php echo ($list->game_backups ?? 0) ? 'checked' : ''; ?>> Backups</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>Max Game Servers</label><input name="max_game_servers" type="number" value="<?php echo $list->max_game_servers ?? 0; ?>" style="width:100%"></div>
</div>
</div>
</div>

<!-- VPS Section -->
<div style="margin-top:16px;border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:16px">
<h4 style="color:var(--accent);margin:0 0 8px;font-size:14px">
<label class="fl-toggle" style="font-weight:700"><input type="checkbox" name="vps_enabled" value="1" onchange="toggleGroup(this, 'vps-group')" <?php echo ($list->vps_enabled ?? 0) ? 'checked' : ''; ?>> VPS (If Assigned)</label>
</h4>
<div id="vps-group" style="display:<?php echo ($list->vps_enabled ?? 0) ? 'block' : 'none'; ?>;margin-top:10px">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px">
<label class="fl-toggle"><input type="checkbox" name="iso_mount" value="1" <?php echo ($list->iso_mount ?? 0) ? 'checked' : ''; ?>> ISO Mount</label>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-top:10px">
<div class="form-group"><label>vCPU</label><input name="vcpu" type="number" value="<?php echo $list->vcpu ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>RAM (GB)</label><input name="ram" type="number" value="<?php echo $list->ram ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Storage (GB)</label><input name="vps_storage" type="number" value="<?php echo $list->vps_storage ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Bandwidth (TB)</label><input name="vps_bandwidth" type="number" value="<?php echo $list->vps_bandwidth ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Snapshots</label><input name="snapshots" type="number" value="<?php echo $list->snapshots ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>Backup Slots</label><input name="vps_backups" type="number" value="<?php echo $list->vps_backups ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>IPv4 Count</label><input name="ipv4" type="number" value="<?php echo $list->ipv4 ?? 0; ?>" style="width:100%"></div>
<div class="form-group"><label>IPv6 Count</label><input name="ipv6" type="number" value="<?php echo $list->ipv6 ?? 0; ?>" style="width:100%"></div>
</div>
</div>
</div>

<!-- Legacy toggles (hidden, kept for backward compat) -->
<div style="display:none">
<label><input type="checkbox" name="radio" value="1" <?php echo $list->radio ? 'checked' : ''; ?>></label>
<label><input type="checkbox" name="shoutcast" value="1" <?php echo $list->shoutcast ? 'checked' : ''; ?>></label>
<label><input type="checkbox" name="game" value="1" <?php echo $list->game ? 'checked' : ''; ?>></label>
</div>

<div style="margin-top:24px;display:flex;gap:12px">
<button type="submit" class="btn primary"><i class="bi bi-check-lg"></i> Update</button>
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
</script>