-- Chatbox Client System (separate from Support Live Chat)

-- Extend chatbox_tenants with package-based limits
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `storage_used` bigint(20) NOT NULL DEFAULT 0;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `storage_limit` bigint(20) NOT NULL DEFAULT 524288000;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `message_limit_days` int(11) NOT NULL DEFAULT 90;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `max_file_size` int(11) NOT NULL DEFAULT 26214400;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `allow_voice` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `allow_video` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `allow_screen_share` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `max_emoji` int(11) NOT NULL DEFAULT 25;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `allow_stickers` tinyint(1) NOT NULL DEFAULT 0;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `max_group_members` int(11) NOT NULL DEFAULT 10;
ALTER TABLE chatbox_tenants ADD COLUMN IF NOT EXISTS `webhook_url` varchar(500) DEFAULT NULL;

-- Private conversations
CREATE TABLE IF NOT EXISTS `chatbox_conversations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('direct','group','support','announcement') NOT NULL DEFAULT 'direct',
  `name` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `tenant_id` int(11) NOT NULL DEFAULT 0,
  `is_pinned` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`),
  KEY `created_by` (`created_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Conversation participants
CREATE TABLE IF NOT EXISTS `chatbox_participants` (
  `conversation_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('owner','admin','moderator','member') NOT NULL DEFAULT 'member',
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_read_at` timestamp NULL DEFAULT NULL,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`conversation_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Extended messages
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `conversation_id` int(11) DEFAULT NULL AFTER `room_id`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `parent_id` bigint(20) DEFAULT NULL AFTER `conversation_id`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `edited_at` timestamp NULL DEFAULT NULL AFTER `created_at`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `is_pinned` tinyint(1) NOT NULL DEFAULT 0 AFTER `edited_at`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `file_url` varchar(500) DEFAULT NULL AFTER `image_url`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `file_name` varchar(255) DEFAULT NULL AFTER `file_url`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `file_size` int(11) DEFAULT NULL AFTER `file_name`;
ALTER TABLE chatbox_messages ADD COLUMN IF NOT EXISTS `mime_type` varchar(100) DEFAULT NULL AFTER `file_size`;

-- Message reactions
CREATE TABLE IF NOT EXISTS `chatbox_reactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message_id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `emoji` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `msg_user_emoji` (`message_id`,`user_id`,`emoji`),
  KEY `message_id` (`message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Custom emojis
CREATE TABLE IF NOT EXISTS `chatbox_emojis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `is_url` tinyint(1) NOT NULL DEFAULT 0,
  `file_size` int(11) DEFAULT NULL,
  `created_by` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sticker packs
CREATE TABLE IF NOT EXISTS `chatbox_sticker_packs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tenant_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `file_url` varchar(500) DEFAULT NULL,
  `sticker_count` int(11) NOT NULL DEFAULT 0,
  `is_animated` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `tenant_id` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- User status history
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `status` enum('online','away','busy','invisible','offline') NOT NULL DEFAULT 'offline';
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `avatar` varchar(500) DEFAULT NULL;
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `bio` text DEFAULT NULL;
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `last_active` timestamp NULL DEFAULT NULL;
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `display_name` varchar(100) DEFAULT NULL;
ALTER TABLE chatbox_users ADD COLUMN IF NOT EXISTS `storage_used` bigint(20) NOT NULL DEFAULT 0;
