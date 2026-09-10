-- Backup Destinations v2
-- Expands destinations to: local, ftp, ftps, sftp, rsync, s3, s3-compat, b2,
-- googledrive, webdav, custom. Adds cloud credential + retention + usage columns
-- and a transfer queue table. Idempotent (MariaDB IF NOT EXISTS / MODIFY).

ALTER TABLE `backup_destinations` MODIFY `type` VARCHAR(50) NOT NULL DEFAULT 'ftp';

ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `bucket` VARCHAR(255) DEFAULT NULL AFTER `path`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `region` VARCHAR(100) DEFAULT NULL AFTER `bucket`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `access_key` VARCHAR(500) DEFAULT NULL AFTER `region`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `secret_key` TEXT DEFAULT NULL AFTER `access_key`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `endpoint` VARCHAR(500) DEFAULT NULL AFTER `secret_key`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `command_template` TEXT DEFAULT NULL AFTER `endpoint`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `retention_daily` INT NOT NULL DEFAULT 7 AFTER `max_retries`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `retention_weekly` INT NOT NULL DEFAULT 4 AFTER `retention_daily`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `retention_monthly` INT NOT NULL DEFAULT 3 AFTER `retention_weekly`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `retention_yearly` INT NOT NULL DEFAULT 1 AFTER `retention_monthly`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `used_bytes` BIGINT NOT NULL DEFAULT 0 AFTER `retention_yearly`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `quota_bytes` BIGINT NOT NULL DEFAULT 0 AFTER `used_bytes`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `last_tested_at` DATETIME DEFAULT NULL AFTER `notes`;
ALTER TABLE `backup_destinations` ADD COLUMN IF NOT EXISTS `last_test_message` VARCHAR(500) DEFAULT NULL AFTER `last_tested_at`;

CREATE TABLE IF NOT EXISTS `backup_transfer_queue` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `destination_id` INT DEFAULT NULL,
    `job_id` INT DEFAULT NULL,
    `filename` VARCHAR(255) NOT NULL,
    `action` ENUM('upload','download') NOT NULL DEFAULT 'upload',
    `status` ENUM('pending','running','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
    `stage` VARCHAR(50) DEFAULT 'queued',
    `size` BIGINT DEFAULT 0,
    `checksum` VARCHAR(128) DEFAULT NULL,
    `attempts` INT DEFAULT 0,
    `max_attempts` INT DEFAULT 3,
    `error_message` TEXT DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tq_dest` (`destination_id`),
    INDEX `idx_tq_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;