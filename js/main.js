/* =====================================================================
   MAIN — shared behaviour on every page
   - Injects business details from config.js into the page
   - Sticky header, mobile navigation, active link
   - Scroll reveal animations, back-to-top
   - FAQ accordion, before/after sliders
   - WhatsApp links, LocalBusiness structured data
   ===================================================================== */
(function () {
  "use strict";
  const CFG = window.SITE_CONFIG || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  /* ---------- WhatsApp helpers (also used by forms.js) ---------- */
  function waLink(message) {
    const num = String(CFG.whatsappNumber || "").replace(/\D/g, "");
    const text = encodeURIComponent(message || CFG.whatsappMessage || "");
    return `https://wa.me/${num}?text=${text}`;
  }
  window.SITE = { waLink, cfg: CFG };

  /* ---------- Inject config values ---------- */
  function get(path) {
    return path.split(".").reduce((o, k) => (o == null ? undefined : o[k]), CFG);
  }
  // <span data-cfg="brandName"></span>
  $$("[data-cfg]").forEach((el) => {
    const v = get(el.dataset.cfg);
    if (v !== undefined && v !== "") el.textContent = v;
  });
  // Full address line
  $$("[data-cfg-address]").forEach((el) => {
    const parts = [CFG.addressLine, CFG.city, CFG.region, CFG.country].filter(Boolean);
    el.textContent = parts.join(", ");
  });
  // Click-to-call / mail
  $$("[data-tel]").forEach((a) => (a.href = `tel:${CFG.phoneTel || ""}`));
  $$("[data-mail]").forEach((a) => (a.href = `mailto:${CFG.email || ""}`));
  // WhatsApp links: optional custom message in data-wa-msg
  $$("[data-wa]").forEach((a) => {
    const msg = a.dataset.waMsg
      ? a.dataset.waMsg.replace("{brand}", CFG.brandName || "")
      : CFG.whatsappMessage;
    a.href = waLink(msg);
    a.target = "_blank";
    a.rel = "noopener";
  });
  // Social links: hide if empty
  $$("[data-social]").forEach((a) => {
    const url = get(`social.${a.dataset.social}`);
    if (url) a.href = url; else a.remove();
  });
  // Footer year
  $$("[data-year]").forEach((el) => (el.textContent = new Date().getFullYear()));
  // Document title: replace {brand}
  if (document.title.includes("{brand}")) document.title = document.title.replace("{brand}", CFG.brandName || "");
  $$('meta[content*="{brand}"]').forEach((m) => (m.content = m.content.replace("{brand}", CFG.brandName || "")));

  /* ---------- Structured data (LocalBusiness) ---------- */
  try {
    const ld = {
      "@context": "https://schema.org",
      "@type": "HomeAndConstructionBusiness",
      name: CFG.brandName,
      description: "Custom furniture manufacturing, professional wood polishing, furniture restoration and complete bungalow furniture solutions.",
      telephone: CFG.phoneTel,
      email: CFG.email,
      url: location.origin + location.pathname.replace(/[^/]*$/, ""),
      address: {
        "@type": "PostalAddress",
        streetAddress: CFG.addressLine,
        addressLocality: CFG.city,
        addressRegion: CFG.region,
        addressCountry: CFG.country,
      },
      areaServed: CFG.seo && CFG.seo.serviceArea,
      openingHours: CFG.hours,
      priceRange: "$$",
    };
    const s = document.createElement("script");
    s.type = "application/ld+json";
    s.textContent = JSON.stringify(ld);
    document.head.appendChild(s);
  } catch (e) { /* non-critical */ }

  /* ---------- Header: scrolled state ---------- */
  const header = $(".site-header");
  const onScroll = () => {
    if (header) header.classList.toggle("scrolled", window.scrollY > 10);
    const top = $(".to-top");
    if (top) top.classList.toggle("show", window.scrollY > 500);
  };
  window.addEventListener("scroll", onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile nav ---------- */
  const toggle = $(".nav-toggle");
  const nav = $("#primary-nav");
  if (toggle && nav) {
    const setOpen = (open) => {
      toggle.setAttribute("aria-expanded", String(open));
      toggle.setAttribute("aria-label", open ? "Close menu" : "Open menu");
      nav.classList.toggle("open", open);
      document.body.classList.toggle("nav-open", open);
    };
    toggle.addEventListener("click", () => setOpen(toggle.getAttribute("aria-expanded") !== "true"));
    $$("a", nav).forEach((a) => a.addEventListener("click", () => setOpen(false)));
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") setOpen(false); });
    window.matchMedia("(min-width: 861px)").addEventListener("change", (e) => { if (e.matches) setOpen(false); });
  }

  /* ---------- Active nav link ---------- */
  const here = location.pathname.split("/").pop() || "index.html";
  $$(".nav-links a").forEach((a) => {
    const href = a.getAttribute("href") || "";
    const target = href.split("#")[0] || "index.html";
    if (target === here && !href.includes("#")) {
      a.setAttribute("aria-current", "page");
      const parent = a.closest(".has-sub");
      if (parent) $(".nav-sub-toggle", parent)?.classList.add("current");
    }
  });

  /* ---------- Services dropdown (click/tap + keyboard) ---------- */
  $$(".has-sub").forEach((li) => {
    const btn = $(".nav-sub-toggle", li);
    if (!btn) return;
    const set = (open) => { li.classList.toggle("open", open); btn.setAttribute("aria-expanded", String(open)); };
    btn.addEventListener("click", (e) => { e.stopPropagation(); set(!li.classList.contains("open")); });
    document.addEventListener("click", (e) => { if (!li.contains(e.target)) set(false); });
    li.addEventListener("keydown", (e) => { if (e.key === "Escape") { set(false); btn.focus(); } });
  });

  /* ---------- Back to top ---------- */
  const toTop = $(".to-top");
  if (toTop) toTop.addEventListener("click", () => window.scrollTo({ top: 0, behavior: "smooth" }));

  /* ---------- Scroll reveal ---------- */
  const revealEls = $$(".reveal");
  if ("IntersectionObserver" in window && revealEls.length) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((en) => { if (en.isIntersecting) { en.target.classList.add("in"); io.unobserve(en.target); } });
    }, { threshold: 0.12, rootMargin: "0px 0px -40px 0px" });
    revealEls.forEach((el) => io.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add("in"));
  }

  /* ---------- FAQ accordion ---------- */
  $$(".faq-item").forEach((item) => {
    const btn = $(".faq-q", item);
    const panel = $(".faq-a", item);
    if (!btn || !panel) return;
    btn.addEventListener("click", () => {
      const open = item.classList.toggle("open");
      btn.setAttribute("aria-expanded", String(open));
      panel.hidden = false;
    });
  });

  /* ---------- Before / after sliders ---------- */
  $$(".ba").forEach((ba) => {
    const range = $('input[type="range"]', ba);
    if (!range) return;
    const set = (v) => ba.style.setProperty("--pos", `${v}%`);
    set(range.value);
    range.addEventListener("input", () => set(range.value));
  });

  /* ---------- Smooth anchor offset for sticky header ---------- */
  $$('a[href^="#"]').forEach((a) => {
    a.addEventListener("click", (e) => {
      const id = a.getAttribute("href").slice(1);
      const target = id && document.getElementById(id);
      if (!target) return;
      e.preventDefault();
      const y = target.getBoundingClientRect().top + window.scrollY - (header ? header.offsetHeight : 0) - 16;
      window.scrollTo({ top: y, behavior: "smooth" });
      history.replaceState(null, "", `#${id}`);
    });
  });
})();
