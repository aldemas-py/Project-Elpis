<?php
/**
 * Elpis Counselling Centre - Book a Session (Inquiry Form)
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$services = getServices();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $service = trim($_POST['service'] ?? '');
    $preferred_date = trim($_POST['preferred_date'] ?? '');
    $message_text = trim($_POST['message'] ?? '');

    if ($name && $email && $phone) {
        try {
            $db = getDB();
            $stmt = $db->prepare("INSERT INTO appointments (name, email, phone, service, preferred_date, message) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $service, $preferred_date ?: null, $message_text]);

            // Send notification email to admin
            $subject = "New Booking Inquiry from " . $name;
            $emailMessage = "A new session booking inquiry has been received:\n\n";
            $emailMessage .= "Name: $name\nEmail: $email\nPhone: $phone\nService: $service\n";
            $emailMessage .= "Preferred Date: " . ($preferred_date ?: 'Not specified') . "\n";
            $emailMessage .= "Message: $message_text\n";
            sendEmail(ADMIN_EMAIL, $subject, $emailMessage);

            $message = "Thank you, $name! Your booking inquiry has been received. Our team will contact you within 24 hours to confirm your session.";
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
        <h1>Book a Session</h1>
        <p>Take the first step — reach out for a confidential consultation</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div style="display:grid;grid-template-columns:1fr 1.5fr;gap:3rem;align-items:start;">
            <!-- Info Side -->
            <div>
                <p class="section-subtitle">Get Started</p>
                <h2>Ready to Begin Your Journey?</h2>
                <p style="margin-top:1rem;color:#555;font-size:1.05rem;line-height:1.8;">
                    Fill out the inquiry form and our team will reach out to you within 24 hours to schedule your
                    confidential session. All information is kept strictly confidential.
                </p>

                <div style="margin-top:2rem;">
                    <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                        <span style="color:#4FA08A;font-size:1.3rem;">&#9742;</span>
                        <div>
                            <strong>Call Us</strong>
                            <p style="color:#666;font-size:0.9rem;">+254 700 000 000</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                        <span style="color:#4FA08A;font-size:1.3rem;">&#9993;</span>
                        <div>
                            <strong>Email Us</strong>
                            <p style="color:#666;font-size:0.9rem;">info@elpiscounselling.co.ke</p>
                        </div>
                    </div>
                    <div style="display:flex;gap:1rem;align-items:flex-start;">
                        <span style="color:#4FA08A;font-size:1.3rem;">&#9906;</span>
                        <div>
                            <strong>Visit Us</strong>
                            <p style="color:#666;font-size:0.9rem;">Krishna Centre, 2nd Floor, Westlands, Nairobi</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="form-section">
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
                <?php endif; ?>

                <form method="POST" action="" data-validate>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" class="form-control" required
                                placeholder="Your full name">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" class="form-control" required
                                placeholder="+254 7XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label for="service">Service Interested In</label>
                            <select id="service" name="service" class="form-control">
                                <option value="">Select a service...</option>
                                <?php foreach ($services as $svc): ?>
                                <option value="<?php echo h($svc['title']); ?>"><?php echo h($svc['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="preferred_date">Preferred Date</label>
                        <input type="date" id="preferred_date" name="preferred_date" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" class="form-control"
                            placeholder="Tell us a bit about what you're going through and how we can help..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;">Submit Inquiry</button>

                    <p style="text-align:center;margin-top:1rem;font-size:0.8rem;color:#999;">
                        Your information is kept strictly confidential. By submitting, you agree to our privacy policy.
                    </p>
                </form>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>