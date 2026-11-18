<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

/**
 * Describes every editable content section. The admin "Content" screen builds its
 * forms from this; the renderer reads the same keys. Field types:
 *   text, textarea, image, url, list (repeatable rows of sub-fields), strings (repeatable single text).
 */
function content_schema(): array
{
    return [
        'site' => ['label' => 'Site identity & header', 'icon' => 'home', 'fields' => [
            'name'        => ['type' => 'text', 'label' => 'Site name (browser title)'],
            'description' => ['type' => 'textarea', 'label' => 'Meta description (search engines)'],
            'logo_mark'   => ['type' => 'text', 'label' => 'Logo mark (1–2 letters in the gold square)'],
            'logo_text'   => ['type' => 'text', 'label' => 'Logo text'],
            'logo_sub'    => ['type' => 'text', 'label' => 'Logo subtitle'],
            'og_image'    => ['type' => 'image', 'label' => 'Share image (Open Graph)'],
        ]],
        'hero' => ['label' => 'Hero / homepage banner', 'icon' => 'image', 'fields' => [
            'eyebrow'   => ['type' => 'text', 'label' => 'Eyebrow line'],
            'title'     => ['type' => 'text', 'label' => 'Heading'],
            'title_em'  => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'lead'      => ['type' => 'textarea', 'label' => 'Paragraph'],
            'btn1_text' => ['type' => 'text', 'label' => 'Primary button text'],
            'btn1_link' => ['type' => 'url', 'label' => 'Primary button link'],
            'btn2_text' => ['type' => 'text', 'label' => 'Secondary button text'],
            'btn2_link' => ['type' => 'url', 'label' => 'Secondary button link'],
            'pills'     => ['type' => 'strings', 'label' => 'Glass pills'],
        ]],
        'marquee' => ['label' => 'Marquee strip', 'icon' => 'tag', 'fields' => [
            'items' => ['type' => 'strings', 'label' => 'Scrolling words'],
        ]],
        'services' => ['label' => 'Services (What We Do)', 'icon' => 'grid', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'title_after' => ['type' => 'text', 'label' => 'Heading — text after italic'],
            'lead'     => ['type' => 'textarea', 'label' => 'Paragraph'],
            'items'    => ['type' => 'list', 'label' => 'Service cards', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Text'],
                'image' => ['type' => 'image', 'label' => 'Image'],
                'link'  => ['type' => 'url', 'label' => 'Link'],
            ]],
        ]],
        'before_after' => ['label' => 'Before & After', 'icon' => 'compare', 'fields' => [
            'eyebrow'      => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'        => ['type' => 'text', 'label' => 'Heading'],
            'title_em'     => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'text'         => ['type' => 'textarea', 'label' => 'Paragraph'],
            'chips'        => ['type' => 'strings', 'label' => 'Finish chips'],
            'before_image' => ['type' => 'image', 'label' => 'Before photo'],
            'after_image'  => ['type' => 'image', 'label' => 'After photo'],
            'btn_text'     => ['type' => 'text', 'label' => 'Button text'],
            'btn_link'     => ['type' => 'url', 'label' => 'Button link'],
        ]],
        'offers_section' => ['label' => 'Offers section copy', 'icon' => 'tag', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'lead'     => ['type' => 'textarea', 'label' => 'Paragraph'],
            'empty'    => ['type' => 'text', 'label' => 'Text when no offers are running'],
        ]],
        'products' => ['label' => 'Products', 'icon' => 'box', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'items'    => ['type' => 'list', 'label' => 'Products (section is hidden while empty)', 'fields' => [
                'name'  => ['type' => 'text', 'label' => 'Name'],
                'price' => ['type' => 'text', 'label' => 'Price (e.g. Rs 45,000)'],
                'text'  => ['type' => 'textarea', 'label' => 'Description'],
                'image' => ['type' => 'image', 'label' => 'Image'],
                'link'  => ['type' => 'url', 'label' => 'Link'],
            ]],
        ]],
        'process' => ['label' => 'Our Process', 'icon' => 'steps', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'steps'    => ['type' => 'list', 'label' => 'Steps', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Text'],
            ]],
        ]],
        'why' => ['label' => 'Why choose us', 'icon' => 'star', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'items'    => ['type' => 'list', 'label' => 'Boxes', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Text'],
            ]],
        ]],
        'testimonials' => ['label' => 'Testimonials', 'icon' => 'quote', 'fields' => [
            'eyebrow'  => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'    => ['type' => 'text', 'label' => 'Heading'],
            'title_em' => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'items'    => ['type' => 'list', 'label' => 'Testimonials (section is hidden while empty)', 'fields' => [
                'name'  => ['type' => 'text', 'label' => 'Customer name'],
                'role'  => ['type' => 'text', 'label' => 'Area / project (e.g. DHA Phase 6 — bedroom set)'],
                'text'  => ['type' => 'textarea', 'label' => 'What they said'],
                'image' => ['type' => 'image', 'label' => 'Photo (optional)'],
            ]],
        ]],
        'cta' => ['label' => 'Call-to-action band', 'icon' => 'megaphone', 'fields' => [
            'eyebrow'   => ['type' => 'text', 'label' => 'Eyebrow'],
            'title'     => ['type' => 'text', 'label' => 'Heading'],
            'title_em'  => ['type' => 'text', 'label' => 'Heading — gold italic part'],
            'text'      => ['type' => 'textarea', 'label' => 'Paragraph'],
            'btn1_text' => ['type' => 'text', 'label' => 'Primary button text'],
            'btn1_link' => ['type' => 'url', 'label' => 'Primary button link'],
            'btn2_text' => ['type' => 'text', 'label' => 'Secondary button text'],
            'btn2_link' => ['type' => 'url', 'label' => 'Secondary button link'],
        ]],
        'about' => ['label' => 'About page', 'icon' => 'info', 'fields' => [
            'h1'           => ['type' => 'text', 'label' => 'Page heading'],
            'h1_em'        => ['type' => 'text', 'label' => 'Page heading — gold italic part'],
            'eyebrow'      => ['type' => 'text', 'label' => 'Section eyebrow'],
            'title'        => ['type' => 'text', 'label' => 'Section heading'],
            'title_em'     => ['type' => 'text', 'label' => 'Section heading — gold italic part'],
            'lead'         => ['type' => 'textarea', 'label' => 'Lead paragraph'],
            'text'         => ['type' => 'textarea', 'label' => 'Second paragraph'],
            'chips'        => ['type' => 'strings', 'label' => 'Chips'],
            'image'        => ['type' => 'image', 'label' => 'Framed photo'],
            'btn_text'     => ['type' => 'text', 'label' => 'Button text'],
            'btn_link'     => ['type' => 'url', 'label' => 'Button link'],
            'boxes_eyebrow'=> ['type' => 'text', 'label' => 'Boxes eyebrow'],
            'boxes_title'  => ['type' => 'text', 'label' => 'Boxes heading'],
            'boxes_title_em' => ['type' => 'text', 'label' => 'Boxes heading — gold italic part'],
            'boxes'        => ['type' => 'list', 'label' => 'Boxes', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'text'  => ['type' => 'textarea', 'label' => 'Text'],
            ]],
            'photo1'       => ['type' => 'image', 'label' => 'Bottom photo 1'],
            'photo2'       => ['type' => 'image', 'label' => 'Bottom photo 2'],
        ]],
        'portfolio' => ['label' => 'Portfolio page', 'icon' => 'images', 'fields' => [
            'h1'      => ['type' => 'text', 'label' => 'Page heading'],
            'h1_em'   => ['type' => 'text', 'label' => 'Page heading — gold italic part'],
            'filters' => ['type' => 'list', 'label' => 'Filters (key is used by the items below)', 'fields' => [
                'key'   => ['type' => 'text', 'label' => 'Key (letters only, e.g. polish)'],
                'label' => ['type' => 'text', 'label' => 'Label'],
            ]],
            'items'   => ['type' => 'list', 'label' => 'Projects', 'fields' => [
                'title' => ['type' => 'text', 'label' => 'Title'],
                'cat'   => ['type' => 'text', 'label' => 'Filter key'],
                'image' => ['type' => 'image', 'label' => 'Photo'],
            ]],
        ]],
        'contact' => ['label' => 'Contact information', 'icon' => 'phone', 'fields' => [
            'h1'          => ['type' => 'text', 'label' => 'Contact page heading'],
            'h1_em'       => ['type' => 'text', 'label' => 'Contact page heading — gold italic part'],
            'phone1'      => ['type' => 'text', 'label' => 'Phone 1 (as shown)'],
            'phone2'      => ['type' => 'text', 'label' => 'Phone 2 (as shown, optional)'],
            'whatsapp'    => ['type' => 'text', 'label' => 'WhatsApp number (digits with country code, e.g. 923098986264)'],
            'whatsapp_msg'=> ['type' => 'textarea', 'label' => 'Default WhatsApp message'],
            'email'       => ['type' => 'text', 'label' => 'Email'],
            'address'     => ['type' => 'textarea', 'label' => 'Address'],
            'area'        => ['type' => 'text', 'label' => 'Service area line'],
            'hours'       => ['type' => 'text', 'label' => 'Opening hours'],
            'form_title'  => ['type' => 'text', 'label' => 'Form title'],
            'form_intro'  => ['type' => 'text', 'label' => 'Form intro line'],
            'form_services' => ['type' => 'strings', 'label' => 'Service options in the form'],
        ]],
        'social' => ['label' => 'Social media links', 'icon' => 'share', 'fields' => [
            'links' => ['type' => 'list', 'label' => 'Links (shown in the footer)', 'fields' => [
                'label' => ['type' => 'text', 'label' => 'Label (Facebook, Instagram, TikTok, YouTube…)'],
                'url'   => ['type' => 'url', 'label' => 'URL'],
            ]],
        ]],
        'footer' => ['label' => 'Footer', 'icon' => 'layout', 'fields' => [
            'text'      => ['type' => 'textarea', 'label' => 'About text'],
            'copyright' => ['type' => 'text', 'label' => 'Copyright line'],
            'right'     => ['type' => 'text', 'label' => 'Right-side text'],
        ]],
    ];
}

/** Clean a submitted content section according to its schema. */
function schema_clean_section(array $fields, $input): array
{
    $out = [];
    $input = is_array($input) ? $input : [];
    foreach ($fields as $key => $f) {
        $v = $input[$key] ?? null;
        switch ($f['type']) {
            case 'strings':
                $out[$key] = array_values(array_filter(array_map('trim', as_list($v)), fn($s) => $s !== ''));
                break;
            case 'list':
                $rows = [];
                foreach (as_list($v) as $row) {
                    $clean = schema_clean_section($f['fields'], $row);
                    if (implode('', array_map(fn($x) => is_array($x) ? '' : (string)$x, $clean)) !== '') {
                        $rows[] = $clean;
                    }
                }
                $out[$key] = $rows;
                break;
            default:
                $out[$key] = is_string($v) ? trim($v) : '';
        }
    }
    return $out;
}
