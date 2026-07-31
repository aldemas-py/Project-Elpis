<?php

/**
 * Elpis Counselling Centre - Setup Script
 * Run this once after importing the database to create the admin user.
 * 
 * Access: http://127.0.0.1/work_folder/Project-Elpis/setup.php
 */

require_once __DIR__ . '/includes/config.php';

echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'>
<title>Setup - Elpis Counselling Centre</title>
<style>
body { font-family: sans-serif; background: #FAF8F2; color: #263447; max-width: 600px; margin: 50px auto; padding: 2rem; }
.card { background: #fff; border-radius: 15px; padding: 2rem; border: 1px solid #D7DDD9; }
h1 { color: #3F5195; }
.success { color: #155724; background: #d4edda; padding: 1rem; border-radius: 10px; }
.error { color: #721c24; background: #f8d7da; padding: 1rem; border-radius: 10px; }
.btn { display: inline-block; padding: 0.8rem 2rem; background: #4FA08A; color: #fff; text-decoration: none; border-radius: 50px; margin-top: 1rem; }
</style></head><body>
<div class='card'>
<h1>🔧 Elpis Setup</h1>";

try {
    $db = getDB();

    // Create tables if they don't exist
    $db->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    // Check if admin exists
    $stmt = $db->query("SELECT COUNT(*) FROM admin_users WHERE username = 'admin'");
    $exists = $stmt->fetchColumn();

    if ($exists) {
        // Update password for existing admin
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE username = 'admin'");
        $stmt->execute([$hash]);
        echo "<div class='success'>✅ Admin user updated! Username: <strong>admin</strong>, Password: <strong>admin123</strong></div>";
    } else {
        // Create new admin
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $stmt = $db->prepare("INSERT INTO admin_users (username, password_hash) VALUES (?, ?)");
        $stmt->execute(['admin', $hash]);
        echo "<div class='success'>✅ Admin user created! Username: <strong>admin</strong>, Password: <strong>admin123</strong></div>";
    }

    echo "<p style='margin-top:1.5rem;'>Database tables are ready.</p>";
    echo "<a href='" . SITE_URL . "/admin/index.php' class='btn'>Go to Admin Login</a>";
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p>Make sure you have imported the database schema first via phpMyAdmin.</p>";
    echo "<p>Steps:<br>
    1. Open <a href='http://127.0.0.1/phpmyadmin/' target='_blank'>phpMyAdmin</a><br>
    2. Create a database named <strong>elpis_counselling</strong><br>
    3. Import the file <strong>sql/database.sql</strong><br>
    4. Refresh this page</p>";
}

echo "</div></body></html>";
