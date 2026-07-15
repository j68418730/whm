-- Migration: Add last_stopped column to streaming_stations
ALTER TABLE `streaming_stations` ADD COLUMN `last_stopped` datetime DEFAULT NULL AFTER `autodj_active`;
