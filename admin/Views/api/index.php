<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API System - Radio Hosting Panel</title>
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
        .table-responsive {
            overflow-x: auto;
            margin-bottom: 2rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-disabled {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - API System</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>API Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Manage API access, tokens, and integrations from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
            <a href="/admin/api/tokens" class="btn">Manage API Tokens</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🔑 Total API Tokens</h3>
                <div class="value><?php echo $apiStats['total_api_tokens']; ?></div>
                <div class="label">All API tokens created</div>
            </div>

            <div class="stat-card">
                <h3>✅ Active API Tokens</h3>
                <div class="value><?php echo $apiStats['active_api_tokens']; ?></div>
                <div class="label">Currently active API tokens</div>
            </div>

            <div class="stat-card">
                <h3>📡 API Requests Today</h3>
                <div class="value><?php echo $apiStats['api_requests_today']; ?></div>
                <div class="label">API requests processed today</div>
            </div>

            <div class="stat-card">
                <h3>🚫 Blocked Requests</h3>
                <div class="value><?php echo $apiStats['blocked_api_requests']; ?></div>
                <div class="label">Blocked or denied API requests</div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>API Type</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - in real implementation, this would come from database -->
                    <tr>
                        <td>WHM API</td>
                        <td><span class="status-badge <?php echo $apiStats['whm_api_enabled'] === 'enabled' ? 'status-active' : 'status-disabled'; ?>">
                            <?php echo ucfirst($apiStats['whm_api_enabled']); ?>
                        </span></td>
                        <td>Admin-level API for server management</td>
                        <td>
                            <a href="/admin/api/whm/settings" class="btn btn-sm btn-secondary">Configure</a>
                        </td>
                    </tr>
                    <tr>
                        <td>UAPI (User API)</td>
                        <td><span class="status-badge <?php echo $apiStats['uapi_enabled'] === 'enabled' ? 'status-active' : 'status-disabled'; ?>">
                            <?php echo ucfirst($apiStats['uapi_enabled']); ?>
                        </span></td>
                        <td>User-level API for account management</td>
                        <td>
                            <a href="/admin/api/uapi/settings" class="btn btn-sm btn-secondary">Configure</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Email API</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>Manage email accounts, forwarders, filters</td>
                        <td>
                            <a href="/admin/api/email" class="btn btn-sm btn-secondary">Manage</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Database API</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td>Manage MySQL databases and users</td>
                        <td>
                            <a href="/admin/api/database" class="btn btn-sm btn-secondary">Manage</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav class="nav">
            <h2>API System Functions</h2>
            <ul>
                <li><a href="/admin/api/tokens">Manage API Tokens</a></li>
                <li><a href="/admin/api/whm">WHM API Settings</a></li>
                <li><a href="/admin/api/uapi">UAPI Settings</a></li>
                <li><a href="/admin/api/email">Email API</a></li>
                <li><a href="/admin/api/database">Database API</a></li>
                <li><a href="/admin/api/dns">DNS API</a></li>
                <li><a href="/admin/api/ssl">SSL API</a></li>
                <li><a href="/admin/api/backup">Backup API</a></li>
                <li><a href="/admin/api/radio">Radio Streaming API</a></li>
                <li><a href="/admin/api/autodj">AutoDJ API</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>