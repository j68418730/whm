<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Radio Hosting Dashboard</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Radio Hosting Dashboard</h1>
            <nav>
                <a href="/radio/create">Create New Stream</a>
                <a href="/radio">My Streams</a>
            </nav>
        </header>

        <main>
            <?php if (empty($streams)): ?>
                <p>You don't have any radio streams yet. <a href="/radio/create">Create your first stream</a>.</p>
            <?php else: ?>
                <div class="streams">
                    <?php foreach ($streams as $stream): ?>
                        <div class="stream-card">
                            <h3><?php echo htmlspecialchars($stream['server_type']); ?> Stream on Port <?php echo $stream['port']; ?></h3>
                            <p>Status: <span class="status status-<?php echo strtolower($stream['status']); ?>"><?php echo ucfirst($stream['status']); ?></span></p>
                            <p>Listeners: <?php echo $stream['listener_count']; ?></p>
                            <div class="actions">
                                <?php if ($stream['status'] === 'stopped'): ?>
                                    <a href="/radio/start/<?php echo $stream['id']; ?>" class="button">Start Stream</a>
                                <?php else: ?>
                                    <a href="/radio/stop/<?php echo $stream['id']; ?>" class="button button-danger">Stop Stream</a>
                                <?php endif; ?>
                                <a href="/radio/stream/<?php echo $stream['id']; ?>" class="button">Manage</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel</p>
        </footer>
    </div>
</body>
</html>