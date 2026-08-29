-- Reseller System Phase 4: link a client account to the retail package the reseller sold.
-- hosting_users.reseller_package_id points at reseller_packages (the reseller's own retail
-- product) so the reseller-sold scope is known without mixing with server packages.
-- Mirrors reseller_packages.package_id FKs to resellers.

ALTER TABLE `hosting_users`
  ADD COLUMN `reseller_package_id` INT NULL AFTER `package_id`,
  ADD KEY `idx_hosting_users_reseller_pkg` (`reseller_package_id`);