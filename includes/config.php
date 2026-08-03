<?php

/**
 * Elpis Counselling Centre - Database Configuration
 */

// Database credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'njengas2_elpis_counselling');
define('DB_USER', 'njengas2_njenga');
define('DB_PASS', '4t]3oyUN;Y52lE');
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
define('SESSION_TIMEOUT', 300); // Admin auto-logout after 5 minutes of inactivity (seconds)

// Session hardening settings (must be set before session_start)
ini_set('session.cookie_httponly', 1);          // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1);          // Only allow cookie-based sessions
ini_set('session.cookie_secure', 0);             // Set to 1 in production with HTTPS
ini_set('session.cookie_lifetime', 0);           // 0 = session cookie: expires when browser closes
ini_set('session.use_strict_mode', 1);           // Reject uninitialized session IDs (anti-fixation)
ini_set('session.cookie_samesite', 'Strict');    // Mitigate CSRF
ini_set('session.gc_maxlifetime', 1800);         // Server-side garbage collection (30 min)

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
        // Ensure session cookie parameters are applied
        session_set_cookie_params([
            'lifetime' => 0,                          // Expires when browser closes
            'path' => '/',
            'httponly' => true,                       // Not accessible via JS
            'secure' => false,                        // Set to true in production with HTTPS
            'samesite' => 'Strict'                    // CSRF protection
        ]);
        session_start();
    }
}