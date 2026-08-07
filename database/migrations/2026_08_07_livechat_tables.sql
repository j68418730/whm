-- 2026-08-07: Fix live support chat (500) + notification system.
CREATE TABLE IF NOT EXISTS notifications (
  id INT(11) NOT NULL AUTO_INCREMENT,
  user_id INT(11) NOT NULL DEFAULT 0,
  type VARCHAR(50) DEFAULT 'info',
  title VARCHAR(255) DEFAULT '',
  message TEXT,
  read_at DATETIME DEFAULT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_user (user_id),
  KEY idx_type (type),
  KEY idx_read (read_at),
  KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_attachments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  message_id INT(11) NOT NULL DEFAULT 0,
  file_url VARCHAR(500) DEFAULT '',
  file_name VARCHAR(255) DEFAULT '',
  file_size INT(11) DEFAULT 0,
  mime_type VARCHAR(100) DEFAULT '',
  uploaded_by INT(11) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_message (message_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS chat_ratings (
  id INT(11) NOT NULL AUTO_INCREMENT,
  chat_id INT(11) NOT NULL DEFAULT 0,
  rating INT(11) DEFAULT 0,
  feedback TEXT,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_chat (chat_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
