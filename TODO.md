
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
- [x] Enhance: Auto-upload a single chosen image via AJAX with a small preview, clear the input to allow adding more, and provide caption editing for every image (new `admin/gallery_image_ajax.php` endpoint)
- [x] Integrate: Gallery images are now managed inside the gallery event form itself (upload field + previews above Brief Description), not as a separate/standalone section
- [x] Allow adding images without saving first: on first upload for a new gallery, the gallery event is auto-created as a draft via AJAX, then the image attaches to it
- [x] Add gallery section to homepage (`index.php`) with masonry grid, hover image slider, and lightbox (keyboard nav + click to view)
- [x] Clarify caption vs description in admin gallery form: per-image caption inputs now labeled "Image N of M — caption for this image only", and the Brief Description field relabeled to "Gallery Description (explains this whole gallery set)" with helper text
- [x] Contact info update: WhatsApp group link, phone (0718674888/0708854435), email (elpiscounselling24@gmail.com), and location (Krishna Centre Building, 2nd Floor, Suite D-16, Westlands) applied across contact.php, footer.php, booking.php, and ADMIN_EMAIL in config.php

