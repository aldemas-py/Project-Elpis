# Restyling with Complementary Contrast Colors

**Goal:** Keep each project's primary colors and add complementary contrasting accent colors (color theory) to improve visual feel.

## Color Theory Strategy
- **Complementary colors** sit opposite each other on the color wheel → maximum contrast & visual energy.
- **60-30-10 rule**: dominant primary + secondary neutral + accent (the complement).

## Projects & Complementary Accents
| # | Project | Primary (kept) | Complementary Accent Added |
|---|---------|----------------|----------------------------|
| 1 | Project-Elpis | Deep Blue `#3F5195`, Teal `#4FA08A` | Coral `#E76F51` |
| 2 | realRealestate | Blue `#1565C0` | Amber/Orange `#FF9800` |
| 3 | todoList | Blue `#2563eb` | Amber `#F59E0B` |
| 4 | WiFiSales | Blue `#2563eb` | Amber `#F59E0B` |
| 5 | portfolio | Blue `#2839d2` | Warm Amber `#F59E0B` |
| 6 | writing_dev | Purple `#7c3aed` + Green `#22c55e` | Gold `#FBBF24` |

## Steps
- [x] 1. Project-Elpis: add coral accent to `assets/css/style.css` + inline PHP color refs
- [x] 2. realRealestate: add amber accent to `assets/css/style.css` (and admin/pages)
- [x] 3. todoList: add amber accent to `css/style.css`
- [x] 4. WiFiSales: add amber accent to `assets/css/style.css`
- [x] 5. portfolio: add warm amber accent to `css/main.css` + `css/theme.css` (redone)
- [x] 6. writing_dev: add gold accent to `css/style.css`
- [x] 7. Verify contrast in all projects
- [x] 8. portfolio: add Policy as Code compliance system (policies/, engine, dashboards, .htaccess, SECURITY.md, COMPLIANCE.md, pipeline gates)
- [x] 9. portfolio: fix broken images (SITE_URL malformed), preview iframe (X-Frame-Options DENY), 127.0.0.1 refused (HTTPS redirect), admin/ Forbidden (admin/index.php redirect)
- [x] 10. portfolio: fix modal Close button — `closeProjectModal` is defined in parent page, iframe content now calls `window.parent.closeProjectModal()`


