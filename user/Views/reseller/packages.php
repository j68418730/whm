<div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<div>
<h3 style="color:var(--accent);margin:0 0 4px">My Packages</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin:0">
<?php $rType = $reseller->type ?? 'web_reseller'; echo $rType === 'icecast_reseller' ? 'As a radio reseller you can sell: <b>Radio / Music, Game Server, Hosting, Custom</b>.' : 'As a web reseller you can sell: <b>Hosting, VPS, Domain, Email, Custom</b>.'; ?>
A package defines what a server can do. Cost is set later on billing products.
</p>
</div>
<a href="#" class="btn primary" id="btnNewPkg"><i class="bi bi-plus-lg"></i> Create Package</a>
</div>

<div class="stats-grid" style="margin-bottom:18px">
<div class="stat-card"><h3>Total Packages</h3><div class="value"><?php echo $packagesStats['total_packages']; ?></div><div class="label">All packages</div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $packagesStats['active_packages']; ?></div><div class="label">Currently available</div></div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="card" id="pkgFormCard" style="display:none">
<h4 style="color:var(--accent);margin-bottom:12px" id="pkgFormTitle"><i class="bi bi-box-seam"></i> Create Package</h4>
<form method="POST" id="pkgForm" action="/reseller/packages/store">
<input type="hidden" name="id" id="pkg_id" value="0">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Package Name *</label>
<input type="text" name="name" id="pkg_name" required placeholder="e.g. Starter">
<small style="color:#64748b">Public id becomes <span id="slugPreview">username_name</span></small>
</div>
<div class="col-md-6">
<label class="form-label">Server Type (locked to your reseller)</label>
<select name="type" id="pkg_type" onchange="toggleStreaming()">
<?php foreach ($allowedTypes as $tk => $tl): ?>
<option value="<?php echo $tk; ?>" <?php echo $tk === 'hosting' ? 'selected' : ''; ?>><?php echo $tl; ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-12">
<label class="form-label">Description</label><input type="text" name="description" id="pkg_desc" placeholder="Short description">
</div>

<div class="col-md-3"><label class="form-label">Disk Space (MB)</label><input type="number" name="disk_space" id="pkg_disk" value="0"><small style="color:#64748b">Shared by all services</small></div>
<div class="col-md-3"><label class="form-label">Bandwidth (GB)</label><input type="number" name="bandwidth" id="pkg_bw" value="0"></div>
<div class="col-md-3"><label class="form-label">Storage (MB)</label><input type="number" name="storage_limit" id="pkg_storage" value="0"><small style="color:#64748b">Media &amp; file uploads</small></div>
<div class="col-md-3"><label class="form-label">Databases</label><input type="number" name="database_limit" id="pkg_dbs" value="0"></div>

<div class="col-md-12">
<h6 style="margin:10px 0 6px;color:var(--accent);font-size:13px;font-weight:700">General Features</h6>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:4px 12px;font-size:12px;padding:10px;background:rgba(0,0,0,.2);border:1px solid var(--border,rgba(0,191,255,.12));border-radius:8px">
<?php
// Mirror admin general features, but SSH is root-only (never offered to resellers/customers).
$genFeatures = ['cron'=>'Cron','ssl'=>'SSL','git'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups','installer'=>'Installer','builder'=>'Website Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API','webhooks'=>'Webhooks','chat'=>'Chatbox','chat_voice'=>'+ Voice','chat_video'=>'+ Video','dj_panel'=>'DJ Panel'];
foreach ($genFeatures as $k=>$l):
    $isSub = in_array($k, ['chat_voice','chat_video']);
?>
<label class="feature-check" style="<?php echo $isSub ? 'padding-left:16px;font-size:11px' : ''; ?>">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>" data-gen="<?php echo $k; ?>"> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
<small style="color:#64748b">SSH is not offered — root only.</small>
</div>

<!-- Streaming Package -->
<div class="col-md-12">
<div style="margin-top:14px;border:1px solid rgba(10,132,255,.25);border-radius:8px;overflow:hidden" id="streamingSection">
<div style="background:rgba(10,132,255,.08);padding:8px 12px;font-size:13px;font-weight:700;color:var(--accent)">
<label style="cursor:pointer"><input type="checkbox" name="custom_streaming_enabled" id="custom_streaming_enabled" value="1" onchange="toggleSection('str-pkg')"> Streaming Package</label>
</div>
<div id="str-pkg" style="display:none;padding:10px 12px;background:rgba(0,0,0,.15)">
<div style="font-size:11px;color:#64748b;padding-bottom:6px">Radio / music streaming server capabilities.</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px">
<?php
$streamingFields = [
 'General' => [
   ['t'=>'select','n'=>'str_engine','l'=>'Streaming Engine','o'=>[''=>'Select...','shoutcast_v1'=>'SHOUTcast v1','shoutcast_v2'=>'SHOUTcast v2','icecast'=>'Icecast'],'d'=>''],
   ['t'=>'cb','n'=>'str_shoutcast_v1','l'=>'SHOUTcast v1'],
   ['t'=>'cb','n'=>'str_shoutcast_v2','l'=>'SHOUTcast v2'],
   ['t'=>'cb','n'=>'str_icecast','l'=>'Icecast'],
   ['t'=>'cb','n'=>'str_future','l'=>'Future Engines'],
 ],
 'Stations' => [
   ['t'=>'num','n'=>'str_max_stations','l'=>'Maximum Stations','d'=>0],
   ['t'=>'num','n'=>'str_max_mounts','l'=>'Max Mount Points','d'=>0],
   ['t'=>'num','n'=>'str_max_relays','l'=>'Max Relays','d'=>0],
   ['t'=>'num','n'=>'str_max_relay_servers','l'=>'Max Relay Servers','d'=>0],
 ],
 'Listeners' => [
   ['t'=>'num','n'=>'str_max_listeners','l'=>'Max Listeners','d'=>0],
   ['t'=>'num','n'=>'str_burst_size','l'=>'Burst Size','d'=>0],
   ['t'=>'num','n'=>'str_conn_limit','l'=>'Connection Limit','d'=>0],
   ['t'=>'num','n'=>'str_reserved_slots','l'=>'Reserved Slots','d'=>0],
 ],
 'Stream Quality' => [
   ['t'=>'num','n'=>'str_max_bitrate','l'=>'Max Bitrate','d'=>0],
   ['t'=>'cb','n'=>'str_codec_mp3','l'=>'MP3'],
   ['t'=>'cb','n'=>'str_codec_aac','l'=>'AAC'],
   ['t'=>'cb','n'=>'str_codec_aacplus','l'=>'AAC+'],
   ['t'=>'cb','n'=>'str_codec_opus','l'=>'Opus'],
   ['t'=>'num','n'=>'str_sample_rate','l'=>'Sample Rate','d'=>44100],
   ['t'=>'cb','n'=>'str_stereo','l'=>'Stereo'],
 ],
 'DJs' => [
   ['t'=>'num','n'=>'str_max_djs','l'=>'Max DJs','d'=>0],
   ['t'=>'cb','n'=>'str_dj_groups','l'=>'DJ Groups'],
   ['t'=>'cb','n'=>'str_dj_scheduling','l'=>'DJ Scheduling'],
   ['t'=>'cb','n'=>'str_dj_live_override','l'=>'Live DJ Override'],
   ['t'=>'cb','n'=>'str_dj_auto_disconnect','l'=>'Auto Disconnect Idle DJs'],
 ],
 'AutoDJ' => [
   ['t'=>'cb','n'=>'str_autodj','l'=>'Enable AutoDJ'],
   ['t'=>'cb','n'=>'str_autodj_liquidsoap','l'=>'Liquidsoap'],
   ['t'=>'cb','n'=>'str_autodj_ffmpeg','l'=>'FFmpeg'],
   ['t'=>'cb','n'=>'str_autodj_playlists','l'=>'Playlists'],
   ['t'=>'cb','n'=>'str_autodj_smart','l'=>'Smart Playlists'],
   ['t'=>'cb','n'=>'str_autodj_scheduled','l'=>'Scheduled Playlists'],
   ['t'=>'cb','n'=>'str_autodj_jingles','l'=>'Jingles'],
   ['t'=>'cb','n'=>'str_autodj_sweepers','l'=>'Sweepers'],
   ['t'=>'cb','n'=>'str_autodj_crossfade','l'=>'Crossfade'],
   ['t'=>'num','n'=>'str_autodj_fade_time','l'=>'Fade Time (s)','d'=>3],
   ['t'=>'cb','n'=>'str_autodj_shuffle','l'=>'Shuffle'],
   ['t'=>'cb','n'=>'str_autodj_rotation','l'=>'Rotation Rules'],
   ['t'=>'cb','n'=>'str_autodj_fallback','l'=>'Fallback Playlist'],
 ],
 'Media Library' => [
   ['t'=>'note','l'=>'Music & Playlist Storage','note'=>'Uses disk allocation above'],
   ['t'=>'num','n'=>'str_upload_limit','l'=>'Upload Limit (MB)','d'=>100],
   ['t'=>'cb','n'=>'str_bulk_upload','l'=>'Bulk Upload'],
   ['t'=>'cb','n'=>'str_zip_upload','l'=>'ZIP Upload'],
   ['t'=>'cb','n'=>'str_file_manager','l'=>'File Manager'],
   ['t'=>'cb','n'=>'str_artwork_upload','l'=>'Artwork Upload'],
 ],
 'SSL' => [
   ['t'=>'cb','n'=>'str_https_stream','l'=>'HTTPS Stream'],
   ['t'=>'cb','n'=>'str_ssl_source','l'=>'SSL Source Connection'],
   ['t'=>'cb','n'=>'str_lets_encrypt','l'=>"Let's Encrypt"],
   ['t'=>'cb','n'=>'str_custom_ssl','l'=>'Custom SSL'],
   ['t'=>'cb','n'=>'str_auto_renewal','l'=>'Auto Renewal'],
 ],
 'Statistics' => [
   ['t'=>'cb','n'=>'str_stats_listener','l'=>'Listener Statistics'],
   ['t'=>'cb','n'=>'str_stats_geo','l'=>'Geographic Statistics'],
   ['t'=>'cb','n'=>'str_stats_device','l'=>'Device Statistics'],
   ['t'=>'cb','n'=>'str_stats_historical','l'=>'Historical Statistics'],
   ['t'=>'cb','n'=>'str_stats_peak','l'=>'Peak Listeners'],
   ['t'=>'cb','n'=>'str_stats_bandwidth','l'=>'Bandwidth Usage'],
   ['t'=>'cb','n'=>'str_stats_reports','l'=>'Reports'],
 ],
 'Public Features' => [
   ['t'=>'cb','n'=>'str_public_player','l'=>'Public Player'],
   ['t'=>'cb','n'=>'str_public_stats','l'=>'Public Statistics'],
   ['t'=>'cb','n'=>'str_song_requests','l'=>'Song Requests'],
   ['t'=>'cb','n'=>'str_dedications','l'=>'Dedications'],
   ['t'=>'cb','n'=>'str_recently_played','l'=>'Recently Played'],
   ['t'=>'cb','n'=>'str_upcoming','l'=>'Upcoming Songs'],
   ['t'=>'cb','n'=>'str_album_artwork','l'=>'Album Artwork'],
   ['t'=>'cb','n'=>'str_musicbrainz','l'=>'MusicBrainz Metadata'],
   ['t'=>'cb','n'=>'str_embed_player','l'=>'Embed Player'],
   ['t'=>'cb','n'=>'str_widgets','l'=>'Widgets'],
 ],
 'Recording' => [
   ['t'=>'cb','n'=>'str_rec_live','l'=>'Record Live Stream'],
   ['t'=>'cb','n'=>'str_rec_scheduled','l'=>'Scheduled Recording'],
   ['t'=>'cb','n'=>'str_rec_download','l'=>'Download Recordings'],
 ],
 'API' => [
   ['t'=>'cb','n'=>'str_api_access','l'=>'API Access'],
   ['t'=>'cb','n'=>'str_api_webhooks','l'=>'Webhooks'],
   ['t'=>'cb','n'=>'str_api_metadata','l'=>'Metadata API'],
   ['t'=>'cb','n'=>'str_api_stats','l'=>'Statistics API'],
 ],
 'Backups' => [
   ['t'=>'cb','n'=>'str_backup_auto','l'=>'Automatic Backups'],
   ['t'=>'cb','n'=>'str_backup_manual','l'=>'Manual Backups'],
   ['t'=>'cb','n'=>'str_backup_restore','l'=>'Restore'],
   ['t'=>'num','n'=>'str_backup_retention','l'=>'Backup Retention (days)','d'=>30],
 ],
 'Monitoring' => [
   ['t'=>'cb','n'=>'str_monitor_health','l'=>'Health Monitoring'],
   ['t'=>'cb','n'=>'str_monitor_auto_restart','l'=>'Auto Restart'],
   ['t'=>'cb','n'=>'str_monitor_cpu','l'=>'CPU Monitoring'],
   ['t'=>'cb','n'=>'str_monitor_ram','l'=>'RAM Monitoring'],
   ['t'=>'cb','n'=>'str_monitor_service','l'=>'Service Monitoring'],
   ['t'=>'cb','n'=>'str_monitor_alerts','l'=>'Alerts'],
 ],
 'Security' => [
   ['t'=>'cb','n'=>'str_sec_ip_whitelist','l'=>'IP Whitelist'],
   ['t'=>'cb','n'=>'str_sec_ip_blacklist','l'=>'IP Blacklist'],
   ['t'=>'cb','n'=>'str_sec_geo_blocking','l'=>'Geo Blocking'],
   ['t'=>'cb','n'=>'str_sec_source_ip','l'=>'Source IP Lock'],
   ['t'=>'cb','n'=>'str_sec_login_attempts','l'=>'Login Attempts'],
   ['t'=>'cb','n'=>'str_sec_two_factor','l'=>'Two-Factor Authentication'],
 ],
];
foreach ($streamingFields as $gName=>$gFields): ?>
<h6 style="grid-column:1/-1;margin:10px 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase"><?php echo $gName; ?></h6>
<?php foreach ($gFields as $f):
    if ($f['t']==='note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0"><em><?php echo $f['l']; ?>: <?php echo $f['note']; ?></em></div>
<?php elseif ($f['t']==='cb'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['n']; ?>]" value="1" data-sp="<?php echo substr($f['n'],4); ?>"> <?php echo $f['l']; ?></label>
<?php elseif ($f['t']==='num'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['l']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['n']; ?>]" data-sp="<?php echo substr($f['n'],4); ?>" data-sp-def="<?php echo $f['d']; ?>" value="<?php echo $f['d']; ?>" style="width:100%;padding:4px 6px;font-size:11px"></div>
<?php elseif ($f['t']==='select'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['l']; ?></label>
<select name="custom_pkg[<?php echo $f['n']; ?>]" data-sp="<?php echo substr($f['n'],4); ?>" data-sp-def="" style="width:100%;padding:4px 6px;font-size:11px">
<?php foreach ($f['o'] as $fv=>$fl): ?>
<option value="<?php echo $fv; ?>"><?php echo $fl; ?></option>
<?php endforeach; ?>
</select></div>
<?php endif; endforeach; endforeach; ?>
</div>
<small style="color:#64748b">Storage uses disk allocation above.</small>
</div>
</div>

<!-- Game Server Package -->
<div class="col-md-12">
<div style="margin-top:14px;border:1px solid rgba(255,149,0,.25);border-radius:8px;overflow:hidden" id="gameSection">
<div style="background:rgba(255,149,0,.08);padding:8px 12px;font-size:13px;font-weight:700;color:#FF9500">
<label style="cursor:pointer"><input type="checkbox" name="custom_game_enabled" id="custom_game_enabled" value="1" onchange="toggleSection('game-pkg')"> Game Server Package</label>
</div>
<div id="game-pkg" style="display:none;padding:10px 12px;background:rgba(0,0,0,.15)">
<div style="font-size:11px;color:#64748b;padding-bottom:6px">Game server hosting capabilities.</div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px">
<?php
$gameFields = [
 'General' => [
   ['t'=>'num','n'=>'game_max_servers','l'=>'Max Game Servers','d'=>0],
   ['t'=>'num','n'=>'game_max_instances','l'=>'Max Instances','d'=>0],
   ['t'=>'cb','n'=>'game_templates','l'=>'Server Templates'],
 ],
 'Steam' => [
   ['t'=>'cb','n'=>'game_steamcmd','l'=>'SteamCMD'],
   ['t'=>'cb','n'=>'game_steam_auto_login','l'=>'Automatic Steam Login'],
   ['t'=>'cb','n'=>'game_workshop','l'=>'Workshop Support'],
   ['t'=>'cb','n'=>'game_workshop_auto_update','l'=>'Workshop Auto Update'],
   ['t'=>'cb','n'=>'game_auto_updates','l'=>'Automatic Game Updates'],
 ],
 'Resources' => [
   ['t'=>'num','n'=>'game_cpu_cores','l'=>'CPU Cores','d'=>1],
   ['t'=>'num','n'=>'game_ram','l'=>'RAM (GB)','d'=>1],
   ['t'=>'cb','n'=>'game_nvme','l'=>'NVMe Storage'],
   ['t'=>'cb','n'=>'game_network_priority','l'=>'Network Priority'],
 ],
 'Network' => [
   ['t'=>'cb','n'=>'game_public_ip','l'=>'Public IP'],
   ['t'=>'cb','n'=>'game_ipv6','l'=>'IPv6'],
   ['t'=>'num','n'=>'game_additional_ports','l'=>'Additional Ports','d'=>0],
   ['t'=>'cb','n'=>'game_custom_ports','l'=>'Custom Ports'],
   ['t'=>'cb','n'=>'game_port_range','l'=>'Port Range'],
 ],
 'Game Features' => [
   ['t'=>'cb','n'=>'game_mod_support','l'=>'Mod Support'],
   ['t'=>'cb','n'=>'game_plugin_support','l'=>'Plugin Support'],
   ['t'=>'cb','n'=>'game_custom_maps','l'=>'Custom Maps'],
   ['t'=>'cb','n'=>'game_custom_config','l'=>'Custom Config Files'],
   ['t'=>'cb','n'=>'game_sched_restarts','l'=>'Scheduled Restarts'],
   ['t'=>'cb','n'=>'game_auto_restart_crash','l'=>'Auto Restart on Crash'],
   ['t'=>'cb','n'=>'game_auto_update','l'=>'Auto Update'],
   ['t'=>'cb','n'=>'game_console','l'=>'Console Access'],
   ['t'=>'cb','n'=>'game_rcon','l'=>'RCON'],
   ['t'=>'cb','n'=>'game_web_console','l'=>'Web Console'],
 ],
 'File Management' => [
   ['t'=>'cb','n'=>'game_file_manager','l'=>'File Manager'],
   ['t'=>'cb','n'=>'game_sftp','l'=>'SFTP'],
   ['t'=>'cb','n'=>'game_ftp','l'=>'FTP'],
   ['t'=>'cb','n'=>'game_upload_manager','l'=>'Upload Manager'],
   ['t'=>'cb','n'=>'game_download_manager','l'=>'Download Manager'],
   ['t'=>'cb','n'=>'game_archive_manager','l'=>'Archive Manager'],
 ],
 'Players' => [
   ['t'=>'num','n'=>'game_max_players','l'=>'Max Player Slots','d'=>0],
   ['t'=>'num','n'=>'game_reserved_slots','l'=>'Reserved Slots','d'=>0],
   ['t'=>'cb','n'=>'game_whitelist','l'=>'Whitelist'],
   ['t'=>'cb','n'=>'game_blacklist','l'=>'Blacklist'],
   ['t'=>'cb','n'=>'game_bans','l'=>'Bans'],
   ['t'=>'cb','n'=>'game_admins','l'=>'Admins'],
 ],
 'Backups' => [
   ['t'=>'cb','n'=>'game_backup_auto','l'=>'Automatic Backups'],
   ['t'=>'cb','n'=>'game_backup_manual','l'=>'Manual Backups'],
   ['t'=>'num','n'=>'game_snapshots','l'=>'Snapshots','d'=>0],
   ['t'=>'cb','n'=>'game_backup_restore','l'=>'Restore'],
   ['t'=>'num','n'=>'game_backup_retention','l'=>'Backup Retention (days)','d'=>14],
 ],
 'Monitoring' => [
   ['t'=>'cb','n'=>'game_monitor_cpu','l'=>'CPU Usage'],
   ['t'=>'cb','n'=>'game_monitor_ram','l'=>'RAM Usage'],
   ['t'=>'cb','n'=>'game_monitor_disk','l'=>'Disk Usage'],
   ['t'=>'cb','n'=>'game_monitor_players','l'=>'Player Count'],
   ['t'=>'cb','n'=>'game_monitor_uptime','l'=>'Uptime'],
   ['t'=>'cb','n'=>'game_monitor_crash','l'=>'Crash Detection'],
   ['t'=>'cb','n'=>'game_monitor_recovery','l'=>'Automatic Recovery'],
 ],
 'Security' => [
   ['t'=>'cb','n'=>'game_sec_firewall','l'=>'Firewall Rules'],
   ['t'=>'cb','n'=>'game_sec_ddos','l'=>'DDoS Protection'],
   ['t'=>'cb','n'=>'game_sec_ip_restrict','l'=>'IP Restrictions'],
   ['t'=>'cb','n'=>'game_sec_two_factor','l'=>'Two-Factor Authentication'],
 ],
 'API' => [
   ['t'=>'cb','n'=>'game_api_rest','l'=>'REST API'],
   ['t'=>'cb','n'=>'game_api_webhooks','l'=>'Webhooks'],
   ['t'=>'cb','n'=>'game_api_console','l'=>'Console API'],
   ['t'=>'cb','n'=>'game_api_stats','l'=>'Statistics API'],
 ],
 'Marketplace' => [
   ['t'=>'cb','n'=>'game_market_mods','l'=>'One-Click Mod Installation'],
   ['t'=>'cb','n'=>'game_market_plugins','l'=>'One-Click Plugin Installation'],
   ['t'=>'cb','n'=>'game_market_maps','l'=>'One-Click Map Installation'],
   ['t'=>'cb','n'=>'game_market_templates','l'=>'Template Marketplace'],
 ],
 'Scheduling' => [
   ['t'=>'cb','n'=>'game_sched_restarts','l'=>'Scheduled Restarts'],
   ['t'=>'cb','n'=>'game_sched_backups','l'=>'Scheduled Backups'],
   ['t'=>'cb','n'=>'game_sched_updates','l'=>'Scheduled Updates'],
   ['t'=>'cb','n'=>'game_sched_events','l'=>'Scheduled Events'],
 ],
 'Logging' => [
   ['t'=>'cb','n'=>'game_logs_console','l'=>'Console Logs'],
   ['t'=>'cb','n'=>'game_logs_player','l'=>'Player Logs'],
   ['t'=>'cb','n'=>'game_logs_chat','l'=>'Chat Logs'],
   ['t'=>'cb','n'=>'game_logs_crash','l'=>'Crash Logs'],
   ['t'=>'cb','n'=>'game_logs_audit','l'=>'Audit Logs'],
 ],
];
foreach ($gameFields as $gName=>$gFields): ?>
<h6 style="grid-column:1/-1;margin:10px 0 4px;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase"><?php echo $gName; ?></h6>
<?php foreach ($gFields as $f):
    if ($f['t']==='note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0"><em><?php echo $f['l']; ?>: <?php echo $f['note']; ?></em></div>
<?php elseif ($f['t']==='cb'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['n']; ?>]" value="1" data-gp="<?php echo substr($f['n'],5); ?>"> <?php echo $f['l']; ?></label>
<?php elseif ($f['t']==='num'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['l']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['n']; ?>]" data-gp="<?php echo substr($f['n'],5); ?>" data-gp-def="<?php echo $f['d']; ?>" value="<?php echo $f['d']; ?>" style="width:100%;padding:4px 6px;font-size:11px"></div>
<?php endif; endforeach; endforeach; ?>
</div>
<small style="color:#64748b">Storage uses disk allocation above.</small>
</div>
</div>

<!-- Products Using This Package (set up later) -->
<div class="col-md-12" style="margin-top:16px;border:1px solid rgba(74,222,128,.15);border-radius:8px;padding:12px">
<h6 style="color:#4ade80;font-size:13px;font-weight:700;margin:0 0 4px">Products Using This Package</h6>
<p style="color:#64748b;font-size:12px;margin:0">Cost is set on billing products linked to this package. This will be set up later.</p>
</div>

<div class="col-md-12 d-flex gap-2 mt-2">
<button type="submit" class="btn btn-primary">Save Package</button>
<button type="button" class="btn btn-secondary" onclick="resetPkg()">Reset</button>
</div>
</div>
</form>
</div>

<style>
.feature-check{display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;padding:2px 4px;border-radius:4px}
.feature-check:hover{background:rgba(0,140,255,.08)}
.pkg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-top:4px}
.pkg-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:10px;padding:16px;transition:.3s}
.pkg-card:hover{border-color:rgba(0,191,255,.2);transform:translateY(-2px)}
.pkg-card .p-name{font-size:15px;font-weight:700;margin-bottom:2px}
.pkg-card .p-type{font-size:11px;color:#64748b;margin-bottom:6px}
.pkg-card .p-features{font-size:11px;color:#94a3b8;margin-bottom:10px;line-height:1.6}
.pkg-card .p-status{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.pkg-card .p-actions{display:flex;gap:6px;margin-top:10px;flex-wrap:wrap}
.pkg-card .p-actions a,.pkg-card .p-actions button{padding:4px 12px;border-radius:5px;font-size:11px;text-decoration:none;font-weight:600;border:none;cursor:pointer;font-family:inherit;color:#e8edf5}
.chip{display:inline-block;padding:1px 7px;border-radius:4px;font-size:10px;font-weight:600;background:rgba(0,140,255,.1);color:#38bdf8;margin:2px 2px 0 0}
</style>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px"><?php echo count($packages ?? []); ?> Retail Package<?php echo count($packages ?? []) !== 1 ? 's' : ''; ?></h4>
<?php if (!empty($packages)): ?>
<div class="pkg-grid">
<?php foreach ($packages as $p): $feats = is_string($p->features??null) ? json_decode($p->features,true) : ($p->features ?? []); $feats = is_array($feats) ? $feats : []; $used = $clientCounts[$p->id] ?? 0;
$sp = $feats['streaming_package'] ?? []; $gp = $feats['game_package'] ?? [];
$genlabels = ['cron'=>'Cron','ssl'=>'SSL','git'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups','installer'=>'Installer','builder'=>'Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API','webhooks'=>'Webhooks','chat'=>'Chat','chat_voice'=>'Voice','chat_video'=>'Video','dj_panel'=>'DJ Panel'];
?>
<div class="pkg-card">
<div class="p-name"><?php echo htmlspecialchars($p->name); ?></div>
<div class="p-type"><?php echo htmlspecialchars($typeLabel($p->type)); ?> · <code style="font-size:10px"><?php echo htmlspecialchars($p->slug ?? ''); ?></code></div>
<div class="p-features">
<?php if ($p->disk_space): ?>💾 Disk <?php echo (int)$p->disk_space; ?> MB<br><?php endif; ?>
<?php if ($p->bandwidth): ?>📶 BW <?php echo (int)$p->bandwidth; ?> GB<br><?php endif; ?>
<?php if ($p->database_limit): ?>🗄 <?php echo (int)$p->database_limit; ?> DBs<br><?php endif; ?>
<?php if ($p->storage_limit): ?>🗃 <?php echo (int)$p->storage_limit; ?> MB storage<br><?php endif; ?>
<?php foreach ($feats as $k=>$v): if (in_array($k,['streaming_package','game_package'])) continue; if ($v && isset($genlabels[$k])): ?><span class="chip"><?php echo $genlabels[$k]; ?></span><?php endif; endforeach; ?>
<?php if (!empty($sp)): ?><div style="margin-top:4px"><strong style="color:var(--accent)">Streaming:</strong><br>
<?php if (isset($sp['max_stations'])): ?>Stations <?php echo (int)$sp['max_stations']; ?> · <?php endif; ?>
<?php if (isset($sp['max_listeners'])): ?>Listeners <?php echo (int)$sp['max_listeners']; ?> · <?php endif; ?>
<?php if (isset($sp['max_bitrate'])): ?>Bitrate <?php echo (int)$sp['max_bitrate']; ?><?php endif; ?></div><?php endif; ?>
<?php if (!empty($gp)): ?><div style="margin-top:4px"><strong style="color:#FF9500">Game Server:</strong><br>
<?php if (isset($gp['max_servers'])): ?>Servers <?php echo (int)$gp['max_servers']; ?> · <?php endif; ?>
<?php if (isset($gp['max_players'])): ?>Players <?php echo (int)$gp['max_players']; ?><?php endif; ?></div><?php endif; ?>
<?php if (!$p->disk_space && !$p->bandwidth && !$p->database_limit && !$p->storage_limit && empty($feats)): ?><span style="color:#475569">Minimal package</span><?php endif; ?>
</div>
<div style="margin-bottom:8px;font-size:11px;color:#64748b">👥 <?php echo $used; ?> client<?php echo $used !== 1 ? 's' : ''; ?> on this package</div>
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:6px">
<span class="p-status" style="background:<?php echo ($p->is_active ?? 1) ? 'rgba(74,222,128,.12);color:#4ade80' : 'rgba(248,113,113,.12);color:#f87171'; ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span>
<a href="/reseller/packages/toggle/<?php echo (int)$p->id; ?>" class="p-status" style="background:rgba(56,189,248,.12);color:#38bdf8;text-decoration:none"><?php echo ($p->is_active ?? 1) ? 'Deactivate' : 'Activate'; ?></a>
</div>
<div class="p-actions">
<button class="btn-sm" style="background:rgba(0,140,255,.1);color:#38bdf8" onclick='editPkg(<?php echo \json_encode($p); ?>)'>Edit</button>
<a href="/reseller/packages/clone/<?php echo (int)$p->id; ?>" style="background:rgba(74,222,128,.1);color:#4ade80" onclick="return confirm('Clone this package?')">Clone</a>
<a href="/reseller/packages/delete/<?php echo (int)$p->id; ?>" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('PERMANENTLY delete this package? This cannot be undone.')">Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="color:#64748b;text-align:center;padding:20px">No packages defined yet. <a href="#" onclick="document.getElementById('btnNewPkg').click();return false;" style="color:var(--primary,#008cff)">Create your first package</a></p>
<?php endif; ?>
</div>

<script>
function esc(s){var d=document.createElement('div');d.textContent=s==null?'':String(s);return d.innerHTML;}
var userSlug = '<?php echo strtolower(preg_replace("/[^a-z0-9]+/","", strtolower($user->name ?? "reseller"))); ?>';
function previewSlug() {
  var n = (document.getElementById('pkg_name').value||'').toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_|_$/g,'');
  document.getElementById('slugPreview').textContent = userSlug + '_' + (n||'name');
}
document.getElementById('pkg_name').addEventListener('input', previewSlug);
previewSlug();
function toggleSection(id){var cbs = { 'str-pkg':'custom_streaming_enabled','game-pkg':'custom_game_enabled' }; document.getElementById(id).style.display = document.getElementById(cbs[id]).checked ? 'block' : 'none'; }
function toggleStreaming(){
  var t = document.getElementById('pkg_type').value;
  document.getElementById('streamingSection').style.display = (t === 'music') ? 'block' : 'none';
}
function showForm(){
  document.getElementById('pkgFormCard').style.display = 'block';
  document.getElementById('pkgFormCard').scrollIntoView({behavior:'smooth',block:'start'});
  document.getElementById('pkg_name').focus();
}
document.getElementById('btnNewPkg').addEventListener('click', function(e){ e.preventDefault(); resetPkg(); showForm(); });
document.getElementById('pkg_type').addEventListener('change', toggleStreaming);
function editPkg(p){
  // Normalize features: new assoc format ({k:v, streaming_package:{...}, game_package:{...}}) or old flat array
  var feats = p.features;
  if (typeof feats === 'string') { try { feats = JSON.parse(feats); } catch(e){ feats = null; } }
  var isNew = feats && typeof feats === 'object' && !Array.isArray(feats);
  var flat = Array.isArray(feats) ? feats : [];
  var gen = isNew ? feats : {};
  var sp = isNew ? (feats.streaming_package || {}) : {};
  var gp = isNew ? (feats.game_package || {}) : {};

  document.getElementById('pkg_id').value = p.id;
  document.getElementById('pkgForm').action = '/reseller/packages/update/' + p.id;
  document.getElementById('pkgFormTitle').textContent = 'Edit Package — ' + p.name;
  document.getElementById('pkg_name').value = p.name;
  if (document.querySelector('#pkg_type option[value="'+p.type+'"]')) document.getElementById('pkg_type').value = p.type;
  document.getElementById('pkg_desc').value = p.description||'';
  document.getElementById('pkg_disk').value = p.disk_space;
  document.getElementById('pkg_bw').value = p.bandwidth;
  document.getElementById('pkg_storage').value = p.storage_limit;
  document.getElementById('pkg_dbs').value = p.database_limit;

  // General features
  document.querySelectorAll('.feature-check input[data-gen]').forEach(function(c){
    var v = (isNew) ? !!(gen[c.getAttribute('data-gen')]) : (flat.indexOf(c.getAttribute('data-gen'))>=0);
    c.checked = v;
  });

  // Streaming package
  var spEnabled = !!(sp && sp.enabled) || !isEmpty(sp);
  document.getElementById('custom_streaming_enabled').checked = spEnabled;
  document.querySelectorAll('#str-pkg [data-sp]').forEach(function(el){
    var k = el.getAttribute('data-sp');
    if (el.type === 'checkbox') { el.checked = !!(sp[k]); }
    else { el.value = (sp[k] !== undefined && sp[k] !== null && sp[k] !== '') ? sp[k] : el.getAttribute('data-sp-def') || ''; }
  });
  toggleSection('str-pkg');

  // Game package
  var gpEnabled = !!(gp && gp.enabled) || !isEmpty(gp);
  document.getElementById('custom_game_enabled').checked = gpEnabled;
  document.querySelectorAll('#game-pkg [data-gp]').forEach(function(el){
    var k = el.getAttribute('data-gp');
    if (el.type === 'checkbox') { el.checked = !!(gp[k]); }
    else { el.value = (gp[k] !== undefined && gp[k] !== null && gp[k] !== '') ? gp[k] : el.getAttribute('data-gp-def') || ''; }
  });
  toggleSection('game-pkg');
  toggleStreaming();
  showForm();
}
function isEmpty(o){ for(var k in o){ if(o.hasOwnProperty(k)) return false; } return true; }
function resetPkg(){
  document.getElementById('pkg_id').value=0; document.getElementById('pkgForm').action='/reseller/packages/store';
  document.getElementById('pkgFormTitle').textContent = 'Create Package';
  document.getElementById('pkgForm').reset(); previewSlug();
  document.getElementById('custom_streaming_enabled').checked = false;
  document.getElementById('custom_game_enabled').checked = false;
  document.getElementById('str-pkg').style.display='none';
  document.getElementById('game-pkg').style.display='none';
  toggleStreaming();
}
</script>
