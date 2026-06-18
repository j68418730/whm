-- Chatbox: Multi-tenant embeddable chat system
-- Completely separate from support chat tables

CREATE TABLE IF NOT EXISTS chatbox_tenants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hosting_user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    widget_title VARCHAR(100) DEFAULT 'Chat Room',
    widget_color VARCHAR(7) DEFAULT '#008cff',
    widget_bg VARCHAR(7) DEFAULT '#0a0e1a',
    widget_text_color VARCHAR(7) DEFAULT '#ffffff',
    font_family VARCHAR(100) DEFAULT 'Inter, sans-serif',
    logo_url VARCHAR(500) DEFAULT NULL,
    custom_css TEXT DEFAULT NULL,
    guest_enabled TINYINT(1) DEFAULT 1,
    registration_enabled TINYINT(1) DEFAULT 1,
    voice_enabled TINYINT(1) DEFAULT 0,
    max_rooms INT DEFAULT 5,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (hosting_user_id)
);

CREATE TABLE IF NOT EXISTS chatbox_rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description VARCHAR(500) DEFAULT NULL,
    type ENUM('public','private','password') DEFAULT 'public',
    password VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES chatbox_tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chatbox_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) DEFAULT NULL,
    display_name VARCHAR(100) DEFAULT NULL,
    role ENUM('owner','admin','mod','member','guest') DEFAULT 'member',
    avatar_url VARCHAR(500) DEFAULT NULL,
    bio TEXT DEFAULT NULL,
    email VARCHAR(255) DEFAULT NULL,
    is_banned TINYINT(1) DEFAULT 0,
    ban_reason VARCHAR(500) DEFAULT NULL,
    voice_denied TINYINT(1) DEFAULT 0,
    last_active DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY (tenant_id, username),
    FOREIGN KEY (tenant_id) REFERENCES chatbox_tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chatbox_messages (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    room_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    username VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    message_type ENUM('text','image','gif','system','voice') DEFAULT 'text',
    image_url VARCHAR(500) DEFAULT NULL,
    created_at BIGINT NOT NULL,
    FOREIGN KEY (tenant_id) REFERENCES chatbox_tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES chatbox_rooms(id) ON DELETE CASCADE,
    INDEX (room_id, id)
);

CREATE TABLE IF NOT EXISTS chatbox_bans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    username VARCHAR(50) DEFAULT NULL,
    reason VARCHAR(500) DEFAULT NULL,
    banned_by INT DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES chatbox_tenants(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chatbox_whitelist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tenant_id INT NOT NULL,
    user_id INT NOT NULL,
    added_by INT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES chatbox_tenants(id) ON DELETE CASCADE
);
