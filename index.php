<?php

/**
 * Elpis Counselling Centre - Homepage
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$services = getServices();
$testimonials = getTestimonials();
$articles = getArticles(3);
$events = getUpcomingEvents(3);
$galleryEvents = getGalleryEvents(6);

include __DIR__ . '/includes/header.php';
?>
<!-- ============================================================
     HERO SECTION
     ============================================================ -->
<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Your Journey to <span>Emotional Wellbeing</span> Begins Here</h1>
            <p>Elpis Counselling Centre provides compassionate, evidence-based mental health and psychosocial support
                tailored to the Kenyan context. Located in Westlands, Nairobi, we're here for you.</p>
            <div class="hero-buttons">
                <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-accent">Book a Session</a>
                <a href="<?php echo SITE_URL; ?>/services.php" class="btn btn-secondary"
                    style="border-color:#fff;color:#fff;">Explore Services</a>
            </div>
        </div>
        <div class="hero-image">
            <img src="<?php echo SITE_URL; ?>/images/project.jpeg"
                alt="Elpis Counselling Centre - Professional Counselling Services">
        </div>
    </div>
</section>

<!-- ============================================================
     SERVICES OVERVIEW
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">What We Offer</p>
            <h2>Our Counselling Services</h2>
            <p>We provide a wide range of professional counselling services designed to support your mental health and
                emotional wellbeing journey.</p>
        </div>

        <div class="services-grid">
            <?php foreach ($services as $service): ?>
                <div class="service-card">
                    <div class="service-icon">&#9733;</div>
                    <h3><?php echo h($service['title']); ?></h3>
                    <p><?php echo h($service['description']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?php echo SITE_URL; ?>/services.php" class="btn btn-primary">View All Services</a>
        </div>
    </div>
</section>

<!-- ============================================================
     ABOUT BRIEF
     ============================================================ -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Who We Are</p>
            <h2>About Elpis Counselling Centre</h2>
        </div>
        <div style="max-width:800px;margin:0 auto;text-align:center;">
            <p style="font-size:1.1rem;line-height:1.8;color:#555;">
                Established in <strong>2018</strong>, Elpis Counselling Centre is a Nairobi-based registered mental
                health and psychosocial support organization fully aligned with <strong>WHO 2026 Mental Health at Work
                    guidelines</strong>. We are committed to democratizing access to evidence-based emotional wellbeing
                support for individuals, families, corporate teams, and marginalized communities across Kenya.
            </p>
            <p style="font-size:1.1rem;line-height:1.8;color:#555;margin-top:1rem;">
                Our practice is grounded in a <strong>trauma-informed, culturally sensitive approach</strong> that
                centers Kenyan lived experiences, avoiding broad-scale uniform policies that often fail to address
                localized cultural stigmas and cohort-based demands.
            </p>
            <div style="margin-top:2rem;">
                <a href="<?php echo SITE_URL; ?>/about.php" class="btn btn-primary">Learn More About Us</a>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     GALLERY OVERVIEW
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Our Gallery</p>
            <h2>Moments From Our Events</h2>
            <p>A visual glimpse of our events, workshops, and the shared experiences that make our community stronger.</p>
        </div>

        <?php if (count($galleryEvents) > 0): ?>
            <div class="gallery-masonry homepage-gallery">
                <?php foreach ($galleryEvents as $gal):
                    $gImages = getGalleryImages($gal['id']);
                    $gFeatured = $gal['featured_image'] ?: ($gImages[0]['image'] ?? '');
                    $gFrameMap = [
                        'standard' => '',
                        'large' => 'gallery-item-large',
                        'tall' => 'gallery-item-tall',
                        'wide' => 'gallery-item-wide',
                    ];
                    $gSizeClass = $gFrameMap[$gal['frame_size'] ?? 'standard'] ?? '';
                ?>
                    <div class="gallery-item <?php echo $gSizeClass; ?>" data-gallery-id="<?php echo $gal['id']; ?>"
                        data-event-name="<?php echo h($gal['event_name']); ?>"
                        data-event-date="<?php echo formatDate($gal['event_date']); ?>"
                        data-description="<?php echo h($gal['description']); ?>">
                        <div class="gallery-item-media">
                            <?php if ($gFeatured): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($gFeatured); ?>"
                                    alt="<?php echo h($gal['event_name']); ?>" class="gallery-featured">
                            <?php else: ?>
                                <div class="gallery-placeholder">&#128248;</div>
                            <?php endif; ?>

                            <?php if (count($gImages) > 1): ?>
                                <div class="gallery-slider">
                                    <?php foreach ($gImages as $gi => $gImg): ?>
                                        <img src="<?php echo SITE_URL; ?>/uploads/<?php echo h($gImg['image']); ?>"
                                            alt="<?php echo h($gal['event_name']); ?> - image <?php echo $gi + 1; ?>"
                                            data-caption="<?php echo h($gImg['caption']); ?>">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="gallery-item-overlay">
                            <h3><?php echo h($gal['event_name']); ?></h3>
                            <span class="gallery-item-date">&#128197; <?php echo formatDate($gal['event_date']); ?></span>
                            <?php if ($gal['description']): ?>
                                <p><?php echo h(truncateText($gal['description'], 90)); ?></p>
                            <?php endif; ?>
                            <span class="gallery-view-hint">&#128269; Click to view</span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:2rem;">
                <p style="color:#999;">Our gallery is being prepared. Check back soon for photos from our events and workshops.</p>
            </div>
        <?php endif; ?>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?php echo SITE_URL; ?>/gallery.php" class="btn btn-primary">View Full Gallery</a>
        </div>
    </div>
</section>

<!-- ============================================================
     TESTIMONIALS
     ============================================================ -->
<section class="section testimonials-section">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle" style="color:#E76F51;">Testimonials</p>
            <h2>What Our Clients Say</h2>
            <p>Hear from those who have walked this journey with us.</p>
        </div>

        <?php if (count($testimonials) > 0): ?>
            <div class="testimonials-grid">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-stars">
                            <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>&#9733;<?php endfor; ?>
                        </div>
                        <div class="testimonial-content">
                            "<?php echo h($testimonial['content']); ?>"
                        </div>
                        <div class="testimonial-author">
                            <div class="testimonial-avatar">
                                <?php echo strtoupper(substr($testimonial['client_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <h4><?php echo h($testimonial['client_name']); ?></h4>
                                <span><?php echo h($testimonial['client_role'] ?? 'Client'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="text-align:center;padding:2rem;">
                <p style="color:rgba(255,255,255,0.6);">Testimonials coming soon. Your journey matters to us.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     LATEST ARTICLES
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Educational Resources</p>
            <h2>Latest Articles & Insights</h2>
            <p>Explore our collection of articles and educational materials on mental health, wellbeing, and personal
                growth.</p>
        </div>

        <?php if (count($articles) > 0): ?>
            <div class="articles-grid">
                <?php foreach ($articles as $article): ?>
                    <div class="article-card">
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
                            <p><?php echo h(truncateText($article['excerpt'] ?? $article['content'], 120)); ?></p>
                            <a href="<?php echo SITE_URL; ?>/article.php?slug=<?php echo h($article['slug']); ?>"
                                class="btn btn-primary btn-sm">Read More</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#128218;</div>
                <p>Articles coming soon. Stay tuned for valuable mental health resources.</p>
            </div>
        <?php endif; ?>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?php echo SITE_URL; ?>/articles.php" class="btn btn-primary">View All Articles</a>
        </div>
    </div>
</section>

<!-- ============================================================
     UPCOMING EVENTS
     ============================================================ -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Events & Workshops</p>
            <h2>Upcoming Events</h2>
            <p>Join our free evidence-based webinars, in-person workshops, and corporate training sessions.</p>
        </div>

        <?php if (count($events) > 0): ?>
            <div class="events-grid">
                <?php foreach ($events as $event): ?>
                    <div class="event-card">
                        <div class="event-card-header">
                            <div class="date"><?php echo formatDate($event['event_date']); ?></div>
                            <h3><?php echo h($event['title']); ?></h3>
                        </div>
                        <div class="event-card-body">
                            <p><?php echo h(truncateText($event['description'], 150)); ?></p>
                            <?php if ($event['venue']): ?>
                                <p style="font-size:0.85rem;color:#4FA08A;">&#9906; <?php echo h($event['venue']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="event-card-footer">
                            <span class="event-price <?php echo $event['price'] == 0 ? 'free' : ''; ?>">
                                <?php echo $event['price'] > 0 ? 'KES ' . number_format($event['price'], 2) : 'Free'; ?>
                            </span>
                            <a href="<?php echo SITE_URL; ?>/events.php?book=<?php echo $event['id']; ?>"
                                class="btn btn-primary btn-sm">Book Now</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">&#128197;</div>
                <p>No upcoming events at the moment. Check back soon!</p>
            </div>
        <?php endif; ?>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?php echo SITE_URL; ?>/events.php" class="btn btn-primary">View All Events</a>
        </div>
    </div>
</section>

<!-- ============================================================
     LOCATION / MAP
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Visit Us</p>
            <h2>Find Us at Westlands</h2>
            <p>Krishna Centre, 2nd Floor, Westlands — Nairobi's Premier Business District</p>
        </div>

        <div class="map-container">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3988.808877621373!2d36.81354331475398!3d-1.264664999073833!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x182f1724f6b1e1e1%3A0x1b1c1b1c1b1c1b1c!2sWestlands%2C%20Nairobi!5e0!3m2!1sen!2ske!4v1"
                loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                title="Elpis Counselling Centre Location - Krishna Centre, Westlands, Nairobi" allowfullscreen>
            </iframe>
        </div>
    </div>
</section>

<!-- ============================================================
     CTA SECTION
     ============================================================ -->
<section class="section section-dark" style="text-align:center;">
    <div class="container">
        <h2 style="font-size:2rem;margin-bottom:1rem;">Ready to Start Your Journey?</h2>
        <p
            style="font-size:1.1rem;margin-bottom:2rem;color:rgba(255,255,255,0.8);max-width:600px;margin-left:auto;margin-right:auto;">
            Take the first step towards emotional wellbeing. Our compassionate team is here to support you.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-accent">Book a Confidential Session</a>
            <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-secondary"
                style="border-color:#fff;color:#fff;">Get in Touch</a>
        </div>
    </div>
</section>

<!-- Lightbox for homepage gallery -->
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
    // Gallery data store for homepage lightbox
    var galleryData = {};
    var currentGalleryId = null;
    var currentImageIndex = 0;
    var homepageSliderTimers = {};

    <?php foreach ($galleryEvents as $event): ?>
        <?php $hImages = getGalleryImages($event['id']); ?>
        galleryData[<?php echo (int)$event['id']; ?>] = {
            name: <?php echo json_encode($event['event_name']); ?>,
            date: <?php echo json_encode(formatDate($event['event_date'])); ?>,
            desc: <?php echo json_encode($event['description']); ?>,
            images: [
                <?php foreach ($hImages as $hImg): ?> {
                        src: <?php echo json_encode(SITE_URL . '/uploads/' . $hImg['image']); ?>,
                        caption: <?php echo json_encode($hImg['caption']); ?>
                    },
                <?php endforeach; ?>
            ]
        };
    <?php endforeach; ?>

    // Hover slider: cycle through the event's images while hovering
    document.querySelectorAll('.homepage-gallery .gallery-item').forEach(function(item) {
        var id = item.getAttribute('data-gallery-id');
        var slider = item.querySelector('.gallery-slider');

        if (slider) {
            var imgs = slider.querySelectorAll('img');
            var idx = 0;

            item.addEventListener('mouseenter', function() {
                slider.style.display = 'flex';
                idx = 0;
                showHomeSlide();
                homepageSliderTimers[id] = setInterval(function() {
                    idx = (idx + 1) % imgs.length;
                    showHomeSlide();
                }, 1200);
            });

            item.addEventListener('mouseleave', function() {
                clearInterval(homepageSliderTimers[id]);
                slider.style.display = 'none';
            });

            function showHomeSlide() {
                imgs.forEach(function(img, i) {
                    img.style.opacity = (i === idx) ? '1' : '0';
                });
            }
        }

        item.addEventListener('click', function() {
            openHomeGalleryLightbox(id);
        });
    });

    function openHomeGalleryLightbox(id) {
        var data = galleryData[id];
        if (!data || data.images.length === 0) {
            var featured = document.querySelector('.homepage-gallery .gallery-item[data-gallery-id="' + id + '"] .gallery-featured');
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
        updateHomeLightbox();
    }

    function closeGalleryLightbox() {
        document.getElementById('galleryLightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    function galleryNav(direction) {
        var data = galleryData[currentGalleryId];
        if (!data) return;
        currentImageIndex = (currentImageIndex + direction + data.images.length) % data.images.length;
        updateHomeLightbox();
    }

    function updateHomeLightbox() {
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

        // Toggle label blocks based on whether there is content
        var capBlock = document.querySelector('.gallery-lightbox-caption-block');
        var descBlock = document.querySelector('.gallery-lightbox-desc-block');
        if (capBlock) capBlock.classList.toggle('has-caption', !!(img.caption || '').trim());
        if (descBlock) descBlock.classList.toggle('has-desc', !!(data.desc || '').trim());
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
    document.getElementById('galleryLightbox') && document.getElementById('galleryLightbox').addEventListener('click', function(e) {
        if (e.target === this) closeGalleryLightbox();
    });
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
