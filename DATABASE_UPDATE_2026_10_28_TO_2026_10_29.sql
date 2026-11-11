-- ==============================================================================
-- DATABASE UPDATE SCRIPT (28-OCT-2026 TO 29-OCT-2026)
-- CRM / ERP: Sales, Quotation-to-Invoice, Attendance, HR & Payroll Engine
--
-- Instructions:
-- Import this SQL file into your Live Database using phpMyAdmin, HeidiSQL, or MySQL CLI:
-- mysql -u [username] -p [database_name] < DATABASE_UPDATE_2026_10_28_TO_2026_10_29.sql
-- ==============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ------------------------------------------------------------------------------
-- 1. UPDATE `invoice_items` TABLE (Adds price column for Tax Invoice conversion)
-- ------------------------------------------------------------------------------
ALTER TABLE `invoice_items`
  ADD COLUMN IF NOT EXISTS `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `description`;

-- Sync existing rate into price if price is 0
UPDATE `invoice_items` SET `price` = `rate` WHERE `price` = 0.00 AND `rate` > 0.00;

-- ------------------------------------------------------------------------------
-- 2. UPDATE `asset_employees` TABLE (Adds Salary & Customizable Settlement Cycles)
-- ------------------------------------------------------------------------------
ALTER TABLE `asset_employees`
  ADD COLUMN IF NOT EXISTS `salary_type` VARCHAR(50) DEFAULT 'weekly' AFTER `designation`,
  ADD COLUMN IF NOT EXISTS `settlement_days` INT(11) DEFAULT 7 AFTER `salary_type`,
  ADD COLUMN IF NOT EXISTS `base_salary` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `settlement_days`,
  ADD COLUMN IF NOT EXISTS `extra_duty_rate` DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER `base_salary`,
  ADD COLUMN IF NOT EXISTS `daily_working_hours` DECIMAL(4,2) NOT NULL DEFAULT 8.00 AFTER `extra_duty_rate`,
  ADD COLUMN IF NOT EXISTS `joining_date` DATE DEFAULT NULL AFTER `daily_working_hours`,
  ADD COLUMN IF NOT EXISTS `address` TEXT DEFAULT NULL AFTER `joining_date`,
  ADD COLUMN IF NOT EXISTS `bank_name` VARCHAR(255) DEFAULT NULL AFTER `address`,
  ADD COLUMN IF NOT EXISTS `account_number` VARCHAR(255) DEFAULT NULL AFTER `bank_name`,
  ADD COLUMN IF NOT EXISTS `ifsc_code` VARCHAR(255) DEFAULT NULL AFTER `account_number`;

-- Ensure salary_type column is VARCHAR(50) for flexible days (e.g. 40_days, 60_days, custom_days)
ALTER TABLE `asset_employees` MODIFY COLUMN `salary_type` VARCHAR(50) DEFAULT 'weekly';

-- ------------------------------------------------------------------------------
-- 3. CREATE `attendances` TABLE (Daily Site Work & Extra Duty / OT Tracking)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `punch_in` time DEFAULT NULL,
  `punch_out` time DEFAULT NULL,
  `status` enum('present','half_day','absent','holiday','leave') NOT NULL DEFAULT 'present',
  `regular_hours` decimal(5,2) NOT NULL DEFAULT 8.00,
  `has_extra_duty` tinyint(1) NOT NULL DEFAULT 0,
  `extra_duty_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `extra_duty_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_duty_job` varchar(255) DEFAULT NULL,
  `extra_duty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_employee_id_foreign` (`employee_id`),
  KEY `attendances_customer_id_foreign` (`customer_id`),
  KEY `attendances_created_by_foreign` (`created_by`),
  KEY `attendances_date_index` (`date`),
  CONSTRAINT `attendances_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `asset_employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. CREATE `payrolls` TABLE (Automated Payroll Engine & Wages Settlement)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payrolls` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_number` varchar(255) NOT NULL,
  `employee_id` bigint(20) unsigned NOT NULL,
  `period_type` varchar(50) DEFAULT 'weekly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days_present` decimal(5,2) NOT NULL DEFAULT 0.00,
  `days_half_day` decimal(5,2) NOT NULL DEFAULT 0.00,
  `days_absent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_working_days` decimal(5,2) NOT NULL DEFAULT 0.00,
  `base_salary_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `base_salary_earned` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_extra_duty_hours` decimal(6,2) NOT NULL DEFAULT 0.00,
  `total_extra_duty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `allowance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `allowance_notes` varchar(255) DEFAULT NULL,
  `deduction_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `deduction_notes` varchar(255) DEFAULT NULL,
  `gross_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','approved','paid') NOT NULL DEFAULT 'draft',
  `payment_method` varchar(255) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `payment_reference` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payrolls_payroll_number_unique` (`payroll_number`),
  KEY `payrolls_employee_id_foreign` (`employee_id`),
  KEY `payrolls_created_by_foreign` (`created_by`),
  CONSTRAINT `payrolls_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `asset_employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. CREATE `payroll_items` TABLE (Itemized Daily Work & Extra Duty Snapshot)
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payroll_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `payroll_id` bigint(20) unsigned NOT NULL,
  `attendance_id` bigint(20) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `site_name` varchar(255) DEFAULT NULL,
  `punch_in` varchar(255) DEFAULT NULL,
  `punch_out` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'present',
  `regular_hours` decimal(5,2) NOT NULL DEFAULT 8.00,
  `extra_duty_hours` decimal(5,2) NOT NULL DEFAULT 0.00,
  `extra_duty_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `extra_duty_job` varchar(255) DEFAULT NULL,
  `extra_duty_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payroll_items_payroll_id_foreign` (`payroll_id`),
  KEY `payroll_items_attendance_id_foreign` (`attendance_id`),
  CONSTRAINT `payroll_items_attendance_id_foreign` FOREIGN KEY (`attendance_id`) REFERENCES `attendances` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payroll_items_payroll_id_foreign` FOREIGN KEY (`payroll_id`) REFERENCES `payrolls` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 6. INSERT PERMISSIONS (HR, Attendance & Payroll)
-- ------------------------------------------------------------------------------
INSERT IGNORE INTO `permissions` (`name`, `display_name`, `module`, `created_at`, `updated_at`) VALUES
('employees.view',     'View Employees',      'Staff & HR', NOW(), NOW()),
('employees.create',   'Create Employees',    'Staff & HR', NOW(), NOW()),
('employees.edit',     'Edit Employees',      'Staff & HR', NOW(), NOW()),
('employees.delete',   'Delete Employees',    'Staff & HR', NOW(), NOW()),
('attendance.view',    'View Attendance',     'Attendance', NOW(), NOW()),
('attendance.create',  'Record Attendance',   'Attendance', NOW(), NOW()),
('attendance.edit',    'Edit Attendance',     'Attendance', NOW(), NOW()),
('attendance.delete',  'Delete Attendance',   'Attendance', NOW(), NOW()),
('payroll.view',       'View Payroll',        'Payroll',    NOW(), NOW()),
('payroll.create',     'Process Payroll',     'Payroll',    NOW(), NOW()),
('payroll.edit',       'Edit / Pay Payroll',  'Payroll',    NOW(), NOW()),
('payroll.delete',     'Delete Payroll',      'Payroll',    NOW(), NOW());

-- Assign all permissions to Super Admin role
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.name = 'super_admin';

-- Assign HR, Attendance & Payroll to Admin role
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.name = 'admin' AND p.name IN (
  'employees.view', 'employees.create', 'employees.edit', 'employees.delete',
  'attendance.view', 'attendance.create', 'attendance.edit', 'attendance.delete',
  'payroll.view', 'payroll.create', 'payroll.edit', 'payroll.delete'
);

-- Assign Attendance and Payroll to Manager & Accountant
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
CROSS JOIN `permissions` p
WHERE r.name IN ('manager', 'accountant') AND p.name IN (
  'employees.view', 'attendance.view', 'attendance.create', 'attendance.edit',
  'payroll.view', 'payroll.create', 'payroll.edit'
);

-- ------------------------------------------------------------------------------
-- 7. UPDATE `settings` TABLE (Brand Logo & Sidebar Configuration)
-- ------------------------------------------------------------------------------
INSERT INTO `settings` (`key`, `value`, `created_at`, `updated_at`) VALUES
('sidebar_logo', 'settings/theme/sidebar_logo.jpg', NOW(), NOW()),
('company_logo', 'settings/general/company_logo.jpg', NOW(), NOW()),
('pdf_invoice_logo', 'settings/general/company_logo.jpg', NOW(), NOW()),
('pdf_estimation_logo', 'settings/general/company_logo.jpg', NOW(), NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW();

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- ==============================================================================
-- END OF SQL UPDATE SCRIPT
-- ==============================================================================

