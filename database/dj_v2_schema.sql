CREATE TABLE IF NOT EXISTS radio_dj_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY, dj_id INT NOT NULL, stream_id INT NOT NULL,
    scheduled_date DATE NOT NULL, time_slot VARCHAR(20) NOT NULL, status ENUM('booked','cancelled') DEFAULT 'booked',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (dj_id, scheduled_date, time_slot)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_applications (
    id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT NOT NULL,
    name VARCHAR(100), email VARCHAR(100), phone VARCHAR(20), bio TEXT,
    why_you TEXT, experience TEXT, status ENUM('pending','approved','rejected') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_messages (
    id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT NOT NULL,
    from_user INT NOT NULL, from_type ENUM('dj','admin') NOT NULL,
    to_user INT DEFAULT NULL, subject VARCHAR(255), message TEXT NOT NULL,
    read_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_billing (
    id INT AUTO_INCREMENT PRIMARY KEY, dj_id INT NOT NULL, stream_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL, description VARCHAR(500), status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    invoice_number VARCHAR(50), paid_at DATETIME DEFAULT NULL, created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_ads (
    id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT NOT NULL,
    banner_url VARCHAR(500), target_url VARCHAR(500), title VARCHAR(255),
    status ENUM('active','inactive') DEFAULT 'active',
    impressions INT DEFAULT 0, clicks INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_bans (
    id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL, reason VARCHAR(500), banned_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS radio_dj_chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY, stream_id INT NOT NULL,
    dj_id INT DEFAULT NULL, username VARCHAR(100), message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS last_active DATETIME DEFAULT NULL;
ALTER TABLE radio_djs ADD COLUMN IF NOT EXISTS ip_address VARCHAR(45) DEFAULT NULL;
