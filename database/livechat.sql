CREATE TABLE IF NOT EXISTS chat_visitors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(64) NOT NULL,
  name VARCHAR(100) DEFAULT 'Visitor',
  email VARCHAR(100) DEFAULT '',
  ip_address VARCHAR(45) DEFAULT '',
  current_page VARCHAR(500) DEFAULT '',
  page_history TEXT,
  time_on_site INT DEFAULT 0,
  first_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  last_seen TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visitor_id INT DEFAULT NULL,
  operator_id INT DEFAULT NULL,
  department VARCHAR(50) DEFAULT 'General',
  status ENUM('waiting','active','closed') DEFAULT 'waiting',
  visitor_name VARCHAR(100) DEFAULT 'Visitor',
  visitor_email VARCHAR(100) DEFAULT '',
  subject VARCHAR(255) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  closed_at TIMESTAMP NULL,
  FOREIGN KEY (visitor_id) REFERENCES chat_visitors(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_messages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  sender_type ENUM('visitor','operator','system') DEFAULT 'visitor',
  sender_name VARCHAR(100) DEFAULT '',
  message TEXT,
  file_url VARCHAR(500) DEFAULT '',
  file_name VARCHAR(255) DEFAULT '',
  is_emote TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_canned_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  message TEXT NOT NULL,
  category VARCHAR(100) DEFAULT 'General',
  operator_group_id INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_operator_groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  department VARCHAR(100) DEFAULT '',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS chat_transcripts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  transcript TEXT,
  rating INT DEFAULT NULL,
  feedback TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (session_id) REFERENCES chat_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;
