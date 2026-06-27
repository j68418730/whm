<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-person-plus"></i> Create Account</h2>
<p style="color:#64748b;margin:4px 0 0">Create a new hosting account. All fields marked * are required.</p>
</div>
</div>

<form method="POST" action="/admin/account/store">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">

<!-- Left Column -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-person"></i> Account Details</h4>
<div class="form-group"><label>Username *</label>
<input name="username" required placeholder="e.g. johndoe" style="width:100%">
<small style="color:#64748b">Used for FTP, SSH, and system user. Lowercase only.</small>
</div>
<div class="form-group"><label>Email *</label>
<input type="email" name="email" required placeholder="user@example.com" style="width:100%">
</div>
<div class="form-group"><label>Password *</label>
<div style="display:flex;gap:6px">
<input type="password" name="password" required minlength="8" id="pw" style="flex:1" placeholder="Min 8 characters">
<button type="button" class="btn btn-sm secondary" onclick="var p=Math.random().toString(36).slice(2,10)+Math.random().toString(36).toUpperCase().slice(2,4);document.getElementById('pw').value=p" style="white-space:nowrap">Generate</button>
</div>
</div>
<div class="form-group"><label>First Name</label><input name="first_name" style="width:100%"></div>
<div class="form-group"><label>Last Name</label><input name="last_name" style="width:100%"></div>
</div>

<!-- Right Column -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-globe"></i> Domain & Package</h4>
<div class="form-group"><label>Domain *</label>
<input name="domain" required placeholder="example.com" style="width:100%">
<small style="color:#64748b">Primary domain for this account.</small>
</div>
<div class="form-group"><label>Package</label>
<select name="package_id" style="width:100%" onchange="updatePkgDetails(this)">
<option value="">-- No Package --</option>
<option value="custom">-- Manual Custom --</option>
<?php if (isset($packages)): foreach ($packages as $p): ?>
<option value="<?php echo $p->id; ?>" data-disk="<?php echo $p->disk_space ?? 0; ?>" data-bw="<?php echo $p->bandwidth ?? 0; ?>" data-email="<?php echo $p->email_accounts ?? 0; ?>" data-db="<?php echo $p->databases ?? 0; ?>" data-price="<?php echo $p->monthly_price ?? 0; ?>">
<?php echo htmlspecialchars($p->name, ENT_QUOTES, 'UTF-8'); ?> ($<?php echo number_format($p->monthly_price ?? 0, 2); ?>/mo)
</option>
<?php endforeach; endif; ?>
</select>
<div id="pkgDetails" style="display:none;margin-top:8px"></div>
</div>
<div id="customPkgFields" style="display:none">
<div style="margin-top:10px;padding:12px;background:rgba(0,140,255,.04);border-radius:8px;border:1px solid rgba(0,140,255,.1)">
<h5 style="margin:0 0 8px;font-size:13px;color:var(--accent)">Resource Limits</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<div class="form-group" style="margin:0"><label style="font-size:11px">Disk Space (GB)</label><input name="custom_disk" type="number" value="10" style="width:100%;padding:5px 8px;font-size:12px"><small>Shared by all services (web, music, game storage, etc.)</small></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Bandwidth (GB)</label><input name="custom_bw" type="number" value="100" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Max Email Accounts</label><input name="custom_email" type="number" value="-1" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Max Databases</label><input name="custom_dbs" type="number" value="-1" style="width:100%;padding:5px 8px;font-size:12px"></div>
<div class="form-group" style="margin:0"><label style="font-size:11px">Price ($/mo)</label><input name="custom_price" type="number" step="0.01" value="0" style="width:100%;padding:5px 8px;font-size:12px"></div>
</div>

<h5 style="margin:12px 0 8px;font-size:13px;color:var(--accent)">General Features</h5>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:12px">
<label><input type="checkbox" name="custom_features[]" value="cron" checked> Cron Jobs</label>
<label><input type="checkbox" name="custom_features[]" value="ssh"> SSH Access</label>
<label><input type="checkbox" name="custom_features[]" value="ssl" checked> SSL</label>
<label><input type="checkbox" name="custom_features[]" value="git" checked> Git</label>
<label><input type="checkbox" name="custom_features[]" value="nodejs"> Node.js</label>
<label><input type="checkbox" name="custom_features[]" value="python"> Python</label>
<label><input type="checkbox" name="custom_features[]" value="ruby"> Ruby</label>
<label><input type="checkbox" name="custom_features[]" value="terminal"> Terminal</label>
<label><input type="checkbox" name="custom_features[]" value="backups" checked> Backups</label>
<label><input type="checkbox" name="custom_features[]" value="installer" checked> One Click Installer</label>
<label><input type="checkbox" name="custom_features[]" value="builder"> Website Builder</label>
<label><input type="checkbox" name="custom_features[]" value="ai_builder"> AI Website Builder</label>
<label><input type="checkbox" name="custom_features[]" value="ai_assistant"> AI Assistant</label>
<label><input type="checkbox" name="custom_features[]" value="marketplace"> Plugin Marketplace</label>
<label><input type="checkbox" name="custom_features[]" value="api"> API Access</label>
<label><input type="checkbox" name="custom_features[]" value="webhooks"> Webhooks</label>
<label><input type="checkbox" name="custom_features[]" value="chat" onchange="toggleCustomChat(this)"> Chatbox</label>
<label id="chat-voice-label" style="display:none;padding-left:16px;font-size:11px"><input type="checkbox" name="custom_features[]" value="chat_voice"> Voice</label>
<label id="chat-video-label" style="display:none;padding-left:16px;font-size:11px"><input type="checkbox" name="custom_features[]" value="chat_video"> Voice + Video</label>
<label><input type="checkbox" name="custom_features[]" value="dj_panel"> DJ Panel</label>
</div>

<!-- Streaming Package Features -->
<div style="margin-top:12px;border:1px solid rgba(10,132,255,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(10,132,255,.06);padding:8px 12px;font-size:13px;font-weight:600;color:var(--accent)">
<label style="cursor:pointer"><input type="checkbox" name="custom_streaming_enabled" value="1" onchange="toggleCustomSection(this, 'streaming-pkg')"> Streaming Package</label>
</div>
<div id="streaming-pkg" style="display:none;padding:10px 12px">

<?php
$streamingGroups = [
    'General' => [
        ['type'=>'select','name'=>'str_engine','label'=>'Streaming Engine','options'=>[''=>'Select...','shoutcast_v1'=>'SHOUTcast v1','shoutcast_v2'=>'SHOUTcast v2','icecast'=>'Icecast']],
        ['type'=>'checkbox','name'=>'str_shoutcast_v1','label'=>'SHOUTcast v1'],
        ['type'=>'checkbox','name'=>'str_shoutcast_v2','label'=>'SHOUTcast v2'],
        ['type'=>'checkbox','name'=>'str_icecast','label'=>'Icecast'],
        ['type'=>'checkbox','name'=>'str_future','label'=>'Future Engines'],
    ],
    'Stations' => [
        ['type'=>'number','name'=>'str_max_stations','label'=>'Maximum Stations','val'=>0],
        ['type'=>'number','name'=>'str_max_mounts','label'=>'Maximum Mount Points','val'=>0],
        ['type'=>'number','name'=>'str_max_relays','label'=>'Maximum Relays','val'=>0],
        ['type'=>'number','name'=>'str_max_relay_servers','label'=>'Maximum Relay Servers','val'=>0],
    ],
    'Listeners' => [
        ['type'=>'number','name'=>'str_max_listeners','label'=>'Maximum Listeners','val'=>0],
        ['type'=>'number','name'=>'str_burst_size','label'=>'Burst Size','val'=>0],
        ['type'=>'number','name'=>'str_conn_limit','label'=>'Connection Limit','val'=>0],
        ['type'=>'number','name'=>'str_reserved_slots','label'=>'Reserved Slots','val'=>0],
    ],
    'Stream Quality' => [
        ['type'=>'number','name'=>'str_max_bitrate','label'=>'Maximum Bitrate','val'=>0],
        ['type'=>'checkbox','name'=>'str_codec_mp3','label'=>'MP3'],
        ['type'=>'checkbox','name'=>'str_codec_aac','label'=>'AAC'],
        ['type'=>'checkbox','name'=>'str_codec_aacplus','label'=>'AAC+'],
        ['type'=>'checkbox','name'=>'str_codec_opus','label'=>'Opus'],
        ['type'=>'number','name'=>'str_sample_rate','label'=>'Sample Rate','val'=>44100],
        ['type'=>'checkbox','name'=>'str_stereo','label'=>'Stereo / Mono'],
    ],
    'DJs' => [
        ['type'=>'number','name'=>'str_max_djs','label'=>'Maximum DJs','val'=>0],
        ['type'=>'checkbox','name'=>'str_dj_groups','label'=>'DJ Groups'],
        ['type'=>'checkbox','name'=>'str_dj_scheduling','label'=>'DJ Scheduling'],
        ['type'=>'checkbox','name'=>'str_dj_live_override','label'=>'Live DJ Override'],
        ['type'=>'checkbox','name'=>'str_dj_auto_disconnect','label'=>'Auto Disconnect Idle DJs'],
    ],
    'AutoDJ' => [
        ['type'=>'checkbox','name'=>'str_autodj','label'=>'Enable AutoDJ'],
        ['type'=>'checkbox','name'=>'str_autodj_liquidsoap','label'=>'Liquidsoap'],
        ['type'=>'checkbox','name'=>'str_autodj_ffmpeg','label'=>'FFmpeg'],
        ['type'=>'checkbox','name'=>'str_autodj_playlists','label'=>'Playlists'],
        ['type'=>'checkbox','name'=>'str_autodj_smart','label'=>'Smart Playlists'],
        ['type'=>'checkbox','name'=>'str_autodj_scheduled','label'=>'Scheduled Playlists'],
        ['type'=>'checkbox','name'=>'str_autodj_jingles','label'=>'Jingles'],
        ['type'=>'checkbox','name'=>'str_autodj_sweepers','label'=>'Sweepers'],
        ['type'=>'checkbox','name'=>'str_autodj_crossfade','label'=>'Crossfade'],
        ['type'=>'number','name'=>'str_autodj_fade_time','label'=>'Fade Time (s)','val'=>3],
        ['type'=>'checkbox','name'=>'str_autodj_shuffle','label'=>'Shuffle'],
        ['type'=>'checkbox','name'=>'str_autodj_rotation','label'=>'Rotation Rules'],
        ['type'=>'checkbox','name'=>'str_autodj_fallback','label'=>'Fallback Playlist'],
    ],
    'Media Library' => [
        ['type'=>'note','label'=>'Music Storage','note'=>'Uses disk space allocation above'],
        ['type'=>'note','label'=>'Playlist Storage','note'=>'Uses disk space allocation above'],
        ['type'=>'number','name'=>'str_upload_limit','label'=>'Upload Limit (MB)','val'=>100],
        ['type'=>'checkbox','name'=>'str_bulk_upload','label'=>'Bulk Upload'],
        ['type'=>'checkbox','name'=>'str_zip_upload','label'=>'ZIP Upload'],
        ['type'=>'checkbox','name'=>'str_file_manager','label'=>'File Manager'],
        ['type'=>'checkbox','name'=>'str_artwork_upload','label'=>'Artwork Upload'],
    ],
    'SSL' => [
        ['type'=>'checkbox','name'=>'str_https_stream','label'=>'HTTPS Stream'],
        ['type'=>'checkbox','name'=>'str_ssl_source','label'=>'SSL Source Connection'],
        ['type'=>'checkbox','name'=>'str_lets_encrypt','label'=>"Let's Encrypt"],
        ['type'=>'checkbox','name'=>'str_custom_ssl','label'=>'Custom SSL'],
        ['type'=>'checkbox','name'=>'str_auto_renewal','label'=>'Auto Renewal'],
    ],
    'Statistics' => [
        ['type'=>'checkbox','name'=>'str_stats_listener','label'=>'Listener Statistics'],
        ['type'=>'checkbox','name'=>'str_stats_geo','label'=>'Geographic Statistics'],
        ['type'=>'checkbox','name'=>'str_stats_device','label'=>'Device Statistics'],
        ['type'=>'checkbox','name'=>'str_stats_historical','label'=>'Historical Statistics'],
        ['type'=>'checkbox','name'=>'str_stats_peak','label'=>'Peak Listeners'],
        ['type'=>'checkbox','name'=>'str_stats_bandwidth','label'=>'Bandwidth Usage'],
        ['type'=>'checkbox','name'=>'str_stats_reports','label'=>'Reports'],
    ],
    'Public Features' => [
        ['type'=>'checkbox','name'=>'str_public_player','label'=>'Public Player'],
        ['type'=>'checkbox','name'=>'str_public_stats','label'=>'Public Statistics'],
        ['type'=>'checkbox','name'=>'str_song_requests','label'=>'Song Requests'],
        ['type'=>'checkbox','name'=>'str_dedications','label'=>'Dedications'],
        ['type'=>'checkbox','name'=>'str_recently_played','label'=>'Recently Played'],
        ['type'=>'checkbox','name'=>'str_upcoming','label'=>'Upcoming Songs'],
        ['type'=>'checkbox','name'=>'str_album_artwork','label'=>'Album Artwork'],
        ['type'=>'checkbox','name'=>'str_musicbrainz','label'=>'MusicBrainz Metadata'],
        ['type'=>'checkbox','name'=>'str_embed_player','label'=>'Embed Player'],
        ['type'=>'checkbox','name'=>'str_widgets','label'=>'Widgets'],
    ],
    'Recording' => [
        ['type'=>'checkbox','name'=>'str_rec_live','label'=>'Record Live Stream'],
        ['type'=>'checkbox','name'=>'str_rec_scheduled','label'=>'Scheduled Recording'],
        ['type'=>'note','label'=>'Recording Storage','note'=>'Uses disk space allocation above'],
        ['type'=>'checkbox','name'=>'str_rec_download','label'=>'Download Recordings'],
    ],
    'API' => [
        ['type'=>'checkbox','name'=>'str_api_access','label'=>'API Access'],
        ['type'=>'checkbox','name'=>'str_api_webhooks','label'=>'Webhooks'],
        ['type'=>'checkbox','name'=>'str_api_metadata','label'=>'Metadata API'],
        ['type'=>'checkbox','name'=>'str_api_stats','label'=>'Statistics API'],
    ],
    'Backups' => [
        ['type'=>'checkbox','name'=>'str_backup_auto','label'=>'Automatic Backups'],
        ['type'=>'checkbox','name'=>'str_backup_manual','label'=>'Manual Backups'],
        ['type'=>'checkbox','name'=>'str_backup_restore','label'=>'Restore'],
        ['type'=>'number','name'=>'str_backup_retention','label'=>'Backup Retention (days)','val'=>30],
    ],
    'Monitoring' => [
        ['type'=>'checkbox','name'=>'str_monitor_health','label'=>'Health Monitoring'],
        ['type'=>'checkbox','name'=>'str_monitor_auto_restart','label'=>'Auto Restart'],
        ['type'=>'checkbox','name'=>'str_monitor_cpu','label'=>'CPU Monitoring'],
        ['type'=>'checkbox','name'=>'str_monitor_ram','label'=>'RAM Monitoring'],
        ['type'=>'checkbox','name'=>'str_monitor_service','label'=>'Service Monitoring'],
        ['type'=>'checkbox','name'=>'str_monitor_alerts','label'=>'Alerts'],
    ],
    'Security' => [
        ['type'=>'checkbox','name'=>'str_sec_ip_whitelist','label'=>'IP Whitelist'],
        ['type'=>'checkbox','name'=>'str_sec_ip_blacklist','label'=>'IP Blacklist'],
        ['type'=>'checkbox','name'=>'str_sec_geo_blocking','label'=>'Geo Blocking'],
        ['type'=>'checkbox','name'=>'str_sec_source_ip','label'=>'Source IP Lock'],
        ['type'=>'checkbox','name'=>'str_sec_login_attempts','label'=>'Login Attempts'],
        ['type'=>'checkbox','name'=>'str_sec_two_factor','label'=>'Two-Factor Authentication'],
    ],
];

foreach ($streamingGroups as $groupName => $fields):
?>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px"><?php echo $groupName; ?></h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<?php foreach ($fields as $f):
    if ($f['type'] === 'note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0">
<em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em>
</div>
<?php elseif ($f['type'] === 'checkbox'): ?>
<label style="cursor:pointer;padding:2px 0"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1"> <?php echo $f['label']; ?></label>
<?php elseif ($f['type'] === 'number'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo $f['val']; ?>" style="width:100%;padding:3px 6px;font-size:11px"></div>
<?php elseif ($f['type'] === 'select'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<select name="custom_pkg[<?php echo $f['name']; ?>]" style="width:100%;padding:3px 6px;font-size:11px">
<?php foreach ($f['options'] as $fv=>$fl): ?>
<option value="<?php echo $fv; ?>"><?php echo $fl; ?></option>
<?php endforeach; ?>
</select></div>
<?php endif; endforeach; ?>
</div>
<?php endforeach; ?>
<div style="margin-top:10px;padding:6px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b">
<strong>Note:</strong> Music &amp; playlist storage use the same disk space allocation defined in Resource Limits above. Separate storage quotas are not available.
</div>
</div>
</div>
<!-- END Streaming Package Features -->

<!-- Game Server Package Features -->
<div style="margin-top:10px;border:1px solid rgba(255,149,0,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(255,149,0,.06);padding:8px 12px;font-size:13px;font-weight:600;color:#FF9500">
<label style="cursor:pointer"><input type="checkbox" name="custom_game_enabled" value="1" onchange="toggleCustomSection(this, 'game-pkg')"> Game Server Package</label>
</div>
<div id="game-pkg" style="display:none;padding:10px 12px">

<?php
$gameGroups = [
    'General' => [
        ['type'=>'number','name'=>'game_max_servers','label'=>'Maximum Game Servers','val'=>0],
        ['type'=>'number','name'=>'game_max_instances','label'=>'Maximum Instances','val'=>0],
        ['type'=>'checkbox','name'=>'game_templates','label'=>'Server Templates'],
    ],
    'Steam' => [
        ['type'=>'checkbox','name'=>'game_steamcmd','label'=>'SteamCMD'],
        ['type'=>'checkbox','name'=>'game_steam_auto_login','label'=>'Automatic Steam Login'],
        ['type'=>'checkbox','name'=>'game_workshop','label'=>'Workshop Support'],
        ['type'=>'checkbox','name'=>'game_workshop_auto_update','label'=>'Workshop Auto Update'],
        ['type'=>'checkbox','name'=>'game_auto_updates','label'=>'Automatic Game Updates'],
    ],
    'Resources' => [
        ['type'=>'note','label'=>'CPU, RAM, Disk','note'=>'Allocated from account resource limits above'],
        ['type'=>'number','name'=>'game_cpu_cores','label'=>'CPU Cores','val'=>1],
        ['type'=>'number','name'=>'game_ram','label'=>'RAM (GB)','val'=>1],
        ['type'=>'checkbox','name'=>'game_nvme','label'=>'NVMe Storage'],
        ['type'=>'checkbox','name'=>'game_network_priority','label'=>'Network Priority'],
    ],
    'Network' => [
        ['type'=>'checkbox','name'=>'game_public_ip','label'=>'Public IP'],
        ['type'=>'checkbox','name'=>'game_ipv6','label'=>'IPv6'],
        ['type'=>'number','name'=>'game_additional_ports','label'=>'Additional Ports','val'=>0],
        ['type'=>'checkbox','name'=>'game_custom_ports','label'=>'Custom Ports'],
        ['type'=>'checkbox','name'=>'game_port_range','label'=>'Port Range'],
    ],
    'Game Features' => [
        ['type'=>'checkbox','name'=>'game_mod_support','label'=>'Mod Support'],
        ['type'=>'checkbox','name'=>'game_plugin_support','label'=>'Plugin Support'],
        ['type'=>'checkbox','name'=>'game_custom_maps','label'=>'Custom Maps'],
        ['type'=>'checkbox','name'=>'game_custom_config','label'=>'Custom Config Files'],
        ['type'=>'checkbox','name'=>'game_sched_restarts','label'=>'Scheduled Restarts'],
        ['type'=>'checkbox','name'=>'game_auto_restart_crash','label'=>'Auto Restart on Crash'],
        ['type'=>'checkbox','name'=>'game_auto_update','label'=>'Auto Update'],
        ['type'=>'checkbox','name'=>'game_console','label'=>'Console Access'],
        ['type'=>'checkbox','name'=>'game_rcon','label'=>'RCON'],
        ['type'=>'checkbox','name'=>'game_web_console','label'=>'Web Console'],
    ],
    'File Management' => [
        ['type'=>'checkbox','name'=>'game_file_manager','label'=>'File Manager'],
        ['type'=>'checkbox','name'=>'game_sftp','label'=>'SFTP'],
        ['type'=>'checkbox','name'=>'game_ftp','label'=>'FTP'],
        ['type'=>'checkbox','name'=>'game_upload_manager','label'=>'Upload Manager'],
        ['type'=>'checkbox','name'=>'game_download_manager','label'=>'Download Manager'],
        ['type'=>'checkbox','name'=>'game_archive_manager','label'=>'Archive Manager'],
    ],
    'Players' => [
        ['type'=>'number','name'=>'game_max_players','label'=>'Maximum Player Slots','val'=>0],
        ['type'=>'number','name'=>'game_reserved_slots','label'=>'Reserved Slots','val'=>0],
        ['type'=>'checkbox','name'=>'game_whitelist','label'=>'Whitelist'],
        ['type'=>'checkbox','name'=>'game_blacklist','label'=>'Blacklist'],
        ['type'=>'checkbox','name'=>'game_bans','label'=>'Bans'],
        ['type'=>'checkbox','name'=>'game_admins','label'=>'Admins'],
    ],
    'Backups' => [
        ['type'=>'checkbox','name'=>'game_backup_auto','label'=>'Automatic Backups'],
        ['type'=>'checkbox','name'=>'game_backup_manual','label'=>'Manual Backups'],
        ['type'=>'number','name'=>'game_snapshots','label'=>'Snapshots','val'=>0],
        ['type'=>'checkbox','name'=>'game_backup_restore','label'=>'Restore'],
        ['type'=>'number','name'=>'game_backup_retention','label'=>'Backup Retention (days)','val'=>14],
    ],
    'Monitoring' => [
        ['type'=>'checkbox','name'=>'game_monitor_cpu','label'=>'CPU Usage'],
        ['type'=>'checkbox','name'=>'game_monitor_ram','label'=>'RAM Usage'],
        ['type'=>'checkbox','name'=>'game_monitor_disk','label'=>'Disk Usage'],
        ['type'=>'checkbox','name'=>'game_monitor_players','label'=>'Player Count'],
        ['type'=>'checkbox','name'=>'game_monitor_uptime','label'=>'Uptime'],
        ['type'=>'checkbox','name'=>'game_monitor_crash','label'=>'Crash Detection'],
        ['type'=>'checkbox','name'=>'game_monitor_recovery','label'=>'Automatic Recovery'],
    ],
    'Security' => [
        ['type'=>'checkbox','name'=>'game_sec_firewall','label'=>'Firewall Rules'],
        ['type'=>'checkbox','name'=>'game_sec_ddos','label'=>'DDoS Protection'],
        ['type'=>'checkbox','name'=>'game_sec_ip_restrict','label'=>'IP Restrictions'],
        ['type'=>'checkbox','name'=>'game_sec_two_factor','label'=>'Two-Factor Authentication'],
    ],
    'API' => [
        ['type'=>'checkbox','name'=>'game_api_rest','label'=>'REST API'],
        ['type'=>'checkbox','name'=>'game_api_webhooks','label'=>'Webhooks'],
        ['type'=>'checkbox','name'=>'game_api_console','label'=>'Console API'],
        ['type'=>'checkbox','name'=>'game_api_stats','label'=>'Statistics API'],
    ],
    'Marketplace' => [
        ['type'=>'checkbox','name'=>'game_market_mods','label'=>'One-Click Mod Installation'],
        ['type'=>'checkbox','name'=>'game_market_plugins','label'=>'One-Click Plugin Installation'],
        ['type'=>'checkbox','name'=>'game_market_maps','label'=>'One-Click Map Installation'],
        ['type'=>'checkbox','name'=>'game_market_templates','label'=>'Template Marketplace'],
    ],
    'Scheduling' => [
        ['type'=>'checkbox','name'=>'game_sched_restarts','label'=>'Scheduled Restarts'],
        ['type'=>'checkbox','name'=>'game_sched_backups','label'=>'Scheduled Backups'],
        ['type'=>'checkbox','name'=>'game_sched_updates','label'=>'Scheduled Updates'],
        ['type'=>'checkbox','name'=>'game_sched_events','label'=>'Scheduled Events'],
    ],
    'Logging' => [
        ['type'=>'checkbox','name'=>'game_logs_console','label'=>'Console Logs'],
        ['type'=>'checkbox','name'=>'game_logs_player','label'=>'Player Logs'],
        ['type'=>'checkbox','name'=>'game_logs_chat','label'=>'Chat Logs'],
        ['type'=>'checkbox','name'=>'game_logs_crash','label'=>'Crash Logs'],
        ['type'=>'checkbox','name'=>'game_logs_audit','label'=>'Audit Logs'],
    ],
];

foreach ($gameGroups as $groupName => $fields):
?>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px"><?php echo $groupName; ?></h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<?php foreach ($fields as $f):
    if ($f['type'] === 'note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0">
<em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em>
</div>
<?php elseif ($f['type'] === 'checkbox'): ?>
<label style="cursor:pointer;padding:2px 0"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1"> <?php echo $f['label']; ?></label>
<?php elseif ($f['type'] === 'number'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo $f['val']; ?>" style="width:100%;padding:3px 6px;font-size:11px"></div>
<?php endif; endforeach; ?>
</div>
<?php endforeach; ?>
<div style="margin-top:10px;padding:6px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b">
<strong>Note:</strong> Game server storage (maps, mods, backups) uses the same disk space allocation defined in Resource Limits above.
</div>
</div>
</div>
<!-- END Game Server Package Features -->

</div>
</div>
</div>
<div class="form-group"><label>PHP Version</label>
<select name="php_version" style="width:100%">
<option value="">Server Default (8.2)</option>
<?php foreach (['5.6','7.0','7.1','7.2','7.3','7.4','8.0','8.1','8.2','8.3','8.4','8.5'] as $v): ?>
<option value="<?php echo $v; ?>"<?php if ($v === '8.2') echo ' selected'; ?>><?php echo $v; ?></option>
<?php endforeach; ?>
</select>
</div>
</div>

<!-- IP Selection -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-ethernet"></i> IP Address</h4>
<?php
$availIps = [];
try {
    $pdo = \Core\Application::getInstance()->get('db')->pdo();
    $q = $pdo->query("SELECT * FROM server_ips WHERE assigned_to IS NULL OR assigned_to = '' ORDER BY ip");
    if ($q) $availIps = $q->fetchAll(PDO::FETCH_OBJ);
} catch (\Exception $e) {}
?>
<div class="form-group">
<select name="ip" style="width:100%">
<option value="" selected>Auto-assign (shared IP)</option>
<?php if (!empty($availIps)): foreach ($availIps as $ip): ?>
<option value="<?php echo htmlspecialchars($ip->ip); ?>"><?php echo htmlspecialchars($ip->ip); ?> (<?php echo htmlspecialchars($ip->server ?? 'main'); ?>)</option>
<?php endforeach; endif; ?>
</select>
<small style="color:#64748b">Choose a specific IP or leave as auto-assign.</small>
</div>
</div>

<!-- Additional Features -->
<div class="card">
<h4 style="color:var(--accent);margin-bottom:16px"><i class="bi bi-gear"></i> Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ssh" checked> SSH Access
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ftp" checked> FTP Access
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="email" checked> Email
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="mysql" checked> MySQL
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="ssl" checked> Free SSL
</label>
<label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#cbd5e1;cursor:pointer">
<input type="checkbox" name="features[]" value="dns" checked> DNS Zone
</label>
</div>
</div>

</div>

<div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-start">
<button type="submit" class="btn btn-lg primary"><i class="bi bi-check-circle"></i> Create Account</button>
<a href="/admin/account" class="btn btn-lg secondary"><i class="bi bi-x-circle"></i> Cancel</a>
</div>
</form>

<script>
function toggleCustomChat(cb) {
    document.getElementById('chat-voice-label').style.display = cb.checked ? 'inline' : 'none';
    document.getElementById('chat-video-label').style.display = cb.checked ? 'inline' : 'none';
}
function toggleCustomSection(cb, id) {
    document.getElementById(id).style.display = cb.checked ? 'block' : 'none';
}
function updatePkgDetails(sel) {
    var opt = sel.options[sel.selectedIndex];
    var div = document.getElementById('pkgDetails');
    var customDiv = document.getElementById('customPkgFields');
    if (opt.value === 'custom') {
        div.style.display = 'none';
        customDiv.style.display = 'block';
        return;
    }
    customDiv.style.display = 'none';
    if (!opt.value || !opt.dataset) { div.style.display = 'none'; return; }
    var disk = opt.dataset.disk || 0;
    var bw = opt.dataset.bw || 0;
    var email = opt.dataset.email || 0;
    var db = opt.dataset.db || 0;
    var price = opt.dataset.price || 0;
    div.innerHTML = '<div style="background:rgba(0,140,255,.06);border-radius:8px;padding:10px;font-size:12px;color:#94a3b8">' +
        '<strong>' + opt.text + '</strong><br>' +
        '💾 ' + disk + ' GB Disk · 📶 ' + bw + ' GB Bandwidth · 📧 ' + email + ' Emails · 🗄 ' + db + ' Databases<br>' +
        '<span style="color:#0A84FF;font-weight:700">$' + parseFloat(price).toFixed(2) + '/month</span></div>';
    div.style.display = 'block';
}
</script>