<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reseller Center - Spectre WHM</title>
    <link rel="stylesheet" href="/css/admin.css">
</head>
<body class="whm-body">
<?php
$resellerStats = array_merge([
    'total_resellers' => 0,
    'active_resellers' => 0,
    'accounts_owned_by_resellers' => 0,
], $resellerStats ?? []);
?>
    <main class="whm-shell">
        <aside class="whm-sidebar">
            <div class="brand">
                <span class="brand-mark">S</span>
                <div><strong>Spectre WHM</strong><small>Reseller Center</small></div>
            </div>
            <a href="/admin/dashboard">Dashboard</a>
            <a href="/admin/reseller" class="active">Manage Resellers</a>
            <a href="/admin/packages">Packages</a>
            <a href="/admin/userfeatures">Feature Manager</a>
            <a href="/admin/radiosettings">Radio Limits</a>
            <a href="/admin/branding">Branding</a>
        </aside>

        <section class="whm-content">
            <div class="topbar">
                <div>
                    <span class="eyebrow">WHM Reseller Management</span>
                    <h1>Reseller Accounts</h1>
                    <p>Create reseller owners, assign hosting accounts, set privileges, and control radio hosting limits per reseller.</p>
                </div>
                <div class="quick-actions">
                    <a class="btn" href="/admin/reseller/create">Create Reseller</a>
                    <a class="btn btn-secondary" href="/admin/packages">Manage Packages</a>
                </div>
            </div>

            <div class="stats whm-stats">
                <div class="stat-card">
                    <h3>Total Resellers</h3>
                    <div class="value"><?php echo (int)$resellerStats['total_resellers']; ?></div>
                    <p>All reseller accounts</p>
                </div>
                <div class="stat-card">
                    <h3>Active Resellers</h3>
                    <div class="value"><?php echo (int)$resellerStats['active_resellers']; ?></div>
                    <p>Allowed to provision accounts</p>
                </div>
                <div class="stat-card">
                    <h3>Owned Accounts</h3>
                    <div class="value"><?php echo (int)$resellerStats['accounts_owned_by_resellers']; ?></div>
                    <p>Customer accounts under resellers</p>
                </div>
            </div>

            <div class="module-grid">
                <section class="module-card">
                    <h2>Privileges</h2>
                    <div class="module-links">
                        <a href="/admin/reseller">Create accounts</a>
                        <a href="/admin/reseller">Suspend accounts</a>
                        <a href="/admin/reseller">Modify packages</a>
                        <a href="/admin/reseller">Access radio manager</a>
                    </div>
                </section>
                <section class="module-card">
                    <h2>Radio Quotas</h2>
                    <div class="module-links">
                        <a href="/admin/radiosettings">Streams per reseller</a>
                        <a href="/admin/radiosettings">Listener caps</a>
                        <a href="/admin/radiosettings">AutoDJ storage</a>
                        <a href="/admin/radiosettings">DJ account limits</a>
                    </div>
                </section>
                <section class="module-card">
                    <h2>Branding</h2>
                    <div class="module-links">
                        <a href="/admin/branding">Private nameservers</a>
                        <a href="/admin/theme">Panel theme</a>
                        <a href="/admin/api">API tokens</a>
                        <a href="/admin/licensing">License status</a>
                    </div>
                </section>
            </div>
        </section>
    </main>
</body>
</html>
