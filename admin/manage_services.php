<?php

/**
 * Elpis Counselling Centre - Admin Services Management
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_service'])) {
    $id = (int)($_POST['service_id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $icon = trim($_POST['icon']);
    $display_order = (int)($_POST['display_order'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if ($title) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE services SET title=?, description=?, icon=?, display_order=?, is_active=? WHERE id=?");
            $stmt->execute([$title, $description, $icon, $display_order, $is_active, $id]);
            $message = 'Service updated successfully.';
        } else {
            $stmt = $db->prepare("INSERT INTO services (title, description, icon, display_order, is_active) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $description, $icon, $display_order, $is_active]);
            $message = 'Service created successfully.';
        }
        $messageType = 'success';
    } else {
        $message = 'Title is required.';
        $messageType = 'error';
    }
}

// Handle Toggle Active via POST (CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_service'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token mismatch. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $id = (int)$_POST['service_id'];
        $stmt = $db->prepare("UPDATE services SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Service status toggled.';
        $messageType = 'success';
    }
}

// Handle Delete via POST (CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token mismatch. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $id = (int)$_POST['service_id'];
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Service deleted successfully.';
        $messageType = 'success';
    }
}

// Get edit data
$editService = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$id]);
    $editService = $stmt->fetch();
}

$services = getAllServices();
?>
<?php include __DIR__ . '/../includes/header.php';
$isAdminPage = true; ?>
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
    }

    .admin-header h1 {
        font-size: 1.5rem;
    }

    .form-container {
        background: #fff;
        border-radius: 12px;
        padding: 2rem;
        border: 1px solid #D7DDD9;
        margin-bottom: 2rem;
    }

    .table-container {
        background: #fff;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #D7DDD9;
        overflow-x: auto;
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

    .btn-danger {
        background: #dc3545;
        color: #fff;
    }

    .btn-danger:hover {
        background: #c82333;
    }

    .btn-toggle {
        background: #4FA08A;
        color: #fff;
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
    }
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">&#9632; Dashboard</a>
<a href="<?php echo SITE_URL; ?>/admin/appointments.php">&#9997; Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/therapy_bookings.php">&#128719; Therapy Room</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php" class="active">&#9733; Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">&#128218; Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">&#128197; Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_gallery.php">&#128248; Gallery</a>
<a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">&#9733; Testimonials</a>
        <a href="<?php echo SITE_URL; ?>/admin/settings.php">&#128100; Account &amp; Settings</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">&#8592; View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">&#128682; Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $editService ? 'Edit Service' : 'Manage Services'; ?></h1>
            <a href="?new=1" class="btn btn-primary btn-sm">+ New Service</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($editService || isset($_GET['new'])): ?>
            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" name="service_id" value="<?php echo $editService['id'] ?? 0; ?>">

                    <div class="form-group">
                        <label for="title">Service Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required
                            value="<?php echo h($editService['title'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="icon">Icon (emoji or name)</label>
                            <input type="text" id="icon" name="icon" class="form-control"
                                value="<?php echo h($editService['icon'] ?? 'heart'); ?>"
                                placeholder="e.g., heart, briefcase, shield">
                        </div>
                        <div class="form-group">
                            <label for="display_order">Display Order</label>
                            <input type="number" id="display_order" name="display_order" class="form-control"
                                value="<?php echo $editService['display_order'] ?? 0; ?>">
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control"
                                rows="4"><?php echo h($editService['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_active" value="1"
                                    <?php echo ($editService && $editService['is_active']) ? 'checked' : ''; ?>>
                                Active (visible on website)
                            </label>
                        </div>

                        <div style="display:flex;gap:1rem;">
                            <button type="submit" name="save_service" class="btn btn-primary">Save Service</button>
                            <a href="<?php echo SITE_URL; ?>/admin/manage_services.php" class="btn btn-secondary">Cancel</a>
                        </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>All Services</h3>
            <?php if (count($services) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $service): ?>
                            <tr style="<?php echo !$service['is_active'] ? 'opacity:0.6;' : ''; ?>">
                                <td><?php echo $service['display_order']; ?></td>
                                <td><strong><?php echo h($service['title']); ?></strong></td>
                                <td><small><?php echo h(truncateText($service['description'], 60)); ?></small></td>
                                <td>
                                    <?php echo $service['is_active'] ? '<span style="color:#4FA08A;">Active</span>' : '<span style="color:#999;">Inactive</span>'; ?>
                                </td>
<td>
                                    <form method="POST" style="display:inline-block;">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="toggle_service" class="btn btn-sm btn-toggle"
                                            style="padding:0.3rem 0.8rem;font-size:0.8rem;">
                                            <?php echo $service['is_active'] ? 'Deactivate' : 'Activate'; ?>
                                        </button>
                                    </form>
                                    <a href="?edit=<?php echo $service['id']; ?>" class="btn btn-sm btn-primary"
                                        style="padding:0.3rem 0.8rem;font-size:0.8rem;">Edit</a>
                                    <form method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Delete this service?')">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                        <button type="submit" name="delete_service" class="btn btn-sm btn-danger"
                                            style="padding:0.3rem 0.8rem;font-size:0.8rem;">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999;text-align:center;padding:2rem;">No services created yet. Click "+ New Service" to get
                    started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>