<?php

/**
 * Elpis Counselling Centre - Services
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$services = getServices();

include __DIR__ . '/includes/header.php';
?>

<section class="page-banner">
    <div class="container">
        <h1>Our Services</h1>
        <p>Comprehensive counselling services tailored to your needs</p>
    </div>
</section>

<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">What We Offer</p>
            <h2>Complete Range of Counselling Services</h2>
            <p>Explore our full range of professional counselling services. Each service is delivered with compassion,
                cultural sensitivity, and evidence-based practice.</p>
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
    </div>
</section>

<section class="section section-light">
    <div class="container">
        <div style="max-width:800px;margin:0 auto;text-align:center;">
            <p class="section-subtitle">Our Modalities</p>
            <h2>Evidence-Based Therapeutic Approaches</h2>
            <p style="margin-top:1rem;font-size:1.05rem;color:#555;">
                Our practice integrates multiple evidence-based modalities to provide the most effective care:
            </p>
            <div
                style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1.5rem;margin-top:2.5rem;">
                <div style="background:#fff;border-radius:12px;padding:1.5rem;border:1px solid #D7DDD9;">
                    <div style="font-weight:700;color:#3F5195;">Cognitive Behavioral Therapy (CBT)</div>
                </div>
                <div style="background:#fff;border-radius:12px;padding:1.5rem;border:1px solid #D7DDD9;">
                    <div style="font-weight:700;color:#3F5195;">Solution-Focused Brief Therapy</div>
                </div>
                <div style="background:#fff;border-radius:12px;padding:1.5rem;border:1px solid #D7DDD9;">
                    <div style="font-weight:700;color:#3F5195;">EMDR Therapy</div>
                </div>
                <div style="background:#fff;border-radius:12px;padding:1.5rem;border:1px solid #D7DDD9;">
                    <div style="font-weight:700;color:#3F5195;">Culturally Adapted Narrative Therapy</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-dark" style="text-align:center;">
    <div class="container">
        <h2 style="margin-bottom:1rem;">Not Sure Which Service Fits Your Needs?</h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:2rem;font-size:1.1rem;">
            Contact us for a free consultation. We'll help you find the right support.
        </p>
        <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-accent">Book a Free Consultation</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>