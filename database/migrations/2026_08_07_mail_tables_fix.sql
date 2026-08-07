-- 2026-08-07: Fix admin/user email pages (HTTP 500).
-- mail_forwarders, mail_autoresponder, mail_spam were missing entirely;
-- mail_accounts was missing columns the controllers/views use.

CREATE TABLE IF NOT EXISTS mail_forwarders (
  id INT(11) NOT NULL AUTO_INCREMENT,
  from_email VARCHAR(255) NOT NULL,
  to_email VARCHAR(255) NOT NULL,
  domain VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mail_autoresponder (
  id INT(11) NOT NULL AUTO_INCREMENT,
  email VARCHAR(255) NOT NULL,
  domain VARCHAR(255) NOT NULL,
  subject VARCHAR(255) DEFAULT '',
  message TEXT,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_domain (domain),
  KEY idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS mail_spam (
  id INT(11) NOT NULL AUTO_INCREMENT,
  domain VARCHAR(255) NOT NULL,
  action VARCHAR(20) NOT NULL DEFAULT 'move_junk',
  threshold VARCHAR(10) NOT NULL DEFAULT '5.0',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_domain (domain)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- mail_accounts: add columns controllers/views expect (keep existing ones for compatibility)
ALTER TABLE mail_accounts
  ADD COLUMN IF NOT EXISTS domain VARCHAR(255) NULL AFTER email,
  ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NULL AFTER domain,
  ADD COLUMN IF NOT EXISTS password_plain VARCHAR(255) NULL AFTER password_hash,
  ADD COLUMN IF NOT EXISTS quota_mb INT(11) NULL DEFAULT 1000 AFTER quota;
