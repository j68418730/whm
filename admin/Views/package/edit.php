<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>
<style>
.pkg-wrap{max-width:1100px}
.pkg-card{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid var(--border,rgba(0,191,255,.08));border-radius:12px;padding:18px;margin-bottom:16px}
.pkg-card h4{font-size:13px;font-weight:700;margin:0 0 12px;display:flex;align-items:center;gap:8px}
.pkg-card h4 small{font-size:11px;color:var(--text_muted,#64748b);font-weight:400}
.pkg-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px}
.pkg-grid .full{grid-column:1/-1}
.pkg-feat{display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:4px;font-size:12px}
.pkg-feat label.feature-check{display:flex;align-items:center;gap:6px;cursor:pointer;padding:3px 6px;border-radius:6px;font-size:12px}
.pkg-feat label.feature-check:hover{background:rgba(0,140,255,.06)}
.pkg-feat label.feature-check input{width:auto;accent-color:var(--primary,#008cff)}
.pkg-sub{background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:8px;padding:10px 12px;margin:8px 0}
.pkg-sub h6{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--text_muted,#64748b);margin:0 0 6px;font-weight:700}
.pkg-sub .row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px}
.pkg-tog{width:100%;text-align:left;background:transparent;border:none;cursor:pointer;padding:0;font:inherit;color:inherit;display:flex;align-items:center;gap:8px}
.pkg-note{grid-column:1/-1;font-size:11px;color:var(--text_muted,#64748b);padding:2px 0}
</style>

<div class="pkg-wrap">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:14px">
<div>
<h3 style="margin:0;color:var(--accent,#008cff)">✏️ Edit Package</h3>
<div style="font-size:12px;color:var(--text_muted,#64748b);margin-top:4px">#<?php echo (int)$package->id; ?> · <?php echo htmlspecialchars($package->type ?? 'web_hosting'); ?> · <?php echo $package->is_active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></div>
</div>
<a href="/admin/packages" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Packages</a>
</div>

<form method="POST" action="/admin/package/edit/<?php echo (int)$package->id; ?>">
<?php
$featuresRaw = $package->features ?? null;
$feats = is_string($featuresRaw) ? json_decode($featuresRaw, true) ?? [] : (is_array($featuresRaw) ? $featuresRaw : []);
$strPkg = $feats['streaming_package'] ?? [];
$gamePkg = $feats['game_package'] ?? [];
function ck($feats, $key, $section=null) {
    if ($section) { return !empty($feats[$section][$key]) ? 'checked' : ''; }
    return !empty($feats[$key]) ? 'checked' : '';
}
function val($feats, $key, $default=0, $section=null) {
    if ($section) return $feats[$section][$key] ?? $default;
    return $feats[$key] ?? $default;
}
function sl($feats, $key, $val, $section=null) {
    if ($section) return ($feats[$section][$key] ?? '') == $val ? 'selected' : '';
    return ($feats[$key] ?? '') == $val ? 'selected' : '';
}

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
        ['type'=>'checkbox','name'=>'str_stereo','label'=>'Stereo'],
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
        ['type'=>'note','label'=>'Music & Playlist Storage','note'=>'Uses disk space allocation above'],
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
        ['type'=>'note','label'=>'CPU, RAM, Disk','note'=>'Allocated from account resource limits'],
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
?>
<div class="pkg-card">
<h4><i class="bi bi-info-circle" style="color:var(--primary,#008cff)"></i> Basic Info</h4>
<div class="pkg-grid">
<div class="form-group"><label class="form-label">Name *</label><input name="name" class="form-control" value="<?php echo htmlspecialchars($package->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label class="form-label">Server Type</label><select name="type" class="form-select" id="pkgType" onchange="toggleStreaming()">
<option value="web_hosting" <?php echo ($package->type ?? '') === 'web_hosting' ? 'selected' : ''; ?>>Web Hosting</option>
<option value="web_reseller" <?php echo ($package->type ?? '') === 'web_reseller' ? 'selected' : ''; ?>>Web Reseller</option>
<option value="icecast" <?php echo ($package->type ?? '') === 'icecast' ? 'selected' : ''; ?>>Icecast Streaming</option>
<option value="icecast_reseller" <?php echo ($package->type ?? '') === 'icecast_reseller' ? 'selected' : ''; ?>>Icecast Reseller</option>
<option value="shoutcast" <?php echo ($package->type ?? '') === 'shoutcast' ? 'selected' : ''; ?>>SHOUTcast</option>
<option value="shoutcast_reseller" <?php echo ($package->type ?? '') === 'shoutcast_reseller' ? 'selected' : ''; ?>>SHOUTcast Reseller</option>
<option value="game_server" <?php echo ($package->type ?? '') === 'game_server' ? 'selected' : ''; ?>>Game Server</option>
<option value="vps" <?php echo ($package->type ?? '') === 'vps' ? 'selected' : ''; ?>>VPS</option>
<option value="dedicated" <?php echo ($package->type ?? '') === 'dedicated' ? 'selected' : ''; ?>>Dedicated</option>
<option value="dev" <?php echo ($package->type ?? '') === 'dev' ? 'selected' : ''; ?>>Dev</option>
</select></div>
<div class="form-group"><label class="form-label">Feature List <a href="/admin/feature-lists" style="color:var(--primary,#008cff);font-size:11px">(Manage)</a></label>
<select name="feature_list_id" class="form-select">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>" <?php echo ($package->feature_list_id ?? '') == $fl->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group"><label class="form-label">PHP Version</label><select name="php_version" class="form-select">
<option value="8.2" <?php echo ($package->php_version ?? '') === '8.2' ? 'selected' : ''; ?>>PHP 8.2</option>
<option value="8.1" <?php echo ($package->php_version ?? '') === '8.1' ? 'selected' : ''; ?>>PHP 8.1</option>
<option value="8.0" <?php echo ($package->php_version ?? '') === '8.0' ? 'selected' : ''; ?>>PHP 8.0</option>
<option value="7.4" <?php echo ($package->php_version ?? '') === '7.4' ? 'selected' : ''; ?>>PHP 7.4</option>
</select></div>
</div>
</div>

<div class="pkg-card">
<h4><i class="bi bi-hdd-stack" style="color:#facc15"></i> Resources <small>disk is shared by all services</small></h4>
<div class="pkg-grid">
<div class="form-group"><label class="form-label">Disk Space (GB)</label><input name="disk_space" type="number" class="form-control" value="<?php echo $package->disk_space ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Bandwidth (GB)</label><input name="bandwidth" type="number" class="form-control" value="<?php echo $package->bandwidth ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Max Domains</label><input name="max_domains" type="number" class="form-control" value="<?php echo $package->max_domains ?? 1; ?>"></div>
<div class="form-group"><label class="form-label">Max Subdomains</label><input name="max_subdomains" type="number" class="form-control" value="<?php echo $package->max_subdomains ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Email Accounts</label><input name="email_accounts" type="number" class="form-control" value="<?php echo $package->email_accounts ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">FTP Accounts</label><input name="ftp_accounts" type="number" class="form-control" value="<?php echo $package->ftp_accounts ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">MySQL Databases</label><input name="databases" type="number" class="form-control" value="<?php echo $package->databases ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Parked Domains</label><input name="parked_domains" type="number" class="form-control" value="<?php echo $package->parked_domains ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Addon Domains</label><input name="addon_domains" type="number" class="form-control" value="<?php echo $package->addon_domains ?? 0; ?>"></div>
</div>
</div>

<div class="pkg-card" id="streamingSection">
<h4><i class="bi bi-broadcast" style="color:#a78bfa"></i> Streaming Limits <small>listener/bitrate/dj quotas</small></h4>
<div class="pkg-grid">
<div class="form-group"><label class="form-label">Listener Limit</label><input name="listener_limit" type="number" class="form-control" value="<?php echo $package->listener_limit ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">Bitrate (kbps)</label><input name="bitrate" type="number" class="form-control" value="<?php echo $package->bitrate ?? 0; ?>"></div>
<div class="form-group"><label class="form-label">DJ Accounts</label><input name="dj_accounts" type="number" class="form-control" value="<?php echo $package->dj_accounts ?? 0; ?>"></div>
</div>
</div>

<div class="pkg-card">
<h4><i class="bi bi-toggles" style="color:var(--primary,#008cff)"></i> General Features</h4>
<div class="pkg-feat">
<?php
$genFeatures = ['cron'=>'Cron','ssh'=>'SSH','ssl'=>'SSL','git'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups','installer'=>'Installer','builder'=>'Website Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API','webhooks'=>'Webhooks','chat'=>'Chatbox','chat_voice'=>'+ Voice','chat_video'=>'+ Video','dj_panel'=>'DJ Panel'];
foreach ($genFeatures as $k=>$l):
    $isSub = in_array($k, ['chat_voice','chat_video']);
?>
<label class="feature-check" style="<?php echo $isSub ? 'padding-left:16px;font-size:11px' : ''; ?>">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>" <?php echo ck($feats, $k); ?>> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
<div style="margin-top:12px;padding:10px 12px;background:rgba(10,132,255,.06);border:1px solid rgba(10,132,255,.12);border-radius:8px">
<label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px;font-weight:600;color:#e0e0e0"><input type="checkbox" name="has_software" value="1" <?php echo !empty($package->has_software) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> Includes PlanetHost Software License</label>
<div style="font-size:11px;color:var(--text_muted,#64748b);margin-top:4px">Clients get their encrypted license file (license.key) in the portal at <code>/user/license/download</code>. Generated via <code>/admin/licensing/generate</code>.</div>
</div>
</div>

<div class="pkg-card" style="border-color:rgba(10,132,255,.2)">
<h4><i class="bi bi-terminal" style="color:#0A84FF"></i> Shell &amp; Terminal Access</h4>
<div class="pkg-grid">
<div class="form-group"><label class="form-label">Shell Access</label>
<select name="shell_access" class="form-select">
<option value="disabled" <?php echo (($package->shell_access ?? 'disabled') === 'disabled') ? 'selected' : ''; ?>>Disabled</option>
<option value="jailed" <?php echo (($package->shell_access ?? 'disabled') === 'jailed') ? 'selected' : ''; ?>>Jailed Shell</option>
<option value="normal" <?php echo (($package->shell_access ?? 'disabled') === 'normal') ? 'selected' : ''; ?>>Normal Shell</option>
</select>
<small style="color:#64748b;font-size:10px">Jailed = customer restricted to their own home dir. Normal = unrestricted (dangerous).</small></div>
</div>
<div class="pkg-feat" style="margin-top:8px">
<label class="feature-check"><input type="checkbox" name="terminal" value="1" <?php echo !empty($package->terminal) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> Terminal</label>
<label class="feature-check"><input type="checkbox" name="ssh_access" value="1" <?php echo !empty($package->ssh_access) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> SSH Access</label>
<label class="feature-check"><input type="checkbox" name="sftp" value="1" <?php echo !empty($package->sftp) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> SFTP</label>
<label class="feature-check"><input type="checkbox" name="api_shell" value="1" <?php echo !empty($package->api_shell) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> API Shell</label>
<label class="feature-check"><input type="checkbox" name="cron" value="1" <?php echo (($package->cron ?? 1)) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff)"> Cron Jobs</label>
</div>
<div style="font-size:11px;color:var(--text_muted,#64748b);margin-top:6px">Controls what this package's accounts can do. The Feature List above controls what shows in the panel; these control the Linux + panel enforcement.</div>
</div>

<!-- Streaming Package -->
<div class="pkg-card" style="border-color:rgba(167,139,250,.2)">
<h4><i class="bi bi-broadcast-pin" style="color:#a78bfa"></i> Streaming Package</h4>
<label class="pkg-tog"><input type="checkbox" name="custom_streaming_enabled" value="1" onchange="toggleSection(this,'str-pkg')" <?php echo !empty($strPkg) ? 'checked' : ''; ?> style="accent-color:#a78bfa;width:auto"> Enable streaming feature set</label>
<div id="str-pkg" style="display:<?php echo !empty($strPkg) ? 'block' : 'none'; ?>;margin-top:10px">
<?php foreach ($streamingGroups as $gName=>$gFields): ?>
<div class="pkg-sub"><h6><?php echo $gName; ?></h6><div class="row3">
<?php foreach ($gFields as $f):
    if ($f['type']==='note'): ?>
<div class="pkg-note"><em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em></div>
<?php else:
    $fn = substr($f['name'] ?? '', 4);
    if ($f['type']==='checkbox'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1" <?php echo ck($strPkg, $fn); ?>> <?php echo $f['label']; ?></label>
<?php elseif ($f['type']==='number'): ?>
<div class="form-group" style="margin:2px 0"><label class="form-label" style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo val($strPkg, $fn, $f['val']); ?>" class="form-control" style="padding:5px 8px;font-size:11px"></div>
<?php elseif ($f['type']==='select'): ?>
<div class="form-group" style="margin:2px 0"><label class="form-label" style="font-size:11px"><?php echo $f['label']; ?></label>
<select name="custom_pkg[<?php echo $f['name']; ?>]" class="form-select" style="padding:5px 8px;font-size:11px">
<?php foreach ($f['options'] as $fv=>$fl): ?>
<option value="<?php echo $fv; ?>" <?php echo sl($strPkg, $fn, $fv); ?>><?php echo $fl; ?></option>
<?php endforeach; ?>
</select></div>
<?php endif; endif; endforeach; ?>
</div></div>
<?php endforeach; ?>
</div>
</div>

<!-- Game Server Package -->
<div class="pkg-card" style="border-color:rgba(251,146,60,.2)">
<h4><i class="bi bi-controller" style="color:#fb923c"></i> Game Server Package</h4>
<label class="pkg-tog"><input type="checkbox" name="custom_game_enabled" value="1" onchange="toggleSection(this,'game-pkg')" <?php echo !empty($gamePkg) ? 'checked' : ''; ?> style="accent-color:#fb923c;width:auto"> Enable game server feature set</label>
<div id="game-pkg" style="display:<?php echo !empty($gamePkg) ? 'block' : 'none'; ?>;margin-top:10px">
<?php foreach ($gameGroups as $gName=>$gFields): ?>
<div class="pkg-sub"><h6><?php echo $gName; ?></h6><div class="row3">
<?php foreach ($gFields as $f):
    if ($f['type']==='note'): ?>
<div class="pkg-note"><em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em></div>
<?php else:
    $fn = substr($f['name'] ?? '', 5);
    if ($f['type']==='checkbox'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1" <?php echo ck($gamePkg, $fn); ?>> <?php echo $f['label']; ?></label>
<?php elseif ($f['type']==='number'): ?>
<div class="form-group" style="margin:2px 0"><label class="form-label" style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo val($gamePkg, $fn, $f['val']); ?>" class="form-control" style="padding:5px 8px;font-size:11px"></div>
<?php endif; endif; endforeach; ?>
</div></div>
<?php endforeach; ?>
</div>
</div>

<div class="pkg-card" style="border-color:rgba(74,222,128,.15)">
<h4><i class="bi bi-box-seam" style="color:#4ade80"></i> Products Using This Package</h4>
<?php if (!empty($billingProducts)): ?>
<div style="display:grid;gap:8px">
<?php foreach ($billingProducts as $bp): ?>
<div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:rgba(255,255,255,.03);border-radius:6px">
<div><span style="font-weight:600;font-size:13px"><?php echo htmlspecialchars($bp->name); ?></span><span style="color:var(--text_muted,#64748b);font-size:11px;margin-left:8px"><?php echo htmlspecialchars($bp->billing_cycle); ?></span></div>
<div style="color:#4ade80;font-weight:700;font-size:14px">$<?php echo number_format($bp->price, 2); ?></div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<p style="color:var(--text_muted,#64748b);font-size:12px;margin:0">No billing products linked to this package.</p>
<?php endif; ?>
</div>

<div class="pkg-card" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap;margin-bottom:0">
<label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer">
<input type="checkbox" name="is_active" value="1" <?php echo ($package->is_active ?? 1) ? 'checked' : ''; ?> style="accent-color:var(--primary,#008cff);width:auto"> Package is Active (visible in store)
</label>
<div style="flex:1;display:flex;gap:12px;justify-content:flex-end">
<button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Update Package</button>
<a href="/admin/packages" class="btn btn-secondary">Cancel</a>
</div>
</div>
</form>
</div>

<script>
function toggleSection(cb, id) {
    document.getElementById(id).style.display = cb.checked ? 'block' : 'none';
}
function toggleStreaming() {
    var t = document.getElementById('pkgType').value;
    var s = document.getElementById('streamingSection');
    if (s) s.style.display = (t === 'icecast' || t === 'icecast_reseller') ? '' : 'none';
}
toggleStreaming();
</script>