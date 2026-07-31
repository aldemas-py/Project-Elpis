<?php

/**
 * Elpis Counselling Centre - Admin Dashboard
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$stats = getDashboardStats();

// Get recent appointments
$db = getDB();
$recentAppointments = $db->query("SELECT * FROM appointments ORDER BY created_at DESC LIMIT 5")->fetchAll();

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
    }

    .admin-sidebar a:hover,
    .admin-sidebar a.active {
        background: rgba(255, 255, 255, 0.1);
        color: #E4CF55;
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

    .status-cancelled {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php" class="active">&#9632; Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">&#9997; Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">&#128218; Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">&#128197; Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">&#9733; Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">&#8592; View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">&#128682; Logout</a>
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