-- Migration: Add missing columns to streaming_stations
ALTER TABLE `streaming_stations` ADD COLUMN `pid_file` varchar(500) DEFAULT NULL AFTER `config_path`;
ALTER TABLE `streaming_stations` ADD COLUMN `last_stopped` datetime DEFAULT NULL AFTER `autodj_active`;
ALTER TABLE `streaming_stations` ADD COLUMN `last_started` datetime DEFAULT NULL AFTER `last_stopped`;
