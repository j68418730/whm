<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Management - Radio Hosting Panel</title>
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
        .limits {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 4px;
            margin-top: 1rem;
        }
        .limits h4 {
            margin-top: 0;
        }
        .limits-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - Package Management</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>Hosting Package Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Create and manage hosting packages from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
            <a href="/admin/package/create" class="btn">Create New Package</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>📦 Total Packages</h3>
                <div class="value"><?php echo $packagesStats['total_packages']; ?></div>
                <div class="label">All hosting packages</div>
            </div>

            <div class="stat-card">
                <h3>✅ Active Packages</h3>
                <div class="value"><?php echo $packagesStats['active_packages']; ?></div>
                <div class="label">Currently available packages</div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Package Name</th>
                        <th>Price</th>
                        <th>Disk Quota</th>
                        <th>Bandwidth</th>
                        <th>Max Domains</th>
                        <th>Max Email Accounts</th>
                        <th>Max Databases</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Sample data - in real implementation, this would come from database -->
                    <tr>
                        <td>Basic</td>
                        <td>$5.99/mo</td>
                        <td>10 GB</td>
                        <td>100 GB</td>
                        <td>1</td>
                        <td>5</td>
                        <td>1</td>
                        <td>
                            <a href="/admin/package/edit/basic" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="/admin/package/delete/basic" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Premium</td>
                        <td>$12.99/mo</td>
                        <td>20 GB</td>
                        <td>500 GB</td>
                        <td>5</td>
                        <td>20</td>
                        <td>5</td>
                        <td>
                            <a href="/admin/package/edit/premium" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="/admin/package/delete/premium" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                    <tr>
                        <td>Enterprise</td>
                        <td>$24.99/mo</td>
                        <td>50 GB</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                        <td>Unlimited</td>
                        <td>
                            <a href="/admin/package/edit/enterprise" class="btn btn-sm btn-secondary">Edit</a>
                            <a href="/admin/package/delete/enterprise" class="btn btn-sm btn-secondary">Delete</a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="limits">
            <h4>Package Limits Reference</h4>
            <div class="limits-item">
                <span>Disk quota:</span>
                <span>Storage space allocated for website files, emails, and databases</span>
            </div>
            <div class="limits-item">
                <span>Monthly bandwidth:</span>
                <span>Data transfer limit for website visitors</span>
            </div>
            <div class="limits-item">
                <span>Max FTP accounts:</span>
                <span>Number of FTP users that can be created</span>
            </div>
            <div class="limits-item">
                <span>Max email accounts:</span>
                <span>Number of email addresses that can be created</span>
            </div>
            <div class="limits-item">
                <span>Max databases:</span>
                <span>Number of MySQL databases that can be created</span>
            </div>
            <div class="limits-item">
                <span>Max subdomains:</span>
                <span>Number of subdomains (sub.domain.com) allowed</span>
            </div>
            <div class="limits-item">
                <span>Max parked domains:</span>
                <span>Number of additional domains pointing to same website</span>
            </div>
            <div class="limits-item">
                <span>Max addon domains:</span>
                <span>Number of completely separate websites allowed</span>
            </div>
        </div>

        <nav class="nav">
            <h2>Package Management Functions</h2>
            <ul>
                <li><a href="/admin/package/create">Create Package</a></li>
                <li><a href="/admin/package/edit">Edit Package</a></li>
                <li><a href="/admin/package/delete">Delete Package</a></li>
                <li><a href="/admin/package/limits">Package Limits</a></li>
                <li><a href="/admin/package/features">Feature Lists</a></li>
                <li><a href="/admin/package/featurelists">Manage Feature Lists</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>