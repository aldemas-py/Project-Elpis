<?php

/**
 * Elpis Counselling Centre - Admin Logout
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Determine redirect target: session-expired vs manual logout
$expired = (isset($_GET['expired']) && $_GET['expired'] == 1) ? '?expired=1' : '';

// Secure logout - destroys session and clears the session cookie
adminLogout($expired ? 'Your session has expired due to inactivity. Please log in again.' : 'You have been logged out successfully.');

header('Location: ' . SITE_URL . '/admin/index.php' . $expired);
exit;