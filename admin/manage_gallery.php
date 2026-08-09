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

// Handle Delete Gallery Event via POST (CSRF protected)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gallery'])) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = 'Security token mismatch. Please refresh and try again.';
        $messageType = 'error';
    } else {
        $galleryId = (int)$_POST['gallery_id'];
        // Delete all images
        $images = getGalleryImages($galleryId);
        foreach ($images as $img) {
            deleteImage($img['image']);
        }
        $db->prepare("DELETE FROM gallery_events WHERE id = ?")->execute([$galleryId]);
        $message = 'Gallery event deleted successfully.';
        $messageType = 'success';
    }
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
        <a href="<?php echo SITE_URL; ?>/admin/settings.php">&#128100; Account &amp; Settings</a>
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
                <form method="POST" action="">
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

                    <!-- Gallery Images: attached to this gallery event (auto-upload with previews) -->
                    <div class="form-group">
                        <label for="imageInput">Gallery Images</label>
                        <input type="file" id="imageInput" name="image" class="form-control" accept="image/*"
                            onchange="uploadImageNow(this)">
                        <small style="color:#999;">Choose an image and it will upload instantly to this gallery event with
                            a preview. You can keep adding more images. JPG, PNG, GIF, WEBP (max 5MB each).</small>
                        <div id="uploadStatus" style="margin-top:0.5rem;font-size:0.85rem;color:#4FA08A;"></div>

<div class="gallery-thumb-grid" id="imagePreviewGrid">
                            <?php if ($editGallery && $editGallery['id']): ?>
                                <?php if ($imageList = getGalleryImages($editGallery['id'])): ?>
                                    <?php $totalImgs = count($imageList); ?>
                                    <?php foreach ($imageList as $imgIdx => $img): ?>
                                        <div class="gallery-thumb" data-image-id="<?php echo $img['id']; ?>"
                                            data-featured="<?php echo ($editGallery['featured_image'] == $img['image']) ? '1' : '0'; ?>">
                                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($img['image']); ?>"
                                                alt="<?php echo h($img['caption'] ?: 'Gallery image'); ?>">
                                            <?php if ($editGallery['featured_image'] == $img['image']): ?>
                                                <span class="badge">Featured</span>
                                            <?php endif; ?>
                                            <div class="actions">
                                                <a href="javascript:void(0);" title="Set as featured"
                                                    onclick="setFeatured(<?php echo $img['id']; ?>)">&#9733;</a>
                                                <span style="color:#fff;font-size:0.7rem;"><?php echo ($img['display_order'] + 1); ?></span>
                                                <a href="javascript:void(0);" title="Delete image"
                                                    onclick="deleteImageNow(<?php echo $img['id']; ?>)">&#128465;</a>
                                            </div>
                                            <label style="display:block;font-size:0.7rem;font-weight:700;color:#3F5195;padding:0.3rem 0.4rem 0;line-height:1.2;">
                                                Image <?php echo $imgIdx + 1; ?> of <?php echo $totalImgs; ?> — caption for this image only
                                            </label>
                                            <input type="text" class="form-control" style="font-size:0.75rem;border-radius:0;"
                                                placeholder="Caption for this image..."
                                                title="Caption that specifically explains this single image"
                                                value="<?php echo h($img['caption']); ?>"
                                                onchange="saveCaption(<?php echo $img['id']; ?>, this.value)">
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>

<div class="form-group">
                        <label for="description">Gallery Description <span style="font-weight:400;color:#999;">(explains this whole gallery set of images)</span></label>
                        <textarea id="description" name="description" class="form-control"
                            rows="3" placeholder="e.g., A recap of our Youth Mental Health Workshop — showing the full session."><?php echo h($editGallery['description'] ?? ''); ?></textarea>
                        <small style="color:#999;">This describes the <strong>entire gallery</strong> (all images together), seen in the overlay and lightbox. Use the per-image caption fields above to explain each individual photo.</small>
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
                                    <form method="POST" style="display:inline-block;"
                                        onsubmit="return confirm('Delete this gallery and all its images?')">
                                        <?php echo csrfField(); ?>
                                        <input type="hidden" name="gallery_id" value="<?php echo $g['id']; ?>">
                                        <button type="submit" name="delete_gallery" class="btn btn-sm btn-danger"
                                            style="padding:0.3rem 0.8rem;font-size:0.8rem;">Delete</button>
                                    </form>
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
    var AJAX_URL = '<?php echo SITE_URL; ?>/admin/gallery_image_ajax.php';
    var GALLERY_ID = <?php echo (int)($editGallery['id'] ?? 0); ?>;

// Create a new gallery event (draft) so we can attach images to it
    function createGalleryForUpload() {
        var fd = new FormData();
        fd.append('action', 'create');
        fd.append('event_name', document.getElementById('event_name').value || 'Untitled Gallery');
        fd.append('event_date', document.getElementById('event_date').value || '');

        return fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    GALLERY_ID = data.id;
                    var hidden = document.querySelector('input[name="gallery_id"]');
                    if (hidden) hidden.value = data.id;
                    return data.id;
                }
                throw new Error(data.message || 'Could not create gallery.');
            });
    }

    // Upload an image immediately when selected
    function uploadImageNow(input) {
        var file = input.files[0];
        if (!file) return;

        var status = document.getElementById('uploadStatus');
        status.textContent = 'Uploading...';

        // If we have no gallery yet, create one first (draft) so images attach to it
        var ready = GALLERY_ID > 0 ? Promise.resolve(GALLERY_ID) : createGalleryForUpload();

        ready.then(function(gid) {
            var fd = new FormData();
            fd.append('action', 'upload');
            fd.append('gallery_id', gid);
            fd.append('image', file);

            return fetch(AJAX_URL, { method: 'POST', body: fd }).then(function(r) { return r.json(); });
        }).then(function(data) {
            if (data.success) {
                status.textContent = 'Uploaded successfully. You can add another image.';
                addImagePreview(data);
            } else {
                status.textContent = 'Error: ' + (data.message || 'Upload failed.');
            }
        }).catch(function(err) {
            status.textContent = 'Error: ' + (err.message || 'Upload failed.');
        }).finally(function() {
            input.value = ''; // clear so user can pick another
        });
    }

// Show a newly uploaded image as a small preview
    function addImagePreview(data) {
        var grid = document.getElementById('imagePreviewGrid');

        var thumb = document.createElement('div');
        thumb.className = 'gallery-thumb';
        thumb.setAttribute('data-image-id', data.id);
        thumb.setAttribute('data-featured', '0');

        var img = document.createElement('img');
        img.src = data.url;
        img.alt = 'Uploaded image';

        var actions = document.createElement('div');
        actions.className = 'actions';

        var star = document.createElement('a');
        star.href = 'javascript:void(0);';
        star.title = 'Set as featured';
        star.innerHTML = '&#9733;';
        star.onclick = function() { setFeatured(data.id); };

        var num = document.createElement('span');
        num.style.cssText = 'color:#fff;font-size:0.7rem;';
        num.textContent = data.display_order;

        var del = document.createElement('a');
        del.href = 'javascript:void(0);';
        del.title = 'Delete image';
        del.innerHTML = '&#128465;';
        del.onclick = function() { deleteImageNow(data.id); };

        actions.appendChild(star);
        actions.appendChild(num);
        actions.appendChild(del);

        // Count how many thumbnails are present to label this image's position
        var totalNow = grid.querySelectorAll('.gallery-thumb').length + 1;

        var capLabel = document.createElement('label');
        capLabel.style.cssText = 'display:block;font-size:0.7rem;font-weight:700;color:#3F5195;padding:0.3rem 0.4rem 0;line-height:1.2;';
        capLabel.textContent = 'Image ' + totalNow + ' of ' + totalNow + ' — caption for this image only';

        var cap = document.createElement('input');
        cap.type = 'text';
        cap.className = 'form-control';
        cap.style.cssText = 'font-size:0.75rem;border-radius:0;';
        cap.placeholder = 'Caption for this image...';
        cap.title = 'Caption that specifically explains this single image';
        cap.onchange = function() { saveCaption(data.id, this.value); };

        thumb.appendChild(img);
        thumb.appendChild(actions);
        thumb.appendChild(capLabel);
        thumb.appendChild(cap);
        grid.appendChild(thumb);
    }

    // Save a caption via AJAX
    function saveCaption(imageId, caption) {
        var fd = new FormData();
        fd.append('action', 'caption');
        fd.append('image_id', imageId);
        fd.append('caption', caption);
        fetch(AJAX_URL, { method: 'POST', body: fd });
    }

    // Set an image as featured via AJAX
    function setFeatured(imageId) {
        if (!confirm('Set this image as the featured image?')) return;
        var fd = new FormData();
        fd.append('action', 'feature');
        fd.append('image_id', imageId);
        fd.append('gallery_id', GALLERY_ID);
        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    // Move the badge to the selected image
                    var thumbs = document.querySelectorAll('#imagePreviewGrid .gallery-thumb');
                    thumbs.forEach(function(t) {
                        t.querySelector('.badge') ? t.querySelector('.badge').remove() : null;
                        t.setAttribute('data-featured', '0');
                        if (parseInt(t.getAttribute('data-image-id')) === imageId) {
                            t.setAttribute('data-featured', '1');
                            var rank = document.createElement('span');
                            rank.className = 'badge';
                            rank.textContent = 'Featured';
                            t.insertBefore(rank, t.firstChild);
                        }
                    });
                } else {
                    alert('Error: ' + (data.message || 'Could not set featured.'));
                }
            });
    }

    // Delete an image via AJAX
    function deleteImageNow(imageId) {
        if (!confirm('Delete this image?')) return;
        var fd = new FormData();
        fd.append('action', 'delete');
        fd.append('image_id', imageId);
        fd.append('gallery_id', GALLERY_ID);
        fetch(AJAX_URL, { method: 'POST', body: fd })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    var thumb = document.querySelector('#imagePreviewGrid .gallery-thumb[data-image-id="' + imageId + '"]');
                    if (thumb) thumb.remove();
                } else {
                    alert('Error: ' + (data.message || 'Could not delete image.'));
                }
            });
    }
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
