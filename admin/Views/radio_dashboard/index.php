<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radio Dashboard - Radio Hosting Panel</title>
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
        .stream-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stream-status.online {
            background: #d4edda;
            color: #155724;
        }
        .stream-status.offline {
            background: #f8d7da;
            color: #721c24;
        }
        .song-info {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
            border-left: 4px solid var(--primary-color);
        }
        .song-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .song-artist {
            font-size: 1rem;
            color: #666;
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
        .chart-container {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .chart-container h3 {
            margin-top: 0;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - Radio Dashboard</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>Radio Streaming Dashboard</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Monitor your radio streams and listeners from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Main Dashboard</a>
            <a href="/admin/streams" class="btn">Manage Streams</a>
        </div>

        <div class="stats-grid">
            <!-- Main Radio Stats -->
            <div class="stat-card">
                <h3>👂 Current Listeners</h3>
                <div class="value"><?php echo $radioStats['current_listeners']; ?></div>
                <div class="label">Listeners tuned in right now</div>
            </div>

            <div class="stat-card">
                <h3>📈 Peak Listeners Today</h3>
                <div class="value><?php echo $radioStats['peak_listeners_today']; ?></div>
                <div class="label">Highest listener count today</div>
            </div>

            <div class="stat-card">
                <h3>📡 Stream Status</h3>
                <div class="value"><span class="stream-status <?php echo $radioStats['stream_status']; ?>"><?php echo ucfirst($radioStats['stream_status']); ?></span></div>
                <div class="label">Current streaming status</div>
            </div>

            <div class="stat-card">
                <h3>🎵 Current Song</h3>
                <div class="value"><?php echo $radioStats['current_song']; ?></div>
                <div class="label">Now playing on stream</div>
            </div>
        </div>

        <div class="stats-grid">
            <!-- System Resource Stats -->
            <div class="stat-card">
                <h3>💻 CPU Usage</h3>
                <div class="value><?php echo $radioStats['cpu_usage']; ?>%</div>
                <div class="label">Radio server CPU utilization</div>
            </div>

            <div class="stat-card">
                <h3>💾 RAM Usage</h3>
                <div class="value><?php echo $radioStats['ram_usage']; ?>%</div>
                <div class="label">Radio server memory utilization</div>
            </div>

            <div class="stat-card">
                <h3>🌐 Bandwidth Usage</h3>
                <div class="value><?php echo $radioStats['bandwidth_usage']; ?> Mbps</div>
                <div class="label">Current network throughput</div>
            </div>

            <div class="stat-card">
                <h3>⏱️ Stream Uptime</h3>
                <div class="value><?php echo $radioStats['stream_uptime']; ?></div>
                <div class="label">How long stream has been running</div>
            </div>
        </div>

        <div class="stats-grid">
            <!-- Stream Count Stats -->
            <div class="stat-card">
                <h3>📊 Total Streams</h3>
                <div class="value><?php echo $radioStats['total_streams']; ?></div>
                <div class="label">Total radio streams configured</div>
            </div>

            <div class="stat-card">
                <h3>🟢 Active Streams</h3>
                <div class="value><?php echo $radioStats['active_streams']; ?></div>
                <div class="label">Currently active streams</div>
            </div>
        </div>

        <div class="song-info">
            <div class="song-title"><?php echo $radioStats['current_song']; ?></div>
            <div class="song-artist">Now playing on your radio stream</div>
        </div>

        <div class="chart-container">
            <h3>📈 Listener Trends (Today)</h3>
            <p>In a real implementation, this would show a chart of listener count over time.</p>
            <p><em>Chart visualization would go here</em></p>
        </div>

        <nav class="nav">
            <h2>Radio Dashboard Functions</h2>
            <ul>
                <li><a href="/admin/streams">Manage Streams</a></li>
                <li><a href="/admin/autodj">Manage AutoDJ</a></li>
                <li><a href="/admin/djs">Manage DJs</a></li>
                <li><a href="/admin/radio/analytics">View Analytics</a></li>
                <li><a href="/admin/radiosettings">Radio Settings</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>