<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
.pkg-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px;margin-top:12px}
.pkg-card{background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;transition:.3s}
.pkg-card:hover{border-color:rgba(0,191,255,.2);transform:translateY(-2px)}
.pkg-card .p-name{font-size:15px;font-weight:700;margin-bottom:4px}
.pkg-card .p-type{font-size:11px;color:#64748b;margin-bottom:6px}
.pkg-card .p-price{font-size:20px;font-weight:800;color:#4ade80;margin-bottom:8px}
.pkg-card .p-price small{font-size:11px;color:#64748b;font-weight:400}
.pkg-card .p-features{font-size:11px;color:#94a3b8;margin-bottom:10px;line-height:1.8}
.pkg-card .p-features span.label{color:#0A84FF;font-weight:600}
.pkg-card .p-status{display:inline-block;padding:2px 8px;border-radius:4px;font-size:10px;font-weight:600}
.pkg-card .p-actions{display:flex;gap:6px;margin-top:10px}
.pkg-card .p-actions a{padding:4px 12px;border-radius:5px;font-size:11px;text-decoration:none;font-weight:600}
.pkg-group-title{font-size:14px;font-weight:700;color:#e0e0e0;margin:0 0 10px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:8px}
.pkg-group-title .cnt{font-size:11px;color:#64748b;font-weight:400}
</style>

<h2>Hosting</h2>
<p style="color:#64748b;margin-bottom:20px">Manage hosting services, mail, databases, FTP, and server configuration.</p>

<div class="section-grid">
<a href="/admin/email" class="section-card"><div class="icon">✉️</div><div class="name">Email</div><div class="desc">Email accounts & routing</div></a>
<a href="/admin/mysql" class="section-card"><div class="icon">🗄️</div><div class="name">Databases</div><div class="desc">MySQL & database management</div></a>
<a href="/admin/ftp" class="section-card"><div class="icon">📁</div><div class="name">FTP</div><div class="desc">FTP accounts & access</div></a>
<a href="/admin/ssl/universal" class="section-card"><div class="icon">🔒</div><div class="name">Universal SSL</div><div class="desc">Multi-service SSL manager</div></a>
<a href="/admin/ssl" class="section-card"><div class="icon">📜</div><div class="name">SSL Certs</div><div class="desc">Certificate list & AutoSSL</div></a>
<a href="/admin/backup" class="section-card"><div class="icon">💾</div><div class="name">Backups</div><div class="desc">Backup & restore</div></a>
<a href="/admin/cron" class="section-card"><div class="icon">⏰</div><div class="name">Cron Jobs</div><div class="desc">Scheduled tasks</div></a>
<a href="/admin/server" class="section-card"><div class="icon">📊</div><div class="name">Resource Usage</div><div class="desc">Server resource statistics</div></a>
<a href="/admin/server/terminal" class="section-card"><div class="icon">💻</div><div class="name">Web Terminal</div><div class="desc">Browser-based terminal</div></a>
<a href="/admin/apache" class="section-card"><div class="icon">🌐</div><div class="name">Apache</div><div class="desc">Apache configuration</div></a>
<a href="/admin/php" class="section-card"><div class="icon">🐘</div><div class="name">PHP</div><div class="desc">PHP settings & extensions</div></a>
<a href="/admin/packages" class="section-card"><div class="icon">📦</div><div class="name">Packages</div><div class="count"><?php echo count($packages ?? []); ?></div><div class="desc">Hosting packages & configs</div></a>
</div>

<div style="margin-top:30px">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<h3 style="color:#e0e0e0;font-size:16px;margin:0">📦 Server Packages</h3>
<a href="/admin/packages" class="btn secondary" style="font-size:11px;padding:6px 14px">Manage All →</a>
</div>

<?php
$typeLabels = [
    'web_hosting' => '🌐 Web Hosting',
    'web_reseller' => '🌐 Web Reseller',
    'icecast' => '🎧 Icecast',
    'icecast_reseller' => '🎧 Icecast Reseller',
    'shoutcast' => '📡 SHOUTcast',
    'shoutcast_reseller' => '📡 SHOUTcast Reseller',
    'game_server' => '🎮 Game Server',
    'vps' => '🖥️ VPS',
    'dedicated' => '🖥️ Dedicated',
    'dev' => '🔧 Dev',
];
foreach ($grouped ?? [] as $type => $pkgs):
if (empty($pkgs)) continue;
?>
<div style="margin-bottom:20px">
<div class="pkg-group-title"><?php echo htmlspecialchars($typeLabels[$type] ?? $type); ?> <span class="cnt"><?php echo count($pkgs); ?> packages</span></div>
<div class="pkg-grid">
<?php foreach ($pkgs as $p):
    $featuresRaw = isset($p->features) ? $p->features : null;
    $feats = is_string($featuresRaw) ? json_decode($featuresRaw, true) ?? [] : (is_array($featuresRaw) ? $featuresRaw : []);
    $strPkg = $feats['streaming_package'] ?? [];
    $gamePkg = $feats['game_package'] ?? [];
?>
<div class="pkg-card">
<div style="display:flex;justify-content:space-between;align-items:start">
<div>
<div class="p-name"><?php echo htmlspecialchars($p->name); ?></div>
<div class="p-type"><?php echo htmlspecialchars($p->description ? substr($p->description, 0, 80) : ''); ?></div>
</div>
<span class="p-status" style="background:<?php echo ($p->is_active ?? 1) ? 'rgba(74,222,128,.12);color:#4ade80' : 'rgba(248,113,113,.12);color:#f87171' ?>"><?php echo ($p->is_active ?? 1) ? 'Active' : 'Inactive'; ?></span>
</div>
<div class="p-features" style="margin-top:6px">
<?php if (!empty($p->disk_space)): ?><span class="label">📁 Disk:</span> <?php echo number_format($p->disk_space); ?> MB<br><?php endif; ?>
<?php if (!empty($p->bandwidth)): ?><span class="label">📶 Bandwidth:</span> <?php echo number_format($p->bandwidth); ?> MB<br><?php endif; ?>
<?php if (!empty($p->email_accounts)): ?><span class="label">✉️ Emails:</span> <?php echo (int)$p->email_accounts; ?><br><?php endif; ?>
<?php if (!empty($p->ftp_accounts)): ?><span class="label">📁 FTP:</span> <?php echo (int)$p->ftp_accounts; ?><br><?php endif; ?>
<?php if (!empty($p->databases)): ?><span class="label">🗄️ DBs:</span> <?php echo (int)$p->databases; ?><br><?php endif; ?>
<?php if (!empty($p->subdomains)): ?><span class="label">🔗 Subdomains:</span> <?php echo (int)$p->subdomains; ?><br><?php endif; ?>
<?php if (!empty($p->addon_domains)): ?><span class="label">➕ Addons:</span> <?php echo (int)$p->addon_domains; ?><br><?php endif; ?>
<?php if (!empty($p->php_version)): ?><span class="label">🐘 PHP:</span> <?php echo htmlspecialchars($p->php_version); ?><br><?php endif; ?>
<?php if (!empty($strPkg)): ?>
<span class="label" style="color:#0A84FF">🎧 Streaming</span>
<?php if (!empty($strPkg['max_listeners'])): ?> — <?php echo $strPkg['max_listeners']; ?> Listeners<?php endif; ?>
<?php if (!empty($strPkg['max_stations'])): ?>, <?php echo $strPkg['max_stations']; ?> Stations<?php endif; ?>
<?php if (!empty($strPkg['max_bitrate'])): ?>, <?php echo $strPkg['max_bitrate']; ?> kbps<?php endif; ?>
<?php if (!empty($strPkg['max_djs'])): ?>, <?php echo $strPkg['max_djs']; ?> DJs<?php endif; ?>
<br>
<?php endif; ?>
<?php if (!empty($gamePkg)): ?>
<span class="label" style="color:#FF9500">🎮 Game</span>
<?php if (!empty($gamePkg['max_servers'])): ?> — <?php echo $gamePkg['max_servers']; ?> Servers<?php endif; ?>
<?php if (!empty($gamePkg['max_players'])): ?>, <?php echo $gamePkg['max_players']; ?> Slots<?php endif; ?>
<br>
<?php endif; ?>
<?php if (!empty($feats['chat'])): ?>💬 Chat<?php if (!empty($feats['chat_voice'])): ?>+Voice<?php endif; ?><?php if (!empty($feats['chat_video'])): ?>+Video<?php endif; ?><br><?php endif; ?>
<?php if (!empty($feats['dj_panel'])): ?>🎤 DJ Panel<br><?php endif; ?>
</div>
<div class="p-actions">
<a href="/admin/package/edit/<?php echo $p->id; ?>" style="background:rgba(0,140,255,.1);color:#38bdf8">Edit</a>
<a href="/admin/package/clone/<?php echo $p->id; ?>" style="background:rgba(74,222,128,.1);color:#4ade80" onclick="return confirm('Clone this package?')">Clone</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
<?php if (empty($grouped)): ?>
<div class="card"><p style="text-align:center;color:#64748b;padding:20px">No packages defined yet. <a href="/admin/package/create">Create your first package</a></p></div>
<?php endif; ?>
</div>
