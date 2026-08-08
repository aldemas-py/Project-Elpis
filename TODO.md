
## Steps
- [x] Therapy Room Booking + Social Links (completed)
- [x] Create SQL migration for gallery tables (`gallery_events`, `gallery_images`)
- [x] Add helper functions to `includes/functions.php` (getGalleryEvents, getGalleryImages, getGalleryEventById)
- [x] Create `admin/manage_gallery.php` (create/edit event, upload multiple images + captions, featured image)
- [x] Create `gallery.php` (masonry grid, hover slider, lightbox)
- [x] Add Gallery link to `includes/header.php` nav
- [x] Add Gallery section to footer Quick Links
- [x] Add Gallery sidebar link to admin pages (dashboard, appointments, therapy_bookings, manage_events, manage_articles, manage_services, manage_testimonials)
- [x] Add gallery CSS to `assets/css/style.css`
- [x] Run SQL migration, lint check PHP files, verify in browser
- [x] Add `frame_size` column to `gallery_events` (SQL migration applied)
- [x] Add Frame Size selector to admin gallery form (Standard/Large/Tall/Wide)
- [x] Update `gallery.php` to use event's selected frame size in masonry grid
- [x] Add `.gallery-item-wide` CSS class for the Wide frame size
- [x] Lint check `manage_gallery.php` and `gallery.php`
- [x] Fix: Upload images form now appears immediately after creating a new gallery event
- [x] Improve: Image upload field moved into the main gallery save form (always visible), with per-image caption inputs and auto-assignment of featured image

