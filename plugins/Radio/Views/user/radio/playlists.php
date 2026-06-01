<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Playlists - User Panel</title>
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
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
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
        .playlist-list {
            margin-top: 20px;
        }
        .playlist-list table {
            width: 100%;
            border-collapse: collapse;
        }
        .playlist-list th,
        .playlist-list td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .playlist-list th {
            background-color: #f2f2f2;
        }
        .playlist-list tr:hover {
            background-color: #f5f5f5;
        }
        .actions-cell {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="/radio/stream/<?php echo $streamId; ?>" class="back-link">&larr; Back to Stream</a>
        <h1>Manage Playlists</h1>

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
            <h2>Create New Playlist</h2>
            <form method="POST" action="/radio/playlist/create">
                <div class="form-group">
                    <label for="name">Playlist Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description (optional)</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="is_default">Set as Default Playlist</label>
                    <input type="checkbox" id="is_default" name="is_default" value="1">
                </div>
                <button type="submit" class="btn">Create Playlist</button>
            </form>
        </div>

        <div class="card">
            <h2>Existing Playlists</h2>
            <?php if (empty($playlists)): ?>
                <p>No playlists found for this stream.</p>
            <?php else: ?>
                <div class="playlist-list">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Default</th>
                                <th class="actions-cell">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($playlists as $index => $playlist): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($playlist['name']); ?></td>
                                    <td><?php echo htmlspecialchars($playlist['description'] ?? ''); ?></td>
                                    <td><?php echo $playlist['is_default'] ? 'Yes' : 'No'; ?></td>
                                    <td class="actions-cell">
                                        <a href="/radio/playlist/<?php echo $playlist['id']; ?>/edit" class="btn btn-sm">Edit</a>
                                        <a href="/radio/playlist/<?php echo $playlist['id']; ?>/delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this playlist?');">Delete</a>
                                        <a href="/radio/playlist/<?php echo $playlist['id']; ?>/songs" class="btn btn-sm">Manage Songs</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>