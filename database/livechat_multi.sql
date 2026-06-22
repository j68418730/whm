-- Multi-tenant Live Chat System for Planet-Hosts

-- =============================================
-- TENANT / PACKAGE LEVEL
-- =============================================

-- Add live_chat_enabled to hosting_packages (if not exists)
ALTER TABLE hosting_packages ADD COLUMN IF NOT EXISTS live_chat_enabled TINYINT(1) DEFAULT 0 AFTER features;

-- =============================================
-- LIVE CHAT TENANTS (one per hosting client)
-- =============================================
CREATE TABLE IF NOT EXISTS chat_tenants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  hosting_user_id INT NOT NULL UNIQUE,
  company_name VARCHAR(200) DEFAULT '',
  widget_title VARCHAR(100) DEFAULT 'Live Chat',
  widget_color VARCHAR(20) DEFAULT '#008cff',
  widget_position ENUM('right','left') DEFAULT 'right',
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (hosting_user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- LIVE CHAT USERS (operators, agents - NOT panel users)
-- =============================================
CREATE TABLE IF NOT EXISTS chat_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  username VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  display_name VARCHAR(200) DEFAULT '',
  email VARCHAR(200) DEFAULT '',
  role ENUM('manager','operator','agent') DEFAULT 'operator',
  status ENUM('online','away','offline') DEFAULT 'offline',
  max_chats INT DEFAULT 5,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES chat_tenants(id) ON DELETE CASCADE,
  UNIQUE KEY (tenant_id, username)
) ENGINE=InnoDB;

-- =============================================
-- DEPARTMENTS (per tenant)
-- =============================================
CREATE TABLE IF NOT EXISTS chat_departments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(100) NOT NULL,
  description TEXT,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES chat_tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- OPERATOR-DEPARTMENT ASSIGNMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS chat_operator_departments (
  operator_id INT NOT NULL,
  department_id INT NOT NULL,
  PRIMARY KEY (operator_id, department_id),
  FOREIGN KEY (operator_id) REFERENCES chat_users(id) ON DELETE CASCADE,
  FOREIGN KEY (department_id) REFERENCES chat_departments(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- CHATS (per tenant)
-- =============================================
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS department_id INT AFTER tenant_id;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS assigned_to INT AFTER department_id;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS tags VARCHAR(500) AFTER subject;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS browser VARCHAR(100) AFTER visitor_email;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS os VARCHAR(50) AFTER browser;
ALTER TABLE chat_sessions ADD COLUMN IF NOT EXISTS device VARCHAR(50) AFTER os;

-- =============================================
-- CHAT MESSAGES (add tenant_id)
-- =============================================
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id;
ALTER TABLE chat_messages ADD COLUMN IF NOT EXISTS is_read TINYINT(1) DEFAULT 0 AFTER is_emote;

-- =============================================
-- CHAT TAGS (per tenant)
-- =============================================
CREATE TABLE IF NOT EXISTS chat_tags (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id INT NOT NULL,
  name VARCHAR(50) NOT NULL,
  color VARCHAR(20) DEFAULT '#64748b',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tenant_id) REFERENCES chat_tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- CHAT-TAG ASSIGNMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS chat_chat_tags (
  chat_id INT NOT NULL,
  tag_id INT NOT NULL,
  PRIMARY KEY (chat_id, tag_id),
  FOREIGN KEY (chat_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (tag_id) REFERENCES chat_tags(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- CHAT RATINGS
-- =============================================
CREATE TABLE IF NOT EXISTS chat_ratings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chat_id INT NOT NULL,
  tenant_id INT NOT NULL,
  rating TINYINT DEFAULT 0,
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (chat_id) REFERENCES chat_sessions(id) ON DELETE CASCADE,
  FOREIGN KEY (tenant_id) REFERENCES chat_tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- CHAT ATTACHMENTS
-- =============================================
CREATE TABLE IF NOT EXISTS chat_attachments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  message_id INT NOT NULL,
  tenant_id INT NOT NULL,
  file_name VARCHAR(255) NOT NULL,
  file_path VARCHAR(500) NOT NULL,
  file_size INT DEFAULT 0,
  mime_type VARCHAR(100) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (message_id) REFERENCES chat_messages(id) ON DELETE CASCADE,
  FOREIGN KEY (tenant_id) REFERENCES chat_tenants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =============================================
-- CANNED RESPONSES (add tenant_id)
-- =============================================
ALTER TABLE chat_canned_responses ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id;
ALTER TABLE chat_canned_responses ADD COLUMN IF NOT EXISTS department_id INT AFTER tenant_id;

-- =============================================
-- VISITORS (add tenant_id)
-- =============================================
ALTER TABLE chat_visitors ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id;
ALTER TABLE chat_visitors ADD COLUMN IF NOT EXISTS browser VARCHAR(100) AFTER page_history;
ALTER TABLE chat_visitors ADD COLUMN IF NOT EXISTS os VARCHAR(50) AFTER browser;
ALTER TABLE chat_visitors ADD COLUMN IF NOT EXISTS device VARCHAR(50) AFTER os;

-- =============================================
-- OPERATOR GROUPS (add tenant_id)
-- =============================================
ALTER TABLE chat_operator_groups ADD COLUMN IF NOT EXISTS tenant_id INT AFTER id;
