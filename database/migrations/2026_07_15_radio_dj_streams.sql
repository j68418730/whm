-- Migration: Create radio_dj_streams junction table for multi-station DJ support
-- Created: 2026-07-15
-- Description: Many-to-many relationship between radio_djs and radio_streams

CREATE TABLE IF NOT EXISTS `radio_dj_streams` (
    `dj_id` INT(11) NOT NULL,
    `stream_id` INT(11) NOT NULL,
    `assigned_by` INT(11) NOT NULL,
    `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `is_active` ENUM('yes', 'no') DEFAULT 'yes',
    PRIMARY KEY (`dj_id`, `stream_id`),
    FOREIGN KEY (`dj_id`) REFERENCES `radio_djs`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`stream_id`) REFERENCES `radio_streams`(`id`) ON DELETE CASCADE,
    INDEX `idx_dj_id` (`dj_id`),
    INDEX `idx_stream_id` (`stream_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;