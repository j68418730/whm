-- Game Server Feature Tables Migration

CREATE TABLE IF NOT EXISTS game_server_players (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    player_name VARCHAR(100) NOT NULL,
    steam_id VARCHAR(50),
    ip_address VARCHAR(45),
    first_seen DATETIME,
    last_seen DATETIME,
    play_time INT DEFAULT 0,
    kills INT DEFAULT 0,
    deaths INT DEFAULT 0,
    is_admin TINYINT(1) DEFAULT 0,
    is_banned TINYINT(1) DEFAULT 0,
    ban_reason TEXT,
    banned_by INT,
    banned_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    player_name VARCHAR(100),
    steam_id VARCHAR(50),
    ip_address VARCHAR(45),
    reason TEXT,
    banned_by INT,
    expires_at DATETIME,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_maps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500),
    file_size BIGINT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    workshop_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(255),
    file_path VARCHAR(500),
    file_size BIGINT DEFAULT 0,
    status ENUM('pending','running','completed','failed') DEFAULT 'pending',
    type ENUM('manual','scheduled') DEFAULT 'manual',
    schedule_cron VARCHAR(100),
    retention_days INT DEFAULT 30,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_firewall_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    rule_type ENUM('allow','deny') DEFAULT 'allow',
    protocol ENUM('tcp','udp','both') DEFAULT 'both',
    port_start INT,
    port_end INT,
    source_ip VARCHAR(45) DEFAULT '0.0.0.0/0',
    description TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_scheduled_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    task_type ENUM('restart','update','backup','command','workshop_update') NOT NULL,
    cron_expression VARCHAR(100) NOT NULL,
    command TEXT,
    is_active TINYINT(1) DEFAULT 1,
    last_run DATETIME,
    next_run DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    type ENUM('email','webhook','discord') DEFAULT 'email',
    event ENUM('server_started','server_stopped','server_crashed','player_joined','backup_completed','backup_failed','low_disk_space','high_cpu','update_available') NOT NULL,
    destination VARCHAR(500),
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_sub_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    password VARCHAR(255) NOT NULL,
    permissions TEXT,
    is_active TINYINT(1) DEFAULT 1,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_voice_servers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    type ENUM('discord','mumble','teamspeak') NOT NULL,
    server_address VARCHAR(255),
    server_port INT,
    server_password VARCHAR(255),
    channel_name VARCHAR(255),
    bot_token TEXT,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS game_server_workshop_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_id INT NOT NULL,
    workshop_id VARCHAR(100) NOT NULL,
    name VARCHAR(255),
    installed TINYINT(1) DEFAULT 0,
    install_path VARCHAR(500),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (server_id) REFERENCES game_servers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add steam_id column to game_servers for ownership
ALTER TABLE game_servers ADD COLUMN IF NOT EXISTS steam_owner_id VARCHAR(50) AFTER steam_guard_code;
ALTER TABLE game_servers ADD COLUMN IF NOT EXISTS steam_owned TINYINT(1) DEFAULT 0 AFTER steam_owner_id;

-- Add backup_size/backup_count to game_servers for quick stats
ALTER TABLE game_servers ADD COLUMN IF NOT EXISTS total_backups INT DEFAULT 0 AFTER last_backup_at;
ALTER TABLE game_servers ADD COLUMN IF NOT EXISTS total_backup_size BIGINT DEFAULT 0 AFTER total_backups;
