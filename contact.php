<?php
/**
 * Elpis Counselling Centre - Contact Us
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $contact_message = trim($_POST['message'] ?? '');

    if ($name && $email && $contact_message) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO contacts (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $contact_message]);

            // Notify admin
            $emailSubject = "New Contact Form Message: " . ($subject ?: 'General Inquiry');
            $emailBody = "New message from $name ($email, $phone)\n\nSubject: $subject\n\nMessage:\n$contact_message";
            sendEmail(ADMIN_EMAIL, $emailSubject, $emailBody);

            $message = "Thank you, $name! Your message has been received. We'll get back to you within 24 hours.";
            $messageType = 'success';
        } catch (Exception $e) {
            $message = "Sorry, something went wrong. Please try again or call us directly.";
            $messageType = 'error';
        }
    } else {
        $message = "Please fill in all required fields.";
        $messageType = 'error';
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Contact Us</h1>
        <p>We're here to listen and support you</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1px 1.5fr;gap:3rem;align-items:start;">
            <!-- Contact Info -->
            <div>
                <p class="section-subtitle">Get in Touch</p>
                <h2>We'd Love to Hear from You</h2>
                <p style="margin-top:1rem;color:#555;font-size:1.05rem;">
                    Whether you have a question, need guidance, or are ready to start your counselling journey, our team
                    is here for you.
                </p>

                <div style="margin-top:2.5rem;">
                    <div class="info-card" style="margin-bottom:1rem;">
                        <div class="info-card-icon">&#9906;</div>
                        <h4>Visit Us</h4>
                        <p>Krishna Centre, 2nd Floor<br>Westlands, Nairobi</p>
                    </div>

                    <div class="info-card" style="margin-bottom:1rem;">
                        <div class="info-card-icon">&#9742;</div>
                        <h4>Call Us</h4>
                        <p>+254 700 000 000<br>+254 700 000 001</p>
                    </div>

                    <div class="info-card">
                        <div class="info-card-icon">&#9993;</div>
                        <h4>Email Us</h4>
                        <p>info@elpiscounselling.co.ke</p>
                    </div>
                </div>

                <div style="margin-top:2rem;">
                    <h4 style="margin-bottom:1rem;">Office Hours</h4>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;font-size:0.9rem;color:#555;">
                        <span>Monday - Friday</span>
                        <span>8:00 AM - 6:00 PM</span>
                        <span>Saturday</span>
                        <span>9:00 AM - 1:00 PM</span>
                        <span>Sunday</span>
                        <span style="color:#999;">Closed</span>
                    </div>
                </div>
            </div>

            <!-- Vertical Divider -->
            <div style="background:#D7DDD9;height:100%;min-height:400px;width:1px;display:block;"></div>

            <!-- Contact Form -->
            <div class="form-section">
                <h3 style="margin-bottom:1.5rem;">Send Us a Message</h3>

                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="subject" name="subject" class="form-control"
                                placeholder="e.g., Counselling inquiry">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-control" required
                            placeholder="How can we help you?"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Send Message</button>
                </form>
            </div>
        </div>

        <!-- Divider on mobile -->
        <hr style="display:none;border:none;border-top:1px solid #D7DDD9;margin:2rem 0;" class="mobile-divider">
    </div>
</section>

<!-- Location Map -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Find Us</p>
            <h2>Visit Our Westlands Clinic</h2>
            <p>Krishna Centre, 2nd Floor — conveniently located in Nairobi's premier business district</p>
        </div>

        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.808877621373!2d36.81354331475398!3d-1.264664999073833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1724f6b1e1e1%3A0x1b1c1b1c1b1c1b1c!2sWestlands%2C%20Nairobi!5e0!3m2!1sen!2ske!4v1"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                title="Elpis Counselling Centre at Krishna Centre, Westlands, Nairobi" allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<style>
@media (max-width: 768px) {
    section.section-white .container>div[style*="grid-template-columns: 1fr 1px 1.5fr;"] {
        grid-template-columns: 1fr !important;
        gap: 2rem !important;
    }

    .mobile-divider {
        display: block !important;
    }

    div[style*="min-height: 400px;"][style*="width: 1px;"] {
        display: none !important;
    }
}
</style>

<?php include __DIR__ . '/includes/footer.php'; ?>