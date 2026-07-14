-- Fix hosting_users.status enum drift.
--
-- The live database was altered out-of-band to a "provisioning state machine"
-- enum ('pending','creating_user',...,'completed','failed','suspended','terminated')
-- which DROPPED the value 'active'. However, every PHP path that writes this column
-- uses 'active' (AutoProvision.php, create_spectre.php, AccountController unsuspend),
-- and the panel reads 'active' as the running state (admin/Views/account/*).
-- The 'completed' / 'creating_*' values are only emitted by provision.sh to a log
-- file (set_status), never written to this column by PHP, so they are safe to drop.
--
-- This restores the enum to match install.sql and the application code. Any rows
-- holding a non-canonical value (e.g. 'completed') are normalized back to 'active'.

UPDATE hosting_users
    SET status = 'active'
    WHERE status IS NULL
       OR status NOT IN ('active', 'suspended', 'terminated');

ALTER TABLE hosting_users
    MODIFY status ENUM('active', 'suspended', 'terminated') DEFAULT 'active';
