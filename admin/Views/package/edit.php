<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:40px;max-width:900px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:24px}
.form-group{margin-bottom:14px}
label{display:block;margin-bottom:4px;color:#94a3b8;font-weight:600;font-size:13px}
input,select,textarea{width:100%;padding:8px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.4);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
input:focus,select:focus{border-color:#0A84FF}
.row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.btn{padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1);text-decoration:none}
.feature-check {display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;padding:2px 4px;border-radius:4px}
.feature-check:hover {background:rgba(0,140,255,.06)}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>Edit Package</h1>
<form method="POST" action="/admin/package/edit/<?php echo $package->id; ?>">
<?php
$feats = is_string($package->features) ? json_decode($package->features, true) ?? [] : ($package->features ?? []);
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
<div class="row">
<div class="form-group"><label>Name</label><input name="name" value="<?php echo htmlspecialchars($package->name ?? '', ENT_QUOTES, 'UTF-8'); ?>" required></div>
<div class="form-group"><label>Type</label><select name="type"><?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>" <?php echo ($package->type ?? '') === $cat->name ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat->icon . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
</div>
<div class="form-group"><label>Description</label><textarea name="description" style="min-height:50px"><?php echo htmlspecialchars($package->description ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></div>
<div class="row">
<div class="form-group"><label>Monthly ($)</label><input name="monthly_price" type="number" step="0.01" value="<?php echo $package->monthly_price ?? 0; ?>"></div>
<div class="form-group"><label>Quarterly ($)</label><input name="quarterly_price" type="number" step="0.01" value="<?php echo $package->quarterly_price ?? 0; ?>"></div>
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="<?php echo $package->semi_annual_price ?? 0; ?>"></div>
<div class="form-group"><label>Annual ($)</label><input name="annual_price" type="number" step="0.01" value="<?php echo $package->annual_price ?? 0; ?>"></div>
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="<?php echo $package->setup_fee ?? 0; ?>"></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="<?php echo $package->sort_order ?? 0; ?>"></div>
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="<?php echo $package->disk_space ?? 0; ?>"><small style="color:#64748b">Shared by all services</small></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="<?php echo $package->bandwidth ?? 0; ?>"></div>
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="<?php echo $package->max_domains ?? 1; ?>"></div>
<div class="form-group"><label>Max Subdomains</label><input name="max_subdomains" type="number" value="<?php echo $package->max_subdomains ?? 0; ?>"></div>
</div>

<div class="form-group"><label>Feature List <a href="/admin/feature-lists" style="color:#0A84FF;font-size:12px">(Manage)</a></label>
<select name="feature_list_id">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>" <?php echo ($package->feature_list_id ?? '') == $fl->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>

<div style="margin:12px 0">
<h4 style="color:var(--accent);font-size:14px;margin-bottom:8px">General Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;font-size:12px">
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
</div>

<!-- Streaming Package -->
<div style="margin:12px 0;border:1px solid rgba(10,132,255,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(10,132,255,.06);padding:8px 12px;font-size:13px;font-weight:600;color:var(--accent)">
<label style="cursor:pointer"><input type="checkbox" name="custom_streaming_enabled" value="1" onchange="toggleSection(this,'str-pkg')" <?php echo !empty($strPkg) ? 'checked' : ''; ?>> Streaming Package</label>
</div>
<div id="str-pkg" style="display:<?php echo !empty($strPkg) ? 'block' : 'none'; ?>;padding:10px 12px">
<?php foreach ($streamingGroups ?? [] as $gName=>$gFields): ?>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase"><?php echo $gName; ?></h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<?php foreach ($gFields as $f):
    $fn = substr($f['name'], 4); // remove "str_" prefix
    if ($f['type']==='note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0"><em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em></div>
<?php elseif ($f['type']==='checkbox'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1" <?php echo ck($strPkg, $fn); ?>> <?php echo $f['label']; ?></label>
<?php elseif ($f['type']==='number'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo val($strPkg, $fn, $f['val']); ?>" style="width:100%;padding:3px 6px;font-size:11px"></div>
<?php elseif ($f['type']==='select'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<select name="custom_pkg[<?php echo $f['name']; ?>]" style="width:100%;padding:3px 6px;font-size:11px">
<?php foreach ($f['options'] as $fv=>$fl): ?>
<option value="<?php echo $fv; ?>" <?php echo sl($strPkg, $fn, $fv); ?>><?php echo $fl; ?></option>
<?php endforeach; ?>
</select></div>
<?php endif; endforeach; ?>
</div>
<?php endforeach; ?>
<div style="margin-top:6px;padding:4px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b"><strong>Note:</strong> Storage uses disk allocation above.</div>
</div>
</div>

<!-- Game Server Package -->
<div style="margin:10px 0;border:1px solid rgba(255,149,0,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(255,149,0,.06);padding:8px 12px;font-size:13px;font-weight:600;color:#FF9500">
<label style="cursor:pointer"><input type="checkbox" name="custom_game_enabled" value="1" onchange="toggleSection(this,'game-pkg')" <?php echo !empty($gamePkg) ? 'checked' : ''; ?>> Game Server Package</label>
</div>
<div id="game-pkg" style="display:<?php echo !empty($gamePkg) ? 'block' : 'none'; ?>;padding:10px 12px">
<?php foreach ($gameGroups ?? [] as $gName=>$gFields): ?>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase"><?php echo $gName; ?></h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<?php foreach ($gFields as $f):
    $fn = substr($f['name'], 5); // remove "game_" prefix
    if ($f['type']==='note'): ?>
<div style="grid-column:1/-1;font-size:11px;color:#64748b;padding:2px 0"><em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em></div>
<?php elseif ($f['type']==='checkbox'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1" <?php echo ck($gamePkg, $fn); ?>> <?php echo $f['label']; ?></label>
<?php elseif ($f['type']==='number'): ?>
<div class="form-group" style="margin:2px 0"><label style="font-size:11px"><?php echo $f['label']; ?></label>
<input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo val($gamePkg, $fn, $f['val']); ?>" style="width:100%;padding:3px 6px;font-size:11px"></div>
<?php endif; endforeach; ?>
</div>
<?php endforeach; ?>
<div style="margin-top:6px;padding:4px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b"><strong>Note:</strong> Storage uses disk allocation above.</div>
</div>
</div>

<div style="margin-top:20px;display:flex;gap:12px">
<button type="submit" class="btn primary">Update Package</button>
<a href="/admin/packages" class="btn secondary">Cancel</a>
</div>
</form>
</div>
<script>
function toggleSection(cb, id) {
    document.getElementById(id).style.display = cb.checked ? 'block' : 'none';
}
</script>
</body>
</html>