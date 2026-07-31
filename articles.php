<?php

/**
 * Elpis Counselling Centre - Articles & Educational Materials
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 9;
$offset = ($page - 1) * $limit;

$db = getDB();
$totalStmt = $db->query("SELECT COUNT(*) FROM articles WHERE is_published = 1");
$totalArticles = $totalStmt->fetchColumn();
$totalPages = ceil($totalArticles / $limit);

$articles = getArticles($limit, $offset);

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Articles & Resources</h1>
        <p>Educational materials to support your mental health and wellbeing journey</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <?php if (count($articles) > 0): ?>
            <div class="search-bar">
                <input type="text" id="articleSearch" placeholder="Search articles..." onkeyup="filterArticles()">
            </div>

            <div class="articles-grid" id="articlesGrid">
                <?php foreach ($articles as $article): ?>
                    <div class="article-card article-item">
                        <div class="article-card-image">
                            <?php if ($article['image']): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($article['image']); ?>"
                                    alt="<?php echo h($article['title']); ?>">
                            <?php else: ?>
                                &#128218;
                            <?php endif; ?>
                        </div>
                        <div class="article-card-body">
                            <div class="meta">
                                <?php echo formatDate($article['created_at']); ?> &middot;
                                <?php echo h($article['category'] ?? 'General'); ?>
                            </div>
                            <h3><a
                                    href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo h($article['slug']); ?>"><?php echo h($article['title']); ?></a>
                            </h3>
                            <p><?php echo h(truncateText($article['excerpt'] ?? $article['content'], 150)); ?></p>
                            <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo h($article['slug']); ?>"
                                class="btn btn-primary btn-sm">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#128218;</div>
                <h3 style="margin-bottom:0.5rem;">Articles Coming Soon</h3>
                <p>We're preparing educational materials to support your mental health journey. Check back soon for expert
                    insights on stress management, emotional wellbeing, relationships, and more.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    function filterArticles() {
        const input = document.getElementById('articleSearch');
        const filter = input.value.toLowerCase();
        const items = document.querySelectorAll('.article-item');

        items.forEach(item => {
            const title = item.querySelector('h3 a').textContent.toLowerCase();
            const desc = item.querySelector('p').textContent.toLowerCase();
            if (title.includes(filter) || desc.includes(filter)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>