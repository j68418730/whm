<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0"><i class="bi bi-ui-checks"></i> Feature Lists</h2>
<p style="color:#64748b;margin:4px 0 0">Manage feature sets that control what hosting accounts can do.</p>
</div>
<a href="/admin/feature-lists/create" class="btn primary"><i class="bi bi-plus-lg"></i> Create Feature List</a>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px">
<?php if (empty($lists)): ?>
<div class="card" style="grid-column:1/-1;text-align:center;padding:40px">
<p style="color:#64748b">No feature lists yet. <a href="/admin/feature-lists/create">Create one</a>.</p>
</div>
<?php else: foreach ($lists as $l): ?>
<div class="card" style="padding:16px">
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px">
<h3 style="margin:0;font-size:15px;color:var(--accent)"><?php echo htmlspecialchars($l->name); ?></h3>
<div style="display:flex;gap:6px">
<a href="/admin/feature-lists/edit/<?php echo $l->id; ?>" class="btn btn-sm secondary"><i class="bi bi-pencil"></i></a>
<a href="/admin/feature-lists/delete/<?php echo $l->id; ?>" class="btn btn-sm secondary" onclick="return confirm('Delete this feature list?')"><i class="bi bi-trash"></i></a>
</div>
</div>

<!-- Resource Limits -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:3px;font-size:11px;color:#94a3b8">
<span>📧 Email: <?php echo $l->email_accounts < 0 ? '∞' : $l->email_accounts; ?></span>
<span>📁 FTP: <?php echo $l->ftp_accounts < 0 ? '∞' : $l->ftp_accounts; ?></span>
<span>🗄 DBs: <?php echo $l->databases < 0 ? '∞' : $l->databases; ?></span>
<span>👤 DB Users: <?php echo $l->database_users < 0 ? '∞' : $l->database_users; ?></span>
<span>📋 Subdomains: <?php echo $l->subdomains < 0 ? '∞' : $l->subdomains; ?></span>
<span>📍 Parked: <?php echo $l->parked_domains < 0 ? '∞' : $l->parked_domains; ?></span>
<span>➕ Addon: <?php echo $l->addon_domains < 0 ? '∞' : $l->addon_domains; ?></span>
</div>

<!-- Feature Badges -->
<div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:8px">
<?php
$badges = [
    ['key' => 'cron_jobs', 'label' => 'Cron'],
    ['key' => 'ssh_access', 'label' => 'SSH'],
    ['key' => 'ssl_allowed', 'label' => 'SSL'],
    ['key' => 'git_access', 'label' => 'Git'],
    ['key' => 'nodejs', 'label' => 'Node.js'],
    ['key' => 'python', 'label' => 'Python'],
    ['key' => 'ruby', 'label' => 'Ruby'],
    ['key' => 'terminal', 'label' => 'Terminal'],
    ['key' => 'backups', 'label' => 'Backups'],
    ['key' => 'installer', 'label' => 'Installer'],
    ['key' => 'builder', 'label' => 'Website Builder'],
    ['key' => 'ai_website_builder', 'label' => 'AI Builder'],
    ['key' => 'ai_assistant', 'label' => 'AI Assistant'],
    ['key' => 'plugin_marketplace', 'label' => 'Marketplace'],
    ['key' => 'api_access', 'label' => 'API'],
    ['key' => 'webhooks', 'label' => 'Webhooks'],
    ['key' => 'chatbox', 'label' => 'Chat'],
    ['key' => 'chatbox_voice', 'label' => 'Chat Voice'],
    ['key' => 'chatbox_video', 'label' => 'Chat Video'],
    ['key' => 'dj_panel', 'label' => 'DJ Panel'],
];
foreach ($badges as $b):
    $val = $l->{$b['key']} ?? 0;
    if ($val):
?>
<span style="background:rgba(0,200,83,.12);color:#00c853;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:600"><?php echo $b['label']; ?></span>
<?php endif; endforeach; ?>
</div>

<!-- Streaming -->
<?php if ($l->streaming_enabled ?? 0): ?>
<div style="margin-top:8px;border-top:1px solid rgba(255,255,255,.05);padding-top:6px">
<div style="font-size:11px;font-weight:600;color:var(--accent);margin-bottom:4px">📻 Streaming</div>
<div style="display:flex;flex-wrap:wrap;gap:4px">
<?php foreach ([
    'shoutcast_v1'=>'SCv1','shoutcast_v2'=>'SCv2','icecast_enabled'=>'Icecast',
    'autodj'=>'AutoDJ','ssl_streaming'=>'SSL','statistics'=>'Stats',
    'recording'=>'Recording','song_requests'=>'Requests'
] as $k=>$v): if ($l->$k ?? 0): ?>
<span style="background:rgba(10,132,255,.12);color:#0A84FF;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:600"><?php echo $v; ?></span>
<?php endif; endforeach; ?>
</div>
<div style="font-size:10px;color:#64748b;margin-top:3px">
 Stations:<?php echo $l->max_stations ?? 0; ?> DJs:<?php echo $l->max_djs ?? 0; ?> Listeners:<?php echo $l->max_listeners ?? 0; ?> Bitrate:<?php echo $l->max_bitrate ?? 0; ?> Playlist:<?php echo ($l->playlist_storage ?? 0) > 0 ? ($l->playlist_storage . 'MB') : 'No'; ?>
</div>
</div>
<?php endif; ?>

<!-- Game Servers -->
<?php if ($l->game_servers_enabled ?? 0): ?>
<div style="margin-top:6px;border-top:1px solid rgba(255,255,255,.05);padding-top:6px">
<div style="font-size:11px;font-weight:600;color:var(--accent);margin-bottom:4px">🎮 Game Servers</div>
<div style="display:flex;flex-wrap:wrap;gap:4px">
<?php foreach ([
    'steamcmd'=>'SteamCMD','workshop'=>'Workshop','mod_support'=>'Mods',
    'scheduled_restarts'=>'Restarts','automatic_updates'=>'Updates','game_backups'=>'Backups'
] as $k=>$v): if ($l->$k ?? 0): ?>
<span style="background:rgba(255,149,0,.12);color:#FF9500;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:600"><?php echo $v; ?></span>
<?php endif; endforeach; ?>
</div>
<div style="font-size:10px;color:#64748b;margin-top:3px">Max: <?php echo $l->max_game_servers ?? 0; ?> servers</div>
</div>
<?php endif; ?>

<!-- VPS -->
<?php if ($l->vps_enabled ?? 0): ?>
<div style="margin-top:6px;border-top:1px solid rgba(255,255,255,.05);padding-top:6px">
<div style="font-size:11px;font-weight:600;color:var(--accent);margin-bottom:4px">🖥 VPS</div>
<div style="display:flex;flex-wrap:wrap;gap:4px">
<?php if ($l->iso_mount ?? 0): ?>
<span style="background:rgba(175,82,222,.12);color:#AF52DE;padding:1px 7px;border-radius:10px;font-size:10px;font-weight:600">ISO Mount</span>
<?php endif; ?>
</div>
<div style="font-size:10px;color:#64748b;margin-top:3px">
 vCPU:<?php echo $l->vcpu ?? 0; ?> RAM:<?php echo $l->ram ?? 0; ?>GB Storage:<?php echo $l->vps_storage ?? 0; ?>GB BW:<?php echo $l->vps_bandwidth ?? 0; ?>TB Snap:<?php echo $l->snapshots ?? 0; ?> Backups:<?php echo $l->vps_backups ?? 0; ?> IPv4:<?php echo $l->ipv4 ?? 0; ?> IPv6:<?php echo $l->ipv6 ?? 0; ?>
</div>
</div>
<?php endif; ?>

<?php
$usedBy = array_filter($packages ?? [], function($p) use ($l) { return $p->feature_list_id == $l->id; });
if (!empty($usedBy)): ?>
<div style="margin-top:8px;font-size:10px;color:#64748b;border-top:1px solid rgba(255,255,255,.05);padding-top:6px">
Used by: <?php echo implode(', ', array_map(function($p) { return htmlspecialchars($p->name); }, $usedBy)); ?>
</div>
<?php endif; ?>
</div>
<?php endforeach; endif; ?>
</div>