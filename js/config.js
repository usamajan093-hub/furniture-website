/* =====================================================================
   BUSINESS CONFIGURATION  —  EDIT THIS FILE TO UPDATE YOUR DETAILS
   ---------------------------------------------------------------------
   Everything the website shows about your business is set here.
   Change the values below, save, and reload the site. Values wrapped
   like [THIS] are placeholders you should replace before going live.
   ===================================================================== */

window.SITE_CONFIG = {
  /* ---- Brand ---- */
  brandName: "FINISHCRAFT",
  brandTagline: "Wood Finishing & Interiors",
  brandSlogan: "From Premium Wood Finishing to Complete Interior Woodwork.",

  /* ---- Contact ---- */
  // Phone shown on the site (any readable format):
  phoneDisplay: "+92 309 8986264",
  // Same phone for click-to-call links (digits and + only, no spaces):
  phoneTel: "+923098986264",

  // WhatsApp number in INTERNATIONAL format, digits only, NO + and NO spaces.
  // Example for a Pakistan number +92 300 1234567  ->  "923001234567"
  whatsappNumber: "923098986264",

  email: "info@example.com",                 // [EDIT]

  /* ---- Address / Location (used for SEO + Contact page) ---- */
  addressLine: "[Street Address]",           // [EDIT]
  city: "[City]",                            // [EDIT]
  region: "[Province/State]",                // [EDIT]
  country: "Pakistan",                       // [EDIT]
  // Paste a Google Maps "embed" src URL here to show a live map on Contact.
  // Leave empty ("") to show a tidy placeholder instead.
  googleMapsEmbedSrc: "",                     // [EDIT — optional]

  /* ---- Working hours (shown on Contact page) ---- */
  hours: "Mon – Sat: 9:00 AM – 8:00 PM",     // [EDIT]

  /* ---- Social links (leave "" to hide a link) ---- */
  social: {
    facebook: "",                            // [EDIT optional]
    instagram: "",                           // [EDIT optional]
  },

  /* ---- Money ---- */
  currency: "PKR",

  /* ---- Pre-filled WhatsApp greeting ---- */
  whatsappMessage:
    "Assalam-o-Alaikum, I am interested in FINISHCRAFT's wood polishing / interior woodwork services. Please share details and a quotation.",

  /* ---- Form delivery ----------------------------------------------------
     How contact / consultation form submissions reach you. Choose ONE mode:

       "whatsapp"  -> opens WhatsApp with the form details pre-filled
                      (no backend needed — works out of the box)
       "email"     -> opens the visitor's email app addressed to you
       "endpoint"  -> sends the data to a backend/form service URL
                      (set formEndpoint below). Good options: Formspree,
                      Getform, Basin, or your own server.
  ------------------------------------------------------------------------ */
  formMode: "whatsapp",

  // Only used when formMode === "endpoint". Paste your form service URL here.
  formEndpoint: "",                          // e.g. "https://formspree.io/f/xxxxxxx"

  /* ---- SEO defaults (per-page titles are set in each HTML file) ---- */
  seo: {
    // Used for location-based SEO text. Keep configurable.
    serviceArea: "[Your City] & surrounding areas",   // [EDIT]
  },
};
