-- ============================================================
-- Seed Admin User (if not already inserted via database.sql)
-- Password: admin123 (bcrypt hash)
-- ============================================================
INSERT INTO admin_users (username, password_hash)
VALUES (
        'admin',
        '$2y$10$3q89xjFLiu.SpW4lfT5IbOkXsWYNfrEbZY29f1KbCQCZTGNWfb2LS'
    ) ON DUPLICATE KEY
UPDATE username = username;