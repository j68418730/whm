<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Stream - Radio Hosting Panel</title>
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
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--primary-color);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 1rem;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
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
        .required::after {
            content: " *";
            color: #dc3545;
        }
    </style>
</head>
<body>
    <header>
        <h1>Radio Hosting Panel - Create Stream</h1>
    </header>

    <div class="container">
        <div class="welcome-message">
            <h2>Create New Radio Stream</h2>
            <p>Welcome, <?php echo htmlspecialchars($user->name); ?>! Create a new radio stream from here.</p>
            <a href="/admin/streams" class="btn btn-secondary">Back to Streams Management</a>
        </div>

        <form action="/admin/streams/create" method="post">
            <div class="form-group">
                <label for="name" class="required">Stream Name</label>
                <input type="text" id="name" name="name" placeholder="Enter stream name (e.g., Main Radio)" required>
            </div>

            <div class="form-group">
                <label for="mount_point" class="required">Mount Point</label>
                <input type="text" id="mount_point" name="mount_point" placeholder="Enter mount point (e.g., /live)" required>
                <small class="form-text text-muted">Must start with a forward slash (/) and contain only letters, numbers, hyphens, and underscores</small>
            </div>

            <div class="form-group">
                <label for="bitrate" class="required">Audio Bitrate</label>
                <select id="bitrate" name="bitrate" required>
                    <option value="">Select bitrate</option>
                    <option value="64kbps">64 kbps</option>
                    <option value="96kbps">96 kbps</option>
                    <option value="128kbps">128 kbps</option>
                    <option value="192kbps">192 kbps</option>
                    <option value="256kbps">256 kbps</option>
                    <option value="320kbps">320 kbps</option>
                </select>
            </div>

            <div class="form-group">
                <label for="format" class="required">Audio Format</label>
                <select id="format" name="format" required>
                    <option value="">Select format</option>
                    <option value="mp3">MP3</option>
                    <option value="aac">AAC</option>
                    <option value="ogg">OGG</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" placeholder="Enter stream description (optional)"></textarea>
            </div>

            <div class="form-group">
                <button type="submit" class="btn">Create Stream</button>
                <a href="/admin/streams" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>