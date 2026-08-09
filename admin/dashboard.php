<?php

/**
 * Elpis Counselling Centre - Admin Dashboard
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$stats = getDashboardStats();

$db = getDB();
$recentAppointments = $db->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Handle therapy room overall visibility toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_therapy_visible'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $current = isTherapyRoomVisible();
        setSetting('therapy_room_visible', $current ? '0' : '1');
        header('Location: ' . SITE_URL . '/admin/dashboard.php?toggled=1');
        exit;
    }
}

// Handle per-room visibility toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_room_visible'])) {
    if (verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $roomKey = $_POST['room_key'] ?? '';
        $roomKeys = ['therapy_room_1_visible', 'therapy_room_2_visible'];
        if (in_array($roomKey, $roomKeys)) {
            $current = getSetting($roomKey, '1') == '1';
            setSetting($roomKey, $current ? '0' : '1');
        }
        header('Location: ' . SITE_URL . '/admin/dashboard.php?toggled=1');
        exit;
    }
}

$therapyVisible = isTherapyRoomVisible();
$room1Visible = isSpecificRoomVisible('Therapy Room 1');
$room2Visible = isSpecificRoomVisible('Therapy Room 2');

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

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #D7DDD9;
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(63, 81, 149, 0.1);
}

.stat-card .stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #3F5195;
}

.stat-card .stat-label {
    color: #999;
    font-size: 0.85rem;
    margin-top: 0.3rem;
}

.stat-card .stat-icon {
    color: #4FA08A;
    font-size: 1.5rem;
    margin-bottom: 0.5rem;
}

.table-container {
    background: #fff;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #D7DDD9;
    overflow-x: auto;
}

.table-container h3 {
    margin-bottom: 1rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
}

table th {
    text-align: left;
    padding: 0.8rem;
    border-bottom: 2px solid #D7DDD9;
    color: #3F5195;
    font-weight: 600;
}

table td {
    padding: 0.8rem;
    border-bottom: 1px solid #EAF4F1;
    color: #555;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.8rem;
    border-radius: 50px;
    font-size: 0.8rem;
    font-weight: 600;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-confirmed,
.status-contacted {
    background: #d4edda;
    color: #155724;
}

.status-completed {
    background: #cce5ff;
    color: #004085;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
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

    .admin-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .stats-grid {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }

    .stat-card {
        padding: 1rem;
    }

    .stat-card .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
<a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="active">Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/therapy_bookings.php">Therapy Room</a>
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
            <h1>Dashboard</h1>
            <span style="color:#999;font-size:0.9rem;">Welcome, <?php echo h($_SESSION['admin_username']); ?></span>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">&#9733;</div>
                <div class="stat-value"><?php echo $stats['total_services']; ?></div>
                <div class="stat-label">Active Services</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#128218;</div>
                <div class="stat-value"><?php echo $stats['total_articles']; ?></div>
                <div class="stat-label">Published Articles</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#128197;</div>
                <div class="stat-value"><?php echo $stats['total_events']; ?></div>
                <div class="stat-label">Upcoming Events</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#9888;</div>
                <div class="stat-value"><?php echo $stats['pending_appointments']; ?></div>
                <div class="stat-label">Pending Appointments</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#9733;</div>
                <div class="stat-value"><?php echo $stats['total_testimonials']; ?></div>
                <div class="stat-label">Approved Testimonials</div>
            </div>
<div class="stat-card">
                <div class="stat-icon">&#128719;</div>
                <div class="stat-value"><?php echo $stats['pending_therapy_bookings']; ?></div>
                <div class="stat-label">Pending Therapy Room</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">&#128248;</div>
                <div class="stat-value"><?php echo $stats['total_galleries']; ?></div>
                <div class="stat-label">Published Galleries</div>
            </div>
        </div>

        <!-- Therapy Room Availability Toggle -->
        <div class="table-container" style="margin-bottom:2rem;">
            <div>
                <h3 style="margin-bottom:0.3rem;">&#128719; Therapy Room Booking Availability</h3>
                <p style="color:#999;font-size:0.85rem;margin-bottom:1.5rem;">Control which therapy rooms visitors can see and book on the public site.</p>
            </div>

            <!-- Overall availability toggle -->
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;padding-bottom:1.25rem;border-bottom:1px solid #EAF4F1;margin-bottom:1.25rem;">
                <div>
                    <strong>Therapy Room Booking (Overall)</strong>
                    <p style="color:#999;font-size:0.85rem;margin-top:0.2rem;">Master switch — hides/disables the entire therapy room booking module.</p>
                </div>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="toggle_therapy_visible" value="1">
                    <button type="submit" class="btn <?php echo $therapyVisible ? 'btn-approve' : 'btn-secondary'; ?>"
                        style="<?php echo $therapyVisible ? '' : 'border:2px solid #E76F51;color:#E76F51;'; ?>">
                        <?php echo $therapyVisible ? '&#10004; Enabled — Click to Disable' : '&#10008; Disabled — Click to Enable'; ?>
                    </button>
                </form>
            </div>

            <!-- Room 1 toggle -->
            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;padding-bottom:1.25rem;border-bottom:1px solid #EAF4F1;margin-bottom:1.25rem;">
                <div>
                    <strong>Therapy Room 1</strong>
                    <p style="color:#999;font-size:0.85rem;margin-top:0.2rem;">Independent toggle for Room 1 visibility.</p>
                </div>
                <form method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="toggle_room_visible" value="1">
                    <input type="hidden" name="room_key" value="therapy_room_1_visible">
                    <button type="submit" class="btn <?php echo $room1Visible ? 'btn-approve' : 'btn-secondary'; ?>"
                        style="<?php echo $room1Visible ? '' : 'border:2px solid #E76F51;color:#E76F51;'; ?>">
                        <?php echo $room1Visible ? '&#10004; Visible — Click to Hide' : '&#10008; Hidden — Click to Show'; ?>
                    </button>
                </form>
            </div>

            <!-- Room 2 toggle -->
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                    <div>
                        <strong>Therapy Room 2</strong>
                        <p style="color:#999;font-size:0.85rem;margin-top:0.2rem;">Independent toggle for Room 2 visibility.</p>
                    </div>
                    <form method="POST">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="toggle_room_visible" value="1">
                        <input type="hidden" name="room_key" value="therapy_room_2_visible">
                        <button type="submit" class="btn <?php echo $room2Visible ? 'btn-approve' : 'btn-secondary'; ?>"
                            style="<?php echo $room2Visible ? '' : 'border:2px solid #E76F51;color:#E76F51;'; ?>">
                            <?php echo $room2Visible ? '&#10004; Visible — Click to Hide' : '&#10008; Hidden — Click to Show'; ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h3>Recent Appointments</h3>
            <?php if (count($recentAppointments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentAppointments as $appt): ?>
                    <tr>
                        <td><strong><?php echo h($appt['name']); ?></strong></td>
                        <td><?php echo h($appt['email']); ?></td>
                        <td><?php echo h($appt['phone']); ?></td>
                        <td><?php echo h($appt['service'] ?: 'General'); ?></td>
                        <td><?php echo formatDate($appt['created_at']); ?></td>
                        <td><span
                                class="status-badge status-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="color:#999;text-align:center;padding:2rem;">No appointments yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
</content>