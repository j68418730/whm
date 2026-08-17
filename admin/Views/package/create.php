<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Create Package - Planet Hosts</title>
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
.feature-check{display:flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;padding:2px 4px;border-radius:4px}
.feature-check:hover{background:rgba(0,140,255,.06)}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>Create Package</h1>
<?php if (isset($_SESSION['error_message'])): ?>
<div style="padding:10px 16px;background:rgba(248,113,113,.12);border:1px solid rgba(248,113,113,.3);border-radius:8px;color:#f87171;font-size:13px;margin-bottom:16px"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<form method="POST" action="/admin/package/create">
<?php echo $csrfField ?? ''; ?>

<div class="row">
<div class="form-group"><label>Name</label><input name="name" required></div>
<div class="form-group"><label>Type</label><select name="type"><?php foreach ($categories as $cat): ?><option value="<?php echo htmlspecialchars($cat->name, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($cat->icon . ' ' . $cat->name, ENT_QUOTES, 'UTF-8'); ?></option><?php endforeach; ?></select></div>
<div class="form-group"><label>Sort Order</label><input name="sort_order" type="number" value="0"></div>
</div>

<div class="form-group"><label>Description</label><textarea name="description" style="min-height:50px"></textarea></div>

<div class="row">
<div class="form-group"><label>Monthly ($)</label><input name="monthly_price" type="number" step="0.01" value="0"></div>
<div class="form-group"><label>Quarterly ($)</label><input name="quarterly_price" type="number" step="0.01" value="0"></div>
<div class="form-group"><label>Semi-Annual ($)</label><input name="semi_annual_price" type="number" step="0.01" value="0"></div>
<div class="form-group"><label>Annual ($)</label><input name="annual_price" type="number" step="0.01" value="0"></div>
<div class="form-group"><label>Setup Fee ($)</label><input name="setup_fee" type="number" step="0.01" value="0"></div>
</div>

<div class="row">
<div class="form-group"><label>Disk Space (GB)</label><input name="disk_space" type="number" value="0"><small style="color:#64748b">Shared by all services</small></div>
<div class="form-group"><label>Bandwidth (GB)</label><input name="bandwidth" type="number" value="0"></div>
<div class="form-group"><label>Max Domains</label><input name="max_domains" type="number" value="1"></div>
<div class="form-group"><label>Max Subdomains</label><input name="max_subdomains" type="number" value="0"></div>
<div class="form-group"><label>Email Accounts</label><input name="email_accounts" type="number" value="0"></div>
<div class="form-group"><label>FTP Accounts</label><input name="ftp_accounts" type="number" value="0"></div>
<div class="form-group"><label>MySQL Databases</label><input name="databases" type="number" value="0"></div>
<div class="form-group"><label>Parked Domains</label><input name="parked_domains" type="number" value="0"></div>
<div class="form-group"><label>Addon Domains</label><input name="addon_domains" type="number" value="0"></div>
</div>

<div class="form-group"><label>Feature List <a href="/admin/feature-lists" style="color:#0A84FF;font-size:12px">(Manage)</a></label>
<select name="feature_list_id">
<option value="">— None —</option>
<?php foreach ($featureLists as $fl): ?>
<option value="<?php echo $fl->id; ?>"><?php echo htmlspecialchars($fl->name); ?></option>
<?php endforeach; ?>
</select>
</div>

<!-- General Features -->
<div style="margin:12px 0">
<h4 style="color:var(--accent);font-size:14px;margin-bottom:8px">General Features</h4>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;font-size:12px">
<?php
$genFeatures = ['cron'=>'Cron','ssh'=>'SSH','ssl'=>'SSL','git'=>'Git','nodejs'=>'Node.js','python'=>'Python','ruby'=>'Ruby','terminal'=>'Terminal','backups'=>'Backups','installer'=>'Installer','builder'=>'Website Builder','ai_builder'=>'AI Builder','ai_assistant'=>'AI Assistant','marketplace'=>'Marketplace','api'=>'API','webhooks'=>'Webhooks','chat'=>'Chatbox','chat_voice'=>'+ Voice','chat_video'=>'+ Video','dj_panel'=>'DJ Panel'];
foreach ($genFeatures as $k=>$l):
    $isSub = in_array($k, ['chat_voice','chat_video']);
?>
<label class="feature-check" style="<?php echo $isSub ? 'padding-left:16px;font-size:11px' : ''; ?>">
<input type="checkbox" name="features[]" value="<?php echo $k; ?>"> <?php echo $l; ?>
</label>
<?php endforeach; ?>
</div>
</div>

<!-- Streaming Package -->
<div style="margin:12px 0;border:1px solid rgba(10,132,255,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(10,132,255,.06);padding:8px 12px;font-size:13px;font-weight:600;color:var(--accent)">
<label style="cursor:pointer"><input type="checkbox" name="custom_streaming_enabled" value="1" onchange="toggleSection(this,'str-pkg')"> Streaming Package</label>
</div>
<div id="str-pkg" style="display:none;padding:10px 12px">
<div class="row" style="grid-template-columns:1fr 1fr">
<div class="form-group"><label>Streaming Engine</label>
<select name="custom_pkg[str_engine]"><option value="">Select...</option><option value="shoutcast_v1">SHOUTcast v1</option><option value="shoutcast_v2">SHOUTcast v2</option><option value="icecast">Icecast</option></select></div>
<div class="form-group"><label>Maximum Stations</label><input type="number" name="custom_pkg[str_max_stations]" value="0"></div>
<div class="form-group"><label>Maximum Listeners</label><input type="number" name="custom_pkg[str_max_listeners]" value="0"></div>
<div class="form-group"><label>Maximum DJs</label><input type="number" name="custom_pkg[str_max_djs]" value="0"></div>
<div class="form-group"><label>Maximum Bitrate</label><input type="number" name="custom_pkg[str_max_bitrate]" value="0"></div>
<div class="form-group"><label>Maximum Mount Points</label><input type="number" name="custom_pkg[str_max_mounts]" value="0"></div>
</div>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase">AutoDJ</h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj]" value="1"> Enable AutoDJ</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj_playlists]" value="1"> Playlists</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj_smart]" value="1"> Smart Playlists</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj_scheduled]" value="1"> Scheduled Playlists</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj_crossfade]" value="1"> Crossfade</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_autodj_shuffle]" value="1"> Shuffle</label>
</div>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase">Features</h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_public_player]" value="1"> Public Player</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_public_stats]" value="1"> Public Statistics</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_song_requests]" value="1"> Song Requests</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_https_stream]" value="1"> HTTPS Stream</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_backup_auto]" value="1"> Automatic Backups</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[str_api_access]" value="1"> API Access</label>
</div>
<div style="margin-top:6px;padding:4px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b"><strong>Note:</strong> Storage uses disk space allocation above.</div>
</div>
</div>

<!-- Game Server Package -->
<div style="margin:10px 0;border:1px solid rgba(255,149,0,.2);border-radius:8px;overflow:hidden">
<div style="background:rgba(255,149,0,.06);padding:8px 12px;font-size:13px;font-weight:600;color:#FF9500">
<label style="cursor:pointer"><input type="checkbox" name="custom_game_enabled" value="1" onchange="toggleSection(this,'game-pkg')"> Game Server Package</label>
</div>
<div id="game-pkg" style="display:none;padding:10px 12px">
<div class="row" style="grid-template-columns:1fr 1fr">
<div class="form-group"><label>Maximum Game Servers</label><input type="number" name="custom_pkg[game_max_servers]" value="0"></div>
<div class="form-group"><label>Maximum Player Slots</label><input type="number" name="custom_pkg[game_max_players]" value="0"></div>
<div class="form-group"><label>CPU Cores</label><input type="number" name="custom_pkg[game_cpu_cores]" value="1"></div>
<div class="form-group"><label>RAM (GB)</label><input type="number" name="custom_pkg[game_ram]" value="1"></div>
</div>
<h6 style="margin:10px 0 4px;font-size:12px;font-weight:600;color:#94a3b8;text-transform:uppercase">Features</h6>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px 12px;font-size:12px;padding:4px 8px;background:rgba(255,255,255,.02);border-radius:4px">
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_steamcmd]" value="1"> SteamCMD</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_mod_support]" value="1"> Mod Support</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_console]" value="1"> Console Access</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_file_manager]" value="1"> File Manager</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_backup_auto]" value="1"> Automatic Backups</label>
<label class="feature-check"><input type="checkbox" name="custom_pkg[game_api_rest]" value="1"> REST API</label>
</div>
<div style="margin-top:6px;padding:4px 8px;background:rgba(255,255,255,.03);border-radius:4px;font-size:11px;color:#64748b"><strong>Note:</strong> Storage uses disk space allocation above.</div>
</div>
</div>

<!-- Streaming Column Fields (legacy) -->
<div style="margin:12px 0;border:1px solid rgba(0,191,255,.15);border-radius:8px;padding:12px">
<h4 style="color:var(--accent);font-size:14px;margin-bottom:8px">Streaming Limits</h4>
<div class="row">
<div class="form-group"><label>Listener Limit</label><input name="listener_limit" type="number" value="0"></div>
<div class="form-group"><label>Bitrate (kbps)</label><input name="bitrate" type="number" value="0"></div>
<div class="form-group"><label>Storage Limit (GB)</label><input name="storage_limit" type="number" value="0"></div>
<div class="form-group"><label>DJ Accounts</label><input name="dj_accounts" type="number" value="0"></div>
<div class="form-group"><label>PHP Version</label><select name="php_version"><option value="8.2">PHP 8.2</option><option value="8.1">PHP 8.1</option><option value="8.0">PHP 8.0</option><option value="7.4">PHP 7.4</option></select></div>
</div>
</div>

<div style="margin-top:20px;display:flex;gap:12px">
<button type="submit" class="btn primary">Create Package</button>
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
