# Project-Elpis Implementation Checklist

## Phase 1: Foundation
- [X] Create TODO.md (this file)
- [X] Create SQL database schema (sql/database.sql)
- [X] Create database config (includes/config.php)
- [X] Create helper functions (includes/functions.php)

## Phase 2: Design System
- [X] Create responsive CSS (assets/css/style.css)
- [X] Create JavaScript (assets/js/main.js)

## Phase 3: Site Shell
- [X] Create header template (includes/header.php)
- [X] Create footer template (includes/footer.php)

## Phase 4: Public Pages
- [X] Create Homepage (index.php)
- [X] Create About page (about.php)
- [X] Create Services page (services.php)
- [X] Create Booking page (booking.php)
- [X] Create Articles list page (articles.php)
- [X] Create Single Article view (article.php)
- [X] Create Events page (events.php)
- [X] Create Contact page (contact.php)

## Phase 5: Admin Panel
- [X] Create Admin Login (admin/index.php)
- [X] Create Admin Dashboard (admin/dashboard.php)
- [X] Create Manage Appointments (admin/appointments.php)
- [X] Create Manage Services (admin/manage_services.php)
- [X] Create Manage Articles (admin/manage_articles.php)
- [X] Create Manage Events (admin/manage_events.php)
- [X] Create Manage Testimonials (admin/manage_testimonials.php)
- [X] Create Admin Logout (admin/logout.php)

## Phase 6: Documentation
- [X] Create comprehensive README with:
  - [X] Compliance & Policy (WHO 2026 guidelines, Data Protection Act 2019, ethical safeguards)
  - [X] Production server installation (Ubuntu/Debian, Apache, MySQL, SSL, cron, email)
  - [X] Security hardening checklist (firewall, Fail2Ban, permissions, uploads protection)
  - [X] M-Pesa integration (sandbox + production)
  - [X] Database schema documentation
  - [X] File structure documentation
  - [X] Color palette & design system
  - [X] Admin panel documentation
  - [X] Maintenance & troubleshooting guide

## Phase 7: Setup & Final Touches
- [ ] Run sql/database.sql in phpMyAdmin to create database and tables
- [ ] Create uploads/ directory (auto-created on first image upload)
- [ ] Default admin credentials: username = admin, password = admin123
- [ ] Review responsive design on mobile/tablet/desktop
- [ ] Replace M-Pesa sandbox credentials in includes/config.php

## Color Palette
- Primary Deep Blue: #3F5195 (nav, footer, headings)
- Secondary Teal Green: #4FA08A (buttons, links, icons)
- Accent Yellow: #E4CF55 (highlights, active menu, CTAs)
- Background Cream: #FAF8F2 (main page bg)
- Light Section: #EAF4F1 (alternating sections)
- Text Dark Navy: #263447 (body text)
- Border/Muted: #D7DDD9 (borders)
