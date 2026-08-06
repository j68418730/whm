-- 2026-08-06: Client Security Center (application-level access control)
-- Purpose: customers control access to THEIR OWN services only. No Linux firewall changes.
-- This is a separate module from /admin/firewall (firewalld/fail2ban/ModSecurity/CSF remain untouched).

-- Core security rules
CREATE TABLE IF NOT EXISTS security_rules (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  rule_type VARCHAR(30) NOT NULL DEFAULT 'user',   -- user | username | email | ip | cidr | country | asn | vpn | proxy | tor | device
  target VARCHAR(255) NOT NULL,                     -- the value (username, email, ip, cidr, ISO country, ASN, etc.)
  service VARCHAR(40) NOT NULL DEFAULT 'all',       -- all | website | chat | radio | requests | downloads | billing | ftp | email | game | api | dj | comments | uploads | contact
  action VARCHAR(30) NOT NULL DEFAULT 'block',      -- block | allow | mute | kick | ban | shadow_ban | slow_mode | whitelist | reserve
  reason VARCHAR(500) DEFAULT NULL,
  expires_at DATETIME DEFAULT NULL,
  created_by VARCHAR(100) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id),
  KEY idx_target (target),
  KEY idx_service (service),
  KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Audit / activity log
CREATE TABLE IF NOT EXISTS security_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  action VARCHAR(100) NOT NULL,
  target VARCHAR(255) DEFAULT NULL,
  service VARCHAR(40) DEFAULT NULL,
  result VARCHAR(30) DEFAULT NULL,                   -- blocked | allowed | logged | alert
  performed_by VARCHAR(100) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  details TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id),
  KEY idx_action (action),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Failed / successful login attempts (per customer, per identity)
CREATE TABLE IF NOT EXISTS security_login_attempts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  username VARCHAR(150) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  user_agent VARCHAR(500) DEFAULT NULL,
  country VARCHAR(4) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id),
  KEY idx_ip (ip_address),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Active client sessions (tracked for "Active Sessions" dashboard)
CREATE TABLE IF NOT EXISTS security_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  session_hash VARCHAR(100) NOT NULL,
  browser VARCHAR(255) DEFAULT NULL,
  device VARCHAR(255) DEFAULT NULL,
  ip_address VARCHAR(45) DEFAULT NULL,
  country VARCHAR(4) DEFAULT NULL,
  trusted TINYINT(1) NOT NULL DEFAULT 0,
  last_active DATETIME DEFAULT CURRENT_TIMESTAMP,
  expires_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_session (session_hash),
  KEY idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Trusted devices / IPs (for login security)
CREATE TABLE IF NOT EXISTS security_trusted (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  kind VARCHAR(10) NOT NULL DEFAULT 'device',       -- device | ip
  value VARCHAR(500) NOT NULL,
  label VARCHAR(255) DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Security alerts / notifications for the customer
CREATE TABLE IF NOT EXISTS security_alerts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  type VARCHAR(40) NOT NULL DEFAULT 'info',          -- block | failed_login | new_device | vpn | country | warning | info
  message VARCHAR(500) NOT NULL,
  is_read TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  KEY idx_customer (customer_id),
  KEY idx_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-customer login security settings
CREATE TABLE IF NOT EXISTS security_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL DEFAULT 0,
  setting_key VARCHAR(50) NOT NULL,
  setting_value VARCHAR(500) DEFAULT NULL,
  UNIQUE KEY uq_customer_key (customer_id, setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
