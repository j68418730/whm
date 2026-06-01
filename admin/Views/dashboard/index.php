<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectre WHM - Hosting & Radio Management</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="whm-body">
<?php
$userName = htmlspecialchars($user->name ?? 'Administrator', ENT_QUOTES, 'UTF-8');
$stats = array_merge([
    'total_streams' => 0,
    'active_streams' => 0,
    'total_listeners' => 0,
    'bandwidth_used' => 0,
], $stats ?? []);

$moduleGroups = [
    'Account Functions' => [
        ['Create a New Account', '/admin/account'],
        ['List Accounts', '/admin/account'],
        ['Suspend / Unsuspend Accounts', '/admin/account'],
        ['Feature Manager', '/admin/userfeatures'],
    ],
    'Reseller Center' => [
        ['Manage Resellers', '/admin/reseller'],
        ['Assign Account Ownership', '/admin/reseller'],
        ['Reseller Privileges', '/admin/reseller'],
        ['Branding', '/admin/branding'],
    ],
    'Packages & Billing' => [
        ['Package Manager', '/admin/packages'],
        ['Upgrade / Downgrade Accounts', '/admin/packages'],
        ['API Access', '/admin/api'],
        ['Licensing', '/admin/licensing'],
    ],
    'Radio Management' => [
        ['Radio Dashboard', '/admin/radio_dashboard'],
        ['Manage Streams', '/admin/streams'],
        ['Create Stream', '/admin/streams/create'],
        ['Radio Settings', '/admin/radiosettings'],
    ],
    'Server Configuration' => [
        ['Server Overview', '/admin/server'],
        ['Apache Configuration', '/admin/apache'],
        ['PHP Management', '/admin/php'],
        ['MySQL Databases', '/admin/mysql'],
    ],
    'Security & Operations' => [
        ['SSL/TLS', '/admin/ssl'],
        ['Security Center', '/admin/security'],
        ['Backup System', '/admin/backup'],
        ['Monitoring', '/admin/monitoring'],
    ],
    'Developer Tools' => [
        ['Terminal', '/admin/terminal'],
        ['Git Deployment', '/admin/git'],
        ['Containers', '/admin/container'],
        ['Installers', '/admin/installers'],
    ],
];
?>
    <main class="whm-shell">
        <aside class="whm-sidebar">
            <div class="brand">
                <span class="brand-mark">S</span>
                <div>
                    <strong>Spectre WHM</strong>
                    <small>Hosting + Radio</small>
                </div>
            </div>
            <a href="/admin/dashboard" class="active">Dashboard</a>
            <a href="/admin/account">Account Functions</a>
            <a href="/admin/reseller">Reseller Center</a>
            <a href="/admin/packages">Packages</a>
            <a href="/admin/streams">Radio Streams</a>
            <a href="/admin/radio_dashboard">Radio Dashboard</a>
            <a href="/admin/server">Server Overview</a>
            <a href="/admin/security">Security Center</a>
            <a href="/admin/backup">Backups</a>
            <a href="/admin/logout">Logout</a>
        </aside>

        <section class="whm-content">
            <div class="topbar">
                <div>
                    <span class="eyebrow">Root Administrator</span>
                    <h1>WHM Control Center</h1>
                    <p>Welcome, <?php echo $userName; ?>. Manage hosting accounts, resellers, packages, services, and radio streaming from one panel.</p>
                </div>
                <div class="quick-actions">
                    <a class="btn" href="/admin/account">Create Account</a>
                    <a class="btn btn-secondary" href="/admin/streams/create">Create Radio Stream</a>
                </div>
            </div>

            <div class="stats whm-stats">
                <div class="stat-card">
                    <h3>Hosting Accounts</h3>
                    <div class="value">0</div>
                    <p>Active cPanel-style accounts</p>
                </div>
                <div class="stat-card">
                    <h3>Resellers</h3>
                    <div class="value">0</div>
                    <p>Delegated account owners</p>
                </div>
                <div class="stat-card">
                    <h3>Radio Streams</h3>
                    <div class="value"><?php echo (int)$stats['total_streams']; ?></div>
                    <p><?php echo (int)$stats['active_streams']; ?> currently active</p>
                </div>
                <div class="stat-card">
                    <h3>Listeners</h3>
                    <div class="value"><?php echo (int)$stats['total_listeners']; ?></div>
                    <p><?php echo number_format($stats['bandwidth_used'] / (1024 * 1024), 2); ?> MB used</p>
                </div>
            </div>

            <section class="radio-command">
                <div>
                    <span class="eyebrow">Built-In Radio Hosting</span>
                    <h2>Icecast, AutoDJ, DJs, playlists, transcoding, and reseller limits</h2>
                    <p>Radio is treated as a native hosting feature, so admins and resellers can provision stations alongside normal web hosting accounts.</p>
                </div>
                <div class="radio-actions">
                    <a class="btn" href="/admin/radio_dashboard">Open Radio Dashboard</a>
                    <a class="btn btn-secondary" href="/admin/radiosettings">Global Radio Settings</a>
                </div>
            </section>

            <div class="module-grid">
                <?php foreach ($moduleGroups as $group => $links): ?>
                    <section class="module-card">
                        <h2><?php echo htmlspecialchars($group, ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="module-links">
                            <?php foreach ($links as $link): ?>
                                <a href="<?php echo htmlspecialchars($link[1], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($link[0], ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</body>
</html>
