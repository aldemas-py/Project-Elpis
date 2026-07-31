# Elpis Counselling Centre

**A Digital Mental Health & Psychosocial Support Platform — Fully Aligned with WHO 2026 Mental Health at Work Guidelines**

Elpis Counselling Centre is a Nairobi-based registered mental health and psychosocial support organization (est. 2018). This web platform provides an engaging, accessible digital presence that connects individuals, couples, families, corporate teams, and marginalized communities to evidence-based emotional wellbeing support across Kenya.

---

## Table of Contents

1. [Compliance & Policy](#compliance--policy)
2. [Features Overview](#features-overview)
3. [Technology Stack](#technology-stack)
4. [Production Server Installation](#production-server-installation)
5. [Security Hardening](#security-hardening)
6. [M-Pesa Integration](#m-pesa-integration)
7. [Database Schema](#database-schema)
8. [File Structure](#file-structure)
9. [Color Palette & Design System](#color-palette--design-system)
10. [Admin Panel](#admin-panel)
11. [Maintenance & Support](#maintenance--support)
12. [License](#license)

---

## Compliance & Policy

### WHO 2026 Mental Health at Work Guidelines Alignment

This platform is designed in accordance with the **World Health Organization (WHO) 2026 Mental Health at Work guidelines**, specifically:

| Guideline | Implementation |
|-----------|---------------|
| **Psychosocial risk management** | Corporate wellness programs and burnout prevention resources for frontline workers |
| **Destigmatization of mental health** | Public-facing educational articles, free daily webinars, and workshop listings |
| **Accessible care pathways** | Simplified booking inquiry system with 24-hour response commitment |
| **Culturally adapted interventions** | Content and services grounded in Kenyan lived experiences; therapeutic modalities (CBT, SFBT, EMDR, culturally adapted narrative therapy) |
| **Confidentiality & privacy** | Session data protected via HTTPS-ready architecture, prepared statements, and session-based admin authentication |

### Data Protection & Privacy Policy

- **Data Minimization**: Only essential personally identifiable information (name, email, phone) is collected through booking and contact forms.
- **Purpose Limitation**: Client data is used exclusively for appointment scheduling, event booking, and service communication.
- **Access Control**: Administrative access is restricted to authenticated users via bcrypt-hashed passwords and PHP session management.
- **Data Retention**: Appointment and contact records are retained for operational purposes; no data is shared with third parties without explicit consent.
- **Secure Transmission**: All form data is processed server-side; XSS prevention via `htmlspecialchars()` on all output.
- **Compliance**: The platform respects Kenyan data protection laws (Data Protection Act, 2019) and WHO confidentiality standards for mental health data.

### Ethical Safeguards

- **Suicide Intervention**: Dedicated service listing with crisis intervention protocols.
- **Trauma-Informed Practice**: All content and service descriptions use non-triggering, compassionate language.
- **Inclusive Access**: Services available to marginalized communities, adolescents, and individuals across socioeconomic backgrounds.
- **Free Resources**: Free evidence-based daily webinars and educational materials are prominently featured.

---

## Features Overview

### Public Pages

| Page | Features |
|------|----------|
| **Homepage** | Hero section with branding, 19 counselling services grid, about brief, testimonials carousel, latest articles, upcoming events, Google Maps embed, CTA |
| **About Us** | Organization history (est. 2018), WHO 2026 alignment, mission & values, therapeutic approach, multidisciplinary team |
| **Services** | Full listing of 19 counselling services + 4 therapeutic modalities (CBT, SFBT, EMDR, narrative therapy) |
| **Book a Session** | Inquiry form with service selection, preferred date picker, validation; 24-hour response commitment |
| **Articles** | Blog with search filter, pagination, category display, single-article view |
| **Events** | Upcoming events with M-Pesa payment modal, free/paid filtering, venue details |
| **Contact** | Contact form, Google Maps embed, office hours, phone/email/physical address |

### Admin Panel

| Page | Features |
|------|----------|
| **Login** | bcrypt password verification, session-based authentication, brute-force resistant |
| **Dashboard** | Stats overview (services, articles, events, appointments, testimonials), recent appointments table |
| **Appointments** | Full list with status management (pending/contacted/completed/cancelled) via dropdown |
| **Services** | Full CRUD with active/inactive toggle, display order, icon field |
| **Articles** | Full CRUD with image upload, rich content, slug generation, publish toggle, category |
| **Events** | Full CRUD with image upload, date/time/venue/price, M-Pesa integration |
| **Testimonials** | Full CRUD with approval toggle, rating system (1–5 stars) |
| **Logout** | Secure session destruction with redirect |

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| **Frontend** | Vanilla HTML5, CSS3 (responsive, mobile-first), Vanilla JavaScript (ES6+) |
| **Backend** | Vanilla PHP 8.x (procedural, no frameworks) |
| **Database** | MySQL 8.x (via MariaDB/MySQL) with PDO prepared statements |
| **Payments** | M-Pesa Daraja API (STK Push — sandbox mode with production-ready hooks) |
| **Web Server** | Apache/Nginx with mod_rewrite, PHP-FPM |
| **Email** | PHP `mail()` with HTML-formatted transactional templates |
| **Authentication** | bcrypt password hashing (`password_hash`/`password_verify`), PHP sessions |
| **Security** | XSS prevention (`htmlspecialchars`), SQL injection prevention (PDO prepared statements), HTTP-only session cookies, CSRF-ready architecture |

---

## Production Server Installation

### Prerequisites

- **Web Server**: Apache 2.4+ or Nginx 1.18+ with PHP 8.0+
- **PHP Extensions**: `pdo_mysql`, `mysqli`, `mbstring`, `gd` (for image uploads), `json`, `session`
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **SSL Certificate**: Let's Encrypt or commercial SSL for HTTPS
- **Domain**: A registered domain pointed to your server's public IP

### Step 1: Server Environment Setup (Ubuntu/Debian Example)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache, PHP, and MySQL
sudo apt install apache2 php php-mysql php-mbstring php-gd php-json libapache2-mod-php mysql-server -y

# Enable Apache modules
sudo a2enmod rewrite
sudo a2enmod headers
sudo systemctl restart apache2

# Secure MySQL installation
sudo mysql_secure_installation
```

### Step 2: Upload Application Files

```bash
# Navigate to web root
cd /var/www/html

# Clone or upload the project
git clone https://github.com/your-username/elpis-counselling.git

# Or using SCP from local machine
scp -r Project-Elpis/* user@your-server:/var/www/html/elpis/

# Set correct permissions
sudo chown -R www-data:www-data /var/www/html/elpis/
sudo chmod -R 755 /var/www/html/elpis/
sudo chmod -R 775 /var/www/html/elpis/uploads/
```

### Step 3: Create Database & Import Schema

```bash
# Log into MySQL
sudo mysql -u root -p

# In MySQL console:
CREATE DATABASE elpis_counselling CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'elpis_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON elpis_counselling.* TO 'elpis_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Import the database schema
mysql -u elpis_user -p elpis_counselling < /var/www/html/elpis/sql/database.sql
```

### Step 4: Configure Application

Edit `/var/www/html/elpis/includes/config.php`:

```php
// Database credentials (use the MySQL user created above)
define('DB_HOST', 'localhost');
define('DB_NAME', 'elpis_counselling');
define('DB_USER', 'elpis_user');
define('DB_PASS', 'your_strong_password_here');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_NAME', 'Elpis Counselling Centre');
define('SITE_TAGLINE', 'Your Journey to Emotional Wellbeing Begins Here');
define('ADMIN_EMAIL', 'admin@yourdomain.com');

// M-Pesa Production Credentials (see M-Pesa section)
define('MPESA_CONSUMER_KEY', 'your_production_consumer_key');
define('MPESA_CONSUMER_SECRET', 'your_production_consumer_secret');
define('MPESA_PASSKEY', 'your_production_passkey');
define('MPESA_SHORTCODE', 'your_production_shortcode');
define('MPESA_ENVIRONMENT', 'production');

// Enable secure cookies over HTTPS
ini_set('session.cookie_secure', 1);
```

### Step 5: Configure Virtual Host (Apache)

Create `/etc/apache2/sites-available/elpis.conf`:

```apache
<VirtualHost *:80>
    ServerName elpiscounselling.co.ke
    ServerAlias www.elpiscounselling.co.ke
    DocumentRoot /var/www/html/elpis

    <Directory /var/www/html/elpis>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/elpis_error.log
    CustomLog ${APACHE_LOG_DIR}/elpis_access.log combined
</VirtualHost>
```

```bash
# Enable site and SSL (using Let's Encrypt)
sudo a2ensite elpis.conf
sudo certbot --apache -d elpiscounselling.co.ke -d www.elpiscounselling.co.ke
sudo systemctl reload apache2
```

### Step 6: Configure PHP Settings

Edit `/etc/php/8.x/apache2/php.ini`:

```ini
upload_max_filesize = 10M
post_max_size = 12M
max_execution_time = 300
session.cookie_httponly = 1
session.use_only_cookies = 1
session.cookie_secure = 1
session.cookie_samesite = "Strict"
date.timezone = "Africa/Nairobi"
```

### Step 7: Set Up Cron Jobs (Optional)

```bash
# Add to crontab (crontab -e)
# Daily backup of database
0 2 * * * mysqldump -u elpis_user -p'password' elpis_counselling > /var/backups/elpis/$(date +\%Y\%m\%d).sql

# Clean up old backups (keep 30 days)
0 3 * * * find /var/backups/elpis/ -type f -mtime +30 -delete
```

### Step 8: Set Up Email (Transactional)

Configure `/etc/ssmtp/ssmtp.conf` or install a mail transfer agent:

```bash
sudo apt install ssmtp -y
sudo nano /etc/ssmtp/ssmtp.conf
```

```
mailhub=smtp.gmail.com:587
AuthUser=your-email@gmail.com
AuthPass=your-app-password
UseSTARTTLS=YES
FromLineOverride=YES
```

### Step 9: Verify Deployment

1. Visit `https://elpiscounselling.co.ke` — homepage should render with all dynamic sections
2. Visit `https://elpiscounselling.co.ke/admin` — login page should load
3. Log in with default credentials: `admin` / `admin123` (change immediately)
4. Test the booking form submission
5. Verify Google Maps embed loads
6. Test mobile responsiveness

---

## Security Hardening

### Production Checklist

- [ ] **Change default admin password** immediately after first login
- [ ] **Enable HTTPS** — all traffic must be encrypted (already configured via config.php)
- [ ] **Set strong database password** (min 16 chars, mixed case + symbols)
- [ ] **Restrict `/uploads/` directory** — add `.htaccess` to prevent PHP execution:

```apache
# In /var/www/html/elpis/uploads/.htaccess
<FilesMatch "\.php$">
    Require all denied
</FilesMatch>
Options -Indexes
```

- [ ] **Disable directory listing** (already done via Apache VirtualHost `Options -Indexes`)
- [ ] **Set restrictive file permissions**:

```bash
find /var/www/html/elpis/ -type f -exec chmod 644 {} \;
find /var/www/html/elpis/ -type d -exec chmod 755 {} \;
chmod 640 /var/www/html/elpis/includes/config.php
chown -R www-data:www-data /var/www/html/elpis/
```

- [ ] **Configure firewall**:

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

- [ ] **Install Fail2Ban** for brute-force protection:

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
```

- [ ] **Regular updates**:

```bash
sudo apt update && sudo apt upgrade -y
```

- [ ] **Monitor logs**:

```bash
sudo tail -f /var/log/apache2/elpis_error.log
sudo tail -f /var/log/apache2/access.log
```

### Built-in Security Features

| Feature | Implementation |
|---------|---------------|
| **SQL Injection Prevention** | All database queries use PDO prepared statements |
| **XSS Prevention** | All user output passed through `htmlspecialchars()` |
| **Password Security** | bcrypt hashing via `password_hash()` with cost factor 10 |
| **Session Security** | HTTP-only cookies, strict session management |
| **File Upload Validation** | File type (MIME), extension, and size (5MB max) validation |
| **Input Sanitization** | All user inputs trimmed and validated server-side |
| **Error Handling** | Custom error messages; database errors not exposed to users |

---

## M-Pesa Integration

### Sandbox (Development)

The platform includes a simulated M-Pesa STK Push integration for testing:

1. Register at [Daraja API Portal](https://developer.safaricom.co.ke/)
2. Obtain sandbox credentials
3. Update `includes/config.php` with sandbox Consumer Key, Secret, and Passkey
4. The `initiateMpesaPayment()` function returns a simulated successful response

### Production (Live)

1. Go through Safaricom's production approval process
2. Update `includes/config.php`:

```php
define('MPESA_ENVIRONMENT', 'production');
define('MPESA_CONSUMER_KEY', 'your_live_key');
define('MPESA_CONSUMER_SECRET', 'your_live_secret');
define('MPESA_PASSKEY', 'your_live_passkey');
define('MPESA_SHORTCODE', 'your_live_shortcode');
```

3. Implement callback URL handling for payment confirmation (webhook-ready architecture)
4. Update `checkMpesaStatus()` to query the live Daraja API

---

## Database Schema

The database `elpis_counselling` contains **8 tables**:

| Table | Purpose | Key Columns |
|-------|---------|-------------|
| `admin_users` | Admin authentication | `id`, `username`, `password_hash` |
| `services` | Counselling services | `id`, `title`, `description`, `display_order`, `is_active` |
| `appointments` | Booking inquiries | `id`, `name`, `email`, `phone`, `service`, `status` |
| `articles` | Educational content | `id`, `title`, `slug`, `content`, `image`, `is_published` |
| `events` | Workshops & webinars | `id`, `title`, `event_date`, `price`, `max_participants` |
| `event_bookings` | Paid event registrations | `id`, `event_id` (FK), `mpesa_code`, `amount`, `status` |
| `testimonials` | Client reviews | `id`, `client_name`, `content`, `rating`, `is_approved` |
| `contacts` | Contact form submissions | `id`, `name`, `email`, `message` |

**ERD Note**: `event_bookings.event_id` references `events.id` with `ON DELETE CASCADE` for referential integrity.

---

## File Structure

```
Project-Elpis/
├── index.php                     # Homepage
├── about.php                     # About Us
├── services.php                  # Services listing
├── booking.php                   # Session inquiry form
├── articles.php                  # Articles list with pagination
├── article.php                   # Single article view
├── events.php                    # Events with M-Pesa booking modal
├── contact.php                   # Contact form + map
├── setup.php                     # Database setup helper
├── TODO.md                       # Project progress tracker
├── LICENSE                       # MIT License
├── README.md                     # This file
│
├── assets/
│   ├── css/
│   │   └── style.css             # Complete responsive stylesheet (700+ lines)
│   └── js/
│       └── main.js               # JavaScript: nav, forms, carousel, back-to-top
│
├── includes/
│   ├── config.php                # Database, site, M-Pesa configuration
│   ├── functions.php             # 20+ helper functions (CRUD, M-Pesa, uploads, email)
│   ├── header.php                # Site header with responsive navigation
│   └── footer.php                # Site footer with 4-column layout
│
├── admin/
│   ├── index.php                 # Admin login (bcrypt authentication)
│   ├── dashboard.php             # Dashboard with stats and recent appointments
│   ├── appointments.php          # Manage booking inquiries
│   ├── manage_services.php       # CRUD for services
│   ├── manage_articles.php       # CRUD for articles with image upload
│   ├── manage_events.php         # CRUD for events with image upload
│   ├── manage_testimonials.php   # CRUD for testimonials with approval
│   └── logout.php                # Secure logout
│
├── sql/
│   ├── database.sql              # Full schema with seed data (19 services, 1 admin user)
│   └── seed_admin.sql            # Admin user seed script
│
├── images/
│   ├── logo.jpeg                 # Elpis Counselling Centre logo
│   └── project.jpeg              # Promotional banner image
│
└── uploads/                      # User-uploaded images (articles, events)
    └── .gitkeep                  # Ensures directory exists in repo
```

---

## Color Palette & Design System

| Role | Color | Hex Code | Usage |
|------|-------|----------|-------|
| **Primary** | Deep Counselling Blue | `#3F5195` | Navigation bar, footer, headings, structural elements |
| **Secondary** | Calm Teal Green | `#4FA08A` | Buttons, links, icons, section headings |
| **Accent** | Warm Hopeful Yellow | `#E4CF55` | Highlights, active menu items, small icons, CTAs (sparingly) |
| **Background** | Soft Cream | `#FAF8F2` | Main page background (dominant) |
| **Light Section** | Pale Teal | `#EAF4F1` | Alternating section backgrounds |
| **Text** | Dark Navy-Grey | `#263447` | Body text (softer than pure black) |
| **Border/Muted** | Soft Grey | `#D7DDD9` | Borders, dividers, muted text |

### Design Principles
- **Cream and white** backgrounds dominate — creates a calm, uncluttered canvas
- **Deep blue** structures the page (nav, footer, headings) — conveys trust and professionalism
- **Teal** provides interactive cues (buttons, links) — soothing yet action-oriented
- **Yellow** used sparingly as a **hopeful accent** — draws attention to key actions without overwhelming
- Fully responsive: mobile-first breakpoints at 480px, 768px, and 1200px+

---

## Admin Panel

### Default Credentials

> **⚠️ IMPORTANT**: Change the default password immediately after first login!

- **URL**: `https://elpiscounselling.co.ke/admin/`
- **Username**: `admin`
- **Password**: `admin123`

### Generating a Secure Password Hash

If you need to reset the admin password, generate a new bcrypt hash:

```bash
php -r "echo password_hash('your_new_strong_password', PASSWORD_BCRYPT);"
```

Then update in MySQL:

```sql
UPDATE admin_users SET password_hash = 'your_new_hash_here' WHERE username = 'admin';
```

### Admin Features

- **Dashboard**: Real-time statistics on services, articles, events, pending appointments, and testimonials
- **Appointments**: Status management with dropdown; filterable by status
- **Services**: Reorder, toggle active/inactive, edit descriptions
- **Articles**: Image upload with auto-delete on replacement; slug auto-generation; publish/unpublish toggle
- **Events**: Image upload; date/time/venue management; free/paid pricing; M-Pesa integration
- **Testimonials**: Star rating (1–5); approve/unapprove toggle for public visibility

---

## Maintenance & Support

### Regular Maintenance Tasks

| Frequency | Task |
|-----------|------|
| **Daily** | Review new appointment inquiries and contact submissions |
| **Weekly** | Approve/reject testimonials; publish new articles |
| **Monthly** | Add upcoming events; review M-Pesa transaction logs |
| **Quarterly** | Update service offerings; review compliance with WHO guidelines |
| **Annually** | SSL certificate renewal; security audit; database optimization |

### Backups

Automated daily database backups are configured via cron (see Step 7). For manual backup:

```bash
mysqldump -u elpis_user -p elpis_counselling > backup_$(date +%Y%m%d).sql
```

### Troubleshooting

| Issue | Possible Cause | Solution |
|-------|---------------|----------|
| **Blank page** | PHP error | Check Apache error log: `sudo tail -f /var/log/apache2/elpis_error.log` |
| **Database connection error** | Wrong credentials | Verify `includes/config.php` DB settings |
| **Image upload fails** | Permissions | Ensure `uploads/` is writable: `chmod 775 uploads/` |
| **M-Pesa payment not working** | Wrong credentials | Verify Daraja API settings in config.php |
| **404 on pages** | Missing mod_rewrite | Enable rewrite: `sudo a2enmod rewrite && sudo systemctl restart apache2` |
| **Session/login issues** | Session configuration | Check `session.cookie_secure` matches HTTPS status |

---

## License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.

Copyright (c) 2026 Elpis Counselling Centre

---

**Elpis Counselling Centre**  
Krishna Centre, 2nd Floor, Westlands  
Nairobi, Kenya  

*"Your Journey to Emotional Wellbeing Begins Here"*
