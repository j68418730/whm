<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Radio Hosting Panel</title>
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
        .stats {
            display: flex;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .stat-card {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            flex: 1;
            min-width: 200px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            margin: 0 0 0.5rem 0;
            color: var(--primary-color);
        }
        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
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
        <h1>Radio Hosting Panel - Admin Dashboard</h1>
    </header>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Total Streams</h3>
                <div class="value"><?php echo $stats['total_streams']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Streams</h3>
                <div class="value"><?php echo $stats['active_streams']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Listeners</h3>
                <div class="value"><?php echo $stats['total_listeners']; ?></div>
            </div>
            <div class="stat-card">
                <h3>Bandwidth Used</h3>
                <div class="value"><?php echo number_format($stats['bandwidth_used'] / (1024*1024), 2); ?> MB</div>
            </div>
        </div>

        <nav class="nav">
            <h2>Admin Navigation</h2>
            <ul>
                <li><a href="/admin/dashboard">SERVER OVERVIEW</a></li>
                <li><a href="/admin/account">ACCOUNT FUNCTIONS</a></li>
                <li><a href="/admin/packages">PACKAGE MANAGEMENT</a></li>
                <li><a href="/admin/reseller">RESELLER MANAGEMENT</a></li>
                <li><a href="/admin/dns">DNS FUNCTIONS</a></li>
                <li><a href="/admin/email">EMAIL ADMINISTRATION</a></li>
                <li><a href="/admin/apache">APACHE CONFIGURATION</a></li>
                <li><a href="/admin/php">PHP MANAGEMENT</a></li>
                <li><a href="/admin/mysql">MYSQL / DATABASE MANAGEMENT</a></li>
                <li><a href="/admin/ftp">FTP MANAGEMENT</a></li>
                <li><a href="/admin/ssl">SSL/TLS MANAGEMENT</a></li>
                <li><a href="/admin/security">SECURITY CENTER</a></li>
                <li><a href="/admin/backup">BACKUP SYSTEM</a></li>
                <li><a href="/admin/server">SERVER CONFIGURATION</a></li>
                <li><a href="/admin/network">NETWORK FUNCTIONS</a></li>
                <li><a href="/admin/monitoring">MONITORING SYSTEM</a></li>
                <li><a href="/admin/software">SOFTWARE MANAGEMENT</a></li>
                <li><a href="/admin/api">API SYSTEM</a></li>
                <li><a href="/admin/branding">BRANDING SYSTEM</a></li>
                <li><a href="/admin/clustering">CLUSTERING & HIGH AVAILABILITY</a></li>
                <li><a href="/admin/filesystem">FILESYSTEM & USER MANAGEMENT</a></li>
                <li><a href="/admin/terminal">TERMINAL & SHELL ACCESS</a></li>
                <li><a href="/admin/metrics">METRICS & ANALYTICS</a></li>
                <li><a href="/admin/installers">INSTALLERS & APPLICATIONS</a></li>
                <li><a href="/admin/userfeatures">USER FEATURE MANAGEMENT</a></li>
                <li><a href="/admin/cron">CRON & TASK AUTOMATION</a></li>
                <li><a href="/admin/git">GIT & DEPLOYMENT</a></li>
                <li><a href="/admin/container">CONTAINER & VIRTUALIZATION SUPPORT</a></li>
                <li><a href="/admin/licensing">LICENSING SYSTEM</a></li>
                <li><a href="/admin/radiosettings">SONI RADIO</a></li>
                <li><a href="/admin/radio_dashboard">RADIO DASHBOARD</a></li>
                <li><a href="/admin/logout">Logout</a></li>
            </ul>
        </nav>

        <div class="welcome-message">
            <h2>Welcome to the Radio Hosting Panel Admin Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! From here you can manage all aspects of the radio hosting system.</p>
            <a href="/admin/theme" class="btn">Customize Your Theme</a>
            <a href="/admin/streams" class="btn">Manage Radio Streams</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>