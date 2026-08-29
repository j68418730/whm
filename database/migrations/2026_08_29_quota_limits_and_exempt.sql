-- Set default quota limits for all hosted packages and permanently exempt
-- the primary (planet-hosts.com) and suggawatz accounts from auto-suspension.
--
-- UNITS (hosting_packages): disk_space = GB, bandwidth = GB  (see
-- database/seed_packages.sql: Starter = disk 1 GB, bw 10 GB, and
-- admin/Views/account/show.php disk% math: disk_used_MB / (disk_space * 1024)).
--
-- 1. Every active hosting package gets a realistic 10 GB disk + 10 GB bandwidth.
--    (disk_space/bandwidth of 0 means "unlimited"; 10 is chosen as a sane cap.)
UPDATE hosting_packages
    SET disk_space = 10,
        bandwidth  = 10
    WHERE is_active = 1;

-- 2. Ensure the per-account suspension columns exist (idempotent; MariaDB
--    supports ADD COLUMN IF NOT EXISTS). These back the admin toggles on
--    admin/Views/account/show.php.
ALTER TABLE hosting_users
    ADD COLUMN IF NOT EXISTS no_auto_suspend TINYINT(1) NOT NULL DEFAULT 0 AFTER suspended_by;
ALTER TABLE hosting_users
    ADD COLUMN IF NOT EXISTS allow_suspension TINYINT(1) NOT NULL DEFAULT 1 AFTER no_auto_suspend;

-- 3. Permanently exempt the primary domain account and suggawatz from ANY
--    disk/bandwidth quota auto-suspension. Both flags together guarantee the
--    account can never be auto-suspended regardless of usage:
--       no_auto_suspend = 1  -> the automation explicitly skips them
--       allow_suspension = 0 -> auto-suspension is disabled (manual only)
UPDATE hosting_users
    SET no_auto_suspend  = 1,
        allow_suspension = 0
    WHERE username IN ('planethosts', 'suggawatz', 'suggawayz')
       OR domain IN ('planet-hosts.com', 'suggawatz.com', 'suggawayz.com');
