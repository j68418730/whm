<?php
$userName = htmlspecialchars($user->name ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$stats = array_merge(['total_streams' => 0, 'active_streams' => 0, 'total_listeners' => 0, 'bandwidth_used' => 0], $stats ?? []);
$addons = $addons ?? [];
$moduleGroups = [
    'Account Functions' => [
        ['Create a New Account', '/admin/account'],['List Accounts', '/admin/account'],['Suspend / Unsuspend Accounts', '/admin/account'],['Feature Manager', '/admin/userfeatures'],
    ],
    'Reseller Center' => [
        ['Manage Resellers', '/admin/reseller'],['Assign Account Ownership', '/admin/reseller'],['Reseller Privileges', '/admin/reseller'],['Branding', '/admin/branding'],
    ],
    'Packages & Billing' => [
        ['Package Manager', '/admin/packages'],['Upgrade / Downgrade Accounts', '/admin/packages'],['API Access', '/admin/api'],['Licensing', '/admin/licensing'],
    ],
    'Radio Management' => [
        ['Radio Dashboard', '/admin/radio_dashboard'],['Manage Streams', '/admin/streams'],['Create Stream', '/admin/streams/create'],['Radio Settings', '/admin/radiosettings'],
    ],
    'Server Configuration' => [
        ['Server Overview', '/admin/server'],['Apache Configuration', '/admin/apache'],['PHP Management', '/admin/php'],['MySQL Databases', '/admin/mysql'],
    ],
    'Security & Operations' => [
        ['SSL/TLS', '/admin/ssl'],['Security Center', '/admin/security'],['Backup System', '/admin/backup'],['Monitoring', '/admin/monitoring'],
    ],
    'Developer Tools' => [
        ['Terminal', '/admin/terminal'],['Git Deployment', '/admin/git'],['Containers', '/admin/container'],['Installers', '/admin/installers'],
    ],
];
?>
<div class="stats-grid">
<div class="stat-card"><h3>Hosting Accounts</h3><div class="value">0</div><div class="label">Active accounts</div></div>
<div class="stat-card"><h3>Resellers</h3><div class="value">0</div><div class="label">Delegated account owners</div></div>
<div class="stat-card"><h3>Radio Streams</h3><div class="value"><?php echo (int)$stats['total_streams']; ?></div><div class="label"><?php echo (int)$stats['active_streams']; ?> currently active</div></div>
<div class="stat-card"><h3>Listeners</h3><div class="value"><?php echo (int)$stats['total_listeners']; ?></div><div class="label">Bandwidth: <?php echo number_format($stats['bandwidth_used'] / (1024 * 1024), 2); ?> MB</div></div>
</div>

<div class="card" style="background:rgba(0,140,255,.05);border-color:rgba(0,140,255,.2);margin-bottom:24px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
<div><span style="color:var(--accent);font-size:13px;text-transform:uppercase;letter-spacing:1px">Built-In Radio Hosting</span>
<h2 style="font-size:20px;margin:4px 0 0">Icecast, AutoDJ, DJs, playlists &amp; transcoding</h2>
<p style="color:var(--text-secondary);margin-top:4px">Radio is treated as a native hosting feature — provision stations alongside web hosting accounts.</p></div>
<div style="display:flex;gap:10px"><a href="/admin/radio_dashboard" class="btn primary">Open Radio Dashboard</a><a href="/admin/radiosettings" class="btn secondary">Radio Settings</a></div>
</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px">
<?php foreach ($moduleGroups as $group => $links): ?>
<div class="card">
<h3 style="color:var(--accent);font-size:15px;margin-bottom:12px"><?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?></h3>
<?php foreach ($links as $link): ?>
<a href="<?php echo htmlspecialchars($link[1], ENT_QUOTES, 'UTF-8'); ?>" style="display:block;padding:8px 12px;border-radius:6px;color:var(--text-table);text-decoration:none;font-size:14px;margin-bottom:2px;transition:.15s"><?php echo htmlspecialchars($link[0], ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>
