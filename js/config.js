/* =====================================================================
   BUSINESS CONFIGURATION  —  EDIT THIS FILE TO UPDATE YOUR DETAILS
   ---------------------------------------------------------------------
   Everything the website shows about your business is set here.
   Change the values below, save, and reload the site. Values wrapped
   like [THIS] are placeholders you should replace before going live.
   ===================================================================== */

window.SITE_CONFIG = {
  /* ---- Brand ---- */
  brandName: "royalwoodinteriors.com",
  brandTagline: "Royal Wood Polish, Deco & Interiors",
  brandSlogan: "Complete Polish Work, Deco & All Kinds of Woodwork — for Homes, Factories & Buildings.",
  // Leadership team shown on Home, About and Contact. Add, remove or reorder freely;
  // leave the list empty ([]) to hide the team section everywhere.
  team: [
    { name: "Abdul Lateef",     role: "Founder & CEO" },
    { name: "Muhammad Asghar",  role: "Managing Director" },
    { name: "Muhammad Tanveer", role: "Managing Director" },
    { name: "Muhammad Akram",   role: "Director" },
  ],
  // Optional owner / trading names in prose. Leave "" to keep them off the site.
  ownerNames: "",
  tradingAs: "",

  /* ---- Contact ---- */
  // Phone shown on the site (any readable format):
  phoneDisplay: "+92 309 8986264",
  // Same phone for click-to-call links (digits and + only, no spaces):
  phoneTel: "+923098986264",

  // Second phone line (shown beside the first; leave both "" to hide it):
  phone2Display: "+92 300 4086113",
  phone2Tel: "+923004086113",

  // WhatsApp number in INTERNATIONAL format, digits only, NO + and NO spaces.
  // Example for a Pakistan number +92 300 1234567  ->  "923001234567"
  whatsappNumber: "923098986264",

  email: "furniturewood786@gmail.com",

  /* ---- Address / Location (used for SEO + Contact page) ---- */
  addressLine: "House No. 452/A, Street No. 54, Sector C, Sir Syed Ahmad Khan Road, near Maryam Masjid, Manzoor Colony",
  city: "Karachi",
  region: "Sindh",                           // [EDIT]
  country: "Pakistan",                       // [EDIT]
  // Paste a Google Maps "embed" src URL here to show a live map on Contact.
  // Leave empty ("") to show a tidy placeholder instead.
  googleMapsEmbedSrc: "",                     // [EDIT — optional]

  /* ---- Working hours (shown on Contact page) ---- */
  hours: "Open daily: 7:00 AM – 11:30 PM",

  /* ---- Social links (leave "" to hide a link) ---- */
  social: {
    facebook: "",                            // [EDIT optional]
    instagram: "",                           // [EDIT optional]
  },

  /* ---- Money ---- */
  currency: "PKR",

  /* ---- Pre-filled WhatsApp greeting ---- */
  whatsappMessage:
    "Assalam-o-Alaikum, I am interested in Royal Wood Interiors' polish work / woodwork services. Please share details and a quotation.",

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
    serviceArea: "Karachi & surrounding areas",   // [EDIT]
  },
};
