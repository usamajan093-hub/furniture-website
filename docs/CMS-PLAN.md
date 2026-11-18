# CMS plan for amroyalinteriors.com

## 1. What the existing site is

- One self-contained `index.html`: inline CSS (design tokens in `:root`), inline vanilla JS.
- Four hash-routed pages inside that file (`#home`, `#about`, `#portfolio`, `#contact`), an intro animation, Ken Burns hero, marquee, before/after slider, masonry portfolio with lightbox, and a contact form that opens WhatsApp.
- Photos in `assets/images/`. Eleven old page files only redirect into the hash routes.
- Hosted on GitHub Pages, which serves static files only. **No server code can run there.** A content-managed site with a login, uploads and publishing needs a web host that runs PHP (any cPanel / shared host, or XAMPP locally).

## 2. Recommended approach (no database)

Flat-file CMS in plain PHP, the way Grav and Kirby work:

- **Storage:** JSON files in `cms/data/` (content, offers, templates, media index, pages, users, activity). Written atomically with file locks. No database, no credentials, nothing to configure. Back up by copying the folder.
- **Frontend:** `index.php` renders the same design the site has today, but reads every word, image and setting from the JSON files. The CSS was moved to `assets/theme.css` unchanged except that hard-coded colours became tokens so templates can restyle it.
- **Admin:** `admin/` with a WordPress-style dashboard, sidebar, data tables, modal forms, media picker and a drag-and-drop page builder. Vanilla CSS/JS, no frameworks.
- **GitHub Pages bridge:** the admin has a "Publish static copy" button that writes `index.html` from the live data, so the static copy on GitHub Pages can still be refreshed by committing.

## 3. Data files (`cms/data/`)

| File | Holds |
|---|---|
| `content.json` | Every editable section: site identity, header/footer, hero, marquee, services, before/after, process, why, CTA, about, portfolio, products, testimonials, offers section copy, contact details, social links |
| `templates.json` | Website templates. Each has a name, description, colours, fonts, intro settings, hero layout and image, section toggles, button style. Exactly one is `active`. |
| `offers.json` | Offers: title, description, image, category, discount (percent or fixed), original and discounted price, start and end dates, status (draft / published), CTA type, label and URL |
| `media.json` | Media library index: file URL, title, category, MIME type, size, dimensions, upload date |
| `pages.json` | Page-builder pages: title, slug, status, placement (own page or on the home page), show in nav, ordered sections (type, background, heading, text, images, buttons) |
| `users.json` | Admin users with `password_hash()` hashes |
| `activity.json` | Last 200 admin actions for the dashboard |
| `attempts.json` | Login rate limiting per IP |

`cms/` and `cms/data/` are blocked from the web by `.htaccess`; `uploads/` cannot execute PHP.

## 4. Publishing rules

- An offer is visible only when status is **published**, the start date (if any) has passed and the end date (if any) has not. Expired offers drop off the site automatically and show as "Expired" in the admin.
- A page is visible only when status is **published**. Drafts can be previewed by a logged-in admin.
- Any template can be previewed by a logged-in admin before it is set active.

## 5. Development steps

1. Core: config, JSON store, auth with CSRF and rate limiting, upload validation, installer.
2. Frontend `index.php` with the renderer and theme tokens; offers, products, testimonials and builder pages added to the existing design.
3. Admin: layout, dashboard, media library, offers.
4. Admin: content manager (schema-driven forms), templates, page builder, settings and static export.
5. Install locally, seed with the real site content, test every create/edit/delete, verify the frontend.
