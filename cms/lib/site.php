<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

/* ---------- templates ---------- */

function template_defaults(): array
{
    return [
        'name'          => 'Dark Luxury',
        'description'   => '',
        'active'        => false,
        'colors'        => [
            'bg' => '#0a0807', 'surface' => '#14100d', 'surface2' => '#1d1712',
            'gold' => '#d4b06a', 'gold_light' => '#f0d9a4', 'gold_dark' => '#b88a3e', 'gold_pale' => '#f6e3b0',
            'heading' => '#ffffff', 'text' => '#fffaf0', 'muted' => '#c9bfad',
            'on_gold' => '#0a0807', 'intro_bg' => '#000000', 'marquee_bg' => '#000000',
            'footer_bg' => '#070605', 'footer_text' => '#c9bfad',
        ],
        'border_alpha'  => '0.22',
        'font_heading'  => 'Playfair Display',
        'font_body'     => 'Inter',
        'button_style'  => 'square',   // square | rounded | pill
        'intro_enabled' => true,
        'intro_word'    => 'ROYAL',
        'intro_sub'     => 'Interiors',
        'hero_layout'   => 'left',     // left | center
        'hero_image'    => 'assets/images/hero-doors-collage.jpg',
        'hero_motion'   => true,
        'show_marquee'  => true,
        'show_services' => true,
        'show_offers'   => true,
        'show_before_after' => true,
        'show_products' => true,
        'show_process'  => true,
        'show_why'      => true,
        'show_testimonials' => true,
        'show_cta'      => true,
        'section_padding' => 110,
    ];
}

function template_merge(?array $t): array
{
    $d = template_defaults();
    if (!$t) {
        return $d;
    }
    $t['colors'] = array_merge($d['colors'], $t['colors'] ?? []);
    return array_merge($d, $t);
}

function active_template(): array
{
    $all = col_all('templates');
    foreach ($all as $t) {
        if (!empty($t['active'])) {
            return template_merge($t);
        }
    }
    return template_merge($all[0] ?? null);
}

function font_clean(string $f): string
{
    return trim(preg_replace('/[^a-zA-Z0-9 ]/', '', $f) ?? '');
}

function google_fonts_url(array $tpl): string
{
    $fams = [];
    foreach (array_unique([$tpl['font_heading'], $tpl['font_body']]) as $f) {
        $f = font_clean((string)$f);
        if ($f === '') {
            continue;
        }
        $fams[] = 'family=' . str_replace(' ', '+', $f) . ':ital,wght@0,400;0,500;0,600;0,700;1,500;1,600';
    }
    return $fams ? 'https://fonts.googleapis.com/css2?' . implode('&', $fams) . '&display=swap' : '';
}

function hex_to_rgb(string $hex): array
{
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }
    if (!preg_match('/^[0-9a-f]{6}$/i', $hex)) {
        return [212, 176, 106];
    }
    return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
}

/** CSS custom properties for a template. */
function template_css(array $tpl): string
{
    $c = $tpl['colors'];
    [$r, $g, $b] = hex_to_rgb($c['gold']);
    [$br, $bg, $bb] = hex_to_rgb($c['bg']);
    $radius = ['square' => '0', 'rounded' => '6px', 'pill' => '999px'][$tpl['button_style']] ?? '0';
    $radiusLg = ['square' => '0', 'rounded' => '10px', 'pill' => '18px'][$tpl['button_style']] ?? '0';
    $vars = [
        '--bg' => $c['bg'], '--surface' => $c['surface'], '--surface-2' => $c['surface2'],
        '--gold' => $c['gold'], '--gold-light' => $c['gold_light'], '--gold-dark' => $c['gold_dark'], '--gold-pale' => $c['gold_pale'],
        '--heading' => $c['heading'], '--text' => $c['text'], '--muted' => $c['muted'], '--on-gold' => $c['on_gold'],
        '--intro-bg' => $c['intro_bg'], '--marquee-bg' => $c['marquee_bg'],
        '--footer-bg' => $c['footer_bg'], '--footer-text' => $c['footer_text'],
        '--border' => "rgba($r,$g,$b,{$tpl['border_alpha']})",
        '--gold-rgb' => "$r,$g,$b", '--bg-rgb' => "$br,$bg,$bb",
        '--grad' => "linear-gradient(135deg,{$c['gold_pale']} 0%,{$c['gold']} 50%,{$c['gold_dark']} 100%)",
        '--serif' => '"' . font_clean((string)$tpl['font_heading']) . '",Georgia,serif',
        '--sans' => '"' . font_clean((string)$tpl['font_body']) . '",system-ui,sans-serif',
        '--radius' => $radius, '--radius-lg' => $radiusLg,
        '--sec' => (int)$tpl['section_padding'] . 'px',
    ];
    $out = ':root{';
    foreach ($vars as $k => $v) {
        $out .= $k . ':' . $v . ';';
    }
    return $out . '}';
}

/* ---------- offers ---------- */

function offer_state(array $o): string
{
    if (($o['status'] ?? 'draft') !== 'published') {
        return 'draft';
    }
    $now = time();
    if (!empty($o['start_date']) && strtotime($o['start_date']) > $now) {
        return 'scheduled';
    }
    if (!empty($o['end_date']) && strtotime($o['end_date'] . ' 23:59:59') < $now) {
        return 'expired';
    }
    return 'active';
}

function visible_offers(): array
{
    return array_values(array_filter(col_all('offers'), fn($o) => offer_state($o) === 'active'));
}

function offer_badge(array $o): string
{
    $type = $o['discount_type'] ?? 'none';
    $v = (float)($o['discount_value'] ?? 0);
    if ($type === 'percent' && $v > 0) {
        return rtrim(rtrim(number_format($v, 1), '0'), '.') . '% OFF';
    }
    if ($type === 'fixed' && $v > 0) {
        return 'Save ' . money($v);
    }
    if (!empty($o['price_original']) && !empty($o['price_discounted']) && $o['price_original'] > $o['price_discounted']) {
        $pct = round((1 - $o['price_discounted'] / $o['price_original']) * 100);
        return $pct > 0 ? $pct . '% OFF' : 'OFFER';
    }
    return 'OFFER';
}

function offer_cta(array $o, array $content): array
{
    $type = $o['cta_type'] ?? 'contact';
    $label = trim((string)($o['cta_label'] ?? ''));
    $url = trim((string)($o['cta_url'] ?? ''));
    $wa = $content['contact']['whatsapp'] ?? '';
    switch ($type) {
        case 'shop':
            return ['label' => $label ?: 'Shop Now', 'url' => $url ?: '#contact'];
        case 'details':
            return ['label' => $label ?: 'View Details', 'url' => $url ?: '#offers'];
        case 'whatsapp':
            $msg = 'Assalam-o-Alaikum, I am interested in your offer: ' . ($o['title'] ?? '');
            return ['label' => $label ?: 'WhatsApp Us', 'url' => 'https://wa.me/' . $wa . '?text=' . rawurlencode($msg)];
        default:
            return ['label' => $label ?: 'Contact Us', 'url' => $url ?: '#contact'];
    }
}

/* ---------- pages ---------- */

function visible_pages(): array
{
    $pages = array_filter(col_all('pages'), fn($p) => ($p['status'] ?? 'draft') === 'published');
    usort($pages, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
    return array_values($pages);
}

function page_section_types(): array
{
    return [
        'banner'  => ['label' => 'Banner (image with heading)', 'hint' => 'Full-width image, heading, text and buttons on top'],
        'text'    => ['label' => 'Heading & text', 'hint' => 'Centered heading with a paragraph'],
        'split'   => ['label' => 'Image + text', 'hint' => 'Photo on one side, heading and text on the other'],
        'gallery' => ['label' => 'Image grid', 'hint' => 'Up to 12 photos in a grid, opens in the lightbox'],
        'cards'   => ['label' => 'Feature cards', 'hint' => 'Three bordered boxes with title and text'],
        'cta'     => ['label' => 'Call to action', 'hint' => 'Band with heading, text and buttons'],
    ];
}

function content_all(): array
{
    return store_read('content', []);
}
