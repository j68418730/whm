<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"content="width=device-width, initial-scale=1.0">
    <title>AutoDJ Management - Radio Hosting Panel</title>
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
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
        .upload-area {
            border: 2px dashed var(--primary-color);
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            margin: 1rem 0;
            background-color: #f8f9fa;
        }
        .upload-area:hover {
            background-color: #e9ecef;
        }
        .upload-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - AutoDJ Management</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>AutoDJ Management</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Manage your AutoDJ music library and playlists from here.</p>
            <a href="/admin/dashboard" class="btn btn-secondary">Back to Dashboard</a>
            <a href="/admin/autodj/upload" class="btn">Upload Music</a>
            <a href="/admin/autodj/playlists" class="btn btn-secondary">Manage Playlists</a>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>🎵 Total Tracks</h3>
                <div class="value"><?php echo $autodjStats['total_tracks']; ?></div>
                <div class="label">Music tracks in library</div>
            </div>

            <div class="stat-card">
                <h3>📋 Total Playlists</h3>
                <div class="value"><?php echo $autodjStats['total_playlists']; ?></div>
                <div class="label">Playlists created</div>
            </div>

            <div class="stat-card">
                <h3>⏰ Scheduled Playlists</h3>
                <div class="value"><?php echo $autodjStats['scheduled_playlists']; ?></div>
                <div class="label">Playlists with active schedules</div>
            </div>

            <div class="stat-card">
                <h3>💾 Storage Used</h3>
                <div class="value"><?php echo $autodjStats['storage_used']; ?> GB</div>
                <div class="label">Disk space used by music library</div>
            </div>
        </div>

        <div class="upload-area">
            <div class="upload-icon">📁</div>
            <h3>Upload Music to AutoDJ Library</h3>
            <p>Drag & drop files here, or click to select files</p>
            <p class="text-muted">Supported formats: MP3, AAC, OGG, WAV, FLAC</p>
            <a href="/admin/autodj/upload" class="btn btn-primary">Select Files</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Stream</th>
                        <th>Status</th>
                        <th>Songs</th>
                        <th>Last Song</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $stations = $stations ?? []; if (empty($stations)): ?>
                    <tr><td colspan="5" style="text-align:center;padding:2rem;color:#999;">No streams found.</td></tr>
                    <?php else: ?>
                    <?php foreach ($stations as $s): ?>
                    <tr>
                        <td><strong><?=htmlspecialchars($s->name)?></strong></td>
                        <td><span class="status-badge <?=$s->autodj_running?'status-active':'status-inactive'?>"><?=$s->autodj_running?'Running':'Stopped'?></span></td>
                        <td><?=(int)$s->track_count?></td>
                        <td><?=htmlspecialchars($s->current_song ?? 'N/A')?></td>
                        <td>
                            <a href="/user/radio/autodj/start/<?=10000+(int)$s->id?>" class="btn btn-sm" <?php if($s->autodj_running):?>style="opacity:.5;pointer-events:none"<?php endif;?>>Start</a>
                            <a href="/user/radio/autodj/stop/<?=10000+(int)$s->id?>" class="btn btn-sm btn-secondary" <?php if(!$s->autodj_running):?>style="opacity:.5;pointer-events:none"<?php endif;?>>Stop</a>
                            <a href="/admin/streams/edit/<?=$s->id?>" class="btn btn-sm btn-secondary">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav class="nav">
            <h2>AutoDJ Management Functions</h2>
            <ul>
                <li><a href="/admin/autodj/upload">Upload Music</a></li>
                <li><a href="/admin/autodj/library">View Music Library</a></li>
                <li><a href="/admin/autodj/playlists">Manage Playlists</a></li>
                <li><a href="/admin/autodj/schedules">Manage Schedules</a></li>
                <li><a href="/admin/autodj/rules">Rotation Rules</a></li>
                <li><a href="/admin/autodj/metadata">Metadata Management</a></li>
            </ul>
        </nav>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>