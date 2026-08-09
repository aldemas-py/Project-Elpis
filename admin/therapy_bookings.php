<?php

/**
 * Elpis Counselling Centre - Admin Therapy Room Bookings
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = 'success';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = (int)$_POST['booking_id'];
    $status = $_POST['status'];
    if (in_array($status, ['pending', 'approved', 'cancelled'])) {
        $stmt = $db->prepare("UPDATE therapy_room_bookings SET status = ? WHERE id = ?");
        $stmt->execute([$status, $bookingId]);
        $message = 'Booking status updated successfully.';
    }
}

// Handle manual scheduling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_booking'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $booking_date = trim($_POST['booking_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $hours = (int)($_POST['hours'] ?? 1);
    $hourlyRate = 500;
    $amount = $hourlyRate * $hours;
    $startTimestamp = strtotime($start_time);
    $endTime = date('H:i', $startTimestamp + ($hours * 3600));

    if ($name && $email && $phone && $booking_date && $start_time) {
        $stmt = $db->prepare("INSERT INTO therapy_room_bookings
            (name, email, phone, booking_date, start_time, end_time, hours, amount, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'approved')");
        $stmt->execute([$name, $email, $phone, $booking_date, $start_time, $endTime, $hours, $amount]);
        $message = 'Therapy room booking scheduled and approved successfully.';
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_booking'])) {
    $bookingId = (int)$_POST['booking_id'];
    $stmt = $db->prepare("DELETE FROM therapy_room_bookings WHERE id = ?");
    $stmt->execute([$bookingId]);
    $message = 'Booking deleted successfully.';
}

$bookings = $db->query("SELECT * FROM therapy_room_bookings ORDER BY booking_date DESC, start_time DESC")->fetchAll();
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
    min-width: 0;
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

.card {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #D7DDD9;
    margin-bottom: 2rem;
}

.card h3 {
    margin-bottom: 1rem;
}

.table-container {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #D7DDD9;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    min-width: 900px;
}

table th {
    text-align: left;
    padding: 0.8rem;
    border-bottom: 2px solid #D7DDD9;
    color: #3F5195;
    font-weight: 600;
    white-space: nowrap;
}

table td {
    padding: 0.8rem;
    border-bottom: 1px solid #EAF4F1;
    color: #555;
    vertical-align: middle;
}

table tbody tr:hover {
    background: #FAF8F2;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.8rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
    white-space: nowrap;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
}

.status-select {
    padding: 0.4rem 0.6rem;
    border: 1px solid #D7DDD9;
    border-radius: 6px;
    font-size: 0.8rem;
    font-family: inherit;
    background: #FAF8F2;
    cursor: pointer;
    transition: border-color 0.3s ease;
}

.status-select:focus {
    outline: none;
    border-color: #4FA08A;
}

.btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    font-family: inherit;
}

.btn-danger-sm {
    background: #f8d7da;
    color: #721c24;
}

.btn-danger-sm:hover {
    background: #dc3545;
    color: #fff;
}

.empty-state-sm {
    color: #999;
    text-align: center;
    padding: 2rem;
    font-size: 0.9rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.4rem;
    font-weight: 600;
    font-size: 0.9rem;
    color: #3F5195;
}

.form-control {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 2px solid #D7DDD9;
    border-radius: 8px;
    font-size: 0.9rem;
    font-family: inherit;
    transition: border-color 0.3s ease;
    background: #FAF8F2;
}

.form-control:focus {
    outline: none;
    border-color: #4FA08A;
    background: #fff;
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

@media (max-width: 600px) {
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
        <a href="<?php echo SITE_URL; ?>/admin/therapy_bookings.php" class="active">Therapy Room</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_gallery.php">Gallery</a>
<a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">Testimonials</a>
        <a href="<?php echo SITE_URL; ?>/admin/settings.php">Account &amp; Settings</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1>Therapy Room Bookings</h1>
            <span style="color:#999;font-size:0.9rem;">KES <?php echo number_format(500, 0); ?>/hour</span>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType === 'error' ? 'error' : 'success'; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <!-- Schedule Manually -->
        <div class="card">
            <h3>&#128203; Schedule a Booking Manually</h3>
            <form method="POST" action="">
                <input type="hidden" name="add_booking" value="1">
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="booking_date">Date *</label>
                        <input type="date" id="booking_date" name="booking_date" class="form-control" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="hours">Duration (hours)</label>
                        <select id="hours" name="hours" class="form-control">
                            <option value="1">1 hour</option>
                            <option value="2">2 hours</option>
                            <option value="3">3 hours</option>
                            <option value="4">4 hours</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-approve">Schedule &amp; Approve</button>
            </form>
        </div>

        <!-- Bookings Table -->
        <div class="table-container">
            <h3 style="margin-bottom:1rem;">All Therapy Room Bookings</h3>
            <?php if (count($bookings) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Hours</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $i => $bk): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo h($bk['name']); ?></strong></td>
                        <td><?php echo h($bk['email']); ?><br><small><?php echo h($bk['phone']); ?></small></td>
                        <td><?php echo formatDate($bk['booking_date']); ?></td>
                        <td><?php echo h($bk['start_time']); ?> - <?php echo h($bk['end_time'] ?: ''); ?></td>
                        <td><?php echo (int)$bk['hours']; ?> hr</td>
                        <td>KES <?php echo number_format($bk['amount'], 2); ?></td>
                        <td><span
                                class="status-badge status-<?php echo $bk['status']; ?>"><?php echo ucfirst($bk['status']); ?></span>
                        </td>
                        <td>
                            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?php echo $bk['id']; ?>">
                                    <select name="status" onchange="this.form.submit()" class="status-select">
                                        <option value="pending"
                                            <?php echo $bk['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="approved"
                                            <?php echo $bk['status'] == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="cancelled"
                                            <?php echo $bk['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                                <form method="POST" style="display:inline;"
                                    onsubmit="return confirm('Delete this booking?');">
                                    <input type="hidden" name="booking_id" value="<?php echo $bk['id']; ?>">
                                    <input type="hidden" name="delete_booking" value="1">
                                    <button type="submit" class="btn-sm btn-danger-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-state-sm">No therapy room bookings yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>

