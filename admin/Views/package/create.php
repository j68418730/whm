<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Package - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;background:#020817;color:#e0e0e0;min-height:100vh}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.92),rgba(2,8,23,.98)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}

.page{max-width:1100px;margin:0 auto;padding:24px 20px 60px}
.page-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px}
.page-header h1{font-size:22px;font-weight:700;color:#fff;display:flex;align-items:center;gap:10px}
.page-header h1 i{color:#0A84FF;font-size:20px}
.page-header .actions{display:flex;gap:8px}
.btn{padding:10px 22px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px;font-family:'Inter',sans-serif}
.btn:active{transform:scale(.97)}
.btn-primary{background:linear-gradient(135deg,#0A84FF,#00C6FF);color:#fff}
.btn-primary:hover{box-shadow:0 4px 20px rgba(10,132,255,.3);transform:translateY(-1px)}
.btn-secondary{background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.08)}
.btn-secondary:hover{border-color:rgba(10,132,255,.3);color:#e0e0e0}
.btn-danger{background:rgba(248,113,113,.1);color:#f87171;border:1px solid rgba(248,113,113,.15)}

.tabs{display:flex;gap:2px;background:rgba(8,16,28,.8);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:4px;margin-bottom:20px;overflow-x:auto}
.tab{padding:10px 18px;border-radius:8px;font-size:13px;font-weight:500;color:#64748b;cursor:pointer;transition:.2s;white-space:nowrap;border:none;background:none;font-family:'Inter',sans-serif;display:flex;align-items:center;gap:6px}
.tab:hover{color:#94a3b8;background:rgba(255,255,255,.03)}
.tab.active{background:rgba(10,132,255,.12);color:#0A84FF;font-weight:600}
.tab i{font-size:13px}
.tab-badge{background:rgba(10,132,255,.2);color:#0A84FF;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:700}

.tab-panel{display:none}
.tab-panel.active{display:block}

.section{background:rgba(8,16,28,.7);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:20px;margin-bottom:16px}
.section-title{font-size:14px;font-weight:700;color:#e0e0e0;margin-bottom:14px;display:flex;align-items:center;gap:8px}
.section-title i{color:#0A84FF;font-size:14px}
.section-title .badge{background:rgba(10,132,255,.12);color:#0A84FF;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:600}

.form-grid{display:grid;gap:12px}
.form-grid.cols-2{grid-template-columns:1fr 1fr}
.form-grid.cols-3{grid-template-columns:1fr 1fr 1fr}
.form-grid.cols-4{grid-template-columns:1fr 1fr 1fr 1fr}
@media(max-width:768px){.form-grid.cols-2,.form-grid.cols-3,.form-grid.cols-4{grid-template-columns:1fr}}

.form-group{display:flex;flex-direction:column;gap:4px}
.form-group label{font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:.5px}
.form-group label .hint{font-weight:400;text-transform:none;letter-spacing:0;color:#475569;font-size:10px}
.form-group input,.form-group select,.form-group textarea{width:100%;padding:9px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;transition:.2s;font-family:'Inter',sans-serif}
.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:#0A84FF;box-shadow:0 0 0 3px rgba(10,132,255,.08)}
.form-group input::placeholder,.form-group textarea::placeholder{color:#334155}
.form-group select option{background:#0a0f1a;color:#e0e0e0}
.form-group textarea{resize:vertical;min-height:60px}

.feature-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:6px}
.feature-check{display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:12px;cursor:pointer;transition:.15s;border:1px solid transparent;background:rgba(255,255,255,.02)}
.feature-check:hover{background:rgba(10,132,255,.06);border-color:rgba(10,132,255,.08)}
.feature-check input[type=checkbox]{width:auto;accent-color:#0A84FF}
.feature-check.checked{background:rgba(10,132,255,.08);border-color:rgba(10,132,255,.15)}
.feature-check.sub{padding-left:28px;font-size:11px;color:#94a3b8}
.feature-check.sub::before{content:''}

.collapse-header{display:flex;align-items:center;justify-content:space-between;cursor:pointer;padding:12px 16px;border-radius:10px;transition:.2s;border:1px solid rgba(255,255,255,.04);background:rgba(255,255,255,.02);margin-bottom:8px}
.collapse-header:hover{border-color:rgba(255,255,255,.08);background:rgba(255,255,255,.03)}
.collapse-header .left{display:flex;align-items:center;gap:10px}
.collapse-header .left i{font-size:16px}
.collapse-header .left .title{font-size:13px;font-weight:600}
.collapse-header .left .desc{font-size:11px;color:#64748b}
.collapse-header .toggle{color:#64748b;font-size:12px;transition:.2s}
.collapse-header.open .toggle{transform:rotate(180deg)}
.collapse-header .switch{position:relative;width:36px;height:20px;background:rgba(255,255,255,.08);border-radius:10px;transition:.2s}
.collapse-header .switch::after{content:'';position:absolute;top:2px;left:2px;width:16px;height:16px;background:#64748b;border-radius:50%;transition:.2s}
.collapse-header .switch.on{background:rgba(74,222,128,.2)}
.collapse-header .switch.on::after{left:18px;background:#4ade80}

.collapse-body{display:none;padding:4px 0 8px}
.collapse-body.open{display:block}

.group-label{font-size:11px;font-weight:700;color:#0A84FF;text-transform:uppercase;letter-spacing:.8px;margin:14px 0 8px;display:flex;align-items:center;gap:6px}
.group-label::after{content:'';flex:1;height:1px;background:rgba(10,132,255,.12)}

.str-streaming{--accent-color:#0A84FF}

.toast{position:fixed;bottom:24px;right:24px;background:#0f172a;border:1px solid rgba(74,222,128,.2);color:#4ade80;padding:12px 20px;border-radius:10px;font-size:13px;font-weight:500;z-index:9999;opacity:0;transform:translateY(10px);transition:.3s;pointer-events:none}
.toast.show{opacity:1;transform:translateY(0)}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="page">

<div class="page-header">
<h1><i class="fa-solid fa-cube"></i> Create Package</h1>
<div class="actions">
<a href="/admin/packages" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Packages</a>
<button type="submit" form="pkgForm" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Package</button>
</div>
</div>

<?php if (isset($_SESSION['error_message'])): ?>
<div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:10px;color:#f87171;font-size:13px;margin-bottom:16px"><i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/package/create" id="pkgForm">
<?php echo $csrfField ?? ''; ?>

<div class="tabs" id="mainTabs">
<button type="button" class="tab active" onclick="showTab('basic',this)"><i class="fa-solid fa-info-circle"></i> Basic Info</button>
<button type="button" class="tab" onclick="showTab('resources',this)"><i class="fa-solid fa-server"></i> Resources</button>
<button type="button" class="tab" onclick="showTab('pricing',this)"><i class="fa-solid fa-dollar-sign"></i> Pricing</button>
<button type="button" class="tab" onclick="showTab('features',this)"><i class="fa-solid fa-puzzle-piece"></i> Features</button>
<button type="button" class="tab" onclick="showTab('streaming',this)"><i class="fa-solid fa-headphones"></i> Streaming</button>
</div>

<!-- TAB: Basic Info -->
<div class="tab-panel active" id="panel-basic">
<div class="section">
<div class="section-title"><i class="fa-solid fa-tag"></i> Package Details</div>
<div class="form-grid cols-3">
<div class="form-group"><label>Package Name *</label><input name="name" required placeholder="e.g. Starter, Pro, Business"></div>
<div class="form-group"><label>Type</label><select name="type"><?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(($cat->icon ?? '📦') . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0" placeholder="0"></div>
</div>
<div class="form-group" style="margin-top:12px"><label>Description</label><textarea name="description" rows="2" placeholder="Brief description for the store page..."></textarea></div>
</div>
</div>

<!-- TAB: Resources -->
<div class="tab-panel" id="panel-resources">
<div class="section">
<div class="section-title"><i class="fa-solid fa-hard-drive"></i> Disk &amp; Bandwidth</div>
<div class="form-grid cols-2">
<div class="form-group"><label>Disk Space <span class="hint">(MB)</span></label><input name="disk_space" type="number" value="1024"><small style="color:#475569;font-size:10px">Stored in MB — 1024 = 1 GB</small></div>
<div class="form-group"><label>Bandwidth <span class="hint">(MB)</span></label><input name="bandwidth" type="number" value="10240"><small style="color:#475569;font-size:10px">Stored in MB — 10240 = 10 GB</small></div>
</div>
</div>
<div class="section">
<div class="section-title"><i class="fa-solid fa-globe"></i> Domains &amp; Accounts</div>
<div class="form-grid cols-3">
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="1"></div>
<div class="form-group"><label>Subdomains</label><input name="max_subdomains" type="number" value="0"></div>
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="0"></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="0"></div>
<div class="form-group"><label>MySQL Databases</label><input name="databases" type="number" value="0"></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="0"></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="0"></div>
</div>
</div>
<div class="section">
<div class="section-title"><i class="fa-solid fa-code"></i> PHP &amp; Limits</div>
<div class="form-grid cols-3">
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="8.2">PHP 8.2</option><option value="8.1">PHP 8.1</option><option value="8.0">PHP 8.0</option><option value="7.4">PHP 7.4</option></select></div>
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="0"></div>
<div class="form-group"><label>Storage Limit <span class="hint">(GB)</span></label><input name="storage_limit" type="number" value="0"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0"></div>
</div>
</div>
<div class="section">
<div class="section-title"><i class="fa-solid fa-list"></i> Feature List</div>
<div class="form-group"><label>Feature List <a href="/admin/feature-lists" style="color:#0A84FF;font-size:11px;font-weight:400;text-transform:none;letter-spacing:0">(Manage lists)</a></label>
<select name="feature_list_id"><option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?><option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option><?php endforeach; ?>
</select></div>
</div>
</div>

<!-- TAB: Pricing -->
<div class="tab-panel" id="panel-pricing">
<div class="section">
<div class="section-title"><i class="fa-solid fa-dollar-sign"></i> Pricing Plans</div>
<div class="form-grid cols-3">
<div class="form-group"><label>Monthly ($)</label><input name="monthly_price" type="number" step="0.01" value="0.00" placeholder="0.00"></div>
<div class="form-group"><label>Quarterly ($)</label><input name="quarterly_price" type="number" step="0.01" value="0.00" placeholder="0.00"><small style="color:#475569;font-size:10px">Auto-calculated in store if 0</small></div>
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="0.00" placeholder="0.00"></div>
<div class="form-group"><label>Annual ($)</label><input name="annual_price" type="number" step="0.01" value="0.00" placeholder="0.00"></div>
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="0.00" placeholder="0.00"></div>
</div>
</div>
</div>

<!-- TAB: Features -->
<div class="tab-panel" id="panel-features">
<div class="section">
<div class="section-title"><i class="fa-solid fa-puzzle-piece"></i> General Features</div>
<div class="feature-grid">
<?php
$genFeatures = ['cron'=>'Cron Jobs','ssh'=>'SSH Access','ssl'=>'SSL Certificate','git'=>'Git Integration','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Web Terminal','backups'=>'Backups','installer'=>'Softaculous Installer','builder'=>'Website Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API Access','webhooks'=>'Webhooks','chat'=>'Chatbox','dj_panel'=>'DJ Panel'];
$genSub = ['chat_voice'=>'Voice Chat','chat_video'=>'Video Chat'];
foreach ($genFeatures as $k=>$l): ?>
<label class="feature-check"><input type="checkbox" name="features[]" value="<?php echo $k; ?>"> <?php echo $l; ?></label>
<?php endforeach; foreach ($genSub as $k=>$l): ?>
<label class="feature-check sub"><input type="checkbox" name="features[]" value="<?php echo $k; ?>"> + <?php echo $l; ?></label>
<?php endforeach; ?>
</div>
</div>
</div>

<!-- TAB: Streaming -->
<div class="tab-panel" id="panel-streaming">
<div class="section str-streaming">
<div class="section-title"><i class="fa-solid fa-headphones"></i> Streaming Package Configuration</div>
<p style="font-size:12px;color:#64748b;margin-bottom:14px">Configure streaming capabilities for this package. Toggle the master switch to enable streaming features.</p>

<div class="collapse-header" id="strMasterToggle" onclick="toggleMaster(this,'strMasterSwitch','strSections')">
<div class="left">
<div class="switch" id="strMasterSwitch"></div>
<div><div class="title" style="color:#0A84FF">Enable Streaming Package</div><div class="desc">Unlock all streaming sub-features below</div></div>
</div>
<div class="toggle"><i class="fa-solid fa-chevron-down"></i></div>
</div>
<div id="strSections" style="display:none">

<?php
$streamingGroups = [
    'General' => [
        ['type'=>'select','name'=>'str_engine','label'=>'Engine','options'=>[''=>'Select...','shoutcast_v1'=>'SHOUTcast v1','shoutcast_v2'=>'SHOUTcast v2','icecast'=>'Icecast']],
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
        ['type'=>'number','name'=>'str_max_bitrate','label'=>'Maximum Bitrate (kbps)','val'=>0],
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
        ['type'=>'note','label'=>'Storage','note'=>'Uses disk space allocation in Resources tab'],
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
        ['type'=>'checkbox','name'=>'str_stats_geo','label'=>'Geographic'],
        ['type'=>'checkbox','name'=>'str_stats_device','label'=>'Device'],
        ['type'=>'checkbox','name'=>'str_stats_historical','label'=>'Historical'],
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
        ['type'=>'note','label'=>'Storage','note'=>'Uses disk space allocation in Resources tab'],
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
        ['type'=>'checkbox','name'=>'str_sec_two_factor','label'=>'Two-Factor Auth'],
    ],
];
foreach ($streamingGroups as $gName => $gFields):
?>
<div class="group-label"><?php echo $gName; ?></div>
<div class="feature-grid">
<?php foreach ($gFields as $f):
    if ($f['type']==='note'): ?>
<div style="grid-column:1/-1;font-size:10px;color:#475569;padding:2px 0"><em><?php echo $f['label']; ?>: <?php echo $f['note']; ?></em></div>
<?php elseif ($f['type']==='checkbox'): ?>
<label class="feature-check"><input type="checkbox" name="custom_pkg[<?php echo $f['name']; ?>]" value="1"> <?php echo $f['label']; ?></label>
<?php elseif ($f['type']==='number'): ?>
<div class="form-group"><label><?php echo $f['label']; ?></label><input type="number" name="custom_pkg[<?php echo $f['name']; ?>]" value="<?php echo $f['val']; ?>"></div>
<?php elseif ($f['type']==='select'): ?>
<div class="form-group"><label><?php echo $f['label']; ?></label><select name="custom_pkg[<?php echo $f['name']; ?>]"><?php foreach ($f['options'] as $fv=>$fl): ?><option value="<?php echo $fv; ?>"><?php echo $fl; ?></option><?php endforeach; ?></select></div>
<?php endif; endforeach; ?>
</div>
<?php endforeach; ?>

<div style="margin-top:12px;padding:8px 12px;background:rgba(10,132,255,.05);border:1px solid rgba(10,132,255,.1);border-radius:8px;font-size:11px;color:#64748b"><i class="fa-solid fa-circle-info"></i> Streaming storage uses the disk space allocation from the Resources tab.</div>
</div>
</div>
</div>

<!-- Sticky Footer -->
<div style="position:fixed;bottom:0;left:0;right:0;background:rgba(2,8,23,.95);border-top:1px solid rgba(255,255,255,.06);padding:12px 20px;display:flex;justify-content:center;gap:12px;z-index:100;backdrop-filter:blur(12px)">
<button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Create Package</button>
<a href="/admin/packages" class="btn btn-secondary"><i class="fa-solid fa-xmark"></i> Cancel</a>
</div>
</form>

</div>

<script>
function showTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(function(p){ p.classList.remove('active'); });
    document.querySelectorAll('.tab').forEach(function(t){ t.classList.remove('active'); });
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
}

function toggleMaster(header, switchId, sectionsId) {
    var sw = document.getElementById(switchId);
    var sec = document.getElementById(sectionsId);
    var on = !sw.classList.contains('on');
    sw.classList.toggle('on', on);
    sec.style.display = on ? 'block' : 'none';
    header.classList.toggle('open', on);

    // Create hidden input to track enabled state
    var existing = document.querySelector('input[name="custom_streaming_enabled"]');
    if (on && !existing) {
        var inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'custom_streaming_enabled';
        inp.value = '1';
        document.getElementById('pkgForm').appendChild(inp);
    } else if (!on && existing) {
        existing.remove();
    }
}

// Tab badge counts on hover
document.querySelectorAll('.tab').forEach(function(tab) {
    tab.addEventListener('mouseenter', function() {
        var panel = document.getElementById('panel-' + this.getAttribute('onclick').match(/'(\w+)'/)[1]);
        if (panel) {
            var count = panel.querySelectorAll('input[type=checkbox]:checked').length;
            var existing = this.querySelector('.tab-badge');
            if (count > 0 && !existing) {
                var badge = document.createElement('span');
                badge.className = 'tab-badge';
                badge.textContent = count;
                this.appendChild(badge);
            } else if (existing) {
                existing.textContent = count || '';
                if (count === 0) existing.remove();
            }
        }
    });
});
</script>
</body>
</html>
