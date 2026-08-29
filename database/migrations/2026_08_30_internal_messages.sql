-- 2026-08-30: Internal staff chat (separate from customer-facing chats)
-- Agents can communicate privately without customers seeing it

CREATE TABLE IF NOT EXISTS internal_messages (
    id INT(11) NOT NULL AUTO_INCREMENT,
    conversation_type ENUM('direct','channel') NOT NULL DEFAULT 'direct',
    channel_name VARCHAR(100) DEFAULT NULL, -- for channel-type: e.g. '#general', '#billing', '#dev'
    sender_id INT(11) NOT NULL,
    recipient_id INT(11) DEFAULT NULL, -- for direct messages
    message TEXT NOT NULL,
    message_type ENUM('text','system','file') DEFAULT 'text',
    file_url VARCHAR(500) DEFAULT '',
    file_name VARCHAR(255) DEFAULT '',
    read_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_conversation (conversation_type, channel_name),
    KEY idx_sender (sender_id),
    KEY idx_recipient (recipient_id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Direct message participants (for quick listing of DM conversations)
CREATE TABLE IF NOT EXISTS internal_conversations (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user1_id INT(11) NOT NULL,
    user2_id INT(11) NOT NULL,
    last_message_id INT(11) DEFAULT NULL,
    last_message_at DATETIME DEFAULT NULL,
    unread_count_1 INT(11) DEFAULT 0,
    unread_count_2 INT(11) DEFAULT 0,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_pair (user1_id, user2_id),
    KEY idx_user1 (user1_id),
    KEY idx_user2 (user2_id),
    KEY idx_last_msg (last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Channel memberships
CREATE TABLE IF NOT EXISTS internal_channel_members (
    id INT(11) NOT NULL AUTO_INCREMENT,
    channel_name VARCHAR(100) NOT NULL,
    admin_id INT(11) NOT NULL,
    role ENUM('member','admin','owner') DEFAULT 'member',
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_read_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_channel_admin (channel_name, admin_id),
    KEY idx_channel (channel_name),
    KEY idx_admin (admin_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;