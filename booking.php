<?php

/**
 * Elpis Counselling Centre - Book a Session / Therapy Room
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$services = getServices();
$message = '';
$messageType = '';
$therapyMessage = '';
$therapyMessageType = '';
$therapyVisible = isTherapyRoomVisible();
$visibleRooms = getVisibleTherapyRooms();

// Handle therapy room booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_therapy_room'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $room = trim($_POST['room'] ?? 'Therapy Room 1');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $hours = (int)($_POST['hours'] ?? 1);

    if ($name && $email && $phone && $booking_date && $start_time) {
        try {
            $db = getDB();
            $hourlyRate = 500;
            $amount = $hourlyRate * $hours;
            // Calculate end time
            $startTimestamp = strtotime($start_time);
            $endTime = date('H:i', $startTimestamp + ($hours * 3600));

// Check for conflicts with approved bookings for the SAME room
            $stmt = $db->prepare("SELECT COUNT(*) FROM therapy_room_bookings
                WHERE status = 'approved' AND room = ? AND booking_date = ?
                AND start_time < ? AND ? < ADDTIME(end_time, '0:0:0')");
            $stmt->execute([$room, $booking_date, $endTime, $start_time]);
            $conflictCount = $stmt->fetchColumn();

            if ($conflictCount > 0) {
                $therapyMessage = "Sorry, that time slot is already booked for " . h($room) . ". Please select a different time or room.";
                $therapyMessageType = 'error';
            } else {
                // Insert booking
                $stmt = $db->prepare("INSERT INTO therapy_room_bookings
                    (room, name, email, phone, booking_date, start_time, end_time, hours, amount, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$room, $name, $email, $phone, $booking_date, $start_time, $endTime, $hours, $amount]);

                $emailSubject = "New Therapy Room Booking Request";
                $emailBody = "A new therapy room booking request has been received:\n\n";
                $emailBody .= "Name: $name\nEmail: $email\nPhone: $phone\n";
                $emailBody .= "Date: " . formatDate($booking_date) . "\n";
                $emailBody .= "Time: $start_time - $endTime\n";
                $emailBody .= "Hours: $hours\nAmount: KES " . number_format($amount, 2) . "\n";
sendEmail(ADMIN_EMAIL, $emailSubject, $emailBody);

                $therapyMessage = "Thank you, $name! Your booking request for $room has been received. Our team will confirm your booking shortly.";
                $therapyMessageType = 'success';
            }
        } catch (Exception $e) {
            $therapyMessage = "Sorry, something went wrong. Please try again or call us directly.";
            $therapyMessageType = 'error';
        }
    } else {
        $therapyMessage = "Please fill in all required fields.";
        $therapyMessageType = 'error';
    }
}

// Handle session booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_session'])) {
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

// Determine active tab
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'room' ? 'room' : 'session';

include __DIR__ . '/includes/header.php';
?>
<style>
.booking-tabs {
    display: flex;
    gap: 1rem;
    margin-bottom: 2rem;
    flex-wrap: wrap;
}

.booking-tab {
    flex: 1;
    min-width: 220px;
    padding: 1.2rem;
    border-radius: 12px;
    background: #EAF4F1;
    border: 2px solid transparent;
    cursor: pointer;
    text-align: left;
    font-family: inherit;
    transition: all 0.3s ease;
}

.booking-tab:hover {
    border-color: #4FA08A;
    transform: translateY(-2px);
}

.booking-tab.active {
    background: #3F5195;
    border-color: #3F5195;
}

.booking-tab.active .tab-title {
    color: #fff;
}

.booking-tab.active .tab-desc {
    color: rgba(255, 255, 255, 0.8);
}

.booking-tab .tab-title {
    font-weight: 700;
    font-size: 1.1rem;
    color: #3F5195;
    display: block;
    margin-bottom: 0.3rem;
}

.booking-tab .tab-desc {
    font-size: 0.8rem;
    color: #666;
    display: block;
}

.room-gallery {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2.5rem;
}

.room-card {
    background: #fff;
    border-radius: 15px;
    overflow: hidden;
    border: 1px solid #D7DDD9;
    transition: all 0.3s ease;
}

.room-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 30px rgba(63, 81, 149, 0.1);
}

.room-card img {
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.room-card-body {
    padding: 1.2rem;
}

.room-card-body h4 {
    color: #3F5195;
    margin-bottom: 0.5rem;
}

.room-card-body p {
    color: #666;
    font-size: 0.9rem;
    margin-bottom: 0.8rem;
}

.room-price {
    font-weight: 700;
    color: #E76F51;
    font-size: 1.1rem;
}

.calendar-section {
    background: #fff;
    border-radius: 15px;
    border: 1px solid #D7DDD9;
    padding: 2rem;
    margin-bottom: 2rem;
}

.calendar-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
    gap: 1rem;
}

.calendar-nav {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.calendar-nav button {
    background: #EAF4F1;
    border: none;
    color: #3F5195;
    width: 36px;
    height: 36px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 1.2rem;
    transition: all 0.3s ease;
}

.calendar-nav button:hover {
    background: #3F5195;
    color: #fff;
}

.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.5rem;
}

.calendar-day-header {
    text-align: center;
    font-weight: 600;
    color: #3F5195;
    font-size: 0.85rem;
    padding: 0.5rem;
}

.calendar-day {
    aspect-ratio: 1;
    border: 1px solid #D7DDD9;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.calendar-day:hover:not(.disabled) {
    background: #EAF4F1;
    border-color: #4FA08A;
}

.calendar-day.selected {
    background: #3F5195 !important;
    color: #fff !important;
    border-color: #3F5195 !important;
    font-weight: 700;
}

.calendar-day.today {
    border-color: #E76F51;
    font-weight: 700;
}

.calendar-day.disabled {
    opacity: 0.35;
    cursor: not-allowed;
    background: #eee;
}

.calendar-day.empty {
    border: none;
    cursor: default;
    background: transparent;
}

.time-slots {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 0.5rem;
    margin-top: 1rem;
}

.time-slot {
    padding: 0.6rem;
    border: 1px solid #D7DDD9;
    border-radius: 8px;
    text-align: center;
    cursor: pointer;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    background: #FAF8F2;
}

.time-slot:hover:not(.disabled) {
    background: #EAF4F1;
    border-color: #4FA08A;
}

.time-slot.selected {
    background: #4FA08A !important;
    color: #fff !important;
    border-color: #4FA08A !important;
    font-weight: 600;
}

.time-slot.disabled {
    opacity: 0.35;
    cursor: not-allowed;
    background: #eee;
    text-decoration: line-through;
}

.booking-summary {
    background: #EAF4F1;
    border-radius: 12px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.booking-summary .summary-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
    font-size: 0.95rem;
}

.booking-summary .summary-total {
    display: flex;
    justify-content: space-between;
    margin-top: 0.8rem;
    padding-top: 0.8rem;
    border-top: 1px solid #4FA08A;
    font-weight: 700;
    color: #3F5195;
    font-size: 1.1rem;
}

.hidden {
    display: none !important;
}

@media (max-width: 600px) {
    .room-gallery {
        grid-template-columns: 1fr;
    }

    .calendar-grid {
        gap: 0.3rem;
    }

    .calendar-day {
        font-size: 0.8rem;
    }
}
</style>

<section class="page-banner">
    <div class="container">
        <h1>Book a Session</h1>
        <p>Choose a counselling session or book our therapy room</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <!-- Tabs -->
        <div class="booking-tabs">
            <button class="booking-tab <?php echo $activeTab === 'session' ? 'active' : ''; ?>"
                onclick="location.href='<?php echo SITE_URL; ?>/booking.php'">
                <span class="tab-title">&#9993; Book a Session</span>
                <span class="tab-desc">Book a counselling session</span>
            </button>
            <button class="booking-tab <?php echo $activeTab === 'room' ? 'active' : ''; ?>"
                onclick="location.href='<?php echo SITE_URL; ?>/booking.php?tab=room'">
                <span class="tab-title">&#128241; Book Therapy Room</span>
                <span class="tab-desc">KES 500/hour</span>
            </button>
        </div>

        <!-- Session Booking Tab -->
        <div id="sessionTab" class="<?php echo $activeTab === 'session' ? '' : 'hidden'; ?>">
            <div class="split-duo">
                <div class="split-info">
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
                                <p style="color:#666;font-size:0.9rem;">0718674888 / 0708854435</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                            <span style="color:#4FA08A;font-size:1.3rem;">&#9993;</span>
                            <div>
                                <strong>Email Us</strong>
                                <p style="color:#666;font-size:0.9rem;">elpiscounselling24@gmail.com</p>
                            </div>
                        </div>
                        <div style="display:flex;gap:1rem;align-items:flex-start;margin-bottom:1.5rem;">
                            <span style="color:#4FA08A;font-size:1.3rem;">&#128172;</span>
                            <div>
                                <strong>WhatsApp</strong>
                                <p style="color:#666;font-size:0.9rem;">Join our WhatsApp group</p>
                                <a href="https://chat.whatsapp.com/Ctq78CoaOcr82hoDUkFoRX?s=cl&p=a&mlu=0"
                                    target="_blank" rel="noopener">chat.whatsapp.com</a>
                            </div>
                        </div>
                        <div style="display:flex;gap:1rem;align-items:flex-start;">
                            <span style="color:#4FA08A;font-size:1.3rem;">&#9906;</span>
                            <div>
                                <strong>Visit Us</strong>
                                <p style="color:#666;font-size:0.9rem;">Krishna Centre Building, 2nd Floor (Suite D-16), Westlands, Nairobi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="split-form form-section">
                    <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
                    <?php endif; ?>

                    <form method="POST" action="" data-validate>
                        <input type="hidden" name="book_session" value="1">
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

        <!-- Therapy Room Booking Tab -->
        <div id="roomTab" class="<?php echo $activeTab === 'room' ? '' : 'hidden'; ?>">
            <?php if (!$therapyVisible): ?>
            <div class="empty-state" style="text-align:center;padding:4rem 2rem;">
                <div class="icon">&#128719;</div>
                <h3 style="margin-bottom:0.5rem;">Therapy Room Booking Coming Soon</h3>
                <p>Our therapy room booking is currently unavailable. Please check back later or contact us directly.</p>
                <p style="margin-top:1rem;color:#999;">Call: 0718674888 / 0708854435</p>
            </div>
            <?php else: ?>
            <?php if ($therapyMessage): ?>
            <div class="alert alert-<?php echo $therapyMessageType; ?>"><?php echo h($therapyMessage); ?></div>
            <?php endif; ?>

<!-- Room Gallery (only visible rooms) -->
            <?php
            $roomMeta = [
                'Therapy Room 1' => ['img' => 'image1.jpeg', 'desc' => 'Our serene first therapy room, designed for comfort and privacy.'],
                'Therapy Room 2' => ['img' => 'image2.jpeg', 'desc' => 'Our spacious second therapy room, ideal for individual and couple sessions.'],
            ];
            ?>
            <div class="section-header" style="text-align:left;margin-bottom:2rem;">
                <p class="section-subtitle">Our Therapy Rooms</p>
                <h2>Choose a Comfortable Space</h2>
                <p>Book our private therapy rooms at KES 500 per hour. Select a room, date, time, and duration below.</p>
            </div>

            <?php if (count($visibleRooms) > 0): ?>
            <div class="room-gallery">
                <?php foreach ($visibleRooms as $roomName): $meta = $roomMeta[$roomName] ?? null; ?>
                <div class="room-card">
                    <img src="<?php echo SITE_URL; ?>/images/<?php echo h($meta['img'] ?? 'image1.jpeg'); ?>" alt="<?php echo h($roomName); ?>">
                    <div class="room-card-body">
                        <h4><?php echo h($roomName); ?></h4>
                        <p><?php echo h($meta['desc'] ?? 'Private therapy room, designed for comfort and privacy.'); ?></p>
                        <span class="room-price">KES 500 / hour</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state" style="text-align:center;padding:2rem;">
                <div class="icon">&#128719;</div>
                <h3 style="margin-bottom:0.5rem;">No Therapy Rooms Available</h3>
                <p>Our therapy rooms are currently unavailable. Please check back later or contact us directly.</p>
            </div>
            <?php endif; ?>

            <!-- Booking Form -->
            <div class="calendar-section">
                <form method="POST" action="" data-validate>
                    <input type="hidden" name="book_therapy_room" value="1">
                    <input type="hidden" name="booking_date" id="selected_date">
                    <input type="hidden" name="start_time" id="selected_time">

                    <?php if (count($visibleRooms) > 0): ?>
                    <div class="form-group" style="margin-bottom:2rem;">
                        <label for="room_select">Select Therapy Room *</label>
                        <select id="room_select" name="room" class="form-control" onchange="changeRoom()" required>
                            <?php foreach ($visibleRooms as $roomName): ?>
                            <option value="<?php echo h($roomName); ?>"><?php echo h($roomName); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>

                    <div class="form-row" style="margin-bottom:2rem;">
                        <div class="form-group">
                            <label for="room_name">Full Name *</label>
                            <input type="text" id="room_name" name="name" class="form-control" required
                                placeholder="Your full name">
                        </div>
                        <div class="form-group">
                            <label for="room_email">Email Address *</label>
                            <input type="email" id="room_email" name="email" class="form-control" required
                                placeholder="your@email.com">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom:2rem;">
                        <div class="form-group">
                            <label for="room_phone">Phone Number *</label>
                            <input type="tel" id="room_phone" name="phone" class="form-control" required
                                placeholder="07XX XXX XXX">
                        </div>
                        <div class="form-group">
                            <label for="room_hours">Duration (hours)</label>
                            <select id="room_hours" name="hours" class="form-control" onchange="updateSummary()">
                                <option value="1">1 hour (KES 500)</option>
                                <option value="2">2 hours (KES 1,000)</option>
                                <option value="3">3 hours (KES 1,500)</option>
                                <option value="4">4 hours (KES 2,000)</option>
                            </select>
                        </div>
                    </div>

                    <div class="calendar-header">
                        <h3>Select Date</h3>
                        <div class="calendar-nav">
                            <button type="button" onclick="changeMonth(-1)">&larr;</button>
                            <span id="calendarLabel" style="font-weight:700;color:#3F5195;min-width:120px;text-align:center;"></span>
                            <button type="button" onclick="changeMonth(1)">&rarr;</button>
                        </div>
                    </div>

                    <div class="calendar-grid" id="calendarGrid"></div>

                    <div id="timeSlotsSection" class="hidden" style="margin-top:2rem;">
                        <h3 style="margin-bottom:0.5rem;">Select Start Time</h3>
                        <p style="color:#999;font-size:0.85rem;margin-bottom:1rem;">Office hours: 8:00 AM - 6:00 PM</p>
                        <div class="time-slots" id="timeSlots"></div>
                    </div>

                    <div id="summarySection" class="booking-summary hidden">
                        <h4 style="margin-bottom:1rem;color:#3F5195;">Booking Summary</h4>
                        <div class="summary-row">
                            <span>Date</span>
                            <span id="summaryDate">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Time</span>
                            <span id="summaryTime">-</span>
                        </div>
                        <div class="summary-row">
                            <span>Hours</span>
                            <span id="summaryHours">-</span>
                        </div>
                        <div class="summary-total">
                            <span>Total</span>
                            <span id="summaryTotal">KES 0</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:1.5rem;">Submit Booking Request</button>
                </form>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php
// Pass approved bookings to JS for marking unavailable slots
$approvedBookings = getApprovedTherapyBookings();
$roomBlockedSlots = [];
foreach ($approvedBookings as $bk) {
    $broom = $bk['room'] ?: 'Therapy Room 1';
    $roomBlockedSlots[$broom][$bk['booking_date']][] = [
        'start' => $bk['start_time'],
        'end' => $bk['end_time']
    ];
}
?>

<script>
// Approved bookings passed from server (room => date => [[start, end], ...])
var roomBlockedSlots = <?php echo json_encode($roomBlockedSlots); ?>;
var selectedRoom = null;
var selectedDate = null;
var selectedTime = null;
var currentMonth = new Date();
currentMonth.setDate(1);

var daysOfWeek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
var officeStart = 8;  // 8 AM
var officeEnd = 18;   // 6 PM

function formatYMD(date) {
    var y = date.getFullYear();
    var m = ('0' + (date.getMonth() + 1)).slice(-2);
    var d = ('0' + date.getDate()).slice(-2);
    return y + '-' + m + '-' + d;
}

function formatDisplayDate(date) {
    return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
}

function isToday(date) {
    var today = new Date();
    return date.toDateString() === today.toDateString();
}

function isPastDate(date) {
    var today = new Date();
    today.setHours(0, 0, 0, 0);
    return date < today;
}

function buildMonthKey(date) {
    return formatYMD(date).slice(0, 7); // YYYY-MM
}

function renderCalendar() {
    var grid = document.getElementById('calendarGrid');
    var label = document.getElementById('calendarLabel');
    grid.innerHTML = '';

    var monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'];
    label.textContent = monthNames[currentMonth.getMonth()] + ' ' + currentMonth.getFullYear();

    // Day headers
    daysOfWeek.forEach(function(day) {
        var el = document.createElement('div');
        el.className = 'calendar-day-header';
        el.textContent = day;
        grid.appendChild(el);
    });

    var firstDay = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
    var startOffset = firstDay.getDay();
    var daysInMonth = new Date(currentMonth.getFullYear(), currentMonth.getMonth() + 1, 0).getDate();

    for (var i = 0; i < startOffset; i++) {
        var empty = document.createElement('div');
        empty.className = 'calendar-day empty';
        grid.appendChild(empty);
    }

    for (var d = 1; d <= daysInMonth; d++) {
        var cell = document.createElement('div');
        cell.className = 'calendar-day';
        cell.textContent = d;

        var dateObj = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), d);
        var dateKey = formatYMD(dateObj);

        if (isToday(dateObj)) cell.classList.add('today');
        if (dateKey === selectedDate) cell.classList.add('selected');

        if (isPastDate(dateObj)) {
            cell.classList.add('disabled');
        } else {
            cell.addEventListener('click', function(key, el) {
                return function() {
                    selectDate(key, el);
                };
            }(dateKey, cell));
        }

        grid.appendChild(cell);
    }
}

function selectDate(key, el) {
    selectedDate = key;
    selectedTime = null;

    // Update UI
    document.querySelectorAll('.calendar-day').forEach(function(d) {
        d.classList.remove('selected');
    });
    el.classList.add('selected');
    document.getElementById('selected_date').value = key;
    document.getElementById('summaryDate').textContent = formatDisplayDate(new Date(key + 'T00:00:00'));

    renderTimeSlots(key);
}

function changeRoom() {
    var roomSel = document.getElementById('room_select');
    if (roomSel) selectedRoom = roomSel.value;
    selectedDate = null;
    selectedTime = null;
    document.getElementById('selected_date').value = '';
    document.getElementById('selected_time').value = '';
    document.getElementById('timeSlotsSection').classList.add('hidden');
    document.getElementById('summarySection').classList.add('hidden');
    // Clear any selected calendar day
    document.querySelectorAll('.calendar-day').forEach(function(d) {
        d.classList.remove('selected');
    });
    renderCalendar();
}

function renderTimeSlots(dateKey) {
    var container = document.getElementById('timeSlots');
    var section = document.getElementById('timeSlotsSection');
    container.innerHTML = '';

    // Filter blocked slots by the currently selected room
    var roomKey = selectedRoom || (document.getElementById('room_select') ? document.getElementById('room_select').value : null);
    if (!roomKey) roomKey = 'Therapy Room 1';
    var roomSlots = roomBlockedSlots[roomKey] || {};
    var slots = roomSlots[dateKey] || [];
    var blocked = [];
    slots.forEach(function(b) {
        blocked.push([b.start, b.end]);
    });

    for (var h = officeStart; h < officeEnd; h++) {
        var timeStr = ('0' + h).slice(-2) + ':00';
        var isBlocked = false;

        blocked.forEach(function(b) {
            if (timeStr >= b[0] && timeStr < b[1]) {
                isBlocked = true;
            }
        });

        var slot = document.createElement('div');
        slot.className = 'time-slot' + (isBlocked ? ' disabled' : '');
        var hourName = (h % 12 === 0) ? 12 : h % 12;
        var ampm = h < 12 ? 'AM' : 'PM';
        slot.textContent = hourName + ':00 ' + ampm;
        slot.dataset.time = timeStr;

        if (isBlocked) {
            slot.title = 'Already booked';
        } else {
            slot.addEventListener('click', function() {
                selectTime(this);
            });
        }

        container.appendChild(slot);
    }

    section.classList.remove('hidden');
}

function selectTime(el) {
    selectedTime = el.dataset.time;
    document.querySelectorAll('.time-slot').forEach(function(t) {
        t.classList.remove('selected');
    });
    el.classList.add('selected');
    document.getElementById('selected_time').value = selectedTime;

    // Update summary
    var hourNum = parseInt(selectedTime.split(':')[0]);
    var hourName = formatTimeDisplay(selectedTime);
    var hours = parseInt(document.getElementById('room_hours').value);
    var endHour = hourNum + hours;
    var endName = formatTimeDisplay(('0' + endHour).slice(-2) + ':00');
    document.getElementById('summaryTime').textContent = hourName + ' - ' + endName;
    document.getElementById('summaryHours').textContent = hours + ' hour' + (hours > 1 ? 's' : '');
    document.getElementById('summaryTotal').textContent = 'KES ' + (500 * hours).toLocaleString();

    document.getElementById('summarySection').classList.remove('hidden');
}

function formatTimeDisplay(time) {
    var parts = time.split(':');
    var h = parseInt(parts[0]);
    var m = parts[1] || '00';
    var ampm = h < 12 ? 'AM' : 'PM';
    var hourName = (h % 12 === 0) ? 12 : h % 12;
    return hourName + ':' + m + ' ' + ampm;
}

function changeMonth(delta) {
    if (delta === -1) {
        // Prevent navigating before current month
        var now = new Date();
        var current = new Date(currentMonth.getFullYear(), currentMonth.getMonth(), 1);
        var nowFirst = new Date(now.getFullYear(), now.getMonth(), 1);
        if (current <= nowFirst) return;
    }
    currentMonth.setMonth(currentMonth.getMonth() + delta);
    selectedDate = null;
    selectedTime = null;
    document.getElementById('selected_date').value = '';
    document.getElementById('selected_time').value = '';
    document.getElementById('timeSlotsSection').classList.add('hidden');
    document.getElementById('summarySection').classList.add('hidden');
    renderCalendar();
}

function updateSummary() {
    if (selectedTime) {
        // Re-select the time to recalc summary
        var hourNum = parseInt(selectedTime.split(':')[0]);
        var hourName = formatTimeDisplay(selectedTime);
        var hours = parseInt(document.getElementById('room_hours').value);
        var endHour = hourNum + hours;
        var endName = formatTimeDisplay(('0' + endHour).slice(-2) + ':00');
        document.getElementById('summaryTime').textContent = hourName + ' - ' + endName;
        document.getElementById('summaryHours').textContent = hours + ' hour' + (hours > 1 ? 's' : '');
        document.getElementById('summaryTotal').textContent = 'KES ' + (500 * hours).toLocaleString();
    }
}

// Initialize
var roomSelInit = document.getElementById('room_select');
if (roomSelInit) {
    selectedRoom = roomSelInit.value;
}
renderCalendar();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

