# amroyalinteriors.com — Royal Wood Interiors

Premium single-file website for a wood polish, deco and woodwork contractor in Karachi.

**Live site:** https://usamajan093-hub.github.io/furniture-website/ (custom domain: amroyalinteriors.com)

## How it works

Everything is in **`index.html`** — inline CSS and JavaScript, no frameworks, no build step. Open the file and it works.

- Four pages routed by URL hash inside the one file: `#home`, `#about`, `#portfolio` (with `?cat=polish|furn|doors|stairs|proj` filters) and `#contact`.
- Full-screen gold intro animation ("ROYAL — INTERIORS"), fixed header, mobile menu, Ken Burns hero, marquee, before/after slider, masonry portfolio with lightbox, contact form that opens WhatsApp with the message pre-filled.
- Old page URLs (`about.html`, `contact.html`, `portfolio.html`, `wood-polishing.html`, …) redirect into the matching hash so shared links keep working.

## Editing business details

Search `index.html` for these and change them in place:

| What | Value |
|---|---|
| Phones | `+92 309 8986264`, `+92 300 4086113` |
| WhatsApp | `923098986264` (used in `wa.me` links and the form) |
| Email | `furniturewood786@gmail.com` |
| Address | House No. 452/A, Street No. 54, Sector C, Sir Syed Ahmad Khan Road, near Maryam Masjid, Manzoor Colony, Karachi |
| Hours | Open daily 7:00 AM – 11:30 PM |

Photos live in `assets/images/`. Replace a file with one of the same name to swap a picture.

## Admin panel (CMS)

The site can also run as a content-managed website on any PHP host (cPanel, shared hosting, XAMPP). No database: all content lives in JSON files under `cms/data/`.

1. Upload the whole folder to the web root of a PHP 8 host.
2. Open `https://your-domain/install.php`, create the admin account. The current text, photos and three templates are imported.
3. Delete `install.php`. Log in at `/admin/`.

| Admin screen | What you manage |
|---|---|
| Dashboard | live offers, templates, image count, recent activity, quick actions |
| Offers | title, description, image, category, percentage or fixed discount, prices, start and expiry dates, CTA button, publish / unpublish / schedule |
| Media Library | drag-and-drop upload (JPG, PNG, WebP, SVG), search, categories, rename, delete, reuse anywhere |
| Website Content | hero, marquee, services, before/after, offers copy, products, process, why us, testimonials, CTA, about, portfolio, contact details, social links, footer |
| Templates | colours, fonts, intro animation, hero layout and photo, button shape, which homepage sections appear; preview, duplicate, set active |
| Page Builder | add sections (banner, text, image + text, gallery, cards, CTA), drag to reorder, backgrounds, preview, publish; a page can be its own page or extra sections on the home page |
| Settings | change password/username, download a backup ZIP, regenerate the static `index.html` for GitHub Pages |

`index.php` renders the live site from the JSON files; `index.html` is the static snapshot that GitHub Pages serves. See `docs/CMS-PLAN.md` for the design.

## Backups

The previous multi-page version is kept at tag `v2.0-amroyal-2026-11-02` and branch `backup-2026-11-02`.

## License

© 2026 Royal Wood Interiors. All rights reserved — see [LICENSE](LICENSE). Not open source.
