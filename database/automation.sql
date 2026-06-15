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
