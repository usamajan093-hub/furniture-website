/* Admin panel behaviour: modals, repeaters, media picker, image fields, confirms. No frameworks. */
(function () {
  "use strict";
  var $ = function (s, r) { return (r || document).querySelector(s); };
  var $$ = function (s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); };
  var CSRF = ($('meta[name="csrf"]') || {}).content || "";

  window.toast = function (msg, ms) {
    var t = $("#toast"); if (!t) return;
    t.textContent = msg; t.classList.add("show");
    clearTimeout(t._h); t._h = setTimeout(function () { t.classList.remove("show"); }, ms || 2600);
  };

  /* ---------- sidebar (mobile) ---------- */
  var st = $("#side-toggle");
  if (st) st.addEventListener("click", function () { $("#sidebar").classList.toggle("open"); });

  /* ---------- modals ---------- */
  window.openModal = function (sel) { var m = typeof sel === "string" ? $(sel) : sel; if (m) { m.classList.add("open"); m.setAttribute("aria-hidden", "false"); } };
  window.closeModal = function (sel) { var m = typeof sel === "string" ? $(sel) : sel; if (m) { m.classList.remove("open"); m.setAttribute("aria-hidden", "true"); } };
  document.addEventListener("click", function (e) {
    var o = e.target.closest("[data-modal-open]"); if (o) { e.preventDefault(); openModal(o.getAttribute("data-modal-open")); }
    var c = e.target.closest("[data-modal-close]"); if (c) { e.preventDefault(); closeModal(c.closest(".modal")); }
    if (e.target.classList.contains("modal")) closeModal(e.target);
  });
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") $$(".modal.open").forEach(closeModal); });

  /* ---------- confirm ---------- */
  document.addEventListener("submit", function (e) {
    var f = e.target;
    if (f.hasAttribute("data-confirm") && !window.confirm(f.getAttribute("data-confirm") || "Are you sure?")) e.preventDefault();
  });

  /* ---------- API helper ---------- */
  window.api = function (action, data, isForm) {
    var opts = { method: "POST", headers: { "X-CSRF": CSRF } };
    if (isForm) { opts.body = data; }
    else { opts.headers["Content-Type"] = "application/json"; opts.body = JSON.stringify(data || {}); }
    return fetch("api.php?action=" + encodeURIComponent(action), opts).then(function (r) { return r.json(); });
  };
  window.apiGet = function (action, params) {
    var q = Object.keys(params || {}).map(function (k) { return encodeURIComponent(k) + "=" + encodeURIComponent(params[k]); }).join("&");
    return fetch("api.php?action=" + encodeURIComponent(action) + (q ? "&" + q : "")).then(function (r) { return r.json(); });
  };

  /* ---------- repeaters ---------- */
  function renumber(rep) {
    $$(".rep-row", rep).forEach(function (row, i) {
      $$("[name]", row).forEach(function (el) { el.name = el.name.replace(/\[(\d+|__i__)\]/, "[" + i + "]"); });
      $$("[id]", row).forEach(function (el) { el.id = el.id.replace(/__i__|\d+$/, String(i)); });
    });
  }
  function addRow(rep, after) {
    var tpl = $("template", rep); if (!tpl) return;
    var frag = tpl.content.cloneNode(true);
    var row = frag.querySelector(".rep-row");
    if (after) after.insertAdjacentElement("afterend", row); else $(".rep-list", rep).appendChild(row);
    renumber(rep); bindImgFields(row);
    var first = $("input,textarea", row); if (first) first.focus();
  }
  document.addEventListener("click", function (e) {
    var add = e.target.closest("[data-rep-add]");
    if (add) { e.preventDefault(); addRow(add.closest(".repeater")); return; }
    var rm = e.target.closest("[data-rep-remove]");
    if (rm) { e.preventDefault(); var rep = rm.closest(".repeater"); rm.closest(".rep-row").remove(); renumber(rep); return; }
    var up = e.target.closest("[data-rep-up]");
    if (up) { e.preventDefault(); var r = up.closest(".rep-row"), p = r.previousElementSibling; if (p) p.insertAdjacentElement("beforebegin", r); renumber(up.closest(".repeater")); return; }
    var dn = e.target.closest("[data-rep-down]");
    if (dn) { e.preventDefault(); var r2 = dn.closest(".rep-row"), n = r2.nextElementSibling; if (n) n.insertAdjacentElement("afterend", r2); renumber(dn.closest(".repeater")); return; }
  });
  // drag to reorder
  var dragRow = null;
  document.addEventListener("dragstart", function (e) {
    var g = e.target.closest ? e.target.closest(".grip") : null; if (!g) return;
    dragRow = g.closest(".rep-row, .pb-sec"); if (!dragRow) return;
    dragRow.classList.add("dragging"); e.dataTransfer.effectAllowed = "move"; e.dataTransfer.setData("text/plain", "");
  });
  document.addEventListener("dragover", function (e) {
    if (!dragRow) return; var over = e.target.closest ? e.target.closest(".rep-row, .pb-sec") : null;
    if (!over || over === dragRow || over.parentNode !== dragRow.parentNode) return;
    e.preventDefault();
    var rect = over.getBoundingClientRect();
    if (e.clientY < rect.top + rect.height / 2) over.insertAdjacentElement("beforebegin", dragRow); else over.insertAdjacentElement("afterend", dragRow);
  });
  document.addEventListener("dragend", function () {
    if (!dragRow) return; dragRow.classList.remove("dragging");
    var rep = dragRow.closest(".repeater"); if (rep) renumber(rep);
    document.dispatchEvent(new CustomEvent("reordered", { detail: dragRow }));
    dragRow = null;
  });
  $$(".grip").forEach(function (g) { g.setAttribute("draggable", "true"); });
  new MutationObserver(function () { $$(".grip:not([draggable])").forEach(function (g) { g.setAttribute("draggable", "true"); }); }).observe(document.body, { childList: true, subtree: true });

  /* ---------- image fields + media picker ---------- */
  var picker = $("#media-picker"), pickerCb = null, pickerItems = [], pickerLoaded = false;
  function setImg(field, url) {
    var input = $("input[type=text]", field), prev = $(".img-prev", field);
    input.value = url; prev.innerHTML = url ? '<img src="../' + url + '" alt="">' : "";
    input.dispatchEvent(new Event("change", { bubbles: true }));
  }
  function bindImgFields(root) {
    $$(".img-field", root || document).forEach(function (f) {
      if (f._bound) return; f._bound = true;
      var input = $("input[type=text]", f);
      $("[data-pick]", f).addEventListener("click", function () { openPicker(function (m) { setImg(f, m.url); }); });
      $("[data-clear]", f).addEventListener("click", function () { setImg(f, ""); });
      input.addEventListener("change", function () { var p = $(".img-prev", f); p.innerHTML = input.value ? '<img src="../' + input.value + '" alt="">' : ""; });
    });
  }
  window.bindImgFields = bindImgFields;
  bindImgFields();
  function renderPicker() {
    var q = ($("#picker-q").value || "").toLowerCase(), cat = $("#picker-cat").value, grid = $("#picker-grid");
    var list = pickerItems.filter(function (m) { return (!q || (m.title + " " + m.url).toLowerCase().indexOf(q) >= 0) && (!cat || m.category === cat); });
    grid.innerHTML = list.length ? list.map(function (m) {
      return '<div class="media-item" data-id="' + m.id + '"><div class="mi"><img src="../' + m.url + '" alt="" loading="lazy"></div><div class="mt"><b>' + esc(m.title) + '</b><span>' + esc(m.category || "") + '</span></div></div>';
    }).join("") : '<div class="empty">No images match.</div>';
  }
  function esc(s) { return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) { return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]; }); }
  window.esc = esc;
  function loadPicker() {
    return apiGet("media_list").then(function (r) {
      pickerItems = r.items || []; pickerLoaded = true;
      var cats = {}; pickerItems.forEach(function (m) { if (m.category) cats[m.category] = 1; });
      var sel = $("#picker-cat"); sel.innerHTML = '<option value="">All categories</option>' + Object.keys(cats).sort().map(function (c) { return '<option>' + esc(c) + '</option>'; }).join("");
      renderPicker();
    });
  }
  window.openPicker = function (cb) {
    if (!picker) return; pickerCb = cb; openModal(picker);
    $("#picker-msg").textContent = "";
    if (!pickerLoaded) { $("#picker-grid").innerHTML = '<div class="empty">Loading…</div>'; loadPicker(); } else renderPicker();
  };
  if (picker) {
    $("#picker-q").addEventListener("input", renderPicker);
    $("#picker-cat").addEventListener("change", renderPicker);
    $("#picker-grid").addEventListener("click", function (e) {
      var it = e.target.closest(".media-item"); if (!it) return;
      var m = pickerItems.filter(function (x) { return x.id === it.dataset.id; })[0];
      if (m && pickerCb) pickerCb(m); closeModal(picker);
    });
    $("#picker-upload").addEventListener("change", function () {
      var files = this.files; if (!files.length) return;
      $("#picker-msg").textContent = "Uploading " + files.length + " file(s)…";
      uploadFiles(files).then(function (res) {
        $("#picker-msg").textContent = res.errors.length ? res.errors.join(" ") : "Uploaded.";
        loadPicker();
      });
      this.value = "";
    });
  }
  window.uploadFiles = function (files) {
    var fd = new FormData();
    Array.prototype.forEach.call(files, function (f) { fd.append("files[]", f); });
    return api("media_upload", fd, true).then(function (r) {
      return { items: r.items || [], errors: r.errors || (r.error ? [r.error] : []) };
    }).catch(function () { return { items: [], errors: ["Upload failed."] }; });
  };

  /* ---------- colour pairs ---------- */
  $$(".color-row").forEach(function (row) {
    var c = $("input[type=color]", row), t = $("input[type=text]", row); if (!c || !t) return;
    c.addEventListener("input", function () { t.value = c.value; });
    t.addEventListener("input", function () { if (/^#[0-9a-f]{6}$/i.test(t.value)) c.value = t.value; });
  });

  /* ---------- copy buttons ---------- */
  document.addEventListener("click", function (e) {
    var b = e.target.closest("[data-copy]"); if (!b) return;
    var v = b.getAttribute("data-copy");
    (navigator.clipboard ? navigator.clipboard.writeText(v) : Promise.reject()).then(function () { toast("Copied: " + v); }, function () { window.prompt("Copy:", v); });
  });

  /* ---------- auto-slug ---------- */
  $$("[data-slug-from]").forEach(function (slug) {
    var src = $(slug.getAttribute("data-slug-from")); if (!src) return;
    src.addEventListener("input", function () { if (!slug.dataset.touched) slug.value = src.value.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, ""); });
    slug.addEventListener("input", function () { slug.dataset.touched = "1"; });
  });
})();
