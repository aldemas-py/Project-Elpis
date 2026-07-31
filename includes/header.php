<?php

/**
 * Elpis Counselling Centre - Site Header
 */
require_once __DIR__ . '/config.php';
startSession();

// Get current page for active nav highlighting
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Elpis Counselling Centre - Nairobi-based mental health and psychosocial support organization. Professional counselling services for individuals, couples, families, and corporate teams.">
    <meta name="keywords" content="counselling, mental health, Nairobi, therapy, Elpis, Westlands, Kenya">
    <meta name="author" content="Elpis Counselling Centre">
    <title><?php echo SITE_NAME; ?> | Professional Counselling Services in Nairobi</title>

    <!-- Styles -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo SITE_URL; ?>/images/logo.jpeg">
</head>

<body>

    <!-- ============================================================
     NAVIGATION
     ============================================================ -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>/index.php" class="navbar-brand">
                <img src="<?php echo SITE_URL; ?>/images/logo.jpeg" alt="Elpis Counselling Centre Logo">
                <span>Elpis</span> Counselling Centre
            </a>

            <button class="nav-toggle" aria-label="Toggle navigation menu">
                <span></span>
                <span></span>
                <span></span>
            </button>

            <div class="nav-links">
                <a href="<?php echo SITE_URL; ?>/index.php"
                    class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a>
                <a href="<?php echo SITE_URL; ?>/about.php"
                    class="<?php echo $current_page == 'about.php' ? 'active' : ''; ?>">About Us</a>
                <a href="<?php echo SITE_URL; ?>/services.php"
                    class="<?php echo $current_page == 'services.php' ? 'active' : ''; ?>">Services</a>
                <a href="<?php echo SITE_URL; ?>/booking.php"
                    class="<?php echo $current_page == 'booking.php' ? 'active' : ''; ?>">Book Session</a>
                <a href="<?php echo SITE_URL; ?>/articles.php"
                    class="<?php echo $current_page == 'articles.php' ? 'active' : ''; ?>">Articles</a>
                <a href="<?php echo SITE_URL; ?>/events.php"
                    class="<?php echo $current_page == 'events.php' ? 'active' : ''; ?>">Events</a>
                <a href="<?php echo SITE_URL; ?>/contact.php"
                    class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a>
            </div>
        </div>
    </nav>
    <!-- ============================================================ -->