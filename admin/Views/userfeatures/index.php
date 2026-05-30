<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Feature Management - Radio Hosting Panel</title>
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
        .actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 1rem;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: var(--secondary-color);
        }
        .btn-secondary:hover {
            background: #5a6268;
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
        .welcome-message {
            text-align: center;
            padding: 2rem;
        }
        .feature-toggle {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .feature-toggle.enabled {
            border-left: 4px solid #28a745;
        }
        .feature-toggle.disabled {
            border-left: 4px solid #dc3545;
        }
        .feature-toggle h4 {
            margin: 0 0 0.5rem 0;
        }
        .feature-toggle .status {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .feature-toggle .status.enabled {
            background: #d4edda;
            color: #155724;
        }
        .feature-toggle .status.disabled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - User Feature Management</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>User Feature Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Enable or disable features for user accounts from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['email_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>📧 Email</h4>
                <p>Allow users to create and manage email accounts</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['email_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['email_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/email" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['ftp_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>📁 FTP</h4>
                <p>Allow users to create and manage FTP accounts</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['ftp_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['ftp_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/ftp" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['cron_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>⏰ Cron Jobs</h4>
                <p>Allow users to create and manage cron jobs</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['cron_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['cron_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/cron" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['ssh_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>💻 SSH Access</h4>
                <p>Allow users to access SSH/shell</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['ssh_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['ssh_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/ssh" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['ssl_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>🔒 SSL/TLS</h4>
                <p>Allow users to manage SSL certificates</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['ssl_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['ssl_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/ssl" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['databases_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>🗄️ Databases</h4>
                <p>Allow users to create and manage databases</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['databases_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['databases_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/databases" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['dns_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>🌐 DNS Management</h4>
                <p>Allow users to manage DNS records</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['dns_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['dns_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/dns" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <div class="feature-toggle <?php echo $userFeaturesStats['git_enabled'] === 'enabled' ? 'enabled' : 'disabled'; ?>">
            <div>
                <h4>🔧 Git Version Control</h4>
                <p>Allow users to use Git for deployment</p>
            </div>
            <div>
                <span class="status <?php echo $userFeaturesStats['git_enabled']; ?>">
                    <?php echo ucfirst($userFeaturesStats['git_enabled']); ?>
                </span>
                <a href="/admin/userfeatures/toggle/git" class="btn btn-sm btn-secondary">Toggle</a>
            </div>
        </div>

        <nav class="nav">
            <h2>User Feature Management Functions</h2>
            <ul>
                <li><a href="/admin/userfeatures/email">Email Feature Settings</a></li>
                <li><a href="/admin/userfeatures/ftp">FTP Feature Settings</a></li>
                <li><a href="/admin/userfeatures/cron">Cron Feature Settings</a></li>
                <li><a href="/admin/userfeatures/ssh">SSH Feature Settings</a></li>
                <li><a href="/admin/userfeatures/ssl">SSL Feature Settings</a></li>
                <li><a href="/admin/userfeatures/databases">Databases Feature Settings</a></li>
                <li><a href="/admin/userfeatures/dns">DNS Feature Settings</a></li>
                <li><a href="/admin/userfeatures/git">Git Feature Settings</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>