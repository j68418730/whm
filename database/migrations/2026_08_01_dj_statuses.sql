-- 2026-08-01: Add suspended / on_leave statuses to radio_djs
--   active    = full access (login + streaming)
--   on_leave  = can login but NO stream/DJ access
--   suspended = no login, no access to anything
--   banned    = no login, no access
--   inactive  = no login, no access
ALTER TABLE radio_djs MODIFY COLUMN status enum('active','inactive','banned','suspended','on_leave') NOT NULL DEFAULT 'active';
