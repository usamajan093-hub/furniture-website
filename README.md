# FINISHCRAFT — Wood Finishing & Interior Woodwork Website

Premium, fully responsive, multi-page website for **FINISHCRAFT** — a wood polishing and finishing specialist that also manufactures custom furniture and delivers complete interior woodwork for houses, bungalows and villas.

Built with **plain HTML, CSS and JavaScript** — no frameworks, no build step, no server required. Open `index.html` and it works. Hosted free on GitHub Pages.

**Live site:** https://usamajan093-hub.github.io/furniture-website/ (custom domain: royalwoodinteriors.com — see section 4)

---

## Pages

| File | Purpose |
|---|---|
| `index.html` | Home — hero, service hierarchy (finishing first), core polishing section with real before/after, finishing close-ups, other services, "hire us for" scope grid, portfolio preview, process, why us, CTA |
| `wood-polishing.html` | **Core specialty** — all finishing services, real before/after, how we finish, doors poster, FAQ |
| `custom-furniture.html` | Bedroom sets, beds, wardrobes, tables, cabinets, living room furniture |
| `interior-woodwork.html` | Doors & windows, kitchens, panels, ceilings, built-ins, staircases |
| `bungalow-projects.html` | Complete bungalow / villa woodwork packages — 8-stage scope |
| `portfolio.html` | Filterable gallery of **real project photos** + lightbox + before/after pairs |
| `about.html` | Company intro, quality commitments, process, approach |
| `contact.html` | Contact details, inquiry form, Google Map slot |
| `consultation.html` | Detailed "Request a Consultation" form (pre-selects service via `?service=`) |
| `services.html`, `projects.html`, `quote.html` | Redirects to the new pages (keep old shared links working) |

Shared: sticky header with Services dropdown, mobile menu, floating WhatsApp button, footer, SEO meta / Open Graph, LocalBusiness structured data, `sitemap.xml`, `robots.txt`.

## Folder structure

```
Website/
├── *.html                  pages (see table above)
├── css/styles.css          complete design system + FINISHCRAFT components
├── js/
│   ├── config.js           ★ ALL BUSINESS DETAILS — edit this first
│   ├── main.js             nav, dropdown, config injection, animations, FAQ, WhatsApp links, structured data
│   ├── gallery.js          portfolio filters + lightbox
│   └── forms.js            form validation, spam protection, delivery
├── assets/
│   ├── favicon.svg
│   └── images/             ★ YOUR REAL PROJECT PHOTOS
├── sitemap.xml, robots.txt
└── README.md
```

---

## 1. Business details — `js/config.js`

Phone and WhatsApp are set. Still placeholders (marked `[EDIT]`): **email, address, city, working hours, social links, Google Maps embed, service area**. Change a value, save, refresh — every page updates.

**Google Map:** Google Maps → your workshop → Share → *Embed a map* → copy only the `src="https://www.google.com/maps/embed?..."` URL into `googleMapsEmbedSrc`.

## 2. Adding your project photos

Put photos in `assets/images/` (JPG or WEBP, ideally under 300 KB — [squoosh.app](https://squoosh.app) compresses them). Then in `portfolio.html` copy one `<div class="gallery-item" …>` block and change:

- `data-cat` — categories, space-separated: `polishing doors windows stairs interior bungalow exterior furniture`
- `data-full` and `<img src>` — the photo path
- `alt`, `<h3>` title, `<small>` label, `<p>` description
- `data-wa-msg` — the WhatsApp message for that project

Before/after pairs: the `.ba-pair` block — two `<figure>`s (before / after) with real photos of the same piece.

Current photos and where they're used:

| File | Subject | Used on |
|---|---|---|
| `door-raw-before-polish.webp` | Main door, raw | Home, polishing, portfolio (before) |
| `door-polished-walnut.webp` | Same door, walnut finish | Home, polishing, interior, portfolio, about |
| `panel-doors-workshop.webp` | Panel doors in workshop | Home, interior, bungalow, furniture, portfolio |
| `staircase-oak-finished.webp` | Finished oak staircase | Home, polishing, bungalow, portfolio |
| `staircase-installation.jpg` | Team fitting stairs | Home, interior, bungalow, about, portfolio |
| `bungalow-window-frames.webp` | Window frames, 3-storey bungalow | Home, interior, bungalow, portfolio |
| `wooden-main-gate.webp` | Timber slat gate | Home, portfolio |
| `poster-doors-dark.webp` / `-light.webp` | Door-making creatives | Interior / polishing pages |

The few remaining stock photos (bedroom, living room, wardrobe on the furniture page; section backgrounds) are illustrative and clearly noted — replace them with your own when available.

## 3. Forms

`formMode` in `config.js`: `"whatsapp"` (default — opens WhatsApp pre-filled), `"email"`, or `"endpoint"` (Formspree / Getform / your own API — stores inquiries). Spam protection built in: honeypot, fill-time check, rate limit, full validation.

## 4. Updating the live site

The site is a git repository connected to GitHub. After editing files:

```bash
git add -A
git commit -m "Describe the change"
git push
```

GitHub Pages redeploys in 1–2 minutes. Backups: tags `v1.0-launch`, `v1.1-before-changes-2026-10-30` and branch `backup-2026-10-30` hold earlier versions.

**Custom domain:** add DNS records (A → 185.199.108/109/110/111.153, CNAME `www` → `usamajan093-hub.github.io`), then set the domain in GitHub → Settings → Pages and enable HTTPS. Update the base URL in `sitemap.xml` and `robots.txt`.

## 5. Running locally

Double-click `index.html`, or:

```bash
python -m http.server 8080
```

## 6. Customising

Colours and fonts are CSS variables at the top of `css/styles.css` (`--walnut`, `--oak`, `--gold` champagne, `--cream`, `--charcoal`). Fonts: Playfair Display + Inter via Google Fonts.

## Launch checklist

- [x] Phone & WhatsApp number
- [x] Real project photos in portfolio
- [ ] Email, address, city, hours in `config.js`
- [ ] About page placeholders written
- [ ] Google Map embed
- [ ] Custom domain (optional)
