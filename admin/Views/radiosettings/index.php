<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soni Radio - Radio Hosting Panel</title>
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
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider {
            background-color: #2196F3;
        }
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group button {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
        }
        .form-group button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - Soni Radio Settings</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>Soni Radio Settings</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Configure global radio settings from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>📻 Global Radio Status</h3>
                <div class="value"><?php echo $radioStats['global_enabled'] ? 'Enabled' : 'Disabled'; ?></div>
                <div class="label">Master switch for radio streaming</div>
            </div>

            <div class="stat-card">
                <h3>📡 Total Streams</h3>
                <div class="value><?php echo $radioStats['total_streams']; ?></div>
                <div class="label">Total radio streams created</div>
            </div>

            <div class="stat-card">
                <h3>🟢 Active Streams</h3>
                <div class="value><?php echo $radioStats['active_streams']; ?></div>
                <div class="label">Currently active streams</div>
            </div>

            <div class="stat-card">
                <h3>🤖 AutoDJ Status</h3>
                <div class="value><?php echo $radioStats['auto_dj_enabled'] ? 'Enabled' : 'Disabled'; ?></div>
                <div class="label">Automatic DJ functionality</div>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Audio Bitrate</td>
                        <td><?php echo $radioStats['bitrate']; ?></td>
                    </tr>
                    <tr>
                        <td>Audio Format</td>
                        <td><?php echo $radioStats['format']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <form action="/admin/radiosettings/update" method="post">
            <div class="form-group">
                <label for="global_enabled">Enable Radio Streaming</label>
                <div class="toggle-switch">
                    <input type="checkbox" id="global_enabled" name="global_enabled" <?php echo $radioStats['global_enabled'] ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="auto_dj_enabled">Enable AutoDJ</label>
                <div class="toggle-switch">
                    <input type="checkbox" id="auto_dj_enabled" name="auto_dj_enabled" <?php echo $radioStats['auto_dj_enabled'] ? 'checked' : ''; ?>>
                    <span class="slider"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="bitrate">Audio Bitrate</label>
                <select id="bitrate" name="bitrate">
                    <option value="64kbps" <?php echo $radioStats['bitrate'] == '64kbps' ? 'selected' : ''; ?>>64 kbps</option>
                    <option value="96kbps" <?php echo $radioStats['bitrate'] == '96kbps' ? 'selected' : ''; ?>>96 kbps</option>
                    <option value="128kbps" <?php echo $radioStats['bitrate'] == '128kbps' ? 'selected' : ''; ?>>128 kbps</option>
                    <option value="192kbps" <?php echo $radioStats['bitrate'] == '192kbps' ? 'selected' : ''; ?>>192 kbps</option>
                    <option value="256kbps" <?php echo $radioStats['bitrate'] == '256kbps' ? 'selected' : ''; ?>>256 kbps</option>
                    <option value="320kbps" <?php echo $radioStats['bitrate'] == '320kbps' ? 'selected' : ''; ?>>320 kbps</option>
                </select>
            </div>

            <div class="form-group">
                <label for="format">Audio Format</label>
                <select id="format" name="format">
                    <option value="mp3" <?php echo $radioStats['format'] == 'mp3' ? 'selected' : ''; ?>>MP3</option>
                    <option value="aac" <?php echo $radioStats['format'] == 'aac' ? 'selected' : ''; ?>>AAC</option>
                    <option value="ogg" <?php echo $radioStats['format'] == 'ogg' ? 'selected' : ''; ?>>OGG</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Save Settings</button>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>