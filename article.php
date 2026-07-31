<?php

/**
 * Elpis Counselling Centre - Single Article View
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$article = getArticleBySlug($slug);

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    include __DIR__ . '/includes/header.php';
    echo '<section class="page-banner"><div class="container"><h1>Article Not Found</h1></div></section>';
    echo '<section class="section section-white"><div class="container" style="text-align:center;padding:4rem 0;"><p style="font-size:1.1rem;color:#999;">The article you\'re looking for doesn\'t exist or has been removed.</p><a href="' . SITE_URL . '/articles.php" class="btn btn-primary" style="margin-top:1.5rem;">Back to Articles</a></div></section>';
    include __DIR__ . '/includes/footer.php';
    exit;
}

include __DIR__ . '/includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <div class="breadcrumb">
            <a href="<?php echo SITE_URL; ?>/index.php">Home</a> &rsaquo;
            <a href="<?php echo SITE_URL; ?>/articles.php">Articles</a> &rsaquo;
            <span><?php echo h($article['title']); ?></span>
        </div>
        <h1><?php echo h($article['title']); ?></h1>
        <div style="color:#999;font-size:0.9rem;margin-top:0.5rem;">
            <span>By <?php echo h($article['author']); ?></span> &middot;
            <span><?php echo formatDate($article['created_at']); ?></span> &middot;
            <span><?php echo h($article['category'] ?? 'General'); ?></span>
        </div>
    </div>
</section>

<article class="section section-white">
    <div class="container">
        <div style="max-width:800px;margin:0 auto;">
            <?php if ($article['image']): ?>
                <div style="border-radius:15px;overflow:hidden;margin-bottom:2rem;">
                    <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($article['image']); ?>" alt="<?php echo h($article['title']); ?>" style="width:100%;max-height:400px;object-fit:cover;">
                </div>
            <?php endif; ?>

            <div style="font-size:1.05rem;line-height:1.9;color:#444;">
                <?php echo $article['content']; ?>
            </div>

            <hr style="border:none;border-top:1px solid #D7DDD9;margin:3rem 0;">

            <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
                <div>
                    <p style="font-size:0.9rem;color:#999;">Published: <?php echo formatDate($article['created_at']); ?></p>
                    <?php if ($article['updated_at'] != $article['created_at']): ?>
                        <p style="font-size:0.9rem;color:#999;">Updated: <?php echo formatDate($article['updated_at']); ?></p>
                    <?php endif; ?>
                </div>
                <a href="<?php echo SITE_URL; ?>/articles.php" class="btn btn-primary btn-sm">&larr; Back to Articles</a>
            </div>
        </div>
    </div>
</article>

<?php include __DIR__ . '/includes/footer.php'; ?>