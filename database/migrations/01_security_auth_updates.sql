ALTER TABLE users 
ADD COLUMN failed_login_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER email_notifications,
ADD COLUMN lockout_until DATETIME NULL AFTER failed_login_attempts,
ADD COLUMN remember_token VARCHAR(255) NULL AFTER lockout_until,
ADD COLUMN reset_token VARCHAR(255) NULL AFTER remember_token,
ADD COLUMN reset_expires_at DATETIME NULL AFTER reset_token;
