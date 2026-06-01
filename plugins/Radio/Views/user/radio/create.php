<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Radio Stream - User Panel</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        <?php 
        // We'll try to get the user's theme settings if available, but for user panel we might not have theme settings yet.
        // For now, we'll use a default style. In a full implementation, the user panel would also have theme settings.
        ?>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
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
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #0056b3;
        }
        .alert {
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        <h1>Create Radio Stream</h1>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="/radio/store">
            <div class="form-group">
                <label for="server_type">Server Type</label>
                <select id="server_type" name="server_type" required>
                    <option value="icecast">Icecast</option>
                    <option value="shoutcast">Shoutcast</option>
                </select>
            </div>
            <div class="form-group">
                <label for="port">Port (optional)</label>
                <input type="number" id="port" name="port" min="1" max="65535" placeholder="Leave blank for default">
            </div>
            <div class="form-group">
                <label for="password">Password (optional)</label>
                <input type="password" id="password" name="password" placeholder="Leave blank to generate a secure password">
                <small>If left blank, a secure password will be generated for you.</small>
            </div>
            <button type="submit" class="btn">Create Stream</button>
        </form>
    </div>
</body>
</html>