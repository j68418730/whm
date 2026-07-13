-- Backup System Tables
-- Adds FTP destination management, job tracking, logs

CREATE TABLE IF NOT EXISTS `backup_destinations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `type` ENUM('ftp','ftps','sftp','local','s3','b2','gcs','azure','webdav') NOT NULL DEFAULT 'ftp',
    `host` VARCHAR(255) DEFAULT NULL,
    `port` INT DEFAULT 21,
    `username` VARCHAR(255) DEFAULT NULL,
    `password` TEXT DEFAULT NULL,
    `path` VARCHAR(255) DEFAULT '/',
    `passive` TINYINT(1) DEFAULT 1,
    `ssl` TINYINT(1) DEFAULT 0,
    `private_key` TEXT DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `is_default` TINYINT(1) DEFAULT 0,
    `max_retries` INT DEFAULT 3,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_jobs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `destination_id` INT DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `type` ENUM('full','incremental','differential','files','database','email','dns','ssl','config') NOT NULL DEFAULT 'full',
    `status` ENUM('pending','running','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    `filename` VARCHAR(255) DEFAULT NULL,
    `size` BIGINT DEFAULT 0,
    `checksum` VARCHAR(128) DEFAULT NULL,
    `compression` ENUM('gzip','bzip2','xz','zip','none') DEFAULT 'gzip',
    `encryption` ENUM('none','aes256','gpg') DEFAULT 'none',
    `schedule` ENUM('manual','hourly','daily','weekly','monthly') DEFAULT 'manual',
    `retention_daily` INT DEFAULT 7,
    `retention_weekly` INT DEFAULT 4,
    `retention_monthly` INT DEFAULT 3,
    `retention_yearly` INT DEFAULT 1,
    `retry_count` INT DEFAULT 0,
    `max_retries` INT DEFAULT 3,
    `error_message` TEXT DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`destination_id`) REFERENCES `backup_destinations`(`id`) ON DELETE SET NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_schedule` (`schedule`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `backup_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT DEFAULT NULL,
    `destination_id` INT DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `status` ENUM('pending','running','completed','failed','warning') NOT NULL DEFAULT 'pending',
    `message` TEXT DEFAULT NULL,
    `file_path` VARCHAR(500) DEFAULT NULL,
    `file_size` BIGINT DEFAULT 0,
    `checksum` VARCHAR(128) DEFAULT NULL,
    `duration_ms` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_id`) REFERENCES `backup_jobs`(`id`) ON DELETE SET NULL,
    INDEX `idx_job` (`job_id`),
    INDEX `idx_dest` (`destination_id`),
    INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `restore_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `job_id` INT DEFAULT NULL,
    `user_id` INT DEFAULT NULL,
    `type` ENUM('full','files','database','email','dns','ssl','config','mailbox','single_file') NOT NULL DEFAULT 'full',
    `source` VARCHAR(500) DEFAULT NULL,
    `status` ENUM('pending','running','completed','failed','partial') NOT NULL DEFAULT 'pending',
    `overwrite` TINYINT(1) DEFAULT 0,
    `items_restored` INT DEFAULT 0,
    `items_failed` INT DEFAULT 0,
    `error_message` TEXT DEFAULT NULL,
    `preview_data` LONGTEXT DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`job_id`) REFERENCES `backup_jobs`(`id`) ON DELETE SET NULL,
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
