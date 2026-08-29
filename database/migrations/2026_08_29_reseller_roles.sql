-- Reseller System Phase 5: reseller-defined permission roles (staff groups).
-- Owner → Manager / Support / Billing / Technician are the built-in reseller_staff
-- roles. This table lets the reseller define CUSTOM permission roles and assign them
-- to staff, mirroring the master admin "Roles" section but scoped to reseller staff.
-- Permissions are stored as a JSON array of permission keys:
--   clients, packages, provisioning, billing, chat, support, branding, staff, api
-- A staff member's effective permissions = union(reseller_staff.role + role template).

CREATE TABLE IF NOT EXISTS `reseller_roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `permissions` TEXT NULL,          -- JSON array of permission keys
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  `updated_at` TIMESTAMP NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reseller_role_reseller` (`reseller_id`),
  CONSTRAINT `reseller_role_fk_reseller` FOREIGN KEY (`reseller_id`) REFERENCES `resellers`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Link a custom role to a staff member (many-to-many)
CREATE TABLE IF NOT EXISTS `reseller_staff_roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `reseller_id` INT NOT NULL,
  `staff_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  `created_at` TIMESTAMP NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reseller_staff_role_unique` (`reseller_id`,`staff_id`,`role_id`),
  CONSTRAINT `reseller_staff_roles_fk_staff` FOREIGN KEY (`staff_id`) REFERENCES `reseller_staff`(`id`) ON DELETE CASCADE,
  CONSTRAINT `reseller_staff_roles_fk_role` FOREIGN KEY (`role_id`) REFERENCES `reseller_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;