<?php

/**
 * Elpis Counselling Centre - Admin Logout
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

startSession();
session_destroy();

header('Location: ' . SITE_URL . '/admin/index.php');
exit;
