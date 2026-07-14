-- Fix hosting_users.status enum drift.
--
-- The live database was altered out-of-band to a "provisioning state machine"
-- enum which DROPPED the value 'active'. However, every PHP path that writes this
-- column uses 'active' (AutoProvision.php, create_spectre.php, AccountController
-- unsuspend), and the panel reads 'active' as the running state (admin/Views/account/*).
-- The 'completed' / 'creating_*' values are only emitted by provision.sh to a log
-- file (set_status), never written to this column by PHP, so they are safe to drop.
--
-- Because the current enum has no 'active', we must widen it first (no truncation),
-- normalize rows, then narrow it back to the canonical set that matches install.sql.

ALTER TABLE hosting_users
    MODIFY status ENUM(
        'pending', 'creating_user', 'creating_directories', 'creating_vhost',
        'creating_dns', 'creating_ssl', 'creating_database', 'creating_ftp',
        'creating_mail', 'applying_quotas', 'running_validation',
        'completed', 'failed', 'active', 'suspended', 'terminated'
    ) DEFAULT 'active';

UPDATE hosting_users
    SET status = 'active'
    WHERE status IS NULL
       OR status NOT IN ('active', 'suspended', 'terminated');

ALTER TABLE hosting_users
    MODIFY status ENUM('active', 'suspended', 'terminated') DEFAULT 'active';
