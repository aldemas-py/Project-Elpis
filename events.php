<?php

/**
 * Elpis Counselling Centre - Events & Booking with M-Pesa
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$events = getUpcomingEvents(20);
$bookingMessage = '';
$bookingMessageType = '';

// Handle M-Pesa booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_event'])) {
    $event_id = (int)$_POST['event_id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if ($event_id && $name && $email && $phone) {
        // Get event details
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM events WHERE id = ? AND is_published = 1");
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        if ($event) {
            $amount = $event['price'];

            // Initiate M-Pesa payment
            $mpesaResponse = initiateMpesaPayment($phone, $amount, 'EVT-' . $event_id . '-' . time());

            if ($mpesaResponse['success']) {
                // Save booking
                $stmt = $db->prepare("INSERT INTO event_bookings (event_id, name, email, phone, mpesa_code, amount, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([
                    $event_id,
                    $name,
                    $email,
                    $phone,
                    $mpesaResponse['CheckoutRequestID'],
                    $amount
                ]);

                $bookingMessage = "Booking confirmed! " . $mpesaResponse['message'] . ". We'll send a confirmation to your email.";
                $bookingMessageType = 'success';
            } else {
                $bookingMessage = "Payment initiation failed. Please try again.";
                $bookingMessageType = 'error';
            }
        }
    } else {
        $bookingMessage = "Please fill in all required fields.";
        $bookingMessageType = 'error';
    }
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Events & Workshops</h1>
        <p>Join our free webinars, workshops, and training sessions</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Upcoming Events</p>
            <h2>Learn, Grow, and Connect</h2>
            <p>We regularly host free evidence-based webinars, in-person workshops, and corporate training sessions
                covering a wide range of mental health and wellbeing topics.</p>
        </div>

        <?php if ($bookingMessage): ?>
            <div class="alert alert-<?php echo $bookingMessageType; ?>"><?php echo h($bookingMessage); ?></div>
        <?php endif; ?>

        <?php if (count($events) > 0): ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card" id="event-<?php echo $event['id']; ?>">
                        <div class="event-card-header">
                            <div class="date"><?php echo formatDate($event['event_date']); ?></div>
                            <div style="font-size:0.85rem;color:#4FA08A;margin-top:0.3rem;">
                                <?php echo h($event['event_time'] ?? 'Time TBD'); ?></div>
                            <h3><?php echo h($event['title']); ?></h3>
                        </div>
                        <div class="event-card-body">
                            <p><?php echo h(truncateText($event['description'], 200)); ?></p>
                            <?php if ($event['venue']): ?>
                                <p style="font-size:0.85rem;color:#4FA08A;margin-top:1rem;">&#9906;
                                    <?php echo h($event['venue']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="event-card-footer">
                            <span class="event-price <?php echo $event['price'] == 0 ? 'free' : ''; ?>">
                                <?php echo $event['price'] > 0 ? 'KES ' . number_format($event['price'], 2) : 'Free Entry'; ?>
                            </span>
                            <button class="btn btn-primary btn-sm"
                                onclick="openBookingModal(<?php echo $event['id']; ?>, '<?php echo h(addslashes($event['title'])); ?>', <?php echo $event['price']; ?>)">Book
                                Now</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#128197;</div>
                <h3 style="margin-bottom:0.5rem;">No Upcoming Events</h3>
                <p>We're planning exciting events and workshops. Check back soon or subscribe to our newsletter for updates.
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Booking Modal -->
<div id="bookingModal"
    style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:2000;align-items:center;justify-content:center;">
    <div
        style="background:#FAF8F2;border-radius:15px;padding:2.5rem;max-width:500px;width:90%;max-height:90vh;overflow-y:auto;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;">
            <h3 style="color:#3F5195;">Book Event</h3>
            <button onclick="closeBookingModal()"
                style="background:none;border:none;font-size:1.5rem;cursor:pointer;color:#999;">&times;</button>
        </div>

        <p id="modalEventInfo" style="color:#4FA08A;font-weight:600;margin-bottom:1.5rem;"></p>

        <form method="POST" action="" data-validate>
            <input type="hidden" name="book_event" value="1">
            <input type="hidden" name="event_id" id="modalEventId">

            <div class="form-group">
                <label for="modalName">Full Name *</label>
                <input type="text" id="modalName" name="name" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="modalEmail">Email Address *</label>
                <input type="email" id="modalEmail" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="modalPhone">M-Pesa Phone Number *</label>
                <input type="tel" id="modalPhone" name="phone" class="form-control" required placeholder="07XX XXX XXX">
                <small style="color:#999;font-size:0.8rem;">Enter the M-Pesa registered number for payment</small>
            </div>

            <p id="modalPrice"
                style="text-align:center;font-size:1.2rem;font-weight:700;color:#3F5195;margin:1.5rem 0;"></p>

            <button type="submit" class="btn btn-primary" style="width:100%;">Proceed to Payment</button>
        </form>
    </div>
</div>

<script>
    function openBookingModal(eventId, eventTitle, price) {
        document.getElementById('modalEventId').value = eventId;
        document.getElementById('modalEventInfo').textContent = eventTitle;
        document.getElementById('modalPrice').innerHTML = price > 0 ? 'Amount: KES ' + price.toLocaleString() :
            '<span style="color:#4FA08A;">Free Event</span>';
        document.getElementById('bookingModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeBookingModal() {
        document.getElementById('bookingModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    // Close modal on outside click
    document.getElementById('bookingModal').addEventListener('click', function(e) {
        if (e.target === this) closeBookingModal();
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>