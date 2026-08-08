<?php

/**
 * Elpis Counselling Centre - Event Gallery
 * Portfolio-style masonry gallery with hover slider and lightbox.
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$events = getGalleryEvents();

include __DIR__ . '/includes/header.php';
?>
<section class="page-banner">
    <div class="container">
        <h1>Gallery</h1>
        <p>Moments and memories from our events and workshops</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <?php if (count($events) > 0): ?>
            <div class="gallery-masonry">
<?php foreach ($events as $event):
                    $images = getGalleryImages($event['id']);
                    $featured = $event['featured_image'] ?: ($images[0]['image'] ?? '');
                    // Map the event's selected frame size to a CSS class
                    $frameMap = [
                        'standard' => '',
                        'large' => 'gallery-item-large',
                        'tall' => 'gallery-item-tall',
                        'wide' => 'gallery-item-wide',
                    ];
                    $sizeClass = $frameMap[$event['frame_size'] ?? 'standard'] ?? '';
                ?>
                    <div class="gallery-item <?php echo $sizeClass; ?>" data-gallery-id="<?php echo $event['id']; ?>"
                        data-event-name="<?php echo h($event['event_name']); ?>"
                        data-event-date="<?php echo formatDate($event['event_date']); ?>"
                        data-description="<?php echo h($event['description']); ?>">
                        <div class="gallery-item-media">
                            <?php if ($featured): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($featured); ?>"
                                    alt="<?php echo h($event['event_name']); ?>" class="gallery-featured">
                            <?php else: ?>
                                <div class="gallery-placeholder">&#128248;</div>
                            <?php endif; ?>

                            <?php if (count($images) > 1): ?>
                                <div class="gallery-slider">
                                    <?php foreach ($images as $i => $img): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($img['image']); ?>"
                                            alt="<?php echo h($event['event_name']); ?> - image <?php echo $i + 1; ?>"
                                            data-caption="<?php echo h($img['caption']); ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="gallery-item-overlay">
                            <h3><?php echo h($event['event_name']); ?></h3>
                            <span class="gallery-item-date">&#128197; <?php echo formatDate($event['event_date']); ?></span>
                            <?php if ($event['description']): ?>
                                <p><?php echo h(truncateText($event['description'], 90)); ?></p>
                            <?php endif; ?>
                            <span class="gallery-view-hint">&#128269; Click to view</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#128248;</div>
                <h3 style="margin-bottom:0.5rem;">No Gallery Items Yet</h3>
                <p>Our gallery is being prepared. Check back soon for photos from our events and workshops.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Lightbox -->
<div id="galleryLightbox" class="gallery-lightbox" style="display:none;">
    <button class="gallery-lightbox-close" onclick="closeGalleryLightbox()">&times;</button>
    <button class="gallery-lightbox-nav gallery-prev" onclick="galleryNav(-1)">&#10094;</button>
    <div class="gallery-lightbox-content">
        <div class="gallery-lightbox-slider">
            <img src="" alt="Gallery image" class="gallery-lightbox-img">
        </div>
<div class="gallery-lightbox-info">
            <h3 class="gallery-lightbox-title"></h3>
            <span class="gallery-lightbox-date"></span>
            <div class="gallery-lightbox-caption-block">
                <span class="gallery-lightbox-label">Caption:</span>
                <p class="gallery-lightbox-caption"></p>
            </div>
            <div class="gallery-lightbox-desc-block">
                <span class="gallery-lightbox-label">About this gallery:</span>
                <p class="gallery-lightbox-desc"></p>
            </div>
            <div class="gallery-lightbox-counter"></div>
        </div>
    </div>
    <button class="gallery-lightbox-nav gallery-next" onclick="galleryNav(1)">&#10095;</button>
</div>

<script>
    // Gallery data store: eventId -> {name, date, desc, images:[{src, caption}]}
    var galleryData = {};
    var currentGalleryId = null;
    var currentImageIndex = 0;
    var sliderTimers = {};

    <?php foreach ($events as $event): ?>
        <?php $images = getGalleryImages($event['id']); ?>
        galleryData[<?php echo (int)$event['id']; ?>] = {
            name: <?php echo json_encode($event['event_name']); ?>,
            date: <?php echo json_encode(formatDate($event['event_date'])); ?>,
            desc: <?php echo json_encode($event['description']); ?>,
            images: [
                <?php foreach ($images as $img): ?> {
                        src: <?php echo json_encode(SITE_URL . '/uploads/' . $img['image']); ?>,
                        caption: <?php echo json_encode($img['caption']); ?>
                    },
                <?php endforeach; ?>
            ]
        };
    <?php endforeach; ?>

    // Hover slider: cycle through the event's images while hovering
    document.querySelectorAll('.gallery-item').forEach(function(item) {
        var id = item.getAttribute('data-gallery-id');
        var media = item.querySelector('.gallery-item-media');
        var slider = item.querySelector('.gallery-slider');

        if (slider) {
            var imgs = slider.querySelectorAll('img');
            var idx = 0;

            item.addEventListener('mouseenter', function() {
                slider.style.display = 'flex';
                idx = 0;
                showSlide();
                sliderTimers[id] = setInterval(function() {
                    idx = (idx + 1) % imgs.length;
                    showSlide();
                }, 1200);
            });

            item.addEventListener('mouseleave', function() {
                clearInterval(sliderTimers[id]);
                slider.style.display = 'none';
            });

            function showSlide() {
                imgs.forEach(function(img, i) {
                    img.style.opacity = (i === idx) ? '1' : '0';
                });
            }
        }

        // Click opens lightbox
        item.addEventListener('click', function() {
            openGalleryLightbox(id);
        });
    });

    function openGalleryLightbox(id) {
        var data = galleryData[id];
        if (!data || data.images.length === 0) {
            // Fall back to featured if no images array
            var featured = document.querySelector('.gallery-item[data-gallery-id="' + id + '"] .gallery-featured');
            if (featured) {
                data = {
                    name: 'Gallery',
                    date: '',
                    desc: '',
                    images: [{ src: featured.src, caption: '' }]
                };
            } else {
                return;
            }
        }
        currentGalleryId = id;
        currentImageIndex = 0;
        document.getElementById('galleryLightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        updateLightbox();
    }

    function closeGalleryLightbox() {
        document.getElementById('galleryLightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    function galleryNav(direction) {
        var data = galleryData[currentGalleryId];
        if (!data) return;
        currentImageIndex = (currentImageIndex + direction + data.images.length) % data.images.length;
        updateLightbox();
    }

    function updateLightbox() {
        var data = galleryData[currentGalleryId];
        if (!data || data.images.length === 0) return;

        var img = data.images[currentImageIndex];
        document.querySelector('.gallery-lightbox-img').src = img.src;
        document.querySelector('.gallery-lightbox-img').alt = img.caption || data.name;
        document.querySelector('.gallery-lightbox-title').textContent = data.name;
        document.querySelector('.gallery-lightbox-date').textContent = data.date;
        document.querySelector('.gallery-lightbox-caption').textContent = img.caption || '';
        document.querySelector('.gallery-lightbox-desc').textContent = data.desc || '';
        document.querySelector('.gallery-lightbox-counter').textContent =
            (currentImageIndex + 1) + ' / ' + data.images.length;
    }

    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        var lightbox = document.getElementById('galleryLightbox');
        if (lightbox.style.display === 'flex') {
            if (e.key === 'Escape') closeGalleryLightbox();
            if (e.key === 'ArrowLeft') galleryNav(-1);
            if (e.key === 'ArrowRight') galleryNav(1);
        }
    });

    // Close on outside click
    document.getElementById('galleryLightbox').addEventListener('click', function(e) {
        if (e.target === this) closeGalleryLightbox();
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
