<?php
declare(strict_types=1);
defined('CMS') or die('No direct access');

function admin_icon(string $name): string
{
    $icons = [
        'dashboard' => '<rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/>',
        'offers'    => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L3 13V3h10l7.6 7.6a2 2 0 0 1 0 2.8z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'media'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/>',
        'content'   => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/>',
        'templates' => '<path d="M12 2 2 7l10 5 10-5-10-5z"/><path d="m2 17 10 5 10-5M2 12l10 5 10-5"/>',
        'pages'     => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
        'settings'  => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1.1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1.1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H9a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V9a1.7 1.7 0 0 0 1.5 1H21a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
        'eye'       => '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>',
        'logout'    => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'upload'    => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12"/>',
        'search'    => '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
        'trash'     => '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
        'edit'      => '<path d="M12 20h9M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'check'     => '<path d="M20 6 9 17l-5-5"/>',
        'copy'      => '<rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>',
        'menu'      => '<path d="M3 6h18M3 12h18M3 18h18"/>',
        'up'        => '<path d="m18 15-6-6-6 6"/>',
        'down'      => '<path d="m6 9 6 6 6-6"/>',
        'x'         => '<path d="M18 6 6 18M6 6l12 12"/>',
        'grip'      => '<circle cx="9" cy="6" r="1.2"/><circle cx="15" cy="6" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="9" cy="18" r="1.2"/><circle cx="15" cy="18" r="1.2"/>',
        'star'      => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
    ];
    return '<svg class="ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ($icons[$name] ?? $icons['dashboard']) . '</svg>';
}

function admin_nav(): array
{
    return [
        'index'     => ['Dashboard', 'dashboard'],
        'offers'    => ['Offers', 'offers'],
        'media'     => ['Media Library', 'media'],
        'content'   => ['Website Content', 'content'],
        'templates' => ['Templates', 'templates'],
        'pages'     => ['Page Builder', 'pages'],
        'settings'  => ['Settings', 'settings'],
    ];
}

function admin_page_start(string $title, string $active, string $actions = ''): void
{
    $user = $_SESSION['user'] ?? '';
    ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · Admin</title>
<meta name="csrf" content="<?= e(csrf_token()) ?>">
<link rel="icon" href="../assets/favicon.svg" type="image/svg+xml">
<link rel="stylesheet" href="assets/admin.css?v=<?= e((string)@filemtime(__DIR__ . '/../assets/admin.css')) ?>">
</head>
<body>
<div class="shell">
<aside class="sidebar" id="sidebar">
  <a class="brand" href="index.php"><span class="brand-mark">R</span><span><b>Royal Interiors</b><small>Admin panel</small></span></a>
  <nav class="side-nav">
<?php foreach (admin_nav() as $file => [$label, $icon]): ?>
    <a href="<?= e($file) ?>.php" class="<?= $active === $file ? 'active' : '' ?>"><?= admin_icon($icon) ?><span><?= e($label) ?></span></a>
<?php endforeach; ?>
  </nav>
  <div class="side-foot">
    <a href="../index.php" target="_blank" rel="noopener"><?= admin_icon('eye') ?><span>View website</span></a>
    <a href="logout.php"><?= admin_icon('logout') ?><span>Log out</span></a>
  </div>
</aside>
<div class="main">
  <header class="topbar">
    <button class="icon-btn only-mobile" id="side-toggle" aria-label="Menu"><?= admin_icon('menu') ?></button>
    <h1><?= e($title) ?></h1>
    <div class="topbar-right">
      <?= $actions ?>
      <a class="btn btn-light btn-sm" href="../index.php" target="_blank" rel="noopener"><?= admin_icon('eye') ?> View site</a>
      <span class="user-chip"><?= e(strtoupper(substr($user, 0, 1))) ?><b><?= e($user) ?></b></span>
    </div>
  </header>
  <div class="content">
<?php foreach (take_flash() as $f): ?>
    <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach;
}

function admin_page_end(): void
{
    ?>
  </div>
</div>
</div>
<div class="modal" id="media-picker" aria-hidden="true"><div class="modal-box modal-wide">
  <div class="modal-head"><h2>Media library</h2><button class="icon-btn" data-modal-close aria-label="Close"><?= admin_icon('x') ?></button></div>
  <div class="picker-tools">
    <label class="search"><?= admin_icon('search') ?><input type="search" id="picker-q" placeholder="Search images…"></label>
    <select id="picker-cat"><option value="">All categories</option></select>
    <label class="btn btn-sm btn-light upload-btn"><?= admin_icon('upload') ?> Upload<input type="file" id="picker-upload" accept="image/*" multiple hidden></label>
  </div>
  <div class="picker-grid" id="picker-grid"></div>
  <p class="muted small" id="picker-msg"></p>
</div></div>
<div class="toast" id="toast"></div>
<script src="assets/admin.js?v=<?= e((string)@filemtime(__DIR__ . '/../assets/admin.js')) ?>"></script>
</body></html>
<?php
}

/** Image chooser bound to a text input. */
function img_field(string $name, string $value, string $label = '', string $id = ''): string
{
    $id = $id ?: 'f' . substr(md5($name), 0, 8);
    $h = '<div class="img-field">';
    if ($label !== '') {
        $h .= '<label for="' . e($id) . '">' . e($label) . '</label>';
    }
    $h .= '<div class="img-field-row"><div class="img-prev">' . ($value !== '' ? '<img src="../' . e($value) . '" alt="">' : '') . '</div>';
    $h .= '<input type="text" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '" placeholder="Choose from media library or paste a path">';
    $h .= '<button type="button" class="btn btn-sm btn-light" data-pick>Choose</button><button type="button" class="btn btn-sm btn-ghost" data-clear>Clear</button></div></div>';
    return $h;
}

function state_badge(string $state): string
{
    $map = ['active' => 'ok', 'published' => 'ok', 'scheduled' => 'info', 'expired' => 'warn', 'draft' => 'muted'];
    return '<span class="badge badge-' . e($map[$state] ?? 'muted') . '">' . e(ucfirst($state)) . '</span>';
}
