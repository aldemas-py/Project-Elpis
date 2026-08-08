<?php

/**
 * Elpis Counselling Centre - Admin Gallery Management
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Handle Create/Update Gallery Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_gallery'])) {
    $id = (int)($_POST['gallery_id'] ?? 0);
    $event_name = trim($_POST['event_name']);
    $event_date = trim($_POST['event_date']) ?: null;
    $description = trim($_POST['description']);
    $frame_size = trim($_POST['frame_size'] ?? 'standard');
    $allowed_sizes = ['standard', 'large', 'tall', 'wide'];
    if (!in_array($frame_size, $allowed_sizes)) $frame_size = 'standard';
    $is_published = isset($_POST['is_published']) ? 1 : 0;

if ($event_name) {
        try {
            if ($id > 0) {
                $stmt = $db->prepare("UPDATE gallery_events SET event_name=?, event_date=?, description=?, frame_size=?, is_published=? WHERE id=?");
                $stmt->execute([$event_name, $event_date, $description, $frame_size, $is_published, $id]);
                $message = 'Gallery event updated successfully.';
            } else {
                $stmt = $db->prepare("INSERT INTO gallery_events (event_name, event_date, description, frame_size, is_published) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$event_name, $event_date, $description, $frame_size, $is_published]);
                $id = (int)$db->lastInsertId();
                $message = 'Gallery event created successfully.';
            }
            $messageType = 'success';

            // If images were uploaded with the save form, process them
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $files = $_FILES['images'];
                $nextOrder = count(getGalleryImages($id));
                for ($i = 0; $i < count($files['name']); $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $fileArray = [
                            'name' => $files['name'][$i],
                            'type' => $files['type'][$i],
                            'tmp_name' => $files['tmp_name'][$i],
                            'error' => $files['error'][$i],
                            'size' => $files['size'][$i],
                        ];
                        try {
                            $filename = uploadImage($fileArray);
                            if ($filename) {
                                $caption = trim($_POST['captions'][$i] ?? '');
                                $db->prepare("INSERT INTO gallery_images (gallery_event_id, image, caption, display_order) VALUES (?, ?, ?, ?)")
                                    ->execute([$id, $filename, $caption, $nextOrder]);
                                $nextOrder++;
                            }
                        } catch (Exception $e) {
                            $message = $e->getMessage();
                            $messageType = 'error';
                        }
                    }
                }
                // Set featured image to first upload if none already set
                $stmt = $db->prepare("SELECT featured_image FROM gallery_events WHERE id = ?");
                $stmt->execute([$id]);
                if (!$stmt->fetchColumn()) {
                    $img = getGalleryImages($id);
                    if (count($img) > 0) {
                        $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$img[0]['image'], $id]);
                    }
                }
            }

            // Reload the gallery so the image upload section reflects new state
            $editGallery = getGalleryEventById($id);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Event name is required.';
        $messageType = 'error';
    }
}

// Handle image uploads (multiple) + captions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_images'])) {
    $gallery_id = (int)$_POST['gallery_id'];
    $captions = $_POST['captions'] ?? [];
    $successCount = 0;

    // Get existing images to determine display_order
    $existing = getGalleryImages($gallery_id);
    $nextOrder = count($existing);

    if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
        $files = $_FILES['images'];
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                $fileArray = [
                    'name' => $files['name'][$i],
                    'type' => $files['type'][$i],
                    'tmp_name' => $files['tmp_name'][$i],
                    'error' => $files['error'][$i],
                    'size' => $files['size'][$i],
                ];
                try {
                    $filename = uploadImage($fileArray);
                    if ($filename) {
                        $caption = trim($captions[$i] ?? '');
                        $stmt = $db->prepare("INSERT INTO gallery_images (gallery_event_id, image, caption, display_order) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$gallery_id, $filename, $caption, $nextOrder]);
                        $nextOrder++;
                        $successCount++;
                    }
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $messageType = 'error';
                }
            }
        }
    }

    if ($successCount > 0) {
        // Set first image as featured if no featured image yet
        $stmt = $db->prepare("SELECT featured_image FROM gallery_events WHERE id = ?");
        $stmt->execute([$gallery_id]);
        if (!$stmt->fetchColumn()) {
            $img = getGalleryImages($gallery_id);
            if (count($img) > 0) {
                $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$img[0]['image'], $gallery_id]);
            }
        }
        $message = $successCount . ' image(s) uploaded successfully.';
        $messageType = 'success';
    } elseif (!$message) {
        $message = 'No images were uploaded.';
        $messageType = 'error';
    }
}

// Set featured image
if (isset($_GET['feature'])) {
    $galleryId = (int)$_GET['feature'];
    $imageId = (int)$_GET['img'];
    $stmt = $db->prepare("SELECT image FROM gallery_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $img = $stmt->fetchColumn();
    if ($img) {
        $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$img, $galleryId]);
        $message = 'Featured image updated.';
        $messageType = 'success';
    }
}

// Delete an image
if (isset($_GET['delete_img'])) {
    $galleryId = (int)$_GET['gallery_id'];
    $imageId = (int)$_GET['delete_img'];
    $stmt = $db->prepare("SELECT image FROM gallery_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $img = $stmt->fetchColumn();
    if ($img) deleteImage($img);
    $db->prepare("DELETE FROM gallery_images WHERE id = ?")->execute([$imageId]);

    // If featured was deleted, reset to first remaining image
    $stmt = $db->prepare("SELECT featured_image FROM gallery_events WHERE id = ?");
    $stmt->execute([$galleryId]);
    if ($stmt->fetchColumn() == $img) {
        $remaining = getGalleryImages($galleryId);
        $newFeatured = count($remaining) > 0 ? $remaining[0]['image'] : null;
        $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$newFeatured, $galleryId]);
    }
    $message = 'Image deleted.';
    $messageType = 'success';
}

// Update image captions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_captions'])) {
    $ids = $_POST['caption_id'] ?? [];
    $captions = $_POST['caption_text'] ?? [];
    $orders = $_POST['display_order'] ?? [];
    foreach ($ids as $i => $capId) {
        $capId = (int)$capId;
        $cap = trim($captions[$i] ?? '');
        $ord = (int)($orders[$i] ?? 0);
        $db->prepare("UPDATE gallery_images SET caption = ?, display_order = ? WHERE id = ?")
            ->execute([$cap, $ord, $capId]);
    }
    $message = 'Captions updated.';
    $messageType = 'success';
}

// Handle Delete Gallery Event
if (isset($_GET['delete'])) {
    $galleryId = (int)$_GET['delete'];
    // Delete all images
    $images = getGalleryImages($galleryId);
    foreach ($images as $img) {
        deleteImage($img['image']);
    }
    $db->prepare("DELETE FROM gallery_events WHERE id = ?")->execute([$galleryId]);
    $message = 'Gallery event deleted successfully.';
    $messageType = 'success';
}

// Get edit data (preserve $editGallery if already set by a save operation above)
if (!isset($editGallery) && isset($_GET['edit'])) {
    $editGallery = getGalleryEventById((int)$_GET['edit']);
}
if (!isset($editGallery)) {
    $editGallery = null;
}

$galleries = $db->query("SELECT * FROM gallery_events ORDER BY event_date DESC, created_at DESC")->fetchAll();
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

    .preview-img {
        width: 70px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
    }

    .gallery-thumb-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
        gap: 0.8rem;
        margin-top: 1rem;
    }

    .gallery-thumb {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        background: #f0f0f0;
    }

    .gallery-thumb img {
        width: 100%;
        height: 90px;
        object-fit: cover;
        display: block;
    }

    .gallery-thumb .badge {
        position: absolute;
        top: 4px;
        left: 4px;
        background: #4FA08A;
        color: #fff;
        font-size: 0.6rem;
        padding: 2px 6px;
        border-radius: 10px;
    }

    .gallery-thumb .actions {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        justify-content: space-around;
        padding: 3px;
        opacity: 0;
        transition: opacity 0.3s;
    }

    .gallery-thumb:hover .actions {
        opacity: 1;
    }

    .gallery-thumb .actions a {
        color: #fff;
        font-size: 0.7rem;
        text-decoration: none;
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
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">&#9733; Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php">&#128218; Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">&#128197; Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_gallery.php" class="active">&#128248; Gallery</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">&#9733; Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">&#8592; View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">&#128682; Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $editGallery ? 'Edit Gallery Event' : 'Manage Gallery'; ?></h1>
            <a href="?new=1" class="btn btn-primary btn-sm">+ New Gallery</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($editGallery || isset($_GET['new'])): ?>
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="gallery_id" value="<?php echo $editGallery['id'] ?? 0; ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="event_name">Event Name *</label>
                            <input type="text" id="event_name" name="event_name" class="form-control" required
                                value="<?php echo h($editGallery['event_name'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="event_date">Event Date</label>
                            <input type="date" id="event_date" name="event_date" class="form-control"
                                value="<?php echo h($editGallery['event_date'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="images">Upload Images (Multiple Allowed)</label>
                        <input type="file" id="images" name="images[]" class="form-control" accept="image/*" multiple
                            onchange="showCaptionInputs(this)">
                        <small style="color:#999;">You can select multiple images. JPG, PNG, GIF, WEBP (max 5MB each).
                            Images are added when you save this gallery.</small>
                        <div id="captionInputs" style="margin-top:0.75rem;"></div>
                    </div>

                    <div class="form-group">
                        <label for="description">Brief Description</label>
                        <textarea id="description" name="description" class="form-control"
                            rows="3"><?php echo h($editGallery['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="frame_size">Frame Size (Masonry Grid)</label>
                        <select id="frame_size" name="frame_size" class="form-control">
                            <?php
                            $frameSizes = [
                                'standard' => 'Standard',
                                'large' => 'Large (Wide + Tall)',
                                'tall' => 'Tall',
                                'wide' => 'Wide',
                            ];
                            $currentFrame = $editGallery['frame_size'] ?? 'standard';
                            foreach ($frameSizes as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo $currentFrame == $val ? 'selected' : ''; ?>>
                                    <?php echo $label; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:#999;">Choose how large this gallery item appears in the masonry grid.</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_published" value="1"
                                <?php echo ($editGallery && $editGallery['is_published']) ? 'checked' : 'checked'; ?>>
                            Publish (visible to visitors)
                        </label>
                    </div>

                    <div style="display:flex;gap:1rem;">
                        <button type="submit" name="save_gallery" class="btn btn-primary">Save Gallery Event</button>
                        <a href="<?php echo SITE_URL; ?>/admin/manage_gallery.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>

<?php if ($editGallery && $editGallery['id']): ?>
                    <hr style="border:none;border-top:1px solid #D7DDD9;margin:2rem 0;">

                    <?php if ($imageList = getGalleryImages($editGallery['id'])): ?>
                        <div class="gallery-thumb-grid">
                            <?php foreach ($imageList as $img): ?>
                                <div class="gallery-thumb">
                                    <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($img['image']); ?>"
                                        alt="<?php echo h($img['caption'] ?: 'Gallery image'); ?>">
                                    <?php if ($editGallery['featured_image'] == $img['image']): ?>
                                        <span class="badge">Featured</span>
                                    <?php endif; ?>
                                    <div class="actions">
                                        <a href="?feature=<?php echo $editGallery['id']; ?>&img=<?php echo $img['id']; ?>"
                                            title="Set as featured">&#9733;</a>
                                        <span style="color:#fff;font-size:0.7rem;"><?php echo ($img['display_order'] + 1); ?></span>
                                        <a href="?edit=<?php echo $editGallery['id']; ?>&delete_img=<?php echo $img['id']; ?>&gallery_id=<?php echo $editGallery['id']; ?>"
                                            title="Delete image"
                                            onclick="return confirm('Delete this image?')">&#128465;</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h4 style="margin:1.5rem 0 0.75rem;">Image Captions & Order</h4>
                        <form method="POST" action="">
                            <input type="hidden" name="gallery_id" value="<?php echo $editGallery['id']; ?>">
                            <?php foreach ($imageList as $i => $img): ?>
                                <div style="display:flex;gap:1rem;align-items:center;margin-bottom:0.5rem;">
                                    <input type="hidden" name="caption_id[]" value="<?php echo $img['id']; ?>">
                                    <input type="number" name="display_order[]" class="form-control"
                                        style="width:80px;" value="<?php echo $img['display_order']; ?>"
                                        min="0" title="Display order">
                                    <input type="text" name="caption_text[]" class="form-control"
                                        placeholder="Image description..."
                                        value="<?php echo h($img['caption']); ?>">
                                </div>
                            <?php endforeach; ?>
                            <button type="submit" name="save_captions" class="btn btn-primary btn-sm"
                                style="margin-top:0.5rem;">Save Captions & Order</button>
                        </form>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>All Galleries</h3>
            <?php if (count($galleries) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Featured</th>
                            <th>Event Name</th>
                            <th>Date</th>
                            <th>Images</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($galleries as $g): ?>
                            <?php $imgCount = count(getGalleryImages($g['id'])); ?>
                            <tr>
                                <td>
                                    <?php if ($g['featured_image']): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($g['featured_image']); ?>"
                                            class="preview-img" alt="">
                                    <?php else: ?>
                                        <span style="color:#D7DDD9;">No img</span>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?php echo h($g['event_name']); ?></strong></td>
                                <td><?php echo $g['event_date'] ? formatDate($g['event_date']) : '—'; ?></td>
                                <td><?php echo $imgCount; ?></td>
                                <td><?php echo $g['is_published'] ? '<span style="color:#4FA08A;">Published</span>' : '<span style="color:#999;">Hidden</span>'; ?>
                                </td>
                                <td>
                                    <a href="?edit=<?php echo $g['id']; ?>" class="btn btn-sm btn-primary"
                                        style="padding:0.3rem 0.8rem;font-size:0.8rem;">Manage</a>
                                    <a href="?delete=<?php echo $g['id']; ?>" class="btn btn-sm btn-danger"
                                        style="padding:0.3rem 0.8rem;font-size:0.8rem;"
                                        onclick="return confirm('Delete this gallery and all its images?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="color:#999;text-align:center;padding:2rem;">No galleries created yet. Click "+ New Gallery" to get
                    started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showCaptionInputs(input) {
        var container = document.getElementById('captionInputs');
        container.innerHTML = '';
        var files = input.files || [];
        for (var i = 0; i < files.length; i++) {
            var row = document.createElement('div');
            row.style.cssText = 'display:flex;gap:0.5rem;align-items:center;margin-bottom:0.4rem;';
            var num = document.createElement('span');
            num.textContent = (i + 1) + '. ' + files[i].name;
            num.style.cssText = 'font-size:0.85rem;color:#555;min-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;';
            var cap = document.createElement('input');
            cap.type = 'text';
            cap.name = 'captions[]';
            cap.className = 'form-control';
            cap.placeholder = 'Caption / description for this image (optional)';
            row.appendChild(num);
            row.appendChild(cap);
            container.appendChild(row);
        }
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
