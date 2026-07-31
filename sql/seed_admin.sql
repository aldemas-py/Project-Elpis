-- ============================================================
-- Seed Admin User (if not already inserted via database.sql)
-- Password: admin123 (bcrypt hash)
-- ============================================================
INSERT INTO admin_users (username, password_hash)
VALUES (
        'admin',
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    ) ON DUPLICATE KEY
UPDATE username = username;