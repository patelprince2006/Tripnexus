ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN verification_code VARCHAR(100);
ALTER TABLE users ADD COLUMN verification_code_expiry TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN email_verified_at TIMESTAMP NULL;
