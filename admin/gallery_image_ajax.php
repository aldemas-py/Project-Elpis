<?php

/**
 * Elpis Counselling Centre - Gallery Image AJAX Handler
 *
 * Handles single-image upload, caption updates, featured image, and delete
 * for gallery images. Used by the Gallery admin page for instant auto-upload
 * with previews.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? '';

// ============================================================
// CREATE a new gallery event (draft) so images can be added
// before the admin clicks "Save". Returns the new gallery id.
// ============================================================
if ($action === 'create') {
    $event_name = trim($_POST['event_name'] ?? '');
    if ($event_name === '') {
        $event_name = 'Untitled Gallery';
    }
    $event_date = trim($_POST['event_date'] ?? '') ?: null;

    $stmt = $db->prepare("INSERT INTO gallery_events (event_name, event_date, is_published) VALUES (?, ?, 0)");
    $stmt->execute([$event_name, $event_date]);
    $newId = (int)$db->lastInsertId();

    echo json_encode(['success' => true, 'id' => $newId]);
    exit;
}

// ============================================================
// UPLOAD a single image to a gallery event
// ============================================================
if ($action === 'upload') {
    $galleryId = (int)($_POST['gallery_id'] ?? 0);
    $gallery = getGalleryEventById($galleryId);

    if (!$gallery) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Gallery not found. Please save the gallery first.']);
        exit;
    }

    if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No image received.']);
        exit;
    }

    try {
        $filename = uploadImage($_FILES['image']);
        $existing = getGalleryImages($galleryId);
        $nextOrder = count($existing);

        $stmt = $db->prepare("INSERT INTO gallery_images (gallery_event_id, image, caption, display_order) VALUES (?, ?, '', ?)");
        $stmt->execute([$galleryId, $filename, $nextOrder]);
        $imageId = (int)$db->lastInsertId();

        // Assign as featured automatically if the gallery has none yet
        $stmt = $db->prepare("SELECT featured_image FROM gallery_events WHERE id = ?");
        $stmt->execute([$galleryId]);
        if (!$stmt->fetchColumn()) {
            $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$filename, $galleryId]);
        }

        echo json_encode([
            'success' => true,
            'id' => $imageId,
            'filename' => $filename,
            'url' => SITE_URL . '/uploads/' . $filename,
            'display_order' => $nextOrder + 1,
        ]);
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ============================================================
// UPDATE a caption for an image
// ============================================================
if ($action === 'caption') {
    $imageId = (int)($_POST['image_id'] ?? $_GET['image_id'] ?? 0);
    $caption = trim($_POST['caption'] ?? $_GET['caption'] ?? '');

    if ($imageId > 0) {
        $db->prepare("UPDATE gallery_images SET caption = ? WHERE id = ?")->execute([$caption, $imageId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid image.']);
    }
    exit;
}

// ============================================================
// SET a featured image for a gallery
// ============================================================
if ($action === 'feature') {
    $imageId = (int)($_POST['image_id'] ?? $_GET['image_id'] ?? 0);
    $galleryId = (int)($_POST['gallery_id'] ?? $_GET['gallery_id'] ?? 0);

    $stmt = $db->prepare("SELECT image FROM gallery_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $filename = $stmt->fetchColumn();

    if ($filename) {
        $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$filename, $galleryId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Image not found.']);
    }
    exit;
}

// ============================================================
// DELETE an image
// ============================================================
if ($action === 'delete') {
    $imageId = (int)($_POST['image_id'] ?? $_GET['image_id'] ?? 0);
    $galleryId = (int)($_POST['gallery_id'] ?? $_GET['gallery_id'] ?? 0);

    $stmt = $db->prepare("SELECT image FROM gallery_images WHERE id = ?");
    $stmt->execute([$imageId]);
    $filename = $stmt->fetchColumn();

    if ($filename) {
        deleteImage($filename);
        $db->prepare("DELETE FROM gallery_images WHERE id = ?")->execute([$imageId]);

        // If the deleted image was the featured one, reset to first remaining
        $stmt = $db->prepare("SELECT featured_image FROM gallery_events WHERE id = ?");
        $stmt->execute([$galleryId]);
        if ($stmt->fetchColumn() == $filename) {
            $remaining = getGalleryImages($galleryId);
            $newFeatured = count($remaining) > 0 ? $remaining[0]['image'] : null;
            $db->prepare("UPDATE gallery_events SET featured_image = ? WHERE id = ?")->execute([$newFeatured, $galleryId]);
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Image not found.']);
    }
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);

