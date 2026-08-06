-- 2026-08-06: Make playlists shareable across all stations on the same account.
-- A playlist's account is derived from its stream's hosting user. When set (default 1),
-- the playlist is visible in every station of that account (icecast + shoutcast v1/v2).
ALTER TABLE radio_playlists ADD COLUMN IF NOT EXISTS account_shared TINYINT(1) NOT NULL DEFAULT 1 AFTER is_default;
