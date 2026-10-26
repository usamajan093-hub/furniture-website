# Furniture Manufacturing & Wood Polishing Website

A premium, fully responsive, multi-page website for a furniture manufacturing, wood polishing, restoration and complete bungalow furniture business.

Built with **plain HTML, CSS and JavaScript** — no frameworks, no build step, no server required. Open `index.html` and it works. Upload the folder to any web host and it's live.

---

## Why this technology?

| Need | How this project meets it |
|---|---|
| Looks premium and loads fast | Hand-written CSS design system, optimised lazy-loaded images, no heavy libraries |
| Easy for the business to update | Every business detail lives in **one file**: `js/config.js` |
| Works everywhere | Deploys to any static host (cPanel, Hostinger, Netlify, Vercel, GitHub Pages, Cloudflare Pages…) |
| Forms without a backend | Inquiries are delivered via **WhatsApp** (default), email, or any form service / your own API |
| SEO ready | Per-page titles & descriptions, Open Graph tags, `sitemap.xml`, `robots.txt`, LocalBusiness structured data |

A database/backend is **not required** for this site to be fully functional. If you later want inquiries stored in a database, see *Form delivery → endpoint mode* below.

---

## Project structure

```
Website/
├── index.html          Home page (hero, services, showcases, before/after, projects, why us, process, testimonials, FAQ, CTA)
├── services.html       All services in detail with benefits + inquiry buttons
├── projects.html       Filterable gallery with lightbox + before/after sliders
├── about.html          Company intro, quality commitment, process, approach, trust
├── contact.html        Contact details, inquiry form, Google Map
├── quote.html          Detailed "Request a Quote" form
├── robots.txt          Search-engine crawl rules
├── sitemap.xml         Page list for search engines
├── README.md           This file
├── assets/
│   └── favicon.svg     Browser-tab icon (edit colours/shape freely)
├── css/
│   └── styles.css      Complete design system — colours, typography, components, responsive rules
└── js/
    ├── config.js       ★ ALL BUSINESS DETAILS — edit this first
    ├── main.js         Navigation, config injection, animations, FAQ, before/after, WhatsApp links, structured data
    ├── gallery.js      Gallery filters + lightbox (projects page only)
    └── forms.js        Form validation, spam protection and delivery (contact + quote pages)
```

---

## 1. Running the site locally

**Option A — just open it**
Double-click `index.html`. Everything works directly from the file system.

**Option B — local web server (recommended for testing forms in `endpoint` mode)**

With Python installed:

```bash
python -m http.server 8080
```

Or with Node.js installed:

```bash
npx serve . -l 8080
```

Then open <http://localhost:8080>.

---

## 2. Setting up your business details (do this first)

Open **`js/config.js`** in any text editor. Every value marked `[EDIT]` should be changed:

```js
brandName: "Artisan Woodcraft",      // your business name
phoneDisplay: "+92 300 0000000",     // phone as shown on the site
phoneTel: "+923000000000",           // same number, digits only, for click-to-call
whatsappNumber: "920000000000",      // international format, digits only, NO "+"
email: "info@example.com",
addressLine / city / region / country,
hours: "Mon – Sat: 9:00 AM – 8:00 PM",
googleMapsEmbedSrc: "",              // see below
social: { facebook: "", instagram: "" },
currency: "PKR",
```

Save the file and refresh — the whole site updates (header, footer, contact page, WhatsApp links, structured data).

### Google Map
1. Open Google Maps → find your workshop → **Share** → **Embed a map** → **Copy HTML**.
2. From the copied code take only the `src="https://www.google.com/maps/embed?..."` URL.
3. Paste it into `googleMapsEmbedSrc` in `config.js`.

### WhatsApp
`whatsappNumber` **must** be in international format with no `+`, spaces or dashes.
Example: Pakistan number `0300 1234567` → `923001234567`.
The greeting message is in `whatsappMessage` — edit freely.

---

## 3. Form delivery (Contact & Quote forms)

Set `formMode` in `config.js`:

| Mode | What happens when a visitor submits | Setup needed |
|---|---|---|
| `"whatsapp"` **(default)** | WhatsApp opens with all form details pre-filled; visitor taps Send | None |
| `"email"` | Visitor's email app opens, addressed to you, details pre-filled | None |
| `"endpoint"` | Data is POSTed to a URL you set in `formEndpoint` | A form service or your own backend |

### Endpoint mode — store inquiries in a database
The quickest way with zero coding is a hosted form service. Any of these work out of the box because the form sends standard `multipart/form-data` and accepts a JSON response:

- **Formspree** — create a form, copy the URL (`https://formspree.io/f/xxxxxxx`) into `formEndpoint`.
- **Getform**, **Basin**, **Web3Forms** — same idea.

These services email you each submission, keep a searchable inbox (your "database"), and handle the reference-image upload.

**Your own backend:** point `formEndpoint` at your API (PHP, Node, Python…). The request is a `POST` with fields `name, phone, email, service, location, budget, message, reference (file), consent, _subject`. Return HTTP 200 on success. Store the fields in your database and reply with JSON.

### Spam protection (already built in)
- Hidden honeypot field — bots that fill it are silently ignored
- Minimum fill-time check (submissions under 2.5 s are rejected)
- Per-session rate limit (15 s between submissions)
- Full client-side validation with clear error messages

For endpoint mode you can additionally enable your form service's reCAPTCHA/hCaptcha option.

---

## 4. Replacing the placeholder images

All images are currently high-quality placeholder photos loaded from Unsplash. To use your own photos:

1. Create a folder `assets/images/` and put your photos in it (recommended: JPG or WEBP, max 1600 px wide, under 300 KB each — use [squoosh.app](https://squoosh.app) to compress).
2. In the HTML, replace the `src="https://images.unsplash.com/..."` with `src="assets/images/your-photo.jpg"`.
3. Update the `alt="..."` text to describe your actual photo (important for SEO and accessibility).

**Gallery items** (`projects.html`): each item is one `<div class="gallery-item" …>` block. Copy a block to add a project, change:
- `data-cat` — one or more categories separated by spaces (`bedroom`, `living`, `dining`, `wardrobe`, `doors`, `kitchen`, `polishing`, `restoration`, `bungalow`, `commercial`)
- `data-full` — large image for the lightbox
- `<img src alt>` — thumbnail
- `<h3>` and `<small>` — title and label
- `data-wa-msg` — the pre-filled WhatsApp text for that project

**Before/after sliders**: replace `ba-before` and `ba-after` image sources with real photos of the same piece.

---

## 5. Content you must edit before going live

Search each file for `[` `]` or the word **Editable** — these are intentional placeholders:

- **Testimonials** (`index.html`) — three placeholder cards. Replace with real reviews or delete. *No reviews were invented.*
- **About page** — company story and team experience paragraphs.
- **Services → Polishing** — list only the polish types/materials you actually offer.
- **Footer / meta** — service area text comes from `config.js → seo.serviceArea`.
- **`sitemap.xml` and `robots.txt`** — replace `https://www.example.com` with your real domain.
- **Open Graph images** — the `og:image` meta tag in each page `<head>`; point these at your own photos once uploaded.

---

## 6. Deploying to a web server

### Shared hosting (cPanel, Hostinger, GoDaddy, etc.)
1. Log in to your hosting control panel → **File Manager** (or connect via FTP with FileZilla).
2. Open the `public_html` folder.
3. Upload **everything** inside the `Website` folder (`index.html`, `css/`, `js/`, `assets/`, etc.).
4. Visit your domain — done.

### Netlify (free)
1. Go to <https://app.netlify.com/drop>.
2. Drag the whole `Website` folder onto the page.
3. You get a live URL instantly; add your custom domain in Site settings.

### Vercel / Cloudflare Pages / GitHub Pages
Push the folder to a Git repository and connect it — no build command, output directory is the root.

### After deploying
- Update `sitemap.xml` and `robots.txt` with the real domain.
- Submit the sitemap in **Google Search Console**.
- Create/claim a **Google Business Profile** and link to the site — this is the biggest factor for "furniture makers near me" searches.
- Test the WhatsApp button and both forms on a real phone.

---

## 7. Customising the design

All colours, fonts and spacing are CSS variables at the top of `css/styles.css`:

```css
--walnut: #3d2a1f;   --charcoal: #1b1917;   --beige: #d9c5a7;
--cream: #f7f2ea;    --gold: #c5a059;
--font-display: "Playfair Display", serif;
--font-body: "Inter", sans-serif;
```

Change a variable and it applies site-wide. Fonts are loaded from Google Fonts in each page's `<head>`.

---

## 8. Browser support & accessibility

- Works in all modern browsers (Chrome, Edge, Firefox, Safari, mobile browsers).
- Keyboard-navigable: menu, FAQ, gallery, lightbox and forms all work without a mouse.
- Respects `prefers-reduced-motion`.
- Semantic headings, ARIA labels, skip link, visible focus states, descriptive alt text.

---

## Checklist before launch

- [ ] `js/config.js` — all `[EDIT]` values set, WhatsApp number tested
- [ ] Testimonials replaced or removed
- [ ] About page placeholders written
- [ ] Polishing list matches real services
- [ ] Photos replaced with your own work
- [ ] `sitemap.xml` / `robots.txt` domain updated
- [ ] Forms tested on mobile
- [ ] Google Business Profile linked
