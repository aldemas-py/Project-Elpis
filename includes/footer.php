<?php

/**
 * Elpis Counselling Centre - Site Footer
 */
?>
<!-- ============================================================
     FOOTER
     ============================================================ -->
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-about">
                <h3><span style="color:#E76F51;">Elpis</span> Counselling Centre</h3>
<p>Your trusted partner in mental health and emotional wellbeing. Based in Westlands, Nairobi, we
                    provide evidence-based, culturally sensitive counselling services for individuals, couples,
                    families, and corporate teams.</p>
                <div class="social-links">
                    <a href="https://chat.whatsapp.com/Ctq78CoaOcr82hoDUkFoRX?s=cl&p=a&mlu=0" target="_blank"
                        rel="noopener" aria-label="WhatsApp">W</a>
                    <a href="https://www.instagram.com/elpiscounselingcenter" target="_blank" rel="noopener"
                        aria-label="Instagram">I</a>
                    <a href="https://www.facebook.com/people/Elpis-Counseling-Centre/61584859559119/" target="_blank"
                        rel="noopener" aria-label="Facebook">F</a>
                </div>
            </div>

            <div>
                <h4>Quick Links</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">About Us</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Our Services</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/booking.php">Book a Session</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/articles.php">Articles & Resources</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/events.php">Upcoming Events</a></li>
                </ul>
            </div>

            <div>
                <h4>Our Services</h4>
                <ul class="footer-links">
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Individual Counselling</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Couple & Marriage</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Family Counselling</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Trauma Therapy</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Addiction Recovery</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/services.php">Corporate Wellness</a></li>
                </ul>
            </div>

<div>
                <h4>Contact Info</h4>
                <ul class="footer-contact">
                    <li>
                        <span class="icon">&#9906;</span>
                        <span>Krishna Centre Building, 2nd Floor (Suite D-16)<br>Westlands, Nairobi</span>
                    </li>
                    <li>
                        <span class="icon">&#9742;</span>
                        <span>0718674888 / 0708854435</span>
                    </li>
                    <li>
                        <span class="icon">&#9993;</span>
                        <span>elpiscounselling24@gmail.com</span>
                    </li>
                    <li>
                        <span class="icon">&#128339;</span>
                        <span>Mon - Fri: 8:00 AM - 6:00 PM<br>Sat: 9:00 AM - 1:00 PM</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</span>
            <span>Registered Mental Health & Psychosocial Support Organization</span>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>

<?php if (isset($isAdminPage) && $isAdminPage): ?>
<!-- Admin Session Auto-Logout: 5 minutes of inactivity -->
<script>
(function() {
    // Session timeout in milliseconds (5 minutes)
    var SESSION_TIMEOUT = <?php echo defined('SESSION_TIMEOUT') ? SESSION_TIMEOUT * 1000 : 300000; ?>;
    var logoutUrl = '<?php echo SITE_URL; ?>/admin/logout.php?expired=1';
    var idleTimer = null;

    function resetIdleTimer() {
        clearTimeout(idleTimer);
        idleTimer = setTimeout(function() {
            // Auto-logout after inactivity
            window.location.href = logoutUrl;
        }, SESSION_TIMEOUT);
    }

    // Reset timer on user activity
    var events = ['click', 'mousemove', 'keydown', 'scroll', 'touchstart', 'mousedown'];
    for (var i = 0; i < events.length; i++) {
        document.addEventListener(events[i], resetIdleTimer);
    }

    // Warn before closing/beforeunload is NOT used (session cookie dies on browser close automatically)
    // Start the idle timer on page load
    resetIdleTimer();
})();
</script>
<?php endif; ?>
</body>

</html>