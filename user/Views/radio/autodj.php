<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage AutoDJ - User Panel</title>
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
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #007bff;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        .card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .card h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-right: 10px;
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
        .song-list {
            margin-top: 20px;
        }
        .song-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .song-list th,
        .song-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .song-list th {
            background-color: #f2f2f2;
        }
        .song-list tr:hover {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/radio/stream/<?php echo $streamId; ?>" class="back-link">&larr; Back to Stream</a>
        <h1>Manage AutoDJ</h1>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>AutoDJ Status</h2>
            <p><strong>Status:</strong> <?php echo ucfirst($autodj->status); ?></p>
            <p><strong>Song Count:</strong> <?php echo $autodj->song_count; ?></p>
            <?php if ($autodj->last_song): ?>
                <p><strong>Last Song:</strong> <?php echo htmlspecialchars($autodj->last_song); ?></p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Add Songs</h2>
            <form method="POST" action="/radio/autodj/add-songs">
                <div class="form-group">
                    <label for="song_files">Upload MP3 Files</label>
                    <input type="file" id="song_files" name="song_files[]" multiple accept=".mp3">
                </div>
                <button type="submit" class="btn">Upload Songs</button>
            </form>
        </div>

        <div class="card">
            <h2>Current Playlist</h2>
            <div class="song-list">
                <?php if (empty($songs)): ?>
                    <p>No songs in the playlist.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Title</th>
                                <th>Artist</th>
                                <th>Album</th>
                                <th>Duration</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($songs as $index => $song): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($song['title'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($song['artist'] ?? 'Unknown'); ?></td>
                                    <td><?php echo htmlspecialchars($song['album'] ?? 'Unknown'); ?></td>
                                    <td><?php echo gmdate("i:s", $song['duration'] ?? 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <h2>Controls</h2>
            <?php if ($autodj->status === 'stopped'): ?>
                <a href="/radio/autodj/start/<?php echo $autodj->id; ?>" class="btn">Start AutoDJ</a>
            <?php else: ?>
                <a href="/radio/autodj/stop/<?php echo $autodj->id; ?>" class="btn btn-danger">Stop AutoDJ</a>
            <?php endif; ?>
            <a href="/radio/autodj/reset/<?php echo $autodj->id; ?>" class="btn">Reset Playlist</a>
        </div>
    </div>
</body>
</html>