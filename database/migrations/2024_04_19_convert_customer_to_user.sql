-- Migration: Convert Customer table to User table
-- Date: 2024-04-19
-- Description: Rename Customer table to User and add role and password_hash fields

USE mobilis_db;

-- Drop views that reference Customer table
DROP VIEW IF EXISTS vw_active_rentals;

-- Rename Customer table to User
SET @table_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'Customer');
SET @sql = IF(@table_exists > 0, 'RENAME TABLE Customer TO User', 'SELECT ''Table Customer does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add role column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'User' AND COLUMN_NAME = 'role');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE User ADD COLUMN role ENUM(''admin'',''staff'',''customer'') NOT NULL DEFAULT ''customer'' AFTER address', 'SELECT ''Column role already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add password_hash column if it doesn't exist
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'User' AND COLUMN_NAME = 'password_hash');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE User ADD COLUMN password_hash VARCHAR(255) NOT NULL AFTER role', 'SELECT ''Column password_hash already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Make license_number and license_expiry nullable (for admin/staff users)
SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'User' AND COLUMN_NAME = 'license_number');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE User MODIFY COLUMN license_number VARCHAR(30) NULL', 'SELECT ''Column license_number not found'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'User' AND COLUMN_NAME = 'license_expiry');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE User MODIFY COLUMN license_expiry DATE NULL', 'SELECT ''Column license_expiry not found'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update foreign key in Rental table from customer_id to user_id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'Rental' AND CONSTRAINT_NAME = 'fk_rent_cust');
SET @sql = IF(@fk_exists > 0, 'ALTER TABLE Rental DROP FOREIGN KEY fk_rent_cust', 'SELECT ''Foreign key fk_rent_cust does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'Rental' AND COLUMN_NAME = 'customer_id');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE Rental CHANGE COLUMN customer_id user_id INT UNSIGNED NOT NULL', 'SELECT ''Column customer_id does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'Rental' AND CONSTRAINT_NAME = 'fk_rent_user');
SET @sql = IF(@fk_exists = 0, 'ALTER TABLE Rental ADD CONSTRAINT fk_rent_user FOREIGN KEY (user_id) REFERENCES User(user_id) ON UPDATE CASCADE ON DELETE RESTRICT', 'SELECT ''Foreign key fk_rent_user already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Update foreign key in PasswordResetRequest table from customer_id to user_id
SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'PasswordResetRequest' AND CONSTRAINT_NAME = 'fk_pwdreset_customer');
SET @sql = IF(@fk_exists > 0, 'ALTER TABLE PasswordResetRequest DROP FOREIGN KEY fk_pwdreset_customer', 'SELECT ''Foreign key fk_pwdreset_customer does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'PasswordResetRequest' AND COLUMN_NAME = 'customer_id');
SET @sql = IF(@col_exists > 0, 'ALTER TABLE PasswordResetRequest CHANGE COLUMN customer_id user_id INT UNSIGNED NULL', 'SELECT ''Column customer_id does not exist'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'mobilis_db' AND TABLE_NAME = 'PasswordResetRequest' AND CONSTRAINT_NAME = 'fk_pwdreset_user');
SET @sql = IF(@fk_exists = 0, 'ALTER TABLE PasswordResetRequest ADD CONSTRAINT fk_pwdreset_user FOREIGN KEY (user_id) REFERENCES User(user_id) ON UPDATE CASCADE ON DELETE SET NULL', 'SELECT ''Foreign key fk_pwdreset_user already exists'' AS message');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add sample admin, staff, and customer accounts if they don't exist
INSERT INTO User (first_name, last_name, email, phone, license_number, license_expiry, address, role, password_hash, created_at)
SELECT seed.first_name, seed.last_name, seed.email, seed.phone, seed.license_number, seed.license_expiry, seed.address, seed.role, seed.password_hash, seed.created_at
FROM (
  SELECT 'Admin' AS first_name, 'User' AS last_name, 'admin@mobilis.ph' AS email, '+63 900 000 0001' AS phone, NULL AS license_number, NULL AS license_expiry, 'Mobilis HQ, Metro Manila' AS address, 'admin' AS role, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password_hash, '2024-01-01 00:00:00' AS created_at
  UNION ALL
  SELECT 'Staff' AS first_name, 'User' AS last_name, 'staff@mobilis.ph' AS email, '+63 900 000 0002' AS phone, NULL AS license_number, NULL AS license_expiry, 'Mobilis HQ, Metro Manila' AS address, 'staff' AS role, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password_hash, '2024-01-01 00:00:00' AS created_at
  UNION ALL
  SELECT 'Customer' AS first_name, 'User' AS last_name, 'customer@mobilis.ph' AS email, '+63 900 000 0003' AS phone, NULL AS license_number, NULL AS license_expiry, 'Metro Manila, Philippines' AS address, 'customer' AS role, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' AS password_hash, '2024-01-01 00:00:00' AS created_at
) AS seed
WHERE NOT EXISTS (
  SELECT 1
  FROM User u
  WHERE u.email = seed.email
);

-- Set default password_hash for existing users that don't have one (password: "password")
UPDATE User SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' WHERE password_hash IS NULL OR password_hash = '';

-- Set default role for existing users that don't have one
UPDATE User SET role = 'customer' WHERE role IS NULL OR role = '';

-- Ensure admin, staff, and customer accounts exist with default password (password: "password")
INSERT IGNORE INTO User (first_name, last_name, email, phone, license_number, license_expiry, address, role, password_hash, created_at)
VALUES
('Admin', 'User', 'admin@mobilis.ph', '+63 900 000 0001', NULL, NULL, 'Mobilis HQ, Metro Manila', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01 00:00:00'),
('Staff', 'User', 'staff@mobilis.ph', '+63 900 000 0002', NULL, NULL, 'Mobilis HQ, Metro Manila', 'staff', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01 00:00:00'),
('Customer', 'User', 'customer@mobilis.ph', '+63 900 000 0003', NULL, NULL, 'Metro Manila, Philippines', 'customer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01 00:00:00');

-- Recreate vw_active_rentals view
CREATE OR REPLACE VIEW vw_active_rentals AS
  SELECT r.rental_id,
         CONCAT(u.first_name,' ',u.last_name) AS customer_name,
         v.plate_number, v.brand, v.model,
         r.pickup_date, r.return_date
  FROM Rental r
  JOIN User u ON r.user_id = u.user_id
  JOIN Vehicle  v ON r.vehicle_id  = v.vehicle_id
  WHERE r.status = 'active';

SELECT 'Customer table converted to User table successfully' AS Status;
