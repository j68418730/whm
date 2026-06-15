-- Radio Hosting Panel Database Schema
-- This schema defines the tables needed for radio hosting functionality
-- Integrated directly into the core panel database

-- Admins Table (for admin panel login with crypto password)
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) DEFAULT '',
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    theme_settings JSON NULL, -- Stores theme customization (background, header, footer, logo, colors, etc.)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Streams Table
CREATE TABLE IF NOT EXISTS radio_streams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    server_type ENUM('icecast') NOT NULL DEFAULT 'icecast',
    port INT NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashed password
    config_path VARCHAR(255) NOT NULL,
    status ENUM('stopped', 'starting', 'running', 'error') NOT NULL DEFAULT 'stopped',
    listener_count INT DEFAULT 0,
    bandwidth_used BIGINT DEFAULT 0, -- in bytes
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio AutoDJ Table
CREATE TABLE IF NOT EXISTS radio_autodj (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    config_path VARCHAR(255) NOT NULL,
    autodj_password VARCHAR(255) NOT NULL,
    status ENUM('stopped', 'running') NOT NULL DEFAULT 'stopped',
    song_count INT DEFAULT 0,
    last_song VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio DJ Accounts Table
CREATE TABLE IF NOT EXISTS radio_djs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL, -- hashed
    name VARCHAR(100),
    email VARCHAR(100),
    max_connections INT DEFAULT 1,
    current_connections INT DEFAULT 0,
    status ENUM('active', 'inactive', 'banned') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stream_username (stream_id, username),
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Playlists Table
CREATE TABLE IF NOT EXISTS radio_playlists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Playlist Items Table (songs in playlists)
CREATE TABLE IF NOT EXISTS radio_playlist_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    playlist_id INT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    title VARCHAR(255),
    artist VARCHAR(100),
    album VARCHAR(100),
    duration INT, -- in seconds
    bitrate INT, -- in kbps
    file_size BIGINT, -- in bytes
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (playlist_id) REFERENCES radio_playlists(id) ON DELETE CASCADE,
    INDEX idx_playlist_id (playlist_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Listener Analytics Table (hourly aggregates)
CREATE TABLE IF NOT EXISTS radio_listener_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    date DATE NOT NULL,
    hour INT NOT NULL, -- 0-23
    peak_listeners INT DEFAULT 0,
    average_listeners DECIMAL(5,2) DEFAULT 0.00,
    total_minutes_listened INT DEFAULT 0, -- sum of listener minutes
    bandwidth_used BIGINT DEFAULT 0, -- in bytes
    unique_listeners INT DEFAULT 0, -- approximate unique listeners
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_stream_date_hour (stream_id, date, hour),
    INDEX idx_stream_id (stream_id),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Transcoding Jobs Table
CREATE TABLE IF NOT EXISTS radio_transcoding_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    stream_id INT NOT NULL,
    source_file VARCHAR(255) NOT NULL,
    target_file VARCHAR(255) NOT NULL,
    target_format VARCHAR(10) NOT NULL,
    target_bitrate INT NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    progress INT DEFAULT 0, -- percentage
    error_message TEXT,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (stream_id) REFERENCES radio_streams(id) ON DELETE CASCADE,
    INDEX idx_stream_id (stream_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Radio Settings Table (for per-account/reseller settings)
CREATE TABLE IF NOT EXISTS radio_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- NULL for global settings, otherwise user ID
    reseller_id INT NULL, -- NULL for non-reseller settings
    global_enabled BOOLEAN DEFAULT FALSE,
    enabled BOOLEAN DEFAULT FALSE,
    server_type ENUM('icecast') DEFAULT 'icecast',
    listener_limit INT DEFAULT 100,
    bandwidth_limit BIGINT DEFAULT 1073741824000, -- 1TB in bytes
    storage_limit BIGINT DEFAULT 10737418240, -- 10GB in bytes
    dj_accounts_limit INT DEFAULT 5,
    playlists_limit INT DEFAULT 10,
    autodj_enabled BOOLEAN DEFAULT TRUE,
    transcoding_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_user_reseller (user_id, reseller_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default global settings (radio disabled by default for safety)
INSERT INTO radio_settings (user_id, reseller_id, global_enabled, enabled) 
VALUES (NULL, NULL, FALSE, FALSE)
ON DUPLICATE KEY UPDATE global_enabled=VALUES(global_enabled), enabled=VALUES(enabled);

-- Insert a default admin user (email: admin@example.com, password: admin) - hashed
-- In production, you should change this password immediately after first login.
INSERT INTO admins (name, email, password_hash, theme_settings) 
VALUES ('Administrator', 'admin@example.com', '$2y$10$F46vzX9GSw/7b6Ul65Jj1uYBBy/QYmCuxC6NdwOKiwXf6W0DxX4gy', '{}')
ON DUPLICATE KEY UPDATE email=VALUES(email);

-- Resellers Table
CREATE TABLE IF NOT EXISTS resellers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    contact_name VARCHAR(100),
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    website VARCHAR(100),
    theme_settings JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Hosting Packages Table (for reseller to assign to users)
CREATE TABLE IF NOT EXISTS hosting_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NULL,
    name VARCHAR(100) NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'web_hosting',
    php_version VARCHAR(10) DEFAULT '',
    description TEXT,
    monthly_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    disk_space BIGINT NOT NULL DEFAULT 0,
    bandwidth BIGINT NOT NULL DEFAULT 0,
    email_accounts INT NOT NULL DEFAULT 0,
    ftp_accounts INT NOT NULL DEFAULT 0,
    `databases` INT NOT NULL DEFAULT 0,
    subdomains INT NOT NULL DEFAULT 0,
    parked_domains INT NOT NULL DEFAULT 0,
    addon_domains INT NOT NULL DEFAULT 0,
    listener_limit INT DEFAULT 0,
    bitrate INT DEFAULT 0,
    storage_limit INT DEFAULT 0,
    dj_accounts INT DEFAULT 0,
    monthly_fee DECIMAL(10,2) DEFAULT 0.00,
    setup_fee DECIMAL(10,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Hosting Users Table (cPanel users)
CREATE TABLE IF NOT EXISTS hosting_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reseller_id INT NOT NULL,
    package_id INT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    domain VARCHAR(255) DEFAULT '',
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    php_version VARCHAR(10) DEFAULT '',
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    phone VARCHAR(20),
    status ENUM('active', 'suspended', 'terminated') DEFAULT 'active',
    theme_settings JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES hosting_packages(id) ON DELETE SET NULL,
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_package_id (package_id),
    INDEX idx_status (status),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- User Radio Settings (per hosting user radio settings)
CREATE TABLE IF NOT EXISTS user_radio_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    reseller_id INT NULL,
    global_enabled BOOLEAN DEFAULT FALSE,
    enabled BOOLEAN DEFAULT FALSE,
    server_type ENUM('icecast') DEFAULT 'icecast',
    listener_limit INT DEFAULT 100,
    bandwidth_limit BIGINT DEFAULT 1073741824000,
    storage_limit BIGINT DEFAULT 10737418240,
    dj_accounts_limit INT DEFAULT 5,
    playlists_limit INT DEFAULT 10,
    autodj_enabled BOOLEAN DEFAULT TRUE,
    transcoding_enabled BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id) ON DELETE SET NULL,
    UNIQUE KEY unique_user_reseller (user_id, reseller_id),
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Plugins Table (Admin-created plugins)
CREATE TABLE IF NOT EXISTS plugins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    creator_admin_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    version VARCHAR(20) NOT NULL,
    license_type ENUM('open_source', 'commercial', 'custom') DEFAULT 'open_source',
    price DECIMAL(10,2) DEFAULT 0.00,
    file_path VARCHAR(255), -- Path to plugin zip/file
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_admin_id) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_creator_admin_id (creator_admin_id),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- Invoices Table (WHMCS-style billing)
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL, -- hosting_users.id, NULL for reseller invoices
    reseller_id INT NULL, -- For reseller-to-admin invoices
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    date DATE NOT NULL,
    due_date DATE NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    tax_rate DECIMAL(5,4) DEFAULT 0.0000,
    tax_amount DECIMAL(10,2) DEFAULT 0.00,
    total DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'sent', 'paid', 'overdue', 'cancelled', 'refunded') DEFAULT 'draft',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE SET NULL,
    FOREIGN KEY (reseller_id) REFERENCES resellers(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_reseller_id (reseller_id),
    INDEX idx_status (status),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- DNS Tables
CREATE TABLE IF NOT EXISTS dns_zones (
    id INT AUTO_INCREMENT PRIMARY KEY, domain VARCHAR(255) NOT NULL UNIQUE,
    ns1 VARCHAR(255), ns2 VARCHAR(255), admin_email VARCHAR(255),
    serial VARCHAR(20), refresh INT DEFAULT 3600, retry INT DEFAULT 1800,
    expire INT DEFAULT 86400, ttl INT DEFAULT 300, is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS dns_records (
    id INT AUTO_INCREMENT PRIMARY KEY, zone_id INT NOT NULL,
    name VARCHAR(255) NOT NULL, type VARCHAR(10) NOT NULL, value TEXT NOT NULL,
    ttl INT DEFAULT 300, priority INT DEFAULT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zone_id) REFERENCES dns_zones(id) ON DELETE CASCADE,
    INDEX idx_zone (zone_id), INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS dns_nameservers (
    id INT AUTO_INCREMENT PRIMARY KEY, nameserver VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS billing_products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  description TEXT,
  type ENUM('hosting','radio','vps','domain','addon','other') DEFAULT 'hosting',
  price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  setup_fee DECIMAL(10,2) DEFAULT 0.00,
  billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial') DEFAULT 'monthly',
  is_active TINYINT(1) DEFAULT 1,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  type ENUM('new','renewal','upgrade','downgrade') DEFAULT 'new',
  status ENUM('pending','active','suspended','cancelled') DEFAULT 'pending',
  total DECIMAL(10,2) DEFAULT 0.00,
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT DEFAULT NULL,
  order_id INT DEFAULT NULL,
  domain VARCHAR(255) DEFAULT '',
  status ENUM('active','suspended','terminated','pending') DEFAULT 'pending',
  billing_cycle ENUM('monthly','quarterly','semiannual','annual','biennial') DEFAULT 'monthly',
  price DECIMAL(10,2) DEFAULT 0.00,
  next_due_date DATE DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  invoice_id INT DEFAULT NULL,
  amount DECIMAL(10,2) NOT NULL,
  method ENUM('credit_card','paypal','bank_transfer','manual','credit') DEFAULT 'manual',
  status ENUM('pending','completed','failed','refunded') DEFAULT 'completed',
  transaction_id VARCHAR(255) DEFAULT '',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE,
  FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_taxes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  rate DECIMAL(5,4) NOT NULL DEFAULT 0.0000,
  country VARCHAR(5) DEFAULT '',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_coupons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(50) NOT NULL UNIQUE,
  type ENUM('percentage','fixed') DEFAULT 'percentage',
  value DECIMAL(10,2) NOT NULL,
  max_uses INT DEFAULT 0,
  used_count INT DEFAULT 0,
  min_total DECIMAL(10,2) DEFAULT 0.00,
  expires_at DATE DEFAULT NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_credits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  description VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS billing_refunds (
  id INT AUTO_INCREMENT PRIMARY KEY,
  payment_id INT DEFAULT NULL,
  invoice_id INT DEFAULT NULL,
  user_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  reason TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS kb_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS kb_articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT DEFAULT NULL,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  content TEXT,
  views INT DEFAULT 0,
  is_published TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES kb_categories(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS announcements (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  content TEXT,
  type ENUM('info','warning','success','danger') DEFAULT 'info',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS automation_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO automation_settings (setting_key, setting_value) VALUES
('auto_provision_enabled', '0'),
('auto_suspend_enabled', '0'),
('auto_suspend_days', '7'),
('auto_terminate_enabled', '0'),
('auto_terminate_days', '30'),
('email_notifications_enabled', '0'),
('smtp_host', ''),
('smtp_port', '587'),
('smtp_username', ''),
('smtp_password', ''),
('smtp_from', 'noreply@planet-hosts.com'),
('sms_notifications_enabled', '0'),
('sms_provider', ''),
('sms_api_key', ''),
('sms_from', ''),
('notify_admin_email', 'admin@planet-hosts.com'),
('notify_admin_sms', '');
CREATE TABLE IF NOT EXISTS webhooks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  url VARCHAR(500) NOT NULL,
  events VARCHAR(255) NOT NULL DEFAULT 'all',
  secret VARCHAR(64) DEFAULT '',
  is_active TINYINT(1) DEFAULT 1,
  last_triggered_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS rate_limit INT DEFAULT 60 AFTER permissions;
ALTER TABLE api_keys ADD COLUMN IF NOT EXISTS is_active TINYINT(1) DEFAULT 1 AFTER rate_limit;
CREATE TABLE IF NOT EXISTS tickets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  subject VARCHAR(255) NOT NULL,
  department VARCHAR(50) DEFAULT 'Technical',
  message TEXT,
  status ENUM('open','answered','closed') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ticket_replies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id INT NOT NULL,
  user_id INT DEFAULT NULL,
  admin_id INT DEFAULT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS node_apps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  domain VARCHAR(255) DEFAULT '',
  port INT DEFAULT 3000,
  entry_point VARCHAR(255) DEFAULT 'app.js',
  status ENUM('running','stopped','error') DEFAULT 'stopped',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS python_apps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  domain VARCHAR(255) DEFAULT '',
  port INT DEFAULT 8000,
  entry_point VARCHAR(255) DEFAULT 'app.py',
  framework VARCHAR(50) DEFAULT 'flask',
  status ENUM('running','stopped','error') DEFAULT 'stopped',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;
