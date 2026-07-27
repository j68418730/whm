-- Chatbox v3 — Room-based system with categories, permissions, product integration

-- Categories (like Discord: Hosting, Radio, Gaming, etc.)
CREATE TABLE IF NOT EXISTS `chat_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extended rooms table (replaces chatbox_conversations for rooms)
CREATE TABLE IF NOT EXISTS `chat_rooms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `owner_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) DEFAULT NULL,
  `package_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(20) DEFAULT NULL,
  `banner` varchar(500) DEFAULT NULL,
  `color` varchar(20) DEFAULT '#008cff',
  `visibility` enum('public','private','password','product','hidden') NOT NULL DEFAULT 'public',
  `password` varchar(255) DEFAULT NULL,
  `max_users` int(11) NOT NULL DEFAULT 0,
  `voice_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `video_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `allow_uploads` tinyint(1) NOT NULL DEFAULT 1,
  `storage_limit` bigint(20) NOT NULL DEFAULT 524288000,
  `file_size_limit` int(11) NOT NULL DEFAULT 26214400,
  `slow_mode` int(11) NOT NULL DEFAULT 0,
  `auto_delete_days` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `owner_id` (`owner_id`),
  KEY `product_id` (`product_id`),
  KEY `package_id` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Room members with roles
CREATE TABLE IF NOT EXISTS `chat_room_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','admin','moderator','vip','member','guest','muted','readonly','bot') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_seen` timestamp NULL DEFAULT NULL,
  `muted_until` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_user` (`room_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Room messages (separate from DM messages)
CREATE TABLE IF NOT EXISTS `chat_messages` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `message` text DEFAULT NULL,
  `message_type` enum('text','image','video','audio','file','voice','system','gif','sticker') NOT NULL DEFAULT 'text',
  `reply_to` bigint(20) DEFAULT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_size` int(11) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `is_edited` tinyint(1) NOT NULL DEFAULT 0,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` bigint(20) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `room_id` (`room_id`),
  KEY `user_id` (`user_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Room permissions per role
CREATE TABLE IF NOT EXISTS `chat_room_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `role` enum('admin','moderator','vip','member','guest') NOT NULL DEFAULT 'member',
  `can_send` tinyint(1) NOT NULL DEFAULT 1,
  `can_upload` tinyint(1) NOT NULL DEFAULT 1,
  `can_voice` tinyint(1) NOT NULL DEFAULT 1,
  `can_video` tinyint(1) NOT NULL DEFAULT 1,
  `can_manage_messages` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_members` tinyint(1) NOT NULL DEFAULT 0,
  `can_manage_room` tinyint(1) NOT NULL DEFAULT 0,
  `can_invite` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `room_role` (`room_id`,`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default categories
INSERT IGNORE INTO chat_categories (id, name, slug, sort_order) VALUES
(1, 'General', 'general', 1),
(2, 'Hosting', 'hosting', 2),
(3, 'Radio', 'radio', 3),
(4, 'Gaming', 'gaming', 4),
(5, 'Billing & Support', 'billing-support', 5);

-- Migrate existing chatbox_conversations group rooms to chat_rooms
INSERT IGNORE INTO chat_rooms (id, owner_id, name, description, color, icon, visibility, created_at)
SELECT id, created_by, COALESCE(name, 'Room'), COALESCE(description, ''), COALESCE(color, '#008cff'), COALESCE(icon, ''), 'public', created_at
FROM chatbox_conversations WHERE type IN ('group', 'announcement');

-- Migrate members
INSERT IGNORE INTO chat_room_members (room_id, user_id, role, joined_at)
SELECT p.conversation_id, p.user_id,
  CASE WHEN p.role = 'owner' THEN 'owner' WHEN p.role = 'admin' THEN 'admin' ELSE 'member' END,
  p.joined_at
FROM chatbox_participants p
JOIN chatbox_conversations c ON c.id = p.conversation_id AND c.type IN ('group', 'announcement');
