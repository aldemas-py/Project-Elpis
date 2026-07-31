<?php

/**
 * Elpis Counselling Centre - Admin Events Management
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_event'])) {
    $id = (int)($_POST['event_id'] ?? 0);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $event_time = trim($_POST['event_time']);
    $venue = trim($_POST['venue']);
    $price = (float)($_POST['price'] ?? 0);
    $max_participants = (int)($_POST['max_participants'] ?? 0) ?: null;
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title && $event_date) {
        try {
            $image = null;
            if ($id > 0) {
                $stmt = $db->prepare("SELECT image FROM events WHERE id = ?");
                $stmt->execute([$id]);
                $existing_image = $stmt->fetchColumn();
                $image = uploadImage($_FILES['image'] ?? [], $existing_image);
            } else {
                $image = uploadImage($_FILES['image'] ?? []);
            }

            if ($id > 0) {
                $stmt = $db->prepare("UPDATE events SET title=?, description=?, event_date=?, event_time=?, venue=?, price=?, image=?, max_participants=?, is_published=? WHERE id=?");
                $stmt->execute([$title, $description, $event_date, $event_time, $venue, $price, $image, $max_participants, $is_published, $id]);
                $message = 'Event updated successfully.';
            } else {
                $stmt = $db->prepare("INSERT INTO events (title, description, event_date, event_time, venue, price, image, max_participants, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $event_date, $event_time, $venue, $price, $image, $max_participants, $is_published]);
                $message = 'Event created successfully.';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Title and date are required.';
        $messageType = 'error';
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT image FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img) deleteImage($img);
    $stmt = $db->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Event deleted successfully.';
    $messageType = 'success';
}

// Get edit data
$editEvent = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$id]);
    $editEvent = $stmt->fetch();
}

$events = $db->query("SELECT * FROM events ORDER BY event_date DESC")->fetchAll();
$bookingCounts = [];
$bStmt = $db->query("SELECT event_id, COUNT(*) as cnt FROM event_bookings GROUP BY event_id");
while ($row = $bStmt->fetch()) {
    $bookingCounts[$row['event_id']] = $row['cnt'];
}
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

    .preview-img {
        width: 60px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>

<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">&#9632; Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">&#9997; Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">&#9733; Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">&#128218; Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php" class="active">&#128197; Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">&#9733; Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">&#8592; View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">&#128682; Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $editEvent ? 'Edit Event' : 'Manage Events'; ?></h1>
            <a href="?new=1" class="btn btn-primary btn-sm">+ New Event</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($editEvent || isset($_GET['new'])): ?>
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="event_id" value="<?php echo $editEvent['id'] ?? 0; ?>">

                    <div class="form-group">
                        <label for="title">Event Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required
                            value="<?php echo h($editEvent['title'] ?? ''); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_date">Date *</label>
                            <input type="date" id="event_date" name="event_date" class="form-control" required
                                value="<?php echo $editEvent['event_date'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label for="event_time">Time</label>
                            <input type="text" id="event_time" name="event_time" class="form-control"
                                value="<?php echo h($editEvent['event_time'] ?? ''); ?>"
                                placeholder="e.g., 10:00 AM - 12:00 PM">
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="venue">Venue</label>
                                <input type="text" id="venue" name="venue" class="form-control"
                                    value="<?php echo h($editEvent['venue'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="price">Price (KES)</label>
                                <input type="number" id="price" name="price" class="form-control" step="0.01"
                                    value="<?php echo $editEvent['price'] ?? 0; ?>">
                                <small style="color:#999;">Set to 0 for free events</small>
                            </div>

                            <div class="form-group">
                                <label for="image">Event Image</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <?php if ($editEvent && $editEvent['image']): ?>
                                    <div style="margin-top:0.5rem;">
                                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($editEvent['image']); ?>"
                                            class="preview-img" alt="Current image">
                                        <small style="color:#999;"> Current image. Upload new to replace.</small>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="form-group">
                                <label for="max_participants">Max Participants</label>
                                <input type="number" id="max_participants" name="max_participants" class="form-control"
                                    value="<?php echo $editEvent['max_participants'] ?? ''; ?>"
                                    placeholder="Leave empty for unlimited">
                            </div>

                            <div class="form-group">
                                <label for="description">Description</label>
                                <textarea id="description" name="description" class="form-control"
                                    rows="5"><?php echo h($editEvent['description'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>
                                    <input type="checkbox" name="is_published" value="1"
                                        <?php echo ($editEvent && $editEvent['is_published']) ? 'checked' : ''; ?>>
                                    Publish (visible on website)
                                </label>
                            </div>

                            <div style="display:flex;gap:1rem;">
                                <button type="submit" name="save_event" class="btn btn-primary">Save Event</button>
                                <a href="<?php echo SITE_URL; ?>/admin/manage_events.php"
                                    class="btn btn-secondary">Cancel</a>
                            </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>All Events</h3>
            <?php if (count($events) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Price</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <?php $past = strtotime($event['event_date']) < time(); ?>
                            <tr style="<?php echo $past ? 'opacity:0.6;' : ''; ?>">
                                <td>
                                    <?php if ($event['image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($event['image']); ?>"
                                            class="preview-img" alt="">
                                    <?php else: ?>
                                        <span style="color:#D7DDD9;">No img</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo h($event['title']); ?></strong></td>
                                <td><?php echo formatDate($event['event_date']); ?></td>
                                <td><?php echo $event['price'] > 0 ? 'KES ' . number_format($event['price'], 2) : 'Free'; ?>
                                </td>
                                <td><?php echo $bookingCounts[$event['id']] ?? 0; ?> bookings</td>
                                <td>
                                    <?php if ($past): ?>
                                        <span style="color:#999;">Past</span>
                                    <?php else: ?>
                                        <?php echo $event['is_published'] ? '<span style="color:#4FA08A;">Published</span>' : '<span style="color:#999;">Draft</span>'; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $event['id']; ?>" class="btn btn-sm btn-primary"
                                        style="padding:0.3rem 0.8rem;font-size:0.8rem;">Edit</a>
                                    <a href="?delete=<?php echo $event['id']; ?>" class="btn btn-sm btn-danger"
                                        style="padding:0.3rem 0.8rem;font-size:0.8rem;"
                                        onclick="return confirm('Delete this event?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999;text-align:center;padding:2rem;">No events created yet. Click "+ New Event" to get
                    started.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php include __DIR__ . '/../includes/footer.php'; ?>