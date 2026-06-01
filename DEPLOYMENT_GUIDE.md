# Planet Hosts Master Panel - Deployment and Testing Guide

This guide provides step-by-step instructions for deploying and testing the Planet Hosts Master Panel on a RHEL/CentOS/Fedora system.

## 📋 **Prerequisites**

- A fresh installation of RHEL 9, CentOS Stream 9, AlmaLinux 9, Rocky Linux 9, or Fedora 38+
- Root access or sudo privileges
- Minimum 2GB RAM, 20GB disk space
- Static IP address recommended for production
- Internet access for package installation

## 🚀 **Deployment Steps**

### Step 1: Prepare the Environment
```bash
# Update system packages
sudo yum update -y   # For RHEL/CentOS
# or
sudo dnf update -y   # For Fedora

# Ensure you have wget/curl for downloading
sudo yum install -y wget curl
```

### Step 2: Obtain the Panel Code
```bash
# Clone the repository or copy the code to your server
# Option A: Git clone (if you have git)
sudo yum install -y git
git clone https://github.com/j68418730/whm.git
cd whm

# Option B: Copy files manually
# Copy the entire whm directory to your server via SCP, rsync, or other means
```

### Step 3: Run the Installer
```bash
# Make installer executable
chmod +x install.sh

# Run the installer as root
sudo ./install.sh

# The installer will:
# 1. Set up repositories (EPEL, RPM Fusion, CRB for Enterprise Linux)
# 2. Install required packages (httpd, mariadb, php, firewalld, etc.)
# 3. Configure firewall (open ports 80, 443, 8000, 8001, 8080)
# 4. Install Icecast (from repo or compile from source if needed)
# 5. Install supporting packages (liquidsoap, ezstream, ffmpeg, etc.)
# 6. Set up Apache virtual host with DocumentRoot pointing to /var/www/radiohosting/public
# 7. Create database and user
# 8. Copy panel files to /var/www/radiohosting
# 9. Set up cron jobs
# 10. Display installation credentials and next steps
```

### Step 4: Post-Installation Verification
After the installer completes, follow the verification checklist below.

## 🔐 **Initial Login and Configuration**

1. **Access the Panel:**
   - Open a web browser and navigate to: `http://[YOUR_SERVER_IP]/`
   - You should see the login page

2. **Login Credentials:**
   - Username: `radiopanel` (system user)
   - Password: [The password shown at the end of installation]
   - Note: This password is also stored in SHA256 hash in `/etc/radiopanel.passhash`

3. **First Actions After Login:**
   - Change the `radiopanel` system password immediately:
     ```bash
     sudo passwd radiopanel
     ```
   - Then update the hash file:
     ```bash
     sudo /path/to/whm/update_panel_hash.sh
     ```
   - Log out and log back in with the new password

4. **Configure Basic Settings:**
   - Go to Admin Panel → Settings → General
   - Set your panel's hostname, email, timezone, etc.
   - Configure email settings for system notifications
   - Set up branding/logo if desired

## 🧪 **Testing Checklist**

Run through these tests to verify your installation is working correctly:

### Core Functionality Tests
- [ ] Dashboard loads without errors
- [ ] Navigation menu works for all sections
- [ ] User can log in and out successfully
- [ ] Password change works for radiopanel user
- [ ] System shows correct server information

### Radio Streaming Tests
- [ ] Radio Dashboard loads and shows current status
- [ ] Create a test radio stream (Admin → Radio → Streams → Create)
- [ ] Verify stream configuration is saved
- [ ] Test starting/stopping stream (if Icecast is installed)
- [ ] Check AutoDJ functionality if Liquidsoap/ezstream available

### Web Hosting Functionality Tests
- [ ] Create a hosting package (Admin → Packages → Create)
- [ ] Create a reseller (Admin → Resellers → Create) [if testing reseller features]
- [ ] Create a hosting user (Admin → Account Functions → Create Account)
- [ ] Verify user can log in to user panel at `http://[YOUR_SERVER_IP]/user/`
- [ ] Test file manager in user panel
- [ ] Test database management (phpMyAdmin integration)
- [ ] Test email account creation (if mail server configured)
- [ ] Test FTP account creation (if FTP server configured)

### System Functionality Tests
- [ ] Check that cron jobs are running (view logs in `/var/log/radiohosting/`)
- [ ] Test backup functionality (Admin → Backup)
- [ ] Test software installer (Admin → Software)
- [ ] Test API access (Admin → API)
- [ ] Test theme customization (Admin → Branding)
- [ ] Test plugin system (Admin → Plugins) [create a test plugin if desired]

### Security Tests
- [ ] Verify firewall is active: `sudo firewall-cmd --state`
- [ ] Verify open ports: `sudo firewall-cmd --list-all`
- [ ] Confirm Apache is running: `sudo systemctl status httpd`
- [ ] Confirm MariaDB is running: `sudo systemctl status mariadb`
- [ ] Check that sensitive files are not world-readable:
  ```bash
  ls -la /etc/radiopanel.passhash  # Should be 640 root:root
  ls -la /var/www/radiohosting/config/database.php  # Should be 640 apache:apache
  ```

## 🔄 **Updating the Panel**

To check for and apply updates:
```bash
cd /var/www/radiohosting
sudo ./update.sh
```
The update script will:
1. Fetch latest changes from GitHub
2. Apply any code updates
3. Provide post-update instructions (clear cache, restart web server, etc.)

## 🗑️ **Uninstallation**

If you need to completely remove the panel:
```bash
cd /var/www/radiohosting
sudo ./uninstall.sh
```
The uninstall script will:
- Stop and disable services (httpd, mariadb, firewalld)
- Remove Apache virtual host and cron job
- Remove panel directory and logs
- Optionally remove database and user
- Optionally remove firewall rules added by installer

## 📞 **Troubleshooting**

### Common Issues and Solutions

**Problem:** Seeing directory listing instead of panel
- **Solution:** Verify Apache virtual host has `DocumentRoot` set to `/var/www/radiohosting/public` and `DirectoryIndex index.php index.html`

**Problem:** 500 Internal Server Error
- **Solution:** Check Apache error logs:
  ```bash
  sudo tail -20 /var/log/httpd/error_log
  sudo tail -20 /var/log/httpd/radiohosting_error.log
  ```
- Common causes: PHP missing modules, syntax errors in code, permission issues

**Problem:** Cannot connect to database
- **Solution:** Verify MariaDB is running and credentials in `/var/www/radiohosting/config/database.php` are correct
- Test connection manually:
  ```bash
  mysql -u radiouser -p[password] radiohosting
  ```

**Problem:** Radio streams not working
- **Solution:** Verify Icecast is installed and running:
  ```bash
  systemctl status icecast
  ```
- Check if ports 8000/tcp are open in firewall
- Review Icecast error logs: `/var/log/icecast/error.log`

**Problem:** Email not sending
- **Solution:** Verify Postfix is running and configured correctly
- Check mail logs: `/var/log/maillog`

## 📝 **Notes**

- **Icecast Installation:** On some RHEL/CentOS/Fedora versions, Icecast may not be available in default repositories. The installer will attempt to compile it from source if needed. If this fails, you can manually install Icecast later using the provided script: `sudo bash /var/www/radiohosting/scripts/icecast_install_source.sh`

- **PHP Version:** The panel is tested with PHP 7.4+ but should work with PHP 8.0-8.2. If you encounter PHP compatibility issues, you may need to adjust PHP version or install specific extensions.

- **SELinux:** If you encounter permission issues related to SELinux, you may need to set appropriate contexts or run in permissive mode for testing (not recommended for production).

- **Production Hardening:** For production use, consider:
  - Installing an SSL certificate (via Let's Encrypt or commercial)
  - Configuring fail2ban or similar intrusion prevention
  - Setting up regular backups
  - Monitoring system resources
  - Restricting SSH access to specific IPs

## 📞 **Support**

For issues or questions, please refer to the GitHub repository:
https://github.com/j68418730/whm

Include relevant logs and details when reporting issues.

---

**Deployment Complete!** Your Planet Hosts Master Panel is now ready to provide web hosting and radio streaming services.