<?php

/**
 * Elpis Counselling Centre - Database Configuration
 *
 * Production-ready configuration.
 *
 * All secrets can be overridden via environment variables (recommended for
 * production). Copy `.env.example` to a private `.env` file (git-ignored) or
 * set the variables in your server environment / hosting panel.
 *
 *   DB_HOST, DB_NAME, DB_USER, DB_PASS
 *   APP_URL, ADMIN_EMAIL
 *   SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_ENCRYPTION
 *   SMTP_FROM_EMAIL, SMTP_FROM_NAME
 *   MPESA_CONSUMER_KEY, MPESA_CONSUMER_SECRET, MPESA_PASSKEY,
 *   MPESA_SHORTCODE, MPESA_ENVIRONMENT
 */

// Load a local .env file if present (development convenience). In production
// prefer real environment variables, which take precedence over .env values.
$__env = [];
$__envFile = __DIR__ . '/../.env';
if (is_file($__envFile)) {
    $__lines = file($__envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($__lines as $__line) {
        $__line = trim($__line);
        if ($__line === '' || $__line[0] === '#') {
            continue;
        }
        $__eq = strpos($__line, '=');
        if ($__eq !== false) {
            $__key = trim(substr($__line, 0, $__eq));
            $__val = trim(substr($__line, $__eq + 1));
            // Strip surrounding quotes
            if (strlen($__val) >= 2 && (($__val[0] === '"' && $__val[-1] === '"') || ($__val[0] === "'" && $__val[-1] === "'"))) {
                $__val = substr($__val, 1, -1);
            }
            $__env[$__key] = $__val;
        }
    }
}

// Helper to read config: real env first, then .env file, then default.
function cfgEnv($name, $default = null)
{
    global $__env;
    $real = getenv($name);
    if ($real !== false && $real !== '') {
        return $real;
    }
    if (isset($__env[$name]) && $__env[$name] !== '') {
        return $__env[$name];
    }
    return $default;
}

// Detect HTTPS once (used for secure cookies and URL building)
$__isHttps = (
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
);


// Database credentials (override via env)
define('DB_HOST', cfgEnv('DB_HOST', 'localhost'));
define('DB_NAME', cfgEnv('DB_NAME', 'siazxqeu_elpisCounselling'));
define('DB_USER', cfgEnv('DB_USER', 'siazxqeu_elpis')); 
define('DB_PASS', cfgEnv('DB_PASS', 'YAnXw@6^-ocT+y7'));
define('DB_CHARSET', cfgEnv('DB_CHARSET', 'utf8mb4'));

// Site configuration
define('SITE_NAME', cfgEnv('SITE_NAME', 'Elpis Counselling Centre'));
define('SITE_TAGLINE', cfgEnv('SITE_TAGLINE', 'Your Journey to Emotional Wellbeing Begins Here'));

// Base URL. Prefer an explicit APP_URL in production to avoid Host-header
// injection. Falls back to auto-detection for local development.
$__appUrl = cfgEnv('APP_URL', '');
if ($__appUrl !== '') {
    $__scheme = '';
    $__host = '';
    $__basePath = '';
    $__appUrl = rtrim($__appUrl, '/');
    $__parts = parse_url($__appUrl);
    $__basePath = isset($__parts['path']) ? $__parts['path'] : '';
    define('SITE_URL', $__appUrl);
} else {
    $protocol = $__isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
    $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $scriptDir = str_replace('\\', '/', __DIR__);
    $relativePath = str_replace($docRoot, '', $scriptDir);
    $basePath = dirname($relativePath);
    define('SITE_URL', $protocol . '://' . $host . $basePath);
}
define('ADMIN_EMAIL', cfgEnv('ADMIN_EMAIL', 'elpiscounselling24@gmail.com'));

// SMTP Configuration (defaults - overridable via admin Settings page / settings table,
// and via environment variables)
define('SMTP_HOST', cfgEnv('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', cfgEnv('SMTP_PORT', '587'));
define('SMTP_USERNAME', cfgEnv('SMTP_USERNAME', 'elpiscounselling24@gmail.com'));
define('SMTP_PASSWORD', cfgEnv('SMTP_PASSWORD', 'your_gmail_app_password')); // Use a Gmail App Password for 2FA accounts
define('SMTP_ENCRYPTION', cfgEnv('SMTP_ENCRYPTION', 'tls')); // 'tls', 'ssl', or ''
define('SMTP_FROM_EMAIL', cfgEnv('SMTP_FROM_EMAIL', 'elpiscounselling24@gmail.com'));
define('SMTP_FROM_NAME', cfgEnv('SMTP_FROM_NAME', 'Elpis Counselling Centre'));

// M-Pesa Daraja API Configuration (override via env in production)
define('MPESA_CONSUMER_KEY', cfgEnv('MPESA_CONSUMER_KEY', 'your_consumer_key'));
define('MPESA_CONSUMER_SECRET', cfgEnv('MPESA_CONSUMER_SECRET', 'your_consumer_secret'));
define('MPESA_PASSKEY', cfgEnv('MPESA_PASSKEY', 'your_passkey'));
define('MPESA_SHORTCODE', cfgEnv('MPESA_SHORTCODE', '174379')); // Sandbox shortcode
define('MPESA_ENVIRONMENT', cfgEnv('MPESA_ENVIRONMENT', 'sandbox')); // 'sandbox' or 'production'

// Session configuration
define('SESSION_TIMEOUT', 300); // Admin auto-logout after 5 minutes of inactivity (seconds)
define('COOKIE_SECURE', $__isHttps ? 1 : 0); // Secure cookies when on HTTPS

// Session hardening settings (must be set before session_start)
ini_set('session.cookie_httponly', 1);          // Prevent JavaScript access to session cookie
ini_set('session.use_only_cookies', 1);          // Only allow cookie-based sessions
ini_set('session.cookie_secure', COOKIE_SECURE); // 1 on HTTPS, 0 on HTTP
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
            // Log the detailed error server-side, never expose it to users.
            error_log('[DB] Connection failed: ' . $e->getMessage());
            http_response_code(500);
            die('A database connection could not be established. Please try again later.');
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
        // Unique session name to isolate this project's sessions from other
        // projects on the same server (prevents cross-project session sharing).
        session_name('ELPIS_SESSION');
        // Ensure session cookie parameters are applied
        session_set_cookie_params([
            'lifetime' => 0,                          // Expires when browser closes
            'path' => '/',
            'httponly' => true,                       // Not accessible via JS
            'secure' => COOKIE_SECURE,                // 1 on HTTPS
            'samesite' => 'Strict'                    // CSRF protection
        ]);
        session_start();
    }
}

// Emit security headers early (when not already sent).
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-XSS-Protection: 1; mode=block');
    // Only set HSTS over HTTPS to avoid breaking local HTTP dev.
    if ($__isHttps) {
        header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
    }
    // A permissive default CSP that allows inline styles/scripts used by this
    // codebase. Tighten this in production if possible.
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data: https:; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' data: https://fonts.gstatic.com; script-src 'self' 'unsafe-inline'; frame-src 'self' https://www.google.com; connect-src 'self'");
}
