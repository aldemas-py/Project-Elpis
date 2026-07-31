<?php

/**
 * Elpis Counselling Centre - About Us
 */
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

include __DIR__ . '/includes/header.php';
?>

<!-- ============================================================
     PAGE BANNER
     ============================================================ -->
<section class="page-banner">
    <div class="container">
        <h1>About Us</h1>
        <p>Learn more about Elpis Counselling Centre and our mission</p>
    </div>
</section>

<!-- ============================================================
     ABOUT CONTENT
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div style="max-width:900px;margin:0 auto;">
            <div style="text-align:center;margin-bottom:3rem;">
                <img src="<?php echo SITE_URL; ?>/images/logo.jpeg" alt="Elpis Counselling Centre Logo"
                    style="height:80px;width:auto;margin:0 auto 1.5rem;">
                <p class="section-subtitle">Our Story</p>
                <h2>Your Trusted Partner in Mental Health</h2>
            </div>

            <div style="font-size:1.05rem;line-height:1.9;color:#555;">
                <p>
                    <strong>Elpis Counselling Centre</strong> is a Nairobi-based registered mental health and
                    psychosocial support organization that is fully aligned with <strong>World Health Organization (WHO)
                        2026 Mental Health at Work guidelines</strong>.
                </p>
                <p style="margin-top:1.5rem;">
                    Established in <strong>2018</strong>, we are committed to democratizing access to evidence-based
                    emotional wellbeing support for individuals, families, corporate teams, and marginalized communities
                    across Kenya. We provide both in-person services at our Westlands, Nairobi clinical hub and virtual
                    sessions to reach clients across the country.
                </p>
                <p style="margin-top:1.5rem;">
                    Our practice is grounded in a <strong>trauma-informed, culturally sensitive approach</strong> that
                    centers Kenyan lived experiences. We deliberately avoid broad-scale uniform policies that often fail
                    to address localized cultural stigmas and cohort-based demands. Every intervention is tailored to
                    the cultural context of Kenyan audiences to maximize relevance and impact.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     MISSION & VALUES
     ============================================================ -->
<section class="section section-light">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">Our Foundation</p>
            <h2>Mission & Core Values</h2>
        </div>

        <div style="max-width:900px;margin:0 auto;">
            <div style="background:#fff;border-radius:15px;padding:2.5rem;margin-bottom:2rem;border:1px solid #D7DDD9;">
                <h3 style="color:#4FA08A;margin-bottom:1rem;">Our Mission</h3>
                <p style="font-size:1.1rem;line-height:1.8;color:#555;">
                    To democratize access to quality, evidence-based mental health and psychosocial support that honors
                    Kenyan lived experiences, reduces stigma, and empowers individuals, families, and communities to
                    thrive.
                </p>
            </div>

            <div class="values-grid">
                <div class="value-card" style="background:#fff;border-radius:15px;border:1px solid #D7DDD9;">
                    <div class="value-icon">&#9829;</div>
                    <h3>Compassion</h3>
                    <p>We approach every client with empathy, respect, and unconditional positive regard.</p>
                </div>
                <div class="value-card" style="background:#fff;border-radius:15px;border:1px solid #D7DDD9;">
                    <div class="value-icon">&#9889;</div>
                    <h3>Evidence-Based</h3>
                    <p>Our modalities include CBT, Solution-Focused Brief Therapy, EMDR, and culturally adapted
                        narrative therapy.</p>
                </div>
                <div class="value-card" style="background:#fff;border-radius:15px;border:1px solid #D7DDD9;">
                    <div class="value-icon">&#127757;</div>
                    <h3>Culturally Grounded</h3>
                    <p>We honor Kenyan cultural contexts and lived experiences in every therapeutic interaction.</p>
                </div>
                <div class="value-card" style="background:#fff;border-radius:15px;border:1px solid #D7DDD9;">
                    <div class="value-icon">&#128101;</div>
                    <h3>Inclusive</h3>
                    <p>We serve individuals, couples, families, adolescents, corporate teams, and marginalized
                        communities.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     OUR APPROACH
     ============================================================ -->
<section class="section section-white">
    <div class="container">
        <div class="section-header">
            <p class="section-subtitle">How We Work</p>
            <h2>Our Therapeutic Approach</h2>
        </div>

        <div style="max-width:900px;margin:0 auto;">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;">
                <div style="background:#EAF4F1;border-radius:15px;padding:2rem;">
                    <h3 style="color:#4FA08A;margin-bottom:1rem;">&#128218; Public Education</h3>
                    <p style="color:#555;">We facilitate free evidence-based daily webinars, in-person workshops, and
                        corporate training sessions covering workplace stress management, healthy boundary setting,
                        emotional regulation, complex trauma recovery, and more.</p>
                </div>
                <div style="background:#EAF4F1;border-radius:15px;padding:2rem;">
                    <h3 style="color:#4FA08A;margin-bottom:1rem;">&#128153; Direct Counselling</h3>
                    <p style="color:#555;">We provide confidential counselling for individuals, couples, families, and
                        adolescent clients, addressing a full spectrum of mental health and psychosocial concerns.</p>
                </div>
                <div style="background:#EAF4F1;border-radius:15px;padding:2rem;">
                    <h3 style="color:#4FA08A;margin-bottom:1rem;">&#128170; Corporate Wellness</h3>
                    <p style="color:#555;">Burnout prevention for frontline workers and psychosocial risk management for
                        corporate teams, aligned with WHO 2026 guidelines.</p>
                </div>
                <div style="background:#EAF4F1;border-radius:15px;padding:2rem;">
                    <h3 style="color:#4FA08A;margin-bottom:1rem;">&#128640; Multidisciplinary Team</h3>
                    <p style="color:#555;">All sessions are delivered by a multidisciplinary team with combined years of
                        experience in social work, clinical psychology, and corporate wellness strategy.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     TEAM CTA
     ============================================================ -->
<section class="section section-dark" style="text-align:center;">
    <div class="container">
        <h2 style="margin-bottom:1rem;">Ready to Begin Your Healing Journey?</h2>
        <p style="color:rgba(255,255,255,0.8);margin-bottom:2rem;font-size:1.1rem;">
            Our team is here to support you with compassion, expertise, and cultural understanding.
        </p>
        <a href="<?php echo SITE_URL; ?>/booking.php" class="btn btn-accent">Book a Confidential Session</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>