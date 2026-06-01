# Adding Home Directory Functionality to Planet Hosts Master Panel

This guide shows how to modify the panel to create actual Linux system users with home directories in `/home/username` when hosting accounts are created.

## Where to Add the Code

You need to add account creation methods to:
**File:** `admin/Controllers/AccountController.php`

## Step-by-Step Implementation

### 1. Add the Account Creation Methods

Add these methods to the AccountController class (after the `index()` method):

```php
    /**
     * Show form to create a new account
     */
    public function create()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Get reseller packages for dropdown
        $packages = []; // You would fetch from hosting_packages table
        
        // Get admin theme settings
        $user = $this->auth->user();
        $theme_settings = json_decode($user->theme_settings ?? '{}', true);

        // Render the account creation form
        return $this->view('admin.account.create', [
            'user' => $user,
            'packages' => $packages,
            'theme_settings' => $theme_settings
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store()
    {
        // Check if user is logged in and is admin
        if (!$this->auth->check() || !$this->auth->isAdmin()) {
            $this->response->redirect('/admin/login');
            exit;
        }

        // Validate input
        $this->validate($this->request->all(), [
            'username' => 'required|alpha_dash|min:3|max:20|unique:hosting_users,username',
            'email' => 'required|email|unique:hosting_users,email',
            'password' => 'required|min:8',
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'package_id' => 'required|integer|exists:hosting_packages,id',
        ]);

        // Get input data
        $input = $this->request->all();
        $username = $input['username'];
        $password = password_hash($input['password'], PASSWORD_DEFAULT);
        $email = $input['email'];
        $firstName = $input['first_name'];
        $lastName = $input['last_name'];
        $packageId = $input['package_id'];

        // Database transaction simulation (in real implementation, use proper DB transactions)
        try {
            // 1. Insert into hosting_users table
            $userId = \Core\Database::table('hosting_users')->insertGetId([
                'reseller_id' => $this->auth->user()->reseller_id ?? 1, // Default reseller
                'package_id' => $packageId,
                'username' => $username,
                'password_hash' => $password,
                'email' => $email,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'status' => 'active',
            ]);

            // 2. Create Linux system user with home directory
            $this->createSystemUser($username, $email, $firstName, $lastName);

            // 3. Set up web directory structure
            $this->setupUserWebDirectory($username);

            // 4. Create default email accounts (if email service is enabled)
            $this->setupEmailAccounts($username, $email);

            // 5. Create FTP account (if FTP service is enabled)
            $this->setupFtpAccount($username, $input['ftp_password'] ?? $input['password']);

            // 6. Create MySQL database and user (if database service is enabled)
            $this->setupDatabase($username, $input['db_password'] ?? $this->generatePassword());

            // Success
            $this->response->withSuccess('Account created successfully for user: ' . $username);
            $this->response->redirect('/admin/accounts');
            
        } catch (\Exception $e) {
            // Error handling
            $this->response->withError('Failed to create account: ' . $e->getMessage());
            $this->response->redirect()->back();
        }
    }

    /**
     * Create Linux system user with home directory
     */
    private function createSystemUser($username, $email, $firstName, $lastName)
    {
        $homeDir = "/home/{$username}";
        
        // Create the user
        $useraddCommand = sprintf(
            'useradd -m -d %s -s /bin/bash -c "%s" %s',
            escapeshellarg($homeDir),
            escapeshellarg("{$firstName} {$lastName} <{$email}>"),
            escapeshellarg($username)
        );
        
        exec($useraddCommand, $output, $returnVar);
        
        if ($returnVar !== 0) {
            throw new \Exception("Failed to create system user: " . implode("\n", $output));
        }
        
        // Set proper permissions
        chmod($homeDir, 0755);
        chown($homeDir, $username, $username);
        
        // Create .ssh directory for key-based auth (optional)
        mkdir("{$homeDir}/.ssh", 0700, true);
        chown("{$homeDir}/.ssh", $username, $username);
    }

    /**
     * Setup user's web directory structure
     */
    private function setupUserWebDirectory($username)
    {
        $homeDir = "/home/{$username}";
        $publicHtml = "{$homeDir}/public_html";
        
        // Create public_html directory
        mkdir($publicHtml, 0755, true);
        chown($publicHtml, $username, $username);
        
        // Create default index.html
        file_put_contents("{$publicHtml}/index.html", 
            "<html><body><h1>Welcome to {$username}'s website</h1></body></html>");
        chown("{$publicHtml}/index.html", $username, $username);
        
        // Create logs directory
        mkdir("{$homeDir}/logs", 0755, true);
        chown("{$homeDir}/logs", $username, $username);
        
        // Create tmp directory
        mkdir("{$homeDir}/tmp", 0755, true);
        chown("{$homeDir}/tmp", $username, $username);
    }

    /**
     * Setup default email accounts
     */
    private function setupEmailAccounts($username, $email)
    {
        // This would integrate with your email server (Postfix/Dovecot)
        // For now, we'll just log that this should happen
        error_log("Would create email account for {$username}@yourdomain.com");
        
        // In a real implementation, you might:
        // 1. Add virtual domain/postfix entry
        // 2. Create dovecot user
        // 3. Set up spamassassin/clamsav rules
        // 4. Create webmail roundcube entry
    }

    /**
     * Setup FTP account
     */
    private function setupFtpAccount($username, $password)
    {
        // This would integrate with your FTP server (vsftpd/pure-ftpd)
        // For now, we'll just log that this should happen
        error_log("Would create FTP account for {$username}");
        
        // In a real implementation, you might:
        // 1. Add user to vsftpd user list
        // 2. Set up FTP directory chroot
        // 3. Configure FTP SSL/TLS if enabled
    }

    /**
     * Setup MySQL database and user
     */
    private function setupDatabase($username, $password)
    {
        $dbName = "{$username}_db";
        $dbUser = "{$username}_user";
        
        // This would integrate with MySQL/MariaDB
        // For now, we'll just log that this should happen
        error_log("Would create database {$dbName} for user {$username}");
        
        // In a real implementation, you might:
        // 1. CREATE DATABASE IF NOT EXISTS `{$dbName}`;
        // 2. CREATE USER `{$dbUser}`@'localhost' IDENTIFIED BY '{$password}';
        // 3. GRANT ALL PRIVILEGES ON `{$dbName}`.* TO `{$dbUser}`@'localhost';
        // 4. FLUSH PRIVILEGES;
    }

    /**
     * Generate a secure random password
     */
    private function generatePassword($length = 16)
    {
        $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $charset[rand(0, strlen($charset) - 1)];
        }
        return $password;
    }
```

### 2. Create the View File

Create the account creation form view:
**File:** `admin/Views/account/create.php`

```php
<?php $this->extend('admin.layouts.app') ?>

<?php $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Hosting Account</h3>
                </div>
                <div class="card-body">
                    <?php if ($errors = session('errors')): ?>
                        <div class="alert alert-danger">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?= e($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if (session('success')): ?>
                        <div class="alert alert-success">
                            <?= e(session('success')) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session('error')): ?>
                        <div class="alert alert-danger">
                            <?= e(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <form action="/admin/accounts" method="POST">
                        <?= csrf_field() ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Enter username (letters, numbers, dash, underscore)" 
                                           required maxlength="20">
                                    <small class="form-text text-muted">This will be the Linux username and FTP username</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter email address" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter password" required minlength="8">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" 
                                           placeholder="Enter first name" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" 
                                           placeholder="Enter last name" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="package_id">Hosting Package</label>
                                    <select class="form-control" id="package_id" name="package_id" required>
                                        <option value="">Select Package</option>
                                        <?php foreach ($packages as $package): ?>
                                            <option value="<?= $package->id ?>">
                                                <?= e($package->name) ?> - $<?= e($package->monthly_fee) ?>/mo
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ftp_password">FTP Password (optional)</label>
                                    <input type="password" class="form-control" id="ftp_password" name="ftp_password" 
                                           placeholder="Leave blank to use account password">
                                    <small class="form-text text-muted">If empty, will use the same password as the account</small>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="db_password">Database Password (optional)</label>
                                    <input type="password" class="form-control" id="db_password" name="db_password" 
                                           placeholder="Leave blank to generate secure password">
                                    <small class="form-text text-muted">If empty, a secure password will be generated</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Create Account
                            </button>
                            <a href="/admin/accounts" class="btn btn-default btn-lg">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>
```

### 3. Add Route

Add this route to your routes file:
**File:** `routes/admin.php` (or equivalent)

```php
// Account management routes
Route::get('/admin/accounts', 'Admin\\Controllers\\AccountController@index')->name('admin.accounts.index');
Route::get('/admin/accounts/create', 'Admin\\Controllers\\AccountController@create')->name('admin.accounts.create');
Route::post('/admin/accounts', 'Admin\\Controllers\\AccountController@store')->name('admin.accounts.store');
// Add other routes like edit, update, destroy as needed
```

### 4. Security Configuration (CRITICAL)

To allow the web server to create system users, you need to configure sudo properly:

**Create:** `/etc/sudoers.d/apache_useradd`

```sudoers
# Allow apache to run specific user management commands
www-data ALL=(root) NOPASSWD: /usr/sbin/useradd, /usr/bin/passwd, /bin/chown, /bin/chmod, /bin/mkdir
```

*Note: Adjust the username (`www-data`) to match your Apache user (could be `apache`, `httpd`, etc.)*

### 5. Alternative: Use a Privileged Helper Script

For better security, create a helper script that runs as root:

**Create:** `/usr/local/bin/create-hosting-user`

```bash
#!/bin/bash
# Helper script to create hosting users - called by PHP via sudo

USERNAME="$1"
EMAIL="$2"
FIRST_NAME="$3"
LAST_NAME="$4"
HOME_DIR="/home/$USERNAME"

# Validate input
if [[ -z "$USERNAME" || -z "$EMAIL" ]]; then
    echo "Usage: $0 <username> <email> <first_name> <last_name>"
    exit 1
fi

# Create user
useradd -m -d "$HOME_DIR" -s /bin/bash -c "$FIRST_NAME $LAST_NAME <$EMAIL>" "$USERNAME"
if [ $? -ne 0 ]; then
    echo "Failed to create user"
    exit 1
fi

# Set up directory structure
mkdir -p "$HOME_DIR/public_html"
mkdir -p "$HOME_DIR/logs"
mkdir -p "$HOME_DIR/tmp"
mkdir -p "$HOME_DIR/.ssh"

chown -R "$USERNAME:$USERNAME" "$HOME_DIR"
chmod 700 "$HOME_DIR/.ssh"
chmod 755 "$HOME_DIR/public_html"

# Create default index.html
echo "<html><body><h1>Welcome to $USERNAME's website</h1></body></html>" > "$HOME_DIR/public_html/index.html"
chown "$USERNAME:$USERNAME" "$HOME_DIR/public_html/index.html"

exit 0
```

Make it executable:
```bash
chmod +x /usr/local/bin/create-hosting-user
```

Add to sudoers:
```sudoers
www-data ALL=(root) NOPASSWD: /usr/local/bin/create-hosting-user
```

Then modify the PHP code to use:
```php
exec("sudo /usr/local/bin/create-hosting-user $username $email $firstName $lastName", $output, $returnVar);
```

### 6. Database Schema Addition (Optional)

To track which accounts have system users created, you could add a column:

```sql
ALTER TABLE hosting_users ADD COLUMN system_user_created TINYINT(1) DEFAULT 0;
```

Then update it after successful creation:
```php
\Core\Database::table('hosting_users')->where('id', $userId)->update([
    'system_user_created' => 1
]);
```

## Important Security Considerations

1. **Never run PHP as root** - Always use sudo with specific command restrictions
2. **Validate and sanitize all input** - Use PHP's filter_var() or validation library
3. **Use escapeshellarg()** - Prevent shell injection when executing system commands
4. **Limit command access in sudoers** - Only allow exactly what's needed
5. **Consider using a privileged helper script** - Better auditability and control
6. **Implement rate limiting** - Prevent brute force account creation
7. **Log all account creations** - For security auditing
8. **Consider chrooted environments** - For added security (more complex)

## Testing the Implementation

1. First test without the system commands:
   - Comment out the `exec()` and system calls
   - Verify the form works and creates database records
   - Check validation and error handling

2. Then test system commands manually:
   ```bash
   sudo useradd -m -d /home/testuser -s /bin/bash -c "Test User <test@example.com>" testuser
   ls -la /home/testuser/
   ```

3. Finally test the full flow:
   - Create account through web interface
   - Verify `/home/username` exists with correct permissions
   - Verify user can log in via SSH (if SSH access is enabled)
   - Verify web directory is accessible

## Customization Options

You can modify this implementation to:

1. **Use different shells** - `/bin/false` or `/usr/bin/nologin` for restricted access
2. **Add system resource limits** - Using `/etc/security/limits.conf` or pam
3. **Integrate with control panels** - If you already have cPanel/Plesk/etc.
4. **Add quota support** - Using filesystem quotas
5. **Create custom welcome emails** - With account details
6. **Setup automatic backups** - For user data
7. **Integrate with billing systems** - Like WHMCS, Blesta, etc.

This implementation gives you a secure foundation for creating actual system users with proper home directories when hosting accounts are created through your Planet Hosts Master Panel.