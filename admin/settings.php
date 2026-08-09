<?php

/**
 * Elpis Counselling Centre - Admin Settings (Email / Profile)
 * Simple, non-technical email settings page.
 * Emails are always sent to elpiscounselling24@gmail.com.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Security: CSRF check
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token mismatch. Please refresh the page and try again.';
        $messageType = 'error';
    } elseif (isset($_POST['save_profile'])) {
        $fromName = trim($_POST['from_name'] ?? '');
        $fromEmail = trim($_POST['from_email'] ?? '');

if ($fromEmail && filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            setSetting('smtp_from_name', $fromName ?: SITE_NAME);
            setSetting('smtp_from_email', $fromEmail);

            // SMTP server settings
            $smtpHost = trim($_POST['smtp_host'] ?? SMTP_HOST);
            $smtpPort = (int)($_POST['smtp_port'] ?? SMTP_PORT);
            $smtpUsername = trim($_POST['smtp_username'] ?? SMTP_USERNAME);
            $smtpPassword = trim($_POST['smtp_password'] ?? SMTP_PASSWORD);
            $smtpEncryption = trim($_POST['smtp_encryption'] ?? SMTP_ENCRYPTION);

            setSetting('smtp_host', $smtpHost);
            setSetting('smtp_port', (string) $smtpPort);
            setSetting('smtp_username', $smtpUsername);
            if ($smtpPassword !== '') {
                setSetting('smtp_password', $smtpPassword);
            }
            setSetting('smtp_encryption', $smtpEncryption);

            $message = 'Your email settings have been saved successfully.';
            $messageType = 'success';
        } else {
            $message = 'Please enter a valid email address.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['send_test'])) {
        // Send a simple test email to the admin inbox
        $result = sendEmail(ADMIN_EMAIL, 'Test Email - ' . SITE_NAME, "This is a test email from your " . SITE_NAME . " website. If you received this, your email system is working correctly!");
        if ($result) {
            $message = 'Test email sent successfully to ' . ADMIN_EMAIL . '. Please check your inbox.';
            $messageType = 'success';
        } else {
            $message = 'Test email could not be sent. Please check the email log for details.';
            $messageType = 'error';
        }
    }
}

$fromName = getSetting('smtp_from_name', SITE_NAME);
$fromEmail = getSetting('smtp_from_email', ADMIN_EMAIL);
$smtpHost = getSetting('smtp_host', SMTP_HOST);
$smtpPort = getSetting('smtp_port', SMTP_PORT);
$smtpUsername = getSetting('smtp_username', SMTP_USERNAME);
$smtpEncryption = getSetting('smtp_encryption', SMTP_ENCRYPTION);

include __DIR__ . '/../includes/header.php';
$isAdminPage = true;
?>
<style>
.admin-layout {
    display: grid;
    grid-template-columns: 250px 1fr;
    min-height: 100vh;
    padding-top: 70px;
}

.admin-sidebar {
    background: #263447;
    padding: 2rem 1rem;
    color: #fff;
    position: sticky;
    top: 70px;
    height: calc(100vh - 70px);
    overflow-y: auto;
}

.admin-sidebar h3 {
    color: #fff;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 1.5rem;
    padding: 0 1rem;
}

.admin-sidebar a {
    display: block;
    padding: 0.8rem 1rem;
    color: rgba(255, 255, 255, 0.7);
    border-radius: 8px;
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
}

.admin-sidebar a:hover,
.admin-sidebar a.active {
    background: rgba(255, 255, 255, 0.1);
    color: #E76F51;
}

.admin-content {
    padding: 2rem;
    background: #FAF8F2;
    min-height: 100vh;
}

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.admin-header h1 {
    font-size: 1.5rem;
}

.settings-card {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    border: 1px solid #D7DDD9;
    margin-bottom: 2rem;
}

.settings-card h3 {
    margin-bottom: 0.3rem;
    color: #3F5195;
}

.settings-card .card-desc {
    color: #999;
    font-size: 0.85rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.2rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 600;
    font-size: 0.9rem;
    color: #3F5195;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 2px solid #D7DDD9;
    border-radius: 10px;
    font-size: 0.95rem;
    font-family: inherit;
    transition: border-color 0.3s ease;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #4FA08A;
}

.alert {
    padding: 1rem 1.2rem;
    border-radius: 10px;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
}

.btn-primary {
    background: #4FA08A;
    color: #fff;
}

.btn-primary:hover {
    background: #3F5195;
}

.btn-secondary {
    background: #EAF4F1;
    color: #3F5195;
}

.btn-secondary:hover {
    background: #d9e9e4;
}

.hint {
    color: #999;
    font-size: 0.8rem;
    margin-top: 0.3rem;
}

.info-box {
    background: #EAF4F1;
    border-radius: 10px;
    padding: 1rem 1.2rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
    color: #3F5195;
}

@media (max-width: 992px) {
    .admin-layout {
        grid-template-columns: 1fr;
        padding-top: 60px;
    }

    .admin-sidebar {
        position: static;
        height: auto;
        padding: 1rem;
    }

    .admin-sidebar h3 {
        margin-bottom: 1rem;
    }

    .admin-sidebar a {
        display: inline-block;
        margin: 0.2rem 0.3rem;
        padding: 0.5rem 1rem;
    }

    .admin-content {
        padding: 1.5rem;
    }
}

@media (max-width: 480px) {
    .admin-content {
        padding: 1rem;
    }

    .form-row {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/therapy_bookings.php">Therapy Room</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_gallery.php">Gallery</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">Testimonials</a>
        <a href="<?php echo SITE_URL; ?>/admin/settings.php" class="active">Settings</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Settings</h1>
            <span style="color:#999;font-size:0.9rem;">Welcome, <?php echo h($_SESSION['admin_username']); ?></span>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <!-- Profile / From details -->
        <div class="settings-card">
            <h3>&#9993; Email Settings</h3>
            <p class="card-desc">Manage the name and email address used when sending notifications.</p>

            <div class="info-box">
                &#128231; All notifications (new bookings, contact messages, and inquiries) are sent to
                <strong><?php echo h(ADMIN_EMAIL); ?></strong> automatically.
            </div>

            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="from_name">Your Name / From Name</label>
                        <input type="text" id="from_name" name="from_name" class="form-control"
                            value="<?php echo h($fromName); ?>" placeholder="Elpis Counselling Centre">
                        <p class="hint">The name shown in the "From" field of emails.</p>
                    </div>
<div class="form-group">
                        <label for="from_email">Reply-to Email</label>
                        <input type="email" id="from_email" name="from_email" class="form-control"
                            value="<?php echo h($fromEmail); ?>" placeholder="you@example.com">
                        <p class="hint">Where replies to your emails should go.</p>
                    </div>
                </div>

                <hr style="border:none;border-top:1px solid #EAF4F1;margin:1.5rem 0;">

                <h4 style="color:#3F5195;margin-bottom:1rem;">&#128273; SMTP Server Settings</h4>
                <p style="color:#999;font-size:0.85rem;margin-bottom:1.5rem;">
                    Configure the outgoing mail server used to deliver notifications. For Gmail, use
                    <code>smtp.gmail.com</code>, port <code>587</code> with TLS and an App Password.
                </p>

                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_host">SMTP Host</label>
                        <input type="text" id="smtp_host" name="smtp_host" class="form-control"
                            value="<?php echo h($smtpHost); ?>" placeholder="smtp.gmail.com">
                    </div>
                    <div class="form-group">
                        <label for="smtp_port">SMTP Port</label>
                        <input type="number" id="smtp_port" name="smtp_port" class="form-control"
                            value="<?php echo h($smtpPort); ?>" placeholder="587">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="smtp_username">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username" class="form-control"
                            value="<?php echo h($smtpUsername); ?>" placeholder="you@gmail.com" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label for="smtp_password">SMTP Password / App Password</label>
                        <input type="password" id="smtp_password" name="smtp_password" class="form-control"
                            placeholder="Leave blank to keep current password" autocomplete="new-password">
                        <p class="hint">For Gmail with 2FA, use a 16-character App Password.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="smtp_encryption">Encryption</label>
                    <select id="smtp_encryption" name="smtp_encryption" class="form-control">
                        <option value="tls" <?php echo $smtpEncryption === 'tls' ? 'selected' : ''; ?>>TLS (recommended)</option>
                        <option value="ssl" <?php echo $smtpEncryption === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                        <option value="" <?php echo $smtpEncryption === '' ? 'selected' : ''; ?>>None</option>
                    </select>
                </div>

                <button type="submit" name="save_profile" value="1" class="btn btn-primary">Save Settings</button>
            </form>
        </div>

        <!-- Test Email -->
        <div class="settings-card">
            <h3>&#128227; Send a Test Email</h3>
            <p class="card-desc">Send a quick test email to verify your website can send notifications.</p>

            <form method="POST" action="">
                <?php echo csrfField(); ?>
                <button type="submit" name="send_test" value="1" class="btn btn-secondary">Send Test Email</button>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
