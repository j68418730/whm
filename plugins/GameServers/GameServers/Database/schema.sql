CREATE TABLE IF NOT EXISTS game_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_type VARCHAR(50) NOT NULL,
    server_name VARCHAR(255) NOT NULL,
    port INT NOT NULL,
    query_port INT DEFAULT NULL,
    rcon_port INT DEFAULT NULL,
    rcon_password VARCHAR(255) DEFAULT NULL,
    max_players INT DEFAULT 16,
    current_players INT DEFAULT 0,
    status ENUM('installing','stopped','running','error') DEFAULT 'stopped',
    install_path VARCHAR(500) NOT NULL,
    config_path VARCHAR(500) DEFAULT NULL,
    pid INT DEFAULT NULL,
    is_demo TINYINT(1) DEFAULT 0,
    demo_expires DATETIME DEFAULT NULL,
    game_version VARCHAR(50) DEFAULT NULL,
    map_name VARCHAR(100) DEFAULT NULL,
    last_ping DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX (user_id),
    FOREIGN KEY (user_id) REFERENCES hosting_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO package_categories (name, icon, sort_order) VALUES ('Game Servers', '🎮', 6);
