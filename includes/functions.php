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
    return isset($_SESSION['admin_id']);
}

/**
 * Redirect if not admin
 */
function requireAdmin()
{
    if (!isAdminLoggedIn()) {
        header('Location: ' . SITE_URL . '/admin/index.php');
        exit;
    }
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
            <hr style='border: 1px solid #E4CF55;'>
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

    return $stats;
}
