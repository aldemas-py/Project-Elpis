# Production Hardening TODO

## Priority 1 (Critical)
- [x] Move credentials to `.env` (git-ignored) with `.env.example`
- [x] Harden `getDB()` error handling (don't expose exception text)
- [ ] Add CSRF to all admin POST actions (appointments, therapy_bookings, manage_*)
- [ ] Convert GET-based deletes/toggles to POST with CSRF
- [ ] Add CSRF to gallery AJAX handler
- [ ] Set `session.cookie_secure` based on HTTPS

## Priority 2 (High)
- [ ] Add brute-force protection to admin login
- [ ] Add security headers (CSP, X-Frame-Options, etc.)
- [ ] Validate real image content on upload (finfo)
- [ ] Enforce slug uniqueness on articles

## Priority 3 (Medium)
- [ ] Harden SITE_URL (configurable constant)
