-- Package shell / terminal access controls
-- Adds per-package settings for shell access, terminal, ssh, sftp, api shell, cron.
-- Feature lists already carry ssh_access / terminal / cron_jobs / api_access flags;
-- these columns drive the actual Linux-level + panel-level enforcement for a package.
-- Idempotent (MariaDB ADD COLUMN IF NOT EXISTS).

ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `shell_access` VARCHAR(20) NOT NULL DEFAULT 'disabled' AFTER `shoutcast_enabled`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `terminal` TINYINT(1) NOT NULL DEFAULT 0 AFTER `shell_access`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `ssh_access` TINYINT(1) NOT NULL DEFAULT 0 AFTER `terminal`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `sftp` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ssh_access`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `api_shell` TINYINT(1) NOT NULL DEFAULT 0 AFTER `sftp`;
ALTER TABLE `hosting_packages` ADD COLUMN IF NOT EXISTS `cron` TINYINT(1) NOT NULL DEFAULT 1 AFTER `api_shell`;

-- Defaults: give the existing "Advanced" (id=4) and "Radio Mini" style packages a
-- jailed shell + terminal so current customers keep working after upgrade.
UPDATE `hosting_packages` SET `shell_access` = 'jailed', `terminal` = 1, `ssh_access` = 1, `sftp` = 1, `api_shell` = 0, `cron` = 1
WHERE `id` = 4;