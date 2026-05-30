<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Settings - Radio Hosting Panel Admin</title>
    <link rel="stylesheet" href="/css/admin.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        header {
            background: #000;
            color: white;
            padding: 1rem;
            text-align: center;
        }
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        h2 {
            margin-top: 0;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        .form-group input[type="text"],
        .form-group input[type="url"],
        .form-group input[type="color"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group input[type="number"] {
            width: 100px;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
            border: none;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        .preview {
            margin-top: 2rem;
            padding: 1rem;
            background: #eee;
            border-radius: 4px;
        }
        .preview-header {
            height: <?php echo $theme_settings['header']['height'] ?? '60px'; ?>;
            background-color: <?php echo $theme_settings['header']['color'] ?? '#000000'; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        .preview-footer {
            height: <?php echo $theme_settings['footer']['height'] ?? '40px'; ?>;
            background-color: <?php echo $theme_settings['footer']['color'] ?? '#000000'; ?>;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-top: 1rem;
        }
        .preview-body {
            min-height: 200px;
            background-color: <?php echo $theme_settings['background']['color'] ?? '#ffffff'; ?>;
            <?php if (!empty($theme_settings['background']['image'])): ?>
            background-image: url('<?php echo htmlspecialchars($theme_settings['background']['image']); ?>');
            background-size: cover;
            background-position: center;
            <?php endif; ?>
            padding: 1rem;
        }
        .color-box {
            display: inline-block;
            width: 20px;
            height: 20px;
            margin-right: 0.5rem;
            border: 1px solid #ccc;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <header>
        <h1>Theme Settings</h1>
        <p>Customize the appearance of your admin panel</p>
    </header>

    <div class="container">
        <?php if(isset($_SESSION['theme_success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['theme_success']); unset($_SESSION['theme_success']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Customize Your Theme</h2>
            <form method="POST" action="/admin/theme/update">
                <div class="form-group">
                    <label for="background_color">Background Color</label>
                    <input type="color" id="background_color" name="background_color" value="<?php echo htmlspecialchars($theme_settings['background']['color'] ?? '#ffffff'); ?>">
                </div>
                <div class="form-group">
                    <label for="background_image">Background Image URL (optional)</label>
                    <input type="url" id="background_image" name="background_image" value="<?php echo htmlspecialchars($theme_settings['background']['image'] ?? ''); ?>" placeholder="https://example.com/image.jpg">
                </div>
                <div class="form-group">
                    <label for="header_color">Header Color</label>
                    <input type="color" id="header_color" name="header_color" value="<?php echo htmlspecialchars($theme_settings['header']['color'] ?? '#000000'); ?>">
                </div>
                <div class="form-group">
                    <label for="header_height">Header Height (px)</label>
                    <input type="number" id="header_height" name="header_height" value="<?php echo htmlspecialchars($theme_settings['header']['height'] ?? '60'); ?>" min="1">
                </div>
                <div class="form-group">
                    <label for="footer_color">Footer Color</label>
                    <input type="color" id="footer_color" name="footer_color" value="<?php echo htmlspecialchars($theme_settings['footer']['color'] ?? '#000000'); ?>">
                </div>
                <div class="form-group">
                    <label for="footer_height">Footer Height (px)</label>
                    <input type="number" id="footer_height" name="footer_height" value="<?php echo htmlspecialchars($theme_settings['footer']['height'] ?? '40'); ?>" min="1">
                </div>
                <div class="form-group">
                    <label for="logo_url">Logo URL (optional)</label>
                    <input type="url" id="logo_url" name="logo_url" value="<?php echo htmlspecialchars($theme_settings['logo']['url'] ?? ''); ?>" placeholder="https://example.com/logo.png">
                </div>
                <div class="form-group">
                    <label for="primary_color">Primary Color</label>
                    <input type="color" id="primary_color" name="primary_color" value="<?php echo htmlspecialchars($theme_settings['colors']['primary'] ?? '#007bff'); ?>">
                </div>
                <div class="form-group">
                    <label for="secondary_color">Secondary Color</label>
                    <input type="color" id="secondary_color" name="secondary_color" value="<?php echo htmlspecialchars($theme_settings['colors']['secondary'] ?? '#6c757d'); ?>">
                </div>
                <button type="submit" class="btn">Save Theme Settings</button>
                <a href="/admin/dashboard" class="btn btn-secondary">Cancel</a>
            </form>
        </div>

        <div class="card">
            <h2>Preview</h2>
            <div class="preview">
                <div class="preview-header">
                    Header
                </div>
                <div class="preview-body">
                    <p>This is a preview of your theme settings.</p>
                    <p>Logo would appear here: <?php echo !empty($theme_settings['logo']['url']) ? '<img src="' . htmlspecialchars($theme_settings['logo']['url']) . '" alt="Logo" style="max-height: 40px;">' : 'Not set'; ?></p>
                </div>
                <div class="preview-footer">
                    Footer
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Radio Hosting Panel. All rights reserved.</p>
    </footer>
</body>
</html>