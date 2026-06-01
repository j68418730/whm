<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Radio Stream - User Panel</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .stream-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .stream-info p {
            margin: 5px 0;
        }
        .stream-info .label {
            font-weight: bold;
            display: inline-block;
            width: 150px;
        }
        .actions {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            margin-right: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #bd2130;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
        .section {
            margin-bottom: 30px;
        }
        .section h2 {
            border-bottom: 2px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .autodj-status {
            padding: 10px;
            background: #e9ecef;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .transcoding-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .transcoding-options button {
            padding: 8px 12px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .transcoding-options button:hover {
            background: #5a6268;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/radio" class="back-link">&larr; Back to Dashboard</a>
        <h1>Manage Radio Stream</h1>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <div class="stream-info">
            <p><span class="label">Stream Type:</span> <?php echo htmlspecialchars($stream['server_type']); ?></p>
            <p><span class="label">Port:</span> <?php echo $stream['port']; ?></p>
            <p><span class="label">Status:</span> 
                <span class="status status-<?php echo strtolower($stream['status']); ?>">
                    <?php echo ucfirst($stream['status']); ?>
                </span>
            </p>
            <p><span class="label">Current Listeners:</span> <?php echo $stream['listener_count']; ?></p>
            <p><span class="label">Mount Point:</span> <?php echo htmlspecialchars($stream['mount_point']); ?></p>
            <p><span class="label">Stream URL:</span> 
                <a href="http://localhost:<?php echo $stream['port']; ?><?php echo $stream['mount_point']; ?>" target="_blank">
                    http://localhost:<?php echo $stream['port']; ?><?php echo $stream['mount_point']; ?>
                </a>
            </p>
        </div>

        <div class="actions">
            <?php if ($stream['status'] === 'stopped'): ?>
                <a href="/radio/start/<?php echo $stream['id']; ?>" class="btn">Start Stream</a>
            <?php else: ?>
                <a href="/radio/stop/<?php echo $stream['id']; ?>" class="btn btn-danger">Stop Stream</a>
            <?php endif; ?>
            <?php if ($autodj): ?>
                <?php if ($autodj->status === 'stopped'): ?>
                    <a href="/radio/autodj/start/<?php echo $autodj->id; ?>" class="btn btn-success">Start AutoDJ</a>
                <?php else: ?>
                    <a href="/radio/autodj/stop/<?php echo $autodj->id; ?>" class="btn btn-danger">Stop AutoDJ</a>
                <?php endif; ?>
                <a href="/radio/autodj/disable/<?php echo $stream['id']; ?>" class="btn">Disable AutoDJ</a>
            <?php else: ?>
                <a href="/radio/autodj/enable/<?php echo $stream['id']; ?>" class="btn btn-success">Enable AutoDJ</a>
            <?php endif; ?>
            <a href="/radio/stream/<?php echo $stream['id']; ?>/manage-djs" class="btn">Manage DJs</a>
            <a href="/radio/stream/<?php echo $stream['id']; ?>/manage-playlists" class="btn">Manage Playlists</a>
        </div>

        <div class="section">
            <h2>AutoDJ Status</h2>
            <?php if ($autodj): ?>
                <div class="autodj-status">
                    <p><strong>Status:</strong> <?php echo ucfirst($autodj->status); ?></p>
                    <p><strong>Song Count:</strong> <?php echo $autodj->song_count; ?></p>
                    <?php if ($autodj->last_song): ?>
                        <p><strong>Last Song:</strong> <?php echo htmlspecialchars($autodj->last_song); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>AutoDJ is not enabled for this stream.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Transcoding Options</h2>
            <p>Transcode the stream to different formats and bitrates:</p>
            <div class="transcoding-options">
                <?php foreach ($transcodingOptions as $option): ?>
                    <button onclick="alert('Transcoding feature would be implemented here. Format: <?php echo $option['format']; ?>, Bitrate: <?php echo $option['bitrate']; ?>kbps')">
                        <?php echo strtoupper($option['format']); ?> <?php echo $option['bitrate']; ?>kbps
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>