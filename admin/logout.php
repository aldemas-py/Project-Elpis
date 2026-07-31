<?php

/**
 * Elpis Counselling Centre - Admin Logout
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Secure logout - destroys session and cookie
adminLogout('You have been logged out successfully.');

header('Location: ' . SITE_URL . '/admin/index.php');
exit;