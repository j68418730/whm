<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Overview - Radio Hosting Panel</title>
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        <?php 
        // Set CSS variables from theme settings
        $bgColor = !empty($theme_settings['background']['color']) ? $theme_settings['background']['color'] : '#ffffff';
        $headerColor = !empty($theme_settings['header']['color']) ? $theme_settings['header']['color'] : '#000000';
        $footerColor = !empty($theme_settings['footer']['color']) ? $theme_settings['footer']['color'] : '#000000';
        $primaryColor = !empty($theme_settings['colors']['primary']) ? $theme_settings['colors']['primary'] : '#007bff';
        $secondaryColor = !empty($theme_settings['colors']['secondary']) ? $theme_settings['colors']['secondary'] : '#6c757d';
        $headerHeight = !empty($theme_settings['header']['height']) ? $theme_settings['header']['height'] : '60px';
        $footerHeight = !empty($theme_settings['footer']['height']) ? $theme_settings['footer']['height'] : '40px';
        ?>
        :root {
            --primary-color: <?php echo $primaryColor; ?>;
            --secondary-color: <?php echo $secondaryColor; ?>;
            --background-color: <?php echo $bgColor; ?>;
            --header-color: <?php echo $headerColor; ?>;
            --footer-color: <?php echo $footerColor; ?>;
            --header-height: <?php echo $headerHeight; ?>;
            --footer-height: <?php echo $footerHeight; ?>;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: var(--background-color);
            color: #333;
        }
        header {
            background-color: var(--header-color);
            color: white;
            height: var(--header-height);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            padding: 2rem;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .stat-card .value {
            font-size: 1.8rem;
            font-weight: bold;
            margin: 0.5rem 0;
        }
        .stat-card .label {
            font-size: 0.9rem;
            color: #666;
        }
        .service-status {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .service-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        .service-item:last-child {
            border-bottom: none;
        }
        .service-status .status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .service-status .status.running {
            background: #d4edda;
            color: #155724;
        }
        .service-status .status.stopped {
            background: #f8d7da;
            color: #721c24;
        }
        .alerts {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        .alert-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1rem;
            flex: 1;
            text-align: center;
        }
        .alert-card h4 {
            margin: 0 0 0.5rem 0;
            color: var(--primary-color);
        }
        .alert-card .value {
            font-size: 1.5rem;
            font-weight: bold;
        }
        .alert-card.security {
            border-left: 4px solid #dc3545;
        }
        .alert-card.update {
            border-left: 4px solid #ffc107;
        }
        .nav {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        .nav h2 {
            margin-top: 0;
        }
        .nav ul {
            list-style: none;
            padding: 0;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        .nav ul li a {
            text-decoration: none;
            color: var(--primary-color);
            font-weight: bold;
        }
        .nav ul li a:hover {
            text-decoration: underline;
        }
        footer {
            background-color: var(--footer-color);
            color: white;
            height: var(--footer-height);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-top: 2rem;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
        }
        .btn:hover {
            background: #0056b3;
        }
        .welcome-message {
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - Server Overview</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>Welcome to the Server Overview</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Here you can monitor your server's vital statistics.</p>
            <a href="/admin/dashboard" class="btn">Back to Admin Dashboard</a>
        </div>

        <!-- System Info -->
        <div class="stat-card" style="grid-column:1/-1">
            <h3>🖥 System Information</h3>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-top:12px">
                <div><strong>Hostname</strong><br><?php echo htmlspecialchars($serverStats['hostname'] ?? 'N/A'); ?></div>
                <div><strong>OS</strong><br><?php echo htmlspecialchars($serverStats['os'] ?? 'N/A'); ?></div>
                <div><strong>Kernel</strong><br><?php echo htmlspecialchars($serverStats['kernel'] ?? 'N/A'); ?></div>
                <div><strong>CPU</strong><br><?php echo htmlspecialchars($serverStats['cpu_model'] ?? 'N/A'); ?></div>
                <div><strong>Uptime</strong><br><?php echo $serverStats['uptime'] ?? 'N/A'; ?></div>
            </div>
        </div>

        <div class="stat-card">
            <h3>⚡ CPU Load</h3>
            <div class="value"><?php echo $serverStats['cpu_load']; ?>%</div>
            <div class="label">Load: <?php echo $serverStats['load_average']['1min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['5min'] ?? '?'; ?> / <?php echo $serverStats['load_average']['15min'] ?? '?'; ?> (1/5/15min)</div>
        </div>

        <div class="stat-card">
            <h3>💾 RAM Usage</h3>
            <div class="value"><?php echo $serverStats['ram_usage']; ?>%</div>
            <div class="label"><?php echo $serverStats['ram_total']; ?> GB total</div>
        </div>

        <div class="stat-card">
            <h3>💿 Disk Usage</h3>
            <div class="value"><?php echo $serverStats['disk_usage']; ?>%</div>
            <div class="label"><?php echo $serverStats['disk_total']; ?> total</div>
        </div>

        <div class="stat-card">
            <h3>👥 Active Accounts</h3>
            <div class="value"><?php echo $serverStats['active_accounts']; ?></div>
            <div class="label">Hosting accounts</div>
        </div>

        <div class="stat-card">
            <h3>🔧 Services</h3>
            <div style="margin-top:8px">
            <?php foreach ($serverStats['service_status'] as $svc => $st): ?>
                <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)">
                    <span style="font-size:13px"><?php echo htmlspecialchars(ucfirst($svc)); ?></span>
                    <span style="font-size:12px;padding:2px 8px;border-radius:4px;<?php echo $st === 'active' ? 'background:#1a3a2a;color:#4ade80' : 'background:#3a1a1a;color:#f87171'; ?>"><?php echo $st; ?></span>
                </div>
            <?php endforeach; ?>
            </div>
        </div>

        <nav class="nav">
            <h2>Quick Navigation</h2>
            <ul>
                <li><a href="/admin/account">Account Functions</a></li>
                <li><a href="/admin/packages">Package Management</a></li>
                <li><a href="/admin/dns">DNS Functions</a></li>
                <li><a href="/admin/email">Email Administration</a></li>
                <li><a href="/admin/apache">Apache Configuration</a></li>
                <li><a href="/admin/php">PHP Management</a></li>
                <li><a href="/admin/mysql">MySQL Management</a></li>
                <li><a href="/admin/ssl">SSL/TLS Management</a></li>
                <li><a href="/admin/security">Security Center</a></li>
                <li><a href="/admin/backup">Backup System</a></li>
                <li><a href="/admin/radiosettings">Soni Radio</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>