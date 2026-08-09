# Elpis Counselling Centre - SMTP Email Fix & Admin Settings

## Task
Replace the fragile `mail()` function (which fails on XAMPP with "Failed to connect to mailserver at localhost port 25") with PHPMailer using dynamic SMTP settings configurable from the admin dashboard.

## Steps
- [x] Download & extract PHPMailer into `includes/phpmailer/`
- [x] Add SMTP default constants to `includes/config.php`
- [x] Add a PHPMailer autoloader (`includes/phpmailer/autoload.php`)
- [x] Rewrite `sendEmail()` in `includes/functions.php` to use PHPMailer + settings table, with graceful error handling
- [x] Add convenience functions: `getSmtpSettings()`, `sendTestEmail()`
- [x] Create `admin/settings.php` page with SMTP configuration form + test email button
- [x] Add "Settings" link to the admin dashboard sidebar
- [x] Add default SMTP settings rows to `sql/database.sql`
- [x] Verify PHPMailer loads correctly (v6.9.1)
- [ ] Test the settings page, send a test email, verify no warning appears
