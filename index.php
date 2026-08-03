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

<?php include __DIR__ . '/includes/footer.php'; ?>