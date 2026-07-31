<?php

/**
 * Elpis Counselling Centre - Admin Testimonials Management
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_testimonial'])) {
    $id = (int)($_POST['testimonial_id'] ?? 0);
    $client_name = trim($_POST['client_name']);
    $client_role = trim($_POST['client_role']);
    $content = trim($_POST['content']);
    $rating = (int)($_POST['rating'] ?? 5);
    $is_approved = isset($_POST['is_approved']) ? 1 : 0;

    if ($client_name && $content) {
        if ($id > 0) {
            $stmt = $db->prepare("UPDATE testimonials SET client_name=?, client_role=?, content=?, rating=?, is_approved=? WHERE id=?");
            $stmt->execute([$client_name, $client_role, $content, $rating, $is_approved, $id]);
            $message = 'Testimonial updated successfully.';
        } else {
            $stmt = $db->prepare("INSERT INTO testimonials (client_name, client_role, content, rating, is_approved) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$client_name, $client_role, $content, $rating, $is_approved]);
            $message = 'Testimonial created successfully.';
        }
        $messageType = 'success';
    } else {
        $message = 'Client name and content are required.';
        $messageType = 'error';
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Testimonial deleted successfully.';
    $messageType = 'success';
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $db->prepare("UPDATE testimonials SET is_approved = NOT is_approved WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Testimonial approval status toggled.';
    $messageType = 'success';
}

$editTestimonial = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$id]);
    $editTestimonial = $stmt->fetch();
}

$testimonials = $db->query("SELECT * FROM testimonials ORDER BY created_at DESC")->fetchAll();
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

    .btn-approve {
        background: #4FA08A;
        color: #fff;
    }
</style>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php" class="active">Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $editTestimonial ? 'Edit Testimonial' : 'Manage Testimonials'; ?></h1>
            <a href="?new=1"
                style="display:inline-block;padding:0.5rem 1.2rem;background:#4FA08A;color:#fff;border-radius:8px;font-size:0.85rem;text-decoration:none;">+
                New Testimonial</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($editTestimonial || isset($_GET['new'])): ?>
            <div class="form-container">
                <form method="POST" action="">
                    <input type="hidden" name="testimonial_id" value="<?php echo $editTestimonial['id'] ?? 0; ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="client_name">Client Name *</label>
                            <input type="text" id="client_name" name="client_name" class="form-control" required
                                value="<?php echo h($editTestimonial['client_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="client_role">Role/Title</label>
                            <input type="text" id="client_role" name="client_role" class="form-control"
                                value="<?php echo h($editTestimonial['client_role'] ?? ''); ?>"
                                placeholder="e.g., Client, Corporate Partner">
                        </div>
                        <div class="form-group">
                            <label for="rating">Rating</label>
                            <select id="rating" name="rating" class="form-control">
                                <?php for ($i = 5; $i >= 1; $i--): ?>
                                    <option value="<?php echo $i; ?>"
                                        <?php echo ($editTestimonial && $editTestimonial['rating'] == $i) ? 'selected' : ''; ?>>
                                        <?php echo $i; ?> Stars</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="content">Testimonial Content *</label>
                            <textarea id="content" name="content" class="form-control" rows="4"
                                required><?php echo h($editTestimonial['content'] ?? ''); ?></textarea>
                        </div>
                        <div class="form-group">
                            <label><input type="checkbox" name="is_approved" value="1"
                                    <?php echo ($editTestimonial && $editTestimonial['is_approved']) ? 'checked' : ''; ?>>
                                Approved (visible on website)</label>
                        </div>
                        <div style="display:flex;gap:1rem;">
                            <button type="submit" name="save_testimonial" class="btn btn-primary">Save Testimonial</button>
                            <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php"
                                class="btn btn-secondary">Cancel</a>
                        </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>All Testimonials</h3>
            <?php if (count($testimonials) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Rating</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($testimonials as $testimonial): ?>
                            <tr>
                                <td><strong><?php echo h($testimonial['client_name']); ?></strong><?php if ($testimonial['client_role']): ?><br><small><?php echo h($testimonial['client_role']); ?></small><?php endif; ?>
                                </td>
                                <td><?php for ($i = 0; $i < $testimonial['rating']; $i++): ?><span
                                            style="color:#E4CF55;">&#9733;</span><?php endfor; ?></td>
                                <td><small><?php echo h(truncateText($testimonial['content'], 80)); ?></small></td>
                                <td><?php echo $testimonial['is_approved'] ? '<span style="color:#4FA08A;">Approved</span>' : '<span style="color:#999;">Pending</span>'; ?>
                                </td>
                                <td><small><?php echo formatDate($testimonial['created_at']); ?></small></td>
                                <td>
                                    <a href="?toggle=<?php echo $testimonial['id']; ?>"
                                        style="display:inline-block;padding:0.3rem 0.8rem;background:#4FA08A;color:#fff;border-radius:5px;font-size:0.8rem;text-decoration:none;"><?php echo $testimonial['is_approved'] ? 'Unapprove' : 'Approve'; ?></a>
                                    <a href="?edit=<?php echo $testimonial['id']; ?>"
                                        style="display:inline-block;padding:0.3rem 0.8rem;background:#3F5195;color:#fff;border-radius:5px;font-size:0.8rem;text-decoration:none;">Edit</a>
                                    <a href="?delete=<?php echo $testimonial['id']; ?>"
                                        style="display:inline-block;padding:0.3rem 0.8rem;background:#dc3545;color:#fff;border-radius:5px;font-size:0.8rem;text-decoration:none;"
                                        onclick="return confirm('Delete this testimonial?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999;text-align:center;padding:2rem;">No testimonials yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>