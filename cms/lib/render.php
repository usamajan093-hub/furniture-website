<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

/**
 * Frontend renderer. Builds the complete public page from JSON content,
 * the active (or previewed) template, visible offers and published pages.
 */

function r_title(string $title, string $em = '', string $after = ''): string
{
    $out = e($title);
    if ($em !== '') {
        $out .= ' <em>' . e($em) . '</em>';
    }
    if ($after !== '') {
        $out .= ' ' . e($after);
    }
    return $out;
}

function r_img(?string $src, string $alt = '', string $extra = '', bool $lazy = true): string
{
    $src = trim((string)$src);
    if ($src === '') {
        return '';
    }
    return '<img src="' . e($src) . '" alt="' . e($alt) . '"' . ($lazy ? ' loading="lazy"' : '') . ($extra ? ' ' . $extra : '') . '>';
}

function r_link_attr(string $url): string
{
    $url = trim($url);
    if ($url === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $url)) {
        $url = '#contact';
    }
    $attr = 'href="' . e($url) . '"';
    if (str_starts_with($url, '#')) {
        $route = substr($url, 1);
        $route = explode('?', $route)[0];
        $attr .= ' data-route="' . e($route) . '"';
    } elseif (preg_match('#^https?://#i', $url)) {
        $attr .= ' target="_blank" rel="noopener"';
    }
    return $attr;
}

function r_btn(string $text, string $url, string $style = 'primary'): string
{
    if (trim($text) === '') {
        return '';
    }
    return '<a class="btn btn-' . e($style) . '" ' . r_link_attr($url) . '>' . e($text) . '</a>';
}

function r_sec_head(array $s, bool $center = false, string $after_key = 'title_after'): string
{
    $h = '<div class="sec-head' . ($center ? ' center' : '') . ' reveal">';
    if (!empty($s['eyebrow'])) {
        $h .= '<span class="eyebrow">' . e($s['eyebrow']) . '</span>';
    }
    $h .= '<h2>' . r_title($s['title'] ?? '', $s['title_em'] ?? '', $s[$after_key] ?? '') . '</h2>';
    if (!empty($s['lead'])) {
        $h .= '<p class="lead"' . ($center ? ' style="margin-inline:auto"' : '') . '>' . e($s['lead']) . '</p>';
    }
    return $h . '</div>';
}

function r_wa_url(array $contact, ?string $msg = null): string
{
    $msg = $msg ?? ($contact['whatsapp_msg'] ?? 'Assalam-o-Alaikum, I am interested in your services.');
    return 'https://wa.me/' . preg_replace('/\D/', '', (string)($contact['whatsapp'] ?? '')) . '?text=' . rawurlencode($msg);
}

function r_tel(string $display): string
{
    return preg_replace('/[^\d+]/', '', $display) ?? '';
}

/* ---------- home sections ---------- */

function r_hero(array $c, array $tpl): string
{
    $h = $c['hero'] ?? [];
    $cls = 'hero' . ($tpl['hero_layout'] === 'center' ? ' center' : '') . (empty($tpl['hero_motion']) ? ' still' : '');
    $out = '<section class="' . $cls . '"><div class="hero-bg">' . r_img($tpl['hero_image'] ?: ($h['image'] ?? ''), $h['title'] ?? '', 'fetchpriority="high"', false) . '</div><div class="wrap">';
    if (!empty($h['eyebrow'])) {
        $out .= '<span class="eyebrow">' . e($h['eyebrow']) . '</span>';
    }
    $out .= '<h1>' . r_title($h['title'] ?? '', $h['title_em'] ?? '') . '</h1>';
    if (!empty($h['lead'])) {
        $out .= '<p class="lead">' . e($h['lead']) . '</p>';
    }
    $out .= '<div class="btns">' . r_btn($h['btn1_text'] ?? '', $h['btn1_link'] ?? '') . r_btn($h['btn2_text'] ?? '', $h['btn2_link'] ?? '', 'ghost') . '</div>';
    $pills = array_filter($h['pills'] ?? []);
    if ($pills) {
        $out .= '<div class="pills">';
        foreach ($pills as $p) {
            $out .= '<span class="pill">' . e($p) . '</span>';
        }
        $out .= '</div>';
    }
    return $out . '</div></section>';
}

function r_marquee(array $c): string
{
    $items = array_filter($c['marquee']['items'] ?? []);
    if (!$items) {
        return '';
    }
    $out = '<div class="marquee" aria-hidden="true"><div class="marquee-track" id="marquee">';
    foreach ($items as $i) {
        $out .= '<span>' . e($i) . '</span>';
    }
    return $out . '</div></div>';
}

function r_services(array $c): string
{
    $s = $c['services'] ?? [];
    $items = $s['items'] ?? [];
    if (!$items) {
        return '';
    }
    $out = '<section id="services"><div class="wrap">' . r_sec_head($s) . '<div class="grid4">';
    foreach ($items as $i => $it) {
        $url = $it['link'] ?? '';
        $tag = trim($url) === '' ? 'div' : 'a';
        $out .= '<' . $tag . ' class="card reveal" ' . ($tag === 'a' ? r_link_attr($url) : '') . ' style="--d:' . ($i * .1) . 's">';
        $out .= '<div class="card-img">' . r_img($it['image'] ?? '', $it['title'] ?? '') . '</div>';
        $out .= '<div class="card-body"><div class="card-num">' . sprintf('%02d', $i + 1) . '</div><h3>' . e($it['title'] ?? '') . '</h3><p>' . e($it['text'] ?? '') . '</p>';
        if ($tag === 'a') {
            $out .= '<span class="more">View work →</span>';
        }
        $out .= '</div></' . $tag . '>';
    }
    return $out . '</div></div></section>';
}

function r_offer_card(array $o, array $c, int $i = 0): string
{
    $cta = offer_cta($o, $c);
    $out = '<div class="card offer reveal" style="--d:' . ($i * .08) . 's"><span class="offer-badge">' . e(offer_badge($o)) . '</span>';
    $out .= '<div class="card-img wide">' . r_img($o['image'] ?? '', $o['title'] ?? '') . '</div><div class="card-body">';
    if (!empty($o['category'])) {
        $out .= '<div class="offer-cat">' . e($o['category']) . '</div>';
    }
    $out .= '<h3>' . e($o['title'] ?? '') . '</h3>';
    if (!empty($o['description'])) {
        $out .= '<p>' . nl2br(e($o['description'])) . '</p>';
    }
    $po = $o['price_original'] ?? null;
    $pd = $o['price_discounted'] ?? null;
    if ($po !== null || $pd !== null) {
        $out .= '<div class="offer-prices">';
        if ($pd !== null) {
            $out .= '<span class="new">' . e(money((float)$pd)) . '</span>';
        }
        if ($po !== null) {
            $out .= '<span class="' . ($pd !== null ? 'old' : 'new') . '">' . e(money((float)$po)) . '</span>';
        }
        $out .= '</div>';
    }
    if (!empty($o['end_date'])) {
        $out .= '<div class="offer-valid">Valid till ' . e(nice_date($o['end_date'])) . '</div>';
    } elseif (!empty($o['start_date'])) {
        $out .= '<div class="offer-valid">From ' . e(nice_date($o['start_date'])) . '</div>';
    }
    $out .= '<a class="btn btn-primary" ' . r_link_attr($cta['url']) . '>' . e($cta['label']) . '</a>';
    return $out . '</div></div>';
}

function r_offers_section(array $c, array $offers, bool $full = false): string
{
    $s = $c['offers_section'] ?? [];
    if (!$offers && !$full) {
        return '';
    }
    $list = $full ? $offers : array_slice($offers, 0, 3);
    $out = '<section id="offers-home" class="bg-surface"><div class="wrap">' . r_sec_head($s, true) . '<div class="grid3">';
    foreach ($list as $i => $o) {
        $out .= r_offer_card($o, $c, $i);
    }
    $out .= '</div>';
    if (!$list) {
        $out = '<section id="offers-home"><div class="wrap">' . r_sec_head($s, true) . '<div class="offers-empty">' . e($s['empty'] ?? 'No offers are running right now. Contact us for a quotation.') . '</div>';
    } elseif (!$full && count($offers) > 3) {
        $out .= '<div class="btns" style="justify-content:center;margin-top:40px"><a class="btn btn-ghost" href="#offers" data-route="offers">All offers →</a></div>';
    }
    return $out . '</div></section>';
}

function r_before_after(array $c): string
{
    $b = $c['before_after'] ?? [];
    if (empty($b['before_image']) || empty($b['after_image'])) {
        return '';
    }
    $out = '<section class="bg-surface" id="before-after"><div class="wrap split"><div class="reveal">';
    if (!empty($b['eyebrow'])) {
        $out .= '<span class="eyebrow">' . e($b['eyebrow']) . '</span>';
    }
    $out .= '<h2>' . r_title($b['title'] ?? '', $b['title_em'] ?? '') . '</h2>';
    if (!empty($b['text'])) {
        $out .= '<p class="lead" style="margin-top:18px">' . e($b['text']) . '</p>';
    }
    $chips = array_filter($b['chips'] ?? []);
    if ($chips) {
        $out .= '<div class="chips">';
        foreach ($chips as $ch) {
            $out .= '<span class="chip">' . e($ch) . '</span>';
        }
        $out .= '</div>';
    }
    $out .= r_btn($b['btn_text'] ?? '', $b['btn_link'] ?? '', 'ghost');
    $out .= '</div><div class="reveal" style="--d:.15s"><div class="ba" id="ba" style="--pos:50%">';
    $out .= r_img($b['after_image'], 'After polish') . r_img($b['before_image'], 'Before polish', 'class="before"');
    $out .= '<div class="ba-handle"></div><span class="ba-label l">Before</span><span class="ba-label r">After</span><input type="range" min="0" max="100" value="50" aria-label="Compare before and after"></div></div></div></section>';
    return $out;
}

function r_products(array $c): string
{
    $p = $c['products'] ?? [];
    $items = $p['items'] ?? [];
    if (!$items) {
        return '';
    }
    $out = '<section id="products"><div class="wrap">' . r_sec_head($p, true) . '<div class="grid3">';
    foreach ($items as $i => $it) {
        $url = trim($it['link'] ?? '');
        $tag = $url === '' ? 'div' : 'a';
        $out .= '<' . $tag . ' class="card reveal" ' . ($tag === 'a' ? r_link_attr($url) : '') . ' style="--d:' . ($i * .08) . 's">';
        $out .= '<div class="card-img wide">' . r_img($it['image'] ?? '', $it['name'] ?? '') . '</div><div class="card-body"><h3>' . e($it['name'] ?? '') . '</h3>';
        if (!empty($it['text'])) {
            $out .= '<p>' . e($it['text']) . '</p>';
        }
        if (!empty($it['price'])) {
            $out .= '<div class="price">' . e($it['price']) . '</div>';
        }
        $out .= '</div></' . $tag . '>';
    }
    return $out . '</div></div></section>';
}

function r_process(array $c): string
{
    $p = $c['process'] ?? [];
    $steps = $p['steps'] ?? [];
    if (!$steps) {
        return '';
    }
    $out = '<section id="process"><div class="wrap">' . r_sec_head($p, true) . '<div class="steps">';
    foreach ($steps as $i => $st) {
        $out .= '<div class="step reveal" style="--d:' . ($i * .1) . 's"><div class="num">' . sprintf('%02d', $i + 1) . '</div><h3>' . e($st['title'] ?? '') . '</h3><p>' . e($st['text'] ?? '') . '</p></div>';
    }
    return $out . '</div></div></section>';
}

function r_why(array $c): string
{
    $w = $c['why'] ?? [];
    $items = $w['items'] ?? [];
    if (!$items) {
        return '';
    }
    $out = '<section id="why" style="padding-top:0"><div class="wrap">' . r_sec_head($w, true) . '<div class="why reveal">';
    foreach ($items as $it) {
        $out .= '<div><i></i><h3>' . e($it['title'] ?? '') . '</h3><p>' . e($it['text'] ?? '') . '</p></div>';
    }
    return $out . '</div></div></section>';
}

function r_testimonials(array $c): string
{
    $t = $c['testimonials'] ?? [];
    $items = $t['items'] ?? [];
    if (!$items) {
        return '';
    }
    $out = '<section id="testimonials" class="bg-surface"><div class="wrap">' . r_sec_head($t, true) . '<div class="quotes">';
    foreach ($items as $i => $q) {
        $out .= '<div class="quote reveal" style="--d:' . ($i * .1) . 's"><p>' . e($q['text'] ?? '') . '</p><div class="quote-who">' . r_img($q['image'] ?? '', $q['name'] ?? '') . '<div><b>' . e($q['name'] ?? '') . '</b><small>' . e($q['role'] ?? '') . '</small></div></div></div>';
    }
    return $out . '</div></div></section>';
}

function r_cta(array $c, array $contact): string
{
    $s = $c['cta'] ?? [];
    if (empty($s['title'])) {
        return '';
    }
    $out = '<section class="cta"><div class="wrap reveal">';
    if (!empty($s['eyebrow'])) {
        $out .= '<span class="eyebrow">' . e($s['eyebrow']) . '</span>';
    }
    $out .= '<h2>' . r_title($s['title'], $s['title_em'] ?? '') . '</h2>';
    if (!empty($s['text'])) {
        $out .= '<p>' . e($s['text']) . '</p>';
    }
    $b2 = $s['btn2_link'] ?? '';
    if ($b2 === '' && !empty($contact['phone1'])) {
        $b2 = 'tel:' . r_tel($contact['phone1']);
    }
    $out .= '<div class="btns">' . r_btn($s['btn1_text'] ?? '', $s['btn1_link'] ?? '') . r_btn($s['btn2_text'] ?? '', $b2, 'ghost') . '</div>';
    return $out . '</div></section>';
}

/* ---------- page builder ---------- */

function r_pb_bg(array $s): string
{
    $bg = $s['bg'] ?? 'base';
    if ($bg === 'surface') {
        return ' class="bg-surface"';
    }
    if ($bg === 'gold') {
        return ' class="bg-gold"';
    }
    if ($bg === 'custom' && preg_match('/^#[0-9a-f]{3,8}$/i', $s['bg_color'] ?? '')) {
        return ' style="background:' . e($s['bg_color']) . '"';
    }
    return '';
}

function r_pb_btns(array $s, bool $center = false): string
{
    $btns = array_filter($s['buttons'] ?? [], fn($b) => trim($b['label'] ?? '') !== '');
    if (!$btns) {
        return '';
    }
    $out = '<div class="btns">';
    foreach ($btns as $b) {
        $out .= r_btn($b['label'], $b['url'] ?? '', ($b['style'] ?? 'primary') === 'ghost' ? 'ghost' : 'primary');
    }
    return $out . '</div>';
}

function r_pb_section(array $s): string
{
    $type = $s['type'] ?? 'text';
    $bgAttr = r_pb_bg($s);
    $head = '';
    if (!empty($s['eyebrow'])) {
        $head .= '<span class="eyebrow">' . e($s['eyebrow']) . '</span>';
    }
    $head .= '<h2>' . r_title($s['heading'] ?? '', $s['heading_em'] ?? '') . '</h2>';
    switch ($type) {
        case 'banner':
            return '<section class="pb-banner"' . $bgAttr . '><div class="pb-bg">' . r_img($s['image'] ?? '', $s['heading'] ?? '') . '</div><div class="wrap reveal">' . $head . (!empty($s['text']) ? '<p>' . nl2br(e($s['text'])) . '</p>' : '') . r_pb_btns($s) . '</div></section>';
        case 'split':
            $rev = ($s['align'] ?? 'left') === 'right' ? ' rev' : '';
            return '<section class="pb-split"' . $bgAttr . '><div class="wrap split' . $rev . '"><div class="reveal">' . r_img($s['image'] ?? '', $s['heading'] ?? '') . '</div><div class="reveal" style="--d:.1s">' . $head . (!empty($s['text']) ? '<p class="lead">' . e($s['text']) . '</p>' : '') . r_pb_btns($s) . '</div></div></section>';
        case 'gallery':
            $imgs = array_filter($s['images'] ?? [], fn($u) => trim((string)$u) !== '');
            $out = '<section class="pb-gallery"' . $bgAttr . '><div class="wrap">';
            if (!empty($s['heading'])) {
                $out .= '<div class="sec-head center reveal">' . $head . (!empty($s['text']) ? '<p class="lead" style="margin-inline:auto">' . e($s['text']) . '</p>' : '') . '</div>';
            }
            $out .= '<div class="grid3">';
            foreach ($imgs as $i => $u) {
                $out .= '<div class="reveal" style="--d:' . ($i * .06) . 's">' . r_img($u, $s['heading'] ?? 'Photo', 'data-lb="1"') . '</div>';
            }
            return $out . '</div></div></section>';
        case 'cards':
            $out = '<section class="pb-cards"' . $bgAttr . '><div class="wrap">';
            if (!empty($s['heading'])) {
                $out .= '<div class="sec-head center reveal">' . $head . (!empty($s['text']) ? '<p class="lead" style="margin-inline:auto">' . e($s['text']) . '</p>' : '') . '</div>';
            }
            $out .= '<div class="boxes">';
            foreach (array_filter($s['cards'] ?? [], fn($c) => trim($c['title'] ?? '') !== '') as $i => $c) {
                $out .= '<div class="box reveal" style="--d:' . ($i * .1) . 's"><i></i><h3>' . e($c['title']) . '</h3><p>' . e($c['text'] ?? '') . '</p></div>';
            }
            return $out . '</div></div></section>';
        case 'cta':
            return '<section class="cta"' . ($bgAttr ?: '') . '><div class="wrap reveal">' . $head . (!empty($s['text']) ? '<p>' . e($s['text']) . '</p>' : '') . r_pb_btns($s) . '</div></section>';
        default:
            return '<section class="pb-text"' . $bgAttr . '><div class="wrap reveal">' . $head . (!empty($s['text']) ? '<p class="lead" style="margin-inline:auto">' . e($s['text']) . '</p>' : '') . r_pb_btns($s) . '</div></section>';
    }
}

function r_page_sections(array $page): string
{
    $out = '';
    foreach ($page['sections'] ?? [] as $s) {
        $out .= r_pb_section($s);
    }
    return $out;
}

/* ---------- other pages ---------- */

function r_page_head(string $eyebrow, string $h1, string $em): string
{
    return '<div class="page-head"><div class="wrap"><span class="eyebrow">' . e($eyebrow) . '</span><h1>' . r_title($h1, $em) . '</h1></div></div>';
}

function r_about(array $c): string
{
    $a = $c['about'] ?? [];
    $out = '<div class="page" id="page-about">' . r_page_head('About', $a['h1'] ?? '', $a['h1_em'] ?? '');
    $out .= '<section><div class="wrap split"><div class="reveal">';
    if (!empty($a['eyebrow'])) {
        $out .= '<span class="eyebrow">' . e($a['eyebrow']) . '</span>';
    }
    $out .= '<h2>' . r_title($a['title'] ?? '', $a['title_em'] ?? '') . '</h2>';
    if (!empty($a['lead'])) {
        $out .= '<p class="lead" style="margin-top:18px">' . e($a['lead']) . '</p>';
    }
    if (!empty($a['text'])) {
        $out .= '<p style="margin-top:16px">' . e($a['text']) . '</p>';
    }
    $chips = array_filter($a['chips'] ?? []);
    if ($chips) {
        $out .= '<div class="chips">';
        foreach ($chips as $ch) {
            $out .= '<span class="chip">' . e($ch) . '</span>';
        }
        $out .= '</div>';
    }
    $out .= r_btn($a['btn_text'] ?? '', $a['btn_link'] ?? '');
    $out .= '</div><div class="frame reveal" style="--d:.15s">' . r_img($a['image'] ?? '', $a['title'] ?? '') . '</div></div></section>';
    $boxes = $a['boxes'] ?? [];
    if ($boxes || !empty($a['photo1']) || !empty($a['photo2'])) {
        $out .= '<section style="padding-top:0"><div class="wrap">';
        if ($boxes) {
            $out .= '<div class="sec-head reveal"><span class="eyebrow">' . e($a['boxes_eyebrow'] ?? '') . '</span><h2>' . r_title($a['boxes_title'] ?? '', $a['boxes_title_em'] ?? '') . '</h2></div><div class="boxes">';
            foreach ($boxes as $i => $b) {
                $out .= '<div class="box reveal" style="--d:' . ($i * .1) . 's"><i></i><h3>' . e($b['title'] ?? '') . '</h3><p>' . e($b['text'] ?? '') . '</p></div>';
            }
            $out .= '</div>';
        }
        if (!empty($a['photo1']) || !empty($a['photo2'])) {
            $out .= '<div class="photos2">' . r_img($a['photo1'] ?? '', 'Our work', 'class="reveal"') . r_img($a['photo2'] ?? '', 'Our work', 'class="reveal" style="--d:.15s"') . '</div>';
        }
        $out .= '</div></section>';
    }
    return $out . '</div>';
}

function r_portfolio(array $c): string
{
    $p = $c['portfolio'] ?? [];
    $filters = $p['filters'] ?? [];
    $labels = [];
    foreach ($filters as $f) {
        $labels[$f['key'] ?? ''] = $f['label'] ?? '';
    }
    $out = '<div class="page" id="page-portfolio">' . r_page_head('Portfolio', $p['h1'] ?? '', $p['h1_em'] ?? '');
    $out .= '<section><div class="wrap"><div class="filters" id="filters" role="group" aria-label="Filter projects"><button class="active" data-f="all">All</button>';
    foreach ($filters as $f) {
        $out .= '<button data-f="' . e($f['key'] ?? '') . '">' . e($f['label'] ?? '') . '</button>';
    }
    $out .= '</div><div class="masonry" id="masonry">';
    foreach ($p['items'] ?? [] as $it) {
        $cat = $it['cat'] ?? '';
        $out .= '<figure class="item reveal" data-cat="' . e($cat) . '">' . r_img($it['image'] ?? '', $it['title'] ?? '') . '<figcaption><small>' . e($labels[$cat] ?? $cat) . '</small><b>' . e($it['title'] ?? '') . '</b></figcaption></figure>';
    }
    return $out . '</div></div></section></div>';
}

function r_offers_page(array $c, array $offers): string
{
    $s = $c['offers_section'] ?? [];
    $out = '<div class="page" id="page-offers">' . r_page_head('Offers', $s['title'] ?? 'Current offers', $s['title_em'] ?? '');
    $out .= '<section><div class="wrap">';
    if ($offers) {
        $out .= '<div class="grid3">';
        foreach ($offers as $i => $o) {
            $out .= r_offer_card($o, $c, $i);
        }
        $out .= '</div>';
    } else {
        $out .= '<div class="offers-empty">' . e($s['empty'] ?? 'No offers are running right now. Contact us for a quotation.') . '</div>';
    }
    return $out . '</div></section></div>';
}

function r_contact(array $c): string
{
    $k = $c['contact'] ?? [];
    $phoneIcon = '<svg viewBox="0 0 24 24"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>';
    $out = '<div class="page" id="page-contact">' . r_page_head('Contact', $k['h1'] ?? '', $k['h1_em'] ?? '');
    $out .= '<section><div class="wrap contact"><div class="info">';
    $d = 0;
    if (!empty($k['phone1'])) {
        $out .= '<a class="card-i reveal" href="tel:' . e(r_tel($k['phone1'])) . '">' . $phoneIcon . '<div><b>Phone</b><span>' . e($k['phone1']) . '</span><small>Tap to call</small></div></a>';
    }
    if (!empty($k['phone2'])) {
        $out .= '<a class="card-i reveal" style="--d:.05s" href="tel:' . e(r_tel($k['phone2'])) . '">' . $phoneIcon . '<div><b>Phone 2</b><span>' . e($k['phone2']) . '</span><small>Tap to call</small></div></a>';
    }
    if (!empty($k['whatsapp'])) {
        $out .= '<a class="card-i reveal" style="--d:.1s" href="' . e(r_wa_url($k)) . '" target="_blank" rel="noopener"><svg viewBox="0 0 24 24"><path d="M20.5 3.5A11.8 11.8 0 0 0 2.3 17.7L1 23l5.4-1.4A11.8 11.8 0 0 0 20.5 3.5z"/><path d="M8.5 7.5c.3-.6.6-.6.9-.6h.6c.2 0 .4 0 .6.5l.8 2c.1.2.1.4 0 .6l-.4.6-.4.4c-.2.2-.3.3-.1.6a9 9 0 0 0 1.7 2.1 8 8 0 0 0 2.4 1.5c.3.1.5.1.6-.1l.9-1.1c.2-.3.4-.2.6-.1l1.9.9c.3.1.4.2.5.3 0 .2 0 .9-.3 1.6-.3.8-1.6 1.5-2.2 1.5-.6.1-1.2.3-4-1-3.3-1.5-5.5-5-5.6-5.2-.2-.3-1.3-1.8-1.3-3.4 0-1.6.8-2.4 1.1-2.7z"/></svg><div><b>WhatsApp</b><span>Chat with us</span><small>Send photos for a quick estimate</small></div></a>';
    }
    if (!empty($k['email'])) {
        $out .= '<a class="card-i reveal" style="--d:.15s" href="mailto:' . e($k['email']) . '"><svg viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg><div><b>Email</b><span>' . e($k['email']) . '</span></div></a>';
    }
    if (!empty($k['address'])) {
        $out .= '<div class="card-i reveal" style="--d:.2s"><svg viewBox="0 0 24 24"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg><div><b>Address</b><span>' . nl2br(e($k['address'])) . '</span>' . (!empty($k['area']) ? '<small>' . e($k['area']) . '</small>' : '') . '</div></div>';
    }
    if (!empty($k['hours'])) {
        $out .= '<div class="card-i reveal" style="--d:.25s"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><div><b>Hours</b><span>' . e($k['hours']) . '</span></div></div>';
    }
    $out .= '</div><form class="form reveal" id="form" style="--d:.1s" novalidate><h3>' . e($k['form_title'] ?? 'Send an inquiry') . '</h3><p>' . e($k['form_intro'] ?? '') . '</p>';
    $out .= '<div class="field"><label for="f-name">Your name</label><input id="f-name" name="name" type="text" autocomplete="name" placeholder="Full name" required></div>';
    $out .= '<div class="field"><label for="f-phone">Phone / WhatsApp</label><input id="f-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="+92 300 1234567" required></div>';
    $out .= '<div class="field"><label for="f-service">Service</label><select id="f-service" name="service" required><option value="">Select a service…</option>';
    foreach (array_filter($k['form_services'] ?? []) as $sv) {
        $out .= '<option>' . e($sv) . '</option>';
    }
    $out .= '</select></div><div class="field"><label for="f-msg">Message</label><textarea id="f-msg" name="message" placeholder="Describe the work, sizes and finish you want." required></textarea></div>';
    $out .= '<button class="btn btn-primary" type="submit">Send via WhatsApp →</button><p class="form-note" id="form-note"></p></form></div></section></div>';
    return $out;
}

function r_logo(array $site): string
{
    $mark = trim((string)($site['logo_mark'] ?? 'R'));
    $markHtml = preg_match('#\.(svg|png|webp|jpe?g)$#i', $mark) ? r_img($mark, $site['logo_text'] ?? '', '', false) : e($mark);
    return '<a class="logo" href="#home" data-route="home" aria-label="Home"><span class="logo-mark">' . $markHtml . '</span><span class="logo-text"><b>' . e($site['logo_text'] ?? '') . '</b><small>' . e($site['logo_sub'] ?? '') . '</small></span></a>';
}

function r_nav_links(array $pages, bool $hasOffers): string
{
    $links = [['home', 'Home'], ['portfolio', 'Portfolio']];
    if ($hasOffers) {
        $links[] = ['offers', 'Offers'];
    }
    $links[] = ['about', 'About'];
    foreach ($pages as $p) {
        if (!empty($p['show_in_nav']) && ($p['placement'] ?? 'standalone') === 'standalone') {
            $links[] = [$p['slug'], $p['nav_label'] ?: $p['title']];
        }
    }
    $links[] = ['contact', 'Contact'];
    $out = '';
    foreach ($links as [$r, $l]) {
        $out .= '<a href="#' . e($r) . '" data-route="' . e($r) . '">' . e($l) . '</a>';
    }
    return $out;
}

/* ---------- full document ---------- */

function render_site(array $opts = []): string
{
    $c        = content_all();
    $tpl      = template_merge($opts['template'] ?? active_template());
    $offers   = visible_offers();
    $pages    = visible_pages();
    $preview  = $opts['preview_page'] ?? null;
    if ($preview) {
        $pages = array_values(array_filter($pages, fn($p) => $p['id'] !== $preview['id']));
        $pages[] = $preview;
    }
    $site     = $c['site'] ?? [];
    $contact  = $c['contact'] ?? [];
    $footer   = $c['footer'] ?? [];
    $social   = array_filter($c['social']['links'] ?? [], fn($l) => trim($l['url'] ?? '') !== '');
    $fonts    = google_fonts_url($tpl);
    $title    = $site['name'] ?? 'Website';
    $wa       = !empty($contact['whatsapp']) ? r_wa_url($contact) : '';
    $hasOffersPage = (bool)$offers;
    $homePages = array_filter($pages, fn($p) => ($p['placement'] ?? 'standalone') === 'home');
    $standalone = array_filter($pages, fn($p) => ($p['placement'] ?? 'standalone') === 'standalone');
    $showIntro = !empty($tpl['intro_enabled']) && trim((string)$tpl['intro_word']) !== '';
    $base = $opts['base'] ?? '';

    ob_start();
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($site['description'] ?? '') ?>">
<meta name="theme-color" content="<?= e($tpl['colors']['bg']) ?>">
<meta property="og:type" content="website">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($site['description'] ?? '') ?>">
<?php if (!empty($site['og_image'])): ?><meta property="og:image" content="<?= e($base . $site['og_image']) ?>">
<?php endif; ?>
<link rel="icon" href="<?= e($base) ?>assets/favicon.svg?v=5" type="image/svg+xml">
<?php if ($fonts): ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="<?= e($fonts) ?>" rel="stylesheet">
<?php endif; ?>
<link rel="stylesheet" href="<?= e($base) ?>assets/theme.css?v=<?= e((string)@filemtime(CMS_ROOT . '/assets/theme.css')) ?>">
<style><?= template_css($tpl) ?></style>
<?php if (!empty($opts['preview_label'])): ?>
<style>.preview-bar{position:fixed;left:0;right:0;bottom:0;z-index:2000;background:#111;color:#f0d9a4;font:600 13px/1 system-ui,sans-serif;letter-spacing:.08em;text-transform:uppercase;padding:12px 18px;text-align:center;border-top:1px solid #d4b06a}</style>
<?php endif; ?>
</head>
<body class="<?= $showIntro ? 'locked' : '' ?>">
<?php if ($showIntro): ?>
<div id="intro" aria-hidden="true"><div class="intro-box"><div class="intro-line top"></div><div class="intro-word" id="intro-word"><?= e($tpl['intro_word']) ?></div><div class="intro-line bottom"></div><?php if (trim((string)$tpl['intro_sub']) !== ''): ?><div class="intro-sub"><?= e($tpl['intro_sub']) ?></div><?php endif; ?></div></div>
<?php endif; ?>
<header class="header" id="header"><div class="wrap"><?= r_logo($site) ?><nav class="nav" aria-label="Main"><?= r_nav_links($standalone, $hasOffersPage) ?></nav><button class="burger" id="burger" aria-label="Open menu" aria-expanded="false"><span></span><span></span><span></span></button></div></header>
<div class="menu" id="menu"><?= r_nav_links($standalone, $hasOffersPage) ?><span class="menu-foot"><?= e(($contact['phone1'] ?? '') . (!empty($contact['phone1']) ? ' · ' : '') . 'Karachi') ?></span></div>
<main>
<div class="page" id="page-home">
<?= r_hero($c, $tpl) ?>
<?= !empty($tpl['show_marquee']) ? r_marquee($c) : '' ?>
<?= !empty($tpl['show_services']) ? r_services($c) : '' ?>
<?= !empty($tpl['show_offers']) ? r_offers_section($c, $offers) : '' ?>
<?= !empty($tpl['show_before_after']) ? r_before_after($c) : '' ?>
<?= !empty($tpl['show_products']) ? r_products($c) : '' ?>
<?= !empty($tpl['show_process']) ? r_process($c) : '' ?>
<?= !empty($tpl['show_why']) ? r_why($c) : '' ?>
<?= !empty($tpl['show_testimonials']) ? r_testimonials($c) : '' ?>
<?php foreach ($homePages as $p) { echo r_page_sections($p); } ?>
<?= !empty($tpl['show_cta']) ? r_cta($c, $contact) : '' ?>
</div>
<?= r_about($c) ?>
<?= r_portfolio($c) ?>
<?= r_offers_page($c, $offers) ?>
<?= r_contact($c) ?>
<?php foreach ($standalone as $p): ?>
<div class="page" id="page-<?= e($p['slug']) ?>"><?= r_page_head($p['eyebrow'] ?? 'Page', $p['title'] ?? '', $p['title_em'] ?? '') ?><?= r_page_sections($p) ?></div>
<?php endforeach; ?>
</main>
<footer><div class="wrap"><div class="foot">
<div><?= r_logo($site) ?><p><?= e($footer['text'] ?? '') ?></p></div>
<div><h4>Pages</h4><ul>
<li><a href="#home" data-route="home">Home</a></li><li><a href="#portfolio" data-route="portfolio">Portfolio</a></li><?php if ($hasOffersPage): ?><li><a href="#offers" data-route="offers">Offers</a></li><?php endif; ?><li><a href="#about" data-route="about">About</a></li>
<?php foreach ($standalone as $p): ?><li><a href="#<?= e($p['slug']) ?>" data-route="<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></li><?php endforeach; ?>
<li><a href="#contact" data-route="contact">Contact</a></li></ul></div>
<div><h4>Follow</h4><ul>
<?php if ($social): foreach ($social as $l): ?><li><a href="<?= e($l['url']) ?>" target="_blank" rel="noopener"><?= e($l['label']) ?></a></li><?php endforeach; else: ?><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp</a></li><?php endif; ?>
</ul></div>
<div><h4>Contact</h4><ul>
<?php if (!empty($contact['phone1'])): ?><li><a href="tel:<?= e(r_tel($contact['phone1'])) ?>"><?= e($contact['phone1']) ?></a></li><?php endif; ?>
<?php if (!empty($contact['phone2'])): ?><li><a href="tel:<?= e(r_tel($contact['phone2'])) ?>"><?= e($contact['phone2']) ?></a></li><?php endif; ?>
<?php if (!empty($contact['email'])): ?><li><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></li><?php endif; ?>
<?php if ($wa): ?><li><a href="<?= e($wa) ?>" target="_blank" rel="noopener">WhatsApp us</a></li><?php endif; ?>
<li><a href="#contact" data-route="contact"><?= e($contact['area'] ?? 'Karachi') ?></a></li></ul></div>
</div><div class="copy"><span><?= e($footer['copyright'] ?? '') ?></span><span><?= e($footer['right'] ?? '') ?></span></div></div></footer>
<?php if ($wa): ?>
<a class="wa" href="<?= e($wa) ?>" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"><svg viewBox="0 0 24 24"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.1-.2.3-.8 1-.9 1.2-.2.2-.3.2-.6.1-.3-.1-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.4-.5c.2-.2.2-.3.3-.5.1-.2 0-.4 0-.5l-.9-2.2c-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1 1-1 2.5s1.1 2.9 1.2 3.1c.1.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3M12 21.8a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4A9.9 9.9 0 1 1 12 21.8m8.4-18.3A11.8 11.8 0 0 0 .2 17.8L0 24l6.3-1.7A11.9 11.9 0 0 0 24 11.9c0-3.2-1.2-6.2-3.5-8.4z"/></svg></a>
<?php endif; ?>
<div class="lightbox" id="lightbox" role="dialog" aria-modal="true" aria-label="Image viewer"><img src="" alt=""><p id="lb-cap"></p></div>
<?php if (!empty($opts['preview_label'])): ?><div class="preview-bar"><?= e($opts['preview_label']) ?></div><?php endif; ?>
<script>
(function(){
  "use strict";
  var $=function(s,r){return (r||document).querySelector(s)}, $$=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))};
  var reduce=window.matchMedia("(prefers-reduced-motion: reduce)").matches;
  var WA=<?= json_encode(preg_replace('/\D/', '', (string)($contact['whatsapp'] ?? ''))) ?>;
  var SITE=<?= json_encode($title) ?>;

  var intro=$("#intro"), revealObs=null;
  function startSite(){
    document.body.classList.remove("locked");
    revealObs=new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add("in"); revealObs.unobserve(e.target);} });
    },{threshold:.12,rootMargin:"0px 0px -6% 0px"});
    $$(".reveal").forEach(function(el){revealObs.observe(el)});
  }
  if(intro){
    var word=$("#intro-word");
    word.innerHTML=word.textContent.split("").map(function(ch,i){return '<span style="animation-delay:'+(0.4+i*0.15)+'s,'+(1.6+i*0.15)+'s">'+(ch===" "?"&nbsp;":ch)+'</span>'}).join("");
    if(reduce){ intro.remove(); startSite(); }
    else{
      setTimeout(function(){ intro.classList.add("out"); },3700);
      setTimeout(function(){ intro.remove(); startSite(); },5300);
    }
  } else { startSite(); }

  var header=$("#header");
  function onScroll(){ header.classList.toggle("scrolled",window.scrollY>40); }
  window.addEventListener("scroll",onScroll,{passive:true}); onScroll();
  var burger=$("#burger"), menu=$("#menu");
  function setMenu(open){ burger.classList.toggle("open",open); menu.classList.toggle("open",open); burger.setAttribute("aria-expanded",open); if(!document.getElementById("intro")) document.body.classList.toggle("locked",open); }
  burger.addEventListener("click",function(){ setMenu(!menu.classList.contains("open")); });

  var routes=$$(".page").map(function(p){return p.id.replace(/^page-/,"")});
  function parse(){
    var h=(location.hash||"#home").slice(1), q={}, parts=h.split("?"); var r=parts[0]||"home";
    if(parts[1]) parts[1].split("&").forEach(function(kv){var p=kv.split("="); q[decodeURIComponent(p[0])]=decodeURIComponent(p[1]||"")});
    if(routes.indexOf(r)<0) r="home";
    return {route:r,q:q};
  }
  function go(){
    var p=parse();
    $$(".page").forEach(function(pg){ pg.classList.toggle("active",pg.id==="page-"+p.route); });
    $$("[data-route]").forEach(function(a){ a.classList.toggle("active",a.dataset.route===p.route && a.closest(".nav,.menu")!==null); });
    setMenu(false);
    window.scrollTo({top:0,behavior:"instant"});
    if(p.route==="portfolio") setFilter(p.q.cat||"all");
    if(revealObs){ $$(".page.active .reveal:not(.in)").forEach(function(el){ revealObs.observe(el); }); }
    var label=p.route==="home"?"":(($(".nav a.active")||{}).textContent||p.route)+" — ";
    document.title=label+SITE;
  }
  window.addEventListener("hashchange",go);
  $$("[data-route]").forEach(function(a){ a.addEventListener("click",function(){ if(location.hash===a.getAttribute("href")) go(); }); });

  var mq=$("#marquee"); if(mq){ mq.innerHTML+=mq.innerHTML; }

  var ba=$("#ba");
  if(ba){
    var range=$("input",ba);
    function setPos(v){ ba.style.setProperty("--pos",v+"%"); }
    range.addEventListener("input",function(){ setPos(range.value); });
    ba.addEventListener("pointermove",function(e){ if(e.buttons||e.pointerType==="touch"){ var r=ba.getBoundingClientRect(); var v=Math.max(0,Math.min(100,(e.clientX-r.left)/r.width*100)); range.value=v; setPos(v);} });
  }

  var filters=$("#filters"), items=$$("#masonry .item");
  function setFilter(f){
    if(!filters) return;
    if(!$$("button",filters).some(function(b){return b.dataset.f===f})) f="all";
    $$("button",filters).forEach(function(b){ b.classList.toggle("active",b.dataset.f===f); });
    items.forEach(function(it){ var show=f==="all"||it.dataset.cat===f; it.classList.toggle("hide",!show); if(show&&revealObs){ it.classList.add("in"); } });
  }
  if(filters){ filters.addEventListener("click",function(e){ var b=e.target.closest("button"); if(!b) return; setFilter(b.dataset.f); history.replaceState(null,"","#portfolio"+(b.dataset.f==="all"?"":"?cat="+b.dataset.f)); }); }
  var lb=$("#lightbox"), lbImg=$("img",lb), lbCap=$("#lb-cap");
  function openLb(src,alt,cap){ lbImg.src=src; lbImg.alt=alt; lbCap.textContent=cap||""; lb.classList.add("open"); document.body.classList.add("locked"); }
  items.forEach(function(it){ it.addEventListener("click",function(){ var im=$("img",it); openLb(im.src,im.alt,$("b",it).textContent); }); });
  $$("img[data-lb]").forEach(function(im){ im.addEventListener("click",function(){ openLb(im.src,im.alt,""); }); });
  function closeLb(){ lb.classList.remove("open"); if(!document.getElementById("intro")) document.body.classList.remove("locked"); }
  lb.addEventListener("click",closeLb);
  document.addEventListener("keydown",function(e){ if(e.key==="Escape"){ closeLb(); setMenu(false);} });

  var form=$("#form"), note=$("#form-note");
  if(form){
    form.addEventListener("submit",function(e){
      e.preventDefault();
      var name=$("#f-name").value.trim(), phone=$("#f-phone").value.trim(), service=$("#f-service").value, msg=$("#f-msg").value.trim();
      if(!name||!phone||!service||!msg){ note.textContent="Please fill in your name, phone, service and message."; note.style.color="#e0a070"; return; }
      var text="Assalam-o-Alaikum, I would like a quotation.\nName: "+name+"\nPhone: "+phone+"\nService: "+service+"\nDetails: "+msg;
      window.open("https://wa.me/"+WA+"?text="+encodeURIComponent(text),"_blank","noopener");
      note.textContent="WhatsApp opened with your message — press send there."; note.style.color="";
    });
  }
  go();
})();
</script>
<script type="application/ld+json"><?= json_encode([
    '@context' => 'https://schema.org', '@type' => 'HomeAndConstructionBusiness',
    'name' => $site['logo_text'] ?? $title, 'url' => 'https://amroyalinteriors.com/',
    'telephone' => array_values(array_filter([r_tel($contact['phone1'] ?? ''), r_tel($contact['phone2'] ?? '')])),
    'email' => $contact['email'] ?? '', 'address' => ['@type' => 'PostalAddress', 'streetAddress' => $contact['address'] ?? '', 'addressLocality' => 'Karachi', 'addressRegion' => 'Sindh', 'addressCountry' => 'PK'],
    'areaServed' => $contact['area'] ?? '', 'openingHours' => 'Mo-Su 07:00-23:30', 'description' => $site['description'] ?? '',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</body>
</html>
<?php
    return (string)ob_get_clean();
}
