<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL / Database Management - Radio Hosting Panel</title>
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
        .status-running {
            background: #d4edda;
            color: #155724;
        }
        .status-stopped {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - MySQL / Database Management</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>MySQL Database Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Manage MySQL servers, databases, users, and phpMyAdmin from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
            <a href="/admin/mysql/phpmyadmin" class="btn">Launch phpMyAdmin</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🐘 MySQL Version</h3>
                <div class="value"><?php echo $mysqlStats['mysql_version']; ?></div>
                <div class="label">Installed MySQL version</div>
            </div>

            <div class="stat-card">
                <h3>🗄️ Total Databases</h3>
                <div class="value"><?php echo $mysqlStats['total_databases']; ?></div>
                <div class="label">Total MySQL databases</div>
            </div>

            <div class="stat-card">
                <h3>👥 Total DB Users</h3>
                <div class="value"><?php echo $mysqlStats['total_db_users']; ?></div>
                <div class="label">Total MySQL users</div>
            </div>

            <div class="stat-card">
                <h3>💾 Database Size</h3>
                <div class="value"><?php echo $mysqlStats['database_size']; ?> GB</div>
                <div class="label">Total database storage used</div>
            </div>

            <div class="stat-card">
                <h3>⚡ Queries/sec</h3>
                <div class="value"><?php echo $mysqlStats['queries_per_second']; ?></div>
                <div class="label">Average queries per second</div>
            </div>

            <div class="stat-card">
                <h3>🐢 Slow Queries</h3>
                <div class="value"><?php echo $mysqlStats['slow_queries']; ?></div>
                <div class="label">Slow queries detected</div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Database Name</th>
                        <th>Size</th>
                        <th>Owner</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - in real implementation, this would come from database -->
                    <tr>
                        <td>account1_wp</td>
                        <td>25 MB</td>
                        <td>user1</td>
                        <td>2026-05-15</td>
                        <td>
                            <a href="/admin/mysql/db/edit/account1_wp" class="btn btn-sm btn-secondary">Manage</a>
                            <a href="/admin/mysql/db/delete/account1_wp" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>account2_store</td>
                        <td>120 MB</td>
                        <td>user2</td>
                        <td>2026-05-20</td>
                        <td>
                            <a href="/admin/mysql/db/edit/account2_store" class="btn btn-sm btn-secondary">Manage</a>
                            <a href="/admin/mysql/db/delete/account2_store" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>account3_blog</td>
                        <td>45 MB</td>
                        <td>user3</td>
                        <td>2026-05-25</td>
                        <td>
                            <a href="/admin/mysql/db/edit/account3_blog" class="btn btn-sm btn-secondary">Manage</a>
                            <a href="/admin/mysql/db/delete/account3_blog" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <nav class="nav">
            <h2>MySQL / Database Management Functions</h2>
            <ul>
                <li><a href="/admin/mysql/server">MySQL Server</a></li>
                <li><a href="/admin/mysql/tune">Tune MySQL</a></li>
                <li><a href="/admin/mysql/root-password">Root Password</a></li>
                <li><a href="/admin/mysql/database-mapping">Database Mapping</a></li>
                <li><a href="/admin/mysql/phpmyadmin">phpMyAdmin Integration</a></li>
                <li><a href="/admin/mysql/db/create">Create Database</a></li>
                <li><a href="/admin/mysql/db/list">List Databases</a></li>
                <li><a href="/admin/mysql/user/create">Create Database User</a></li>
                <li><a href="/admin/mysql/user/permissions">Database Permissions</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>