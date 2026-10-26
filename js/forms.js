/* =====================================================================
   FORMS — validation + delivery for Contact and Quote forms
   Delivery mode comes from config.js (formMode):
     "whatsapp" -> open WhatsApp with the message pre-filled
     "email"    -> open mail client with the message pre-filled
     "endpoint" -> POST to formEndpoint (Formspree / Getform / your API)
   Spam protection: honeypot field + minimum fill time + basic rate limit.
   ===================================================================== */
(function () {
  "use strict";
  const CFG = window.SITE_CONFIG || {};
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));

  const ICON_OK = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>';
  const ICON_ERR = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>';
  const ICON_INFO = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>';

  const rules = {
    name: (v) => (v.trim().length >= 2 ? "" : "Please enter your full name."),
    phone: (v) => (/^[+\d][\d\s\-()]{7,19}$/.test(v.trim()) ? "" : "Please enter a valid phone number (e.g. +92 300 1234567)."),
    email: (v) => (!v.trim() || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? "" : "Please enter a valid email address."),
    service: (v) => (v ? "" : "Please select the service you need."),
    location: (v) => (v.trim().length >= 2 ? "" : "Please tell us the project location."),
    message: (v) => (v.trim().length >= 10 ? "" : "Please describe your requirement in a few words (at least 10 characters)."),
    consent: (v, el) => (el.checked ? "" : "Please confirm you agree to be contacted."),
  };

  function validateField(input) {
    const field = input.closest(".field");
    const rule = rules[input.dataset.validate];
    if (!rule || !field) return true;
    const msg = rule(input.value, input);
    field.classList.toggle("invalid", !!msg);
    const err = $(".error-msg", field);
    if (err) err.textContent = msg;
    input.setAttribute("aria-invalid", msg ? "true" : "false");
    return !msg;
  }

  function setStatus(form, type, html) {
    const box = $(".form-status", form);
    if (!box) return;
    const icon = type === "success" ? ICON_OK : type === "error" ? ICON_ERR : ICON_INFO;
    box.className = `form-status show ${type}`;
    box.innerHTML = `${icon}<div>${html}</div>`;
    box.setAttribute("role", type === "error" ? "alert" : "status");
    box.scrollIntoView({ behavior: "smooth", block: "nearest" });
  }

  function collect(form) {
    const data = {};
    $$("input, select, textarea", form).forEach((el) => {
      if (!el.name || el.type === "file" || el.type === "checkbox" || el.classList.contains("hp")) return;
      data[el.name] = el.value.trim();
    });
    return data;
  }

  function buildMessage(form, data) {
    const kind = form.dataset.form === "quote" ? "Quote Request" : "Inquiry";
    const label = (name) => {
      const el = form.elements[name];
      const lab = el && el.id && $(`label[for="${el.id}"]`);
      return lab ? lab.textContent.replace(/\*|\(optional\)/gi, "").trim() : name;
    };
    const lines = [`Assalam-o-Alaikum, this is a new ${kind} from your website (${CFG.brandName || ""}).`, ""];
    Object.entries(data).forEach(([k, v]) => { if (v) lines.push(`${label(k)}: ${v}`); });
    const file = $('input[type="file"]', form);
    if (file && file.files && file.files.length) lines.push("", `(I have a reference image to share: ${file.files[0].name})`);
    return lines.join("\n");
  }

  async function deliver(form, data) {
    const mode = CFG.formMode || "whatsapp";
    if (mode === "endpoint" && CFG.formEndpoint) {
      const fd = new FormData(form);
      fd.delete("website"); // honeypot
      fd.append("_subject", `New ${form.dataset.form === "quote" ? "quote request" : "inquiry"} — ${CFG.brandName || "website"}`);
      const res = await fetch(CFG.formEndpoint, { method: "POST", body: fd, headers: { Accept: "application/json" } });
      if (!res.ok) throw new Error(`Server responded ${res.status}`);
      return { via: "endpoint" };
    }
    const msg = buildMessage(form, data);
    if (mode === "email") {
      const subject = encodeURIComponent(`New ${form.dataset.form === "quote" ? "quote request" : "inquiry"} from website`);
      window.location.href = `mailto:${CFG.email}?subject=${subject}&body=${encodeURIComponent(msg)}`;
      return { via: "email" };
    }
    // default: WhatsApp
    const url = window.SITE ? window.SITE.waLink(msg) : `https://wa.me/${CFG.whatsappNumber}?text=${encodeURIComponent(msg)}`;
    const w = window.open(url, "_blank", "noopener");
    if (!w) window.location.href = url;
    return { via: "whatsapp" };
  }

  $$("form[data-form]").forEach((form) => {
    const started = Date.now();
    const inputs = $$("[data-validate]", form);
    inputs.forEach((el) => {
      el.addEventListener("blur", () => validateField(el));
      el.addEventListener("input", () => { if (el.closest(".field").classList.contains("invalid")) validateField(el); });
      el.addEventListener("change", () => validateField(el));
    });

    // Reference image preview (client-side only)
    const file = $('input[type="file"]', form);
    const preview = $(".file-preview", form);
    if (file && preview) {
      file.addEventListener("change", () => {
        const f = file.files && file.files[0];
        preview.classList.remove("show");
        if (!f) return;
        if (!/^image\//.test(f.type)) { setStatus(form, "error", "Please choose an image file (JPG, PNG or WEBP)."); file.value = ""; return; }
        if (f.size > 5 * 1024 * 1024) { setStatus(form, "error", "Image is too large. Please choose a file under 5 MB."); file.value = ""; return; }
        const img = $("img", preview) || preview.appendChild(document.createElement("img"));
        img.src = URL.createObjectURL(f);
        img.alt = "Reference image preview";
        preview.classList.add("show");
        $(".form-status", form).classList.remove("show");
      });
    }

    form.addEventListener("submit", async (e) => {
      e.preventDefault();
      // Spam checks
      const hp = $(".hp", form);
      if (hp && hp.value) return; // bot filled the hidden field — silently ignore
      if (Date.now() - started < 2500) { setStatus(form, "error", "That was quick! Please take a moment to review your details and try again."); return; }
      const last = Number(sessionStorage.getItem("lastSubmit") || 0);
      if (Date.now() - last < 15000) { setStatus(form, "info", "You just sent a request a moment ago. Please wait a few seconds before sending another."); return; }

      // Validate all
      let ok = true, first = null;
      inputs.forEach((el) => { const v = validateField(el); if (!v) { ok = false; first = first || el; } });
      if (!ok) { setStatus(form, "error", "Please correct the highlighted fields and try again."); first.focus(); return; }

      const btn = $('button[type="submit"]', form);
      const label = btn ? btn.innerHTML : "";
      if (btn) { btn.disabled = true; btn.textContent = "Sending…"; }
      try {
        const { via } = await deliver(form, collect(form));
        sessionStorage.setItem("lastSubmit", String(Date.now()));
        const brand = CFG.brandName || "we";
        if (via === "whatsapp") {
          setStatus(form, "success", `<strong>WhatsApp is opening with your details.</strong> Press <em>Send</em> in WhatsApp to deliver your request${file && file.files.length ? ", then attach your reference image in the chat" : ""}. If WhatsApp did not open, <a href="${window.SITE.waLink(buildMessage(form, collect(form)))}" target="_blank" rel="noopener">tap here</a>.`);
        } else if (via === "email") {
          setStatus(form, "success", `<strong>Your email app is opening with your details.</strong> Press <em>Send</em> to deliver your request to ${brand}.`);
        } else {
          setStatus(form, "success", `<strong>Thank you! Your request has been sent.</strong> ${brand} will contact you shortly on the phone number you provided.`);
          form.reset();
          if (preview) preview.classList.remove("show");
        }
      } catch (err) {
        setStatus(form, "error", `Sorry, we couldn't send your request right now. Please try again, or contact us directly on <a data-wa href="${window.SITE.waLink()}" target="_blank" rel="noopener">WhatsApp</a> or <a href="tel:${CFG.phoneTel}">${CFG.phoneDisplay}</a>.`);
        console.error(err);
      } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = label; }
      }
    });
  });
})();
