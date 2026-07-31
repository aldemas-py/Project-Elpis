<?php

/**
 * Elpis Counselling Centre - Admin Appointments
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $appt_id = (int)$_POST['appointment_id'];
    $status = $_POST['status'];
    $stmt = $db->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->execute([$status, $appt_id]);
    $message = 'Appointment status updated successfully.';
}

$appointments = $db->query("SELECT * FROM appointments ORDER BY created_at DESC")->fetchAll();
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
    color: #E4CF55;
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

.empty-state-sm {
    color: #999;
    text-align: center;
    padding: 2rem;
    font-size: 0.9rem;
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

    .table-container {
        padding: 1rem;
        border-radius: 8px;
    }

    table {
        font-size: 0.8rem;
    }

    table th,
    table td {
        padding: 0.6rem;
    }

    .status-select {
        font-size: 0.75rem;
        padding: 0.3rem 0.4rem;
    }
}
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php" class="active">Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">Logout</a>
    </div>
    <div class="admin-content">
        <div class="admin-header">
            <h1>Appointments</h1>
        </div>
        <?php if ($message): ?>
        <div class="alert alert-success"><?php echo h($message); ?></div>
        <?php endif; ?>
        <div class="table-container">
            <?php if (count($appointments) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Service</th>
                        <th>Preferred Date</th>
                        <th>Message</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $i => $appt): ?>
                    <tr>
                        <td><?php echo $i + 1; ?></td>
                        <td><strong><?php echo h($appt['name']); ?></strong></td>
                        <td><?php echo h($appt['email']); ?><br><small><?php echo h($appt['phone']); ?></small></td>
                        <td><?php echo h($appt['service'] ?: '-'); ?></td>
                        <td><?php echo $appt['preferred_date'] ? formatDate($appt['preferred_date']) : '-'; ?></td>
                        <td><small><?php echo h(truncateText($appt['message'], 50)); ?></small></td>
                        <td><small><?php echo formatDate($appt['created_at'], 'M j, g:i A'); ?></small></td>
                        <td><span
                                class="status-badge status-<?php echo $appt['status']; ?>"><?php echo ucfirst($appt['status']); ?></span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select">
                                    <option value="pending"
                                        <?php echo $appt['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="contacted"
                                        <?php echo $appt['status'] == 'contacted' ? 'selected' : ''; ?>>Contacted
                                    </option>
                                    <option value="completed"
                                        <?php echo $appt['status'] == 'completed' ? 'selected' : ''; ?>>Completed
                                    </option>
                                    <option value="cancelled"
                                        <?php echo $appt['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled
                                    </option>
                                </select>
                                <input type="hidden" name="update_status" value="1">
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="empty-state-sm">No appointments received yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>