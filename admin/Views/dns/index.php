<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DNS Functions - Radio Hosting Panel</title>
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
        .record-type {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .record-type.A { background: #d1ecf1; color: #0c5460; }
        .record-type.AAAA { background: #d4edda; color: #155724; }
        .record-type.CNAME { background: #fff3cd; color: #856404; }
        .record-type.MX { background: #f8d7da; color: #721c24; }
        .record-type.TXT { background: #e2e3e5; color: #383d41; }
        .record-type.SPF { background: #e2e3e5; color: #383d41; }
        .record-type.DKIM { background: #e2e3e5; color: #383d41; }
        .record-type.SRV { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - DNS Functions</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>DNS Zone Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Manage DNS zones and records from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
            <a href="/admin/dns/create-zone" class="btn">Create DNS Zone</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🌐 Total DNS Zones</h3>
                <div class="value"><?php echo $dnsStats['total_zones']; ?></div>
                <div class="label">All DNS zones managed</div>
            </div>

            <div class="stat-card">
                <h3>✅ Active Zones</h3>
                <div class="value"><?php echo $dnsStats['active_zones']; ?></div>
                <div class="label">Currently active DNS zones</div>
            </div>

            <div class="stat-card">
                <h3>📋 Total DNS Records</h3>
                <div class="value"><?php echo $dnsStats['total_records']; ?></div>
                <div class="label">All DNS records across zones</div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Zone Name</th>
                        <th>Record Type</th>
                        <th>Record Name</th>
                        <th>Record Value</th>
                        <th>TTL</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td colspan="6" style="text-align:center;padding:2rem;color:#999;">No DNS zones configured yet.</td></tr>
                </tbody>
            </table>
        </div>

        <nav class="nav">
            <h2>DNS Functions</h2>
            <ul>
                <li><a href="/admin/dns/create-zone">Create DNS Zone</a></li>
                <li><a href="/admin/dns/list-zones">List DNS Zones</a></li>
                <li><a href="/admin/dns/edit-zone">Edit DNS Zone</a></li>
                <li><a href="/admin/dns/delete-zone">Delete DNS Zone</a></li>
                <li><a href="/admin/dns/add-record">Add DNS Record</a></li>
                <li><a href="/admin/dns/edit-record">Edit DNS Record</a></li>
                <li><a href="/admin/dns/delete-record">Delete DNS Record</a></li>
                <li><a href="/admin/dns/clustering">DNS Clustering</a></li>
                <li><a href="/admin/dns/failover">Failover DNS</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>