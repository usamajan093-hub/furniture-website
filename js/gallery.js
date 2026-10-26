/* =====================================================================
   GALLERY — category filters + lightbox
   Markup expectations:
     .filters > button.filter-btn[data-filter="all|bedroom|..."]
     .gallery > div.gallery-item[data-cat="bedroom"][data-full=large-image][role=button][tabindex=0]
       img[alt]  + .gallery-overlay (h3 = title, small = category label, a.gallery-wa)
   ===================================================================== */
(function () {
  "use strict";
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const gallery = $(".gallery");
  if (!gallery) return;
  const items = $$(".gallery-item", gallery);
  const buttons = $$(".filter-btn");
  const empty = $(".gallery-empty");

  /* ---------- Filtering ---------- */
  function applyFilter(cat) {
    let shown = 0;
    items.forEach((it) => {
      const cats = (it.dataset.cat || "").split(/\s+/);
      const show = cat === "all" || cats.includes(cat);
      it.classList.toggle("hidden", !show);
      if (show) shown++;
    });
    buttons.forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.filter === cat)));
    if (empty) empty.classList.toggle("show", shown === 0);
    // Keep URL in sync so filters are shareable (?cat=polishing)
    const url = new URL(location.href);
    if (cat === "all") url.searchParams.delete("cat"); else url.searchParams.set("cat", cat);
    history.replaceState(null, "", url);
  }
  buttons.forEach((b) => b.addEventListener("click", () => applyFilter(b.dataset.filter)));

  const initial = new URL(location.href).searchParams.get("cat");
  const valid = buttons.some((b) => b.dataset.filter === initial);
  applyFilter(valid ? initial : "all");

  /* ---------- Lightbox ---------- */
  const lb = $(".lightbox");
  if (!lb) return;
  const lbImg = $("img", lb);
  const lbCap = $(".lightbox-caption", lb);
  let current = -1;

  const visible = () => items.filter((i) => !i.classList.contains("hidden"));

  function open(index) {
    const list = visible();
    if (!list.length) return;
    current = (index + list.length) % list.length;
    const it = list[current];
    const img = $("img", it);
    const title = $("h3", it);
    lbImg.src = it.dataset.full || (img ? img.src : "");
    lbImg.alt = img ? img.alt : "";
    lbCap.textContent = title ? title.textContent : (img ? img.alt : "");
    lb.classList.add("open");
    lb.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
    $(".lightbox-close", lb).focus();
  }
  function close() {
    lb.classList.remove("open");
    lb.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    lbImg.src = "";
  }

  items.forEach((it) => {
    it.addEventListener("click", (e) => {
      // Let the WhatsApp button inside the card do its own thing
      if (e.target.closest(".gallery-wa")) return;
      e.preventDefault();
      open(visible().indexOf(it));
    });
    it.addEventListener("keydown", (e) => {
      if (e.target !== it) return;
      if (e.key === "Enter" || e.key === " ") { e.preventDefault(); open(visible().indexOf(it)); }
    });
  });
  $(".lightbox-close", lb).addEventListener("click", close);
  $(".lightbox-nav.prev", lb).addEventListener("click", () => open(current - 1));
  $(".lightbox-nav.next", lb).addEventListener("click", () => open(current + 1));
  lb.addEventListener("click", (e) => { if (e.target === lb) close(); });
  document.addEventListener("keydown", (e) => {
    if (!lb.classList.contains("open")) return;
    if (e.key === "Escape") close();
    if (e.key === "ArrowLeft") open(current - 1);
    if (e.key === "ArrowRight") open(current + 1);
  });
})();
