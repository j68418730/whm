-- 2026-08-30: Agent presence for real-time support desktop
-- Tracks online/away/busy/offline status per agent for routing and presence UI

CREATE TABLE IF NOT EXISTS agent_presence (
    id INT(11) NOT NULL AUTO_INCREMENT,
    admin_id INT(11) NOT NULL,
    status ENUM('online','away','busy','offline') NOT NULL DEFAULT 'offline',
    status_message VARCHAR(255) DEFAULT '',
    last_heartbeat DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_admin (admin_id),
    KEY idx_status (status),
    KEY idx_heartbeat (last_heartbeat)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Foreign key to admins (if admins table exists with id column)
-- ALTER TABLE agent_presence ADD CONSTRAINT fk_agent_presence_admin FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE;