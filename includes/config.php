<?php

/**
 * Elpis Counselling Centre - Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'elpis_counselling');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Elpis Counselling Centre');
define('SITE_TAGLINE', 'Your Journey to Emotional Wellbeing Begins Here');

// Auto-detect base URL dynamically (works regardless of subfolder)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$scriptDir = str_replace('\\', '/', __DIR__);
$relativePath = str_replace($docRoot, '', $scriptDir);
$basePath = dirname($relativePath);
define('SITE_URL', $protocol . '://' . $host . $basePath);
define('ADMIN_EMAIL', 'info@elpiscounselling.co.ke');

// M-Pesa Daraja API Configuration (Sandbox)
define('MPESA_CONSUMER_KEY', 'your_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_consumer_secret');
define('MPESA_PASSKEY', 'your_passkey');
define('MPESA_SHORTCODE', '174379'); // Sandbox shortcode
define('MPESA_ENVIRONMENT', 'sandbox'); // 'sandbox' or 'production'

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

/**
 * Get PDO database connection
 */
function getDB()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    return $pdo;
}

/**
 * Start session if not already started
 */
function startSession()
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}
