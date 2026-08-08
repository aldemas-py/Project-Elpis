<?php

/**
 * Elpis Counselling Centre - Helper Functions
 */

require_once __DIR__ . '/config.php';

/**
 * Sanitize output for HTML
 */
function h($str)
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a URL-friendly slug
 */
function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

/**
 * Check if admin is logged in
 */
function isAdminLoggedIn()
{
    startSession();

    if (!isset($_SESSION['admin_id'])) {
        return false;
    }

    // Check for session timeout (5 minutes of inactivity)
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > SESSION_TIMEOUT) {
            adminLogout('Session expired due to inactivity. Please log in again.');
            return false;
        }
    }

    // Update last activity timestamp
    $_SESSION['last_activity'] = time();

    return true;
}

/**
 * Redirect if not admin
 */
function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/index.php?expired=1');
        exit;
    }
}

/**
 * Complete admin logout - destroys session securely
 */
function adminLogout($redirectMessage = '')
{
    $cookieName = session_name();

    if (session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        session_unset();
        session_destroy();
        session_write_close();
    }

    if (isset($_COOKIE[$cookieName]) || ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            $cookieName,
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
        unset($_COOKIE[$cookieName]);
    }

    if ($redirectMessage) {
        startSession();
        $_SESSION = [];
        $_SESSION['logout_message'] = $redirectMessage;
        session_write_close();
    }

    return true;
}

/**
 * Format date nicely
 */
function formatDate($date, $format = 'F j, Y')
{
    return date($format, strtotime($date));
}

/**
 * Truncate text
 */
function truncateText($text, $length = 150)
{
    if (strlen($text) <= $length) return $text;
    return substr($text, 0, $length) . '...';
}

/**
 * Get all published services
 */
function getServices()
{
    $db = getDB();
    $stmt = $db->query("SELECT * FROM services WHERE is_active = 1 ORDER BY display_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get published articles
 */
function getArticles($limit = 10, $offset = 0)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM articles WHERE is_published = 1 ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->execute([$limit, $offset]);
    return $stmt->fetchAll();
}

/**
 * Get article by slug
 */
function getArticleBySlug($slug)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM articles WHERE slug = ? AND is_published = 1 LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetch();
}

/**
 * Get upcoming events
 */
function getUpcomingEvents($limit = 10)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM events WHERE is_published = 1 AND event_date >= CURDATE() ORDER BY event_date ASC LIMIT ?");
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

/**
 * Get approved testimonials
 */
function getTestimonials()
{
    $db = getDB();
    $stmt = $db->query("SELECT * FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC");
    return $stmt->fetchAll();
}

/**
 * Send email (basic mail function)
 */
function sendEmail($to, $subject, $message)
{
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . SITE_NAME . " <" . ADMIN_EMAIL . ">\r\n";

    $htmlMessage = "
    <html>
    <body style='font-family: Arial, sans-serif; color: #263447;'>
        <div style='max-width: 600px; margin: 0 auto; background: #FAF8F2; padding: 30px;'>
            <h2 style='color: #3F5195;'>" . SITE_NAME . "</h2>
            <hr style='border: 1px solid #E76F51;'>
            <div style='padding: 20px 0;'>" . nl2br($message) . "</div>
            <hr style='border: 1px solid #D7DDD9;'>
            <p style='color: #4FA08A; font-size: 12px;'>
                Krishna Centre, 2nd Floor, Westlands, Nairobi<br>
                " . ADMIN_EMAIL . "
            </p>
        </div>
    </body>
    </html>";

    return mail($to, $subject, $htmlMessage, $headers);
}

/**
 * M-Pesa STK Push Simulation
 * In production, this would call the Daraja API
 */
function initiateMpesaPayment($phone, $amount, $account_ref)
{
    // Format phone to 254XXXXXXXXX
    $phone = preg_replace('/^0/', '254', $phone);
    $phone = preg_replace('/^\+/', '', $phone);

    // Simulated response for sandbox
    // In production: cURL to Daraja API
    return [
        'success' => true,
        'message' => 'STK Push sent to ' . $phone . ' for KES ' . number_format($amount, 2),
        'MerchantRequestID' => 'SIMU-' . time(),
        'CheckoutRequestID' => 'SIMU-' . uniqid(),
        'ResponseCode' => '0',
        'ResponseDescription' => 'Success. Request accepted for processing'
    ];
}

/**
 * Check M-Pesa payment status (simulated)
 */
function checkMpesaStatus($checkoutRequestID)
{
    // In production: query Daraga API status
    return 'success'; // simulated
}

/**
 * Upload an image file and return the filename
 */
function uploadImage($file, $existingFile = null)
{
    $uploadDir = __DIR__ . '/../uploads/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    if (empty($file) || !isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        return $existingFile;
    }

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize = 5 * 1024 * 1024;

    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type. Allowed: JPG, PNG, GIF, WEBP');
    }

    if ($file['size'] > $maxSize) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '_' . time() . '.' . strtolower($ext);
    $destPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $destPath)) {
        if ($existingFile && file_exists($uploadDir . $existingFile)) {
            unlink($uploadDir . $existingFile);
        }
        return $filename;
    }

    throw new Exception('Failed to upload file.');
}

/**
 * Delete an uploaded image
 */
function deleteImage($filename)
{
    if ($filename) {
        $path = __DIR__ . '/../uploads/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

/**
 * Get all services (including inactive for admin)
 */
function getAllServices()
{
    $db = getDB();
    $stmt = $db->query("SELECT * FROM services ORDER BY display_order ASC");
    return $stmt->fetchAll();
}

/**
 * Get dashboard stats
 */
function getDashboardStats()
{
    $db = getDB();
    $stats = [];

    $stats['total_services'] = $db->query("SELECT COUNT(*) FROM services WHERE is_active = 1")->fetchColumn();
    $stats['total_articles'] = $db->query("SELECT COUNT(*) FROM articles WHERE is_published = 1")->fetchColumn();
    $stats['total_events'] = $db->query("SELECT COUNT(*) FROM events WHERE is_published = 1")->fetchColumn();
    $stats['pending_appointments'] = $db->query("SELECT COUNT(*) FROM appointments WHERE status = 'pending'")->fetchColumn();
    $stats['total_testimonials'] = $db->query("SELECT COUNT(*) FROM testimonials WHERE is_approved = 1")->fetchColumn();
$stats['pending_therapy_bookings'] = $db->query("SELECT COUNT(*) FROM therapy_room_bookings WHERE status = 'pending'")->fetchColumn();
    $stats['total_galleries'] = $db->query("SELECT COUNT(*) FROM gallery_events WHERE is_published = 1")->fetchColumn();

    return $stats;
}

/**
 * Get a setting value from the settings table
 */
function getSetting($key, $default = null)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $value = $stmt->fetchColumn();
    return $value === false ? $default : $value;
}

/**
 * Set a setting value in the settings table
 */
function setSetting($key, $value)
{
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    return $stmt->execute([$key, $value]);
}

/**
 * Check if therapy room booking availability is visible to the public
 */
function isTherapyRoomVisible()
{
    return getSetting('therapy_room_visible', '1') == '1';
}

/**
 * Check if a specific therapy room is visible to the public
 */
function isSpecificRoomVisible($room)
{
    $keyMap = [
        'Therapy Room 1' => 'therapy_room_1_visible',
        'Therapy Room 2' => 'therapy_room_2_visible',
    ];
    $key = $keyMap[$room] ?? null;
    if ($key === null) {
        return isTherapyRoomVisible();
    }
    return getSetting($key, '1') == '1';
}

/**
 * Get the list of therapy rooms that are visible to the public
 */
function getVisibleTherapyRooms()
{
    $rooms = ['Therapy Room 1', 'Therapy Room 2'];
    $visible = [];
    foreach ($rooms as $room) {
        if (isSpecificRoomVisible($room)) {
            $visible[] = $room;
        }
    }
    return $visible;
}

/**
 * Get approved therapy room bookings (optionally filtered by room)
 */
function getApprovedTherapyBookings($room = null)
{
    $db = getDB();
    if ($room) {
        $stmt = $db->prepare("SELECT * FROM therapy_room_bookings WHERE status = 'approved' AND room = ? ORDER BY booking_date ASC, start_time ASC");
        $stmt->execute([$room]);
        return $stmt->fetchAll();
    }
    $stmt = $db->query("SELECT * FROM therapy_room_bookings WHERE status = 'approved' ORDER BY booking_date ASC, start_time ASC");
    return $stmt->fetchAll();
}

/**
 * Get all therapy room bookings (for admin)
 */
function getAllTherapyBookings()
{
    $db = getDB();
    $stmt = $db->query("SELECT * FROM therapy_room_bookings ORDER BY booking_date DESC, start_time DESC");
    return $stmt->fetchAll();
}

/**
 * Get published gallery events
 */
function getGalleryEvents($limit = 0)
{
    $db = getDB();
    $sql = "SELECT * FROM gallery_events WHERE is_published = 1 ORDER BY event_date DESC, created_at DESC";
    if ($limit > 0) {
        $sql .= " LIMIT " . (int)$limit;
    }
    return $db->query($sql)->fetchAll();
}

/**
 * Get all gallery events (including unpublished, for admin)
 */
function getAllGalleryEvents()
{
    $db = getDB();
    return $db->query("SELECT * FROM gallery_events ORDER BY event_date DESC, created_at DESC")->fetchAll();
}

/**
 * Get a single gallery event by ID
 */
function getGalleryEventById($id)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM gallery_events WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get images for a gallery event
 */
function getGalleryImages($galleryEventId)
{
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM gallery_images WHERE gallery_event_id = ? ORDER BY display_order ASC, id ASC");
    $stmt->execute([$galleryEventId]);
    return $stmt->fetchAll();
}
