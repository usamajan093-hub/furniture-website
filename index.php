<?php
declare(strict_types=1);
require __DIR__ . '/cms/bootstrap.php';

if (!cms_installed()) {
    if (is_file(__DIR__ . '/install.php')) {
        redirect('install.php');
    }
    http_response_code(503);
    die('The site has not been set up yet. Run install.php.');
}

$opts = [];
// Admin-only previews of a template or a draft page.
if (isset($_GET['preview_template']) || isset($_GET['preview_page'])) {
    session_start_secure();
    if (is_logged_in()) {
        if (!empty($_GET['preview_template'])) {
            $t = col_find('templates', (string)$_GET['preview_template']);
            if ($t) {
                $opts['template'] = $t;
                $opts['preview_label'] = 'Template preview: ' . ($t['name'] ?? '');
            }
        }
        if (!empty($_GET['preview_page'])) {
            $p = col_find('pages', (string)$_GET['preview_page']);
            if ($p) {
                $opts['preview_page'] = $p;
                $opts['preview_label'] = 'Page preview: ' . ($p['title'] ?? '') . ' (' . ($p['status'] ?? 'draft') . ')';
            }
        }
    }
}

header('Content-Type: text/html; charset=utf-8');
echo render_site($opts);
