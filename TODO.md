# Project-Elpis Implementation Checklist

## Phase 1: Foundation
- [x] Create TODO.md (this file)
- [x] Create SQL database schema (`sql/database.sql`)
- [x] Create database config (`includes/config.php`)
- [x] Create helper functions (`includes/functions.php`)

## Phase 2: Design System
- [x] Create responsive CSS (`assets/css/style.css`)
- [x] Create JavaScript (`assets/js/main.js`)

## Phase 3: Site Shell
- [x] Create header template (`includes/header.php`)
- [x] Create footer template (`includes/footer.php`)

## Phase 4: Public Pages
- [x] Create Homepage (`index.php`)
- [x] Create About page (`about.php`)
- [x] Create Services page (`services.php`)
- [x] Create Booking page (`booking.php`)
- [x] Create Articles page (`articles.php`)
- [x] Create Article detail page (`article.php`)
- [x] Create Events page (`events.php`)
- [x] Create Contact page (`contact.php`)

## Phase 5: Admin Panel
- [x] Create Admin Login (`admin/index.php`)
- [x] Create Admin Dashboard (`admin/dashboard.php`)
- [x] Create Manage Appointments (`admin/appointments.php`)
- [x] Create Manage Articles (`admin/manage_articles.php`)
- [x] Create Manage Events (`admin/manage_events.php`)
- [x] Create Manage Services (`admin/manage_services.php`)
- [x] Create Manage Testimonials (`admin/manage_testimonials.php`)
- [x] Create Admin Logout (`admin/logout.php`)

## Phase 6: Final Touches
- [x] Add database setup script (`setup.php`)
- [x] Add session security (CSRF, strict mode, same-site cookies, auto-logout)
- [x] Add cPanel deployment config (`.cpanel.yml` / `.cp.yml`)
- [x] Review and test all pages
- [x] Verify responsive design

## Color Palette
- Primary Deep Blue: #3F5195
- Secondary Teal Green: #4FA08A
- Accent Yellow: #E4CF55
- Background Cream: #FAF8F2
- Light Section: #EAF4F1
- Text Dark Navy: #263447
- Border/Muted: #D7DDD9

## Design Principles (Client-approved)
- Cream/white backgrounds dominate
- Blue & teal used for structure (headers, nav, footer, buttons)
- Yellow used sparingly as hopeful accent

## Deployment Notes
- config.php uses live DB credentials (njengas2_elpis_counselling)
- cPanel deployment tasks configured to push to /home/njengas2/public_html/
- setup.php and sql/ are excluded from production deployment
