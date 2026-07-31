<?php

/**
 * Elpis Counselling Centre - Admin Articles Management
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$message = '';
$messageType = '';

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_article'])) {
    $id = (int)($_POST['article_id'] ?? 0);
    $title = trim($_POST['title']);
    $slug = trim($_POST['slug']) ?: createSlug($title);
    $content = $_POST['content'];
    $excerpt = trim($_POST['excerpt']);
    $author = trim($_POST['author']) ?: 'Elpis Counselling Centre';
    $category = trim($_POST['category']);
    $is_published = isset($_POST['is_published']) ? 1 : 0;

    if ($title && $content) {
        try {
            $image = null;
            if ($id > 0) {
                $stmt = $db->prepare("SELECT image FROM articles WHERE id = ?");
                $stmt->execute([$id]);
                $existing_image = $stmt->fetchColumn();
                $image = uploadImage($_FILES['image'] ?? [], $existing_image);
            } else {
                $image = uploadImage($_FILES['image'] ?? []);
            }

            if ($id > 0) {
                $stmt = $db->prepare("UPDATE articles SET title=?, slug=?, content=?, excerpt=?, author=?, category=?, image=?, is_published=? WHERE id=?");
                $stmt->execute([$title, $slug, $content, $excerpt, $author, $category, $image, $is_published, $id]);
                $message = 'Article updated successfully.';
            } else {
                $stmt = $db->prepare("INSERT INTO articles (title, slug, content, excerpt, author, category, image, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $slug, $content, $excerpt, $author, $category, $image, $is_published]);
                $message = 'Article created successfully.';
            }
            $messageType = 'success';
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    } else {
        $message = 'Title and content are required.';
        $messageType = 'error';
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $db->prepare("SELECT image FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $img = $stmt->fetchColumn();
    if ($img) deleteImage($img);
    $stmt = $db->prepare("DELETE FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $message = 'Article deleted successfully.';
    $messageType = 'success';
}

// Get edit data
$editArticle = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $editArticle = $stmt->fetch();
}

$articles = $db->query("SELECT * FROM articles ORDER BY created_at DESC")->fetchAll();
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

<div class="admin-layout">
    <div class="admin-sidebar">
        <h3>Admin Panel</h3>
        <a href="<?php echo SITE_URL; ?>/admin/dashboard.php">&#9632; Dashboard</a>
        <a href="<?php echo SITE_URL; ?>/admin/appointments.php">&#9997; Appointments</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_services.php">&#9733; Services</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php" class="active">&#128218; Articles</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_events.php">&#128197; Events</a>
        <a href="<?php echo SITE_URL; ?>/admin/manage_testimonials.php">&#9733; Testimonials</a>
        <hr style="border-color:rgba(255,255,255,0.1);margin:1.5rem 0;">
        <a href="<?php echo SITE_URL; ?>/index.php">&#8592; View Site</a>
        <a href="<?php echo SITE_URL; ?>/admin/logout.php">&#128682; Logout</a>
    </div>

    <div class="admin-content">
        <div class="admin-header">
            <h1><?php echo $editArticle ? 'Edit Article' : 'Manage Articles'; ?></h1>
            <a href="?new=1" class="btn btn-primary btn-sm">+ New Article</a>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo h($message); ?></div>
        <?php endif; ?>

        <?php if ($editArticle || isset($_GET['new'])): ?>
        <div class="form-container">
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="article_id" value="<?php echo $editArticle['id'] ?? 0; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Title *</label>
                        <input type="text" id="title" name="title" class="form-control" required
                            value="<?php echo h($editArticle['title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="slug">Slug (URL)</label>
                        <input type="text" id="slug" name="slug" class="form-control"
                            value="<?php echo h($editArticle['slug'] ?? ''); ?>"
                            placeholder="Leave blank to auto-generate">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="author">Author</label>
                            <input type="text" id="author" name="author" class="form-control"
                                value="<?php echo h($editArticle['author'] ?? 'Elpis Counselling Centre'); ?>">
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" class="form-control"
                                value="<?php echo h($editArticle['category'] ?? 'General'); ?>">
                        </div>

                        <div class="form-group">
                            <label for="image">Featured Image</label>
                            <input type="file" id="image" name="image" class="form-control" accept="image/*">
                            <?php if ($editArticle && $editArticle['image']): ?>
                            <div style="margin-top:0.5rem;">
                                <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($editArticle['image']); ?>"
                                    class="preview-img" alt="Current image">
                                <small style="color:#999;"> Current image. Upload new to replace.</small>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Excerpt (Short description)</label>
                            <textarea id="excerpt" name="excerpt" class="form-control"
                                rows="2"><?php echo h($editArticle['excerpt'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="content">Content *</label>
                            <textarea id="content" name="content" class="form-control" rows="15"
                                style="font-family:monospace;"><?php echo h($editArticle['content'] ?? ''); ?></textarea>
                            <small style="color:#999;">HTML content supported. Use proper formatting.</small>
                        </div>

                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="is_published" value="1"
                                    <?php echo ($editArticle && $editArticle['is_published']) ? 'checked' : ''; ?>>
                                Publish (visible to visitors)
                            </label>
                        </div>

                        <div style="display:flex;gap:1rem;">
                            <button type="submit" name="save_article" class="btn btn-primary">Save Article</button>
                            <a href="<?php echo SITE_URL; ?>/admin/manage_articles.php"
                                class="btn btn-secondary">Cancel</a>
                        </div>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-container">
            <h3>All Articles</h3>
            <?php if (count($articles) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                    <tr>
                        <td>
                            <?php if ($article['image']): ?>
                            <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($article['image']); ?>"
                                class="preview-img" alt="">
                            <?php else: ?>
                            <span style="color:#D7DDD9;">No img</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo h($article['title']); ?></strong></td>
                        <td><?php echo h($article['author']); ?></td>
                        <td><?php echo h($article['category']); ?></td>
                        <td><?php echo $article['is_published'] ? '<span style="color:#4FA08A;">Published</span>' : '<span style="color:#999;">Draft</span>'; ?>
                        </td>
                        <td><small><?php echo formatDate($article['created_at']); ?></small></td>
                        <td>
                            <a href="?edit=<?php echo $article['id']; ?>" class="btn btn-sm btn-primary"
                                style="padding:0.3rem 0.8rem;font-size:0.8rem;">Edit</a>
                            <a href="?delete=<?php echo $article['id']; ?>" class="btn btn-sm btn-danger"
                                style="padding:0.3rem 0.8rem;font-size:0.8rem;"
                                onclick="return confirm('Delete this article?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="color:#999;text-align:center;padding:2rem;">No articles created yet. Click "+ New Article" to get
                started.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>