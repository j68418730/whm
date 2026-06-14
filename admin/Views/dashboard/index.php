<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spectre WHM - Root Hosting Management</title>
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
$addons = $addons ?? [];

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
    'Packages' => [
        ['Package Manager', '/admin/packages'],
        ['Upgrade / Downgrade Accounts', '/admin/packages'],
        ['Feature Manager', '/admin/userfeatures'],
        ['Licensing', '/admin/licensing'],
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
                    <small>Root Hosting Panel</small>
                </div>
            </div>
            <a href="/admin/dashboard" class="active">Dashboard</a>
            <a href="/admin/account">Account Functions</a>
            <a href="/admin/reseller">Reseller Center</a>
            <a href="/admin/packages">Packages</a>
            <a href="/admin/server">Server Overview</a>
            <a href="/admin/security">Security Center</a>
            <a href="/admin/backup">Backups</a>
            <?php foreach ($addons as $addon): ?>
                <?php if (!empty($addon['admin_url'])): ?>
                    <a href="<?php echo htmlspecialchars($addon['admin_url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($addon['name'], ENT_QUOTES, 'UTF-8'); ?> Add-on</a>
                <?php endif; ?>
            <?php endforeach; ?>
            <a href="/admin/logout">Logout</a>
        </aside>

        <section class="whm-content">
            <div class="topbar">
                <div>
                    <span class="eyebrow">Root Administrator</span>
                    <h1>WHM Control Center</h1>
                    <p>Welcome, <?php echo $userName; ?>. Manage hosting accounts, resellers, packages, DNS, services, security, backups, and server operations from the core panel.</p>
                </div>
                <div class="quick-actions">
                    <a class="btn" href="/admin/account">Create Account</a>
                    <a class="btn btn-secondary" href="/admin/packages">Manage Packages</a>
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
                    <h3>Enabled Add-ons</h3>
                    <div class="value"><?php echo count($addons); ?></div>
                    <p>Streaming, billing, and future modules</p>
                </div>
                <div class="stat-card">
                    <h3>Server Services</h3>
                    <div class="value">0</div>
                    <p>Provisioning engine pending</p>
                </div>
            </div>

            <section class="radio-command addon-command">
                <div>
                    <span class="eyebrow">Add-on Architecture</span>
                    <h2>WHM stays the main panel. Streaming and billing load as add-ons.</h2>
                    <p>Core hosting features live in the WHM side. Radio and billing can be enabled, disabled, sold, and developed independently.</p>
                </div>
                <?php if (!empty($addons)): ?>
                    <div class="radio-actions">
                        <?php foreach ($addons as $index => $addon): ?>
                            <?php if (!empty($addon['admin_url'])): ?>
                                <a class="btn<?php echo $index === 0 ? '' : ' btn-secondary'; ?>" href="<?php echo htmlspecialchars($addon['admin_url'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($addon['name'], ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <?php if (!empty($addons)): ?>
                <div class="module-grid addon-grid">
                    <?php foreach ($addons as $addon): ?>
                        <section class="module-card addon-card">
                            <span class="eyebrow"><?php echo htmlspecialchars($addon['category'], ENT_QUOTES, 'UTF-8'); ?> add-on</span>
                            <h2><?php echo htmlspecialchars($addon['name'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <p><?php echo htmlspecialchars($addon['description'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php if (!empty($addon['features'])): ?>
                                <div class="module-links feature-list">
                                    <?php foreach ($addon['features'] as $feature): ?>
                                        <span><?php echo htmlspecialchars($feature, ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($addon['admin_url'])): ?>
                                <a class="btn" href="<?php echo htmlspecialchars($addon['admin_url'], ENT_QUOTES, 'UTF-8'); ?>">Open Add-on</a>
                            <?php endif; ?>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

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
