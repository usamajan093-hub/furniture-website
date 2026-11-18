<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$offers   = col_all('offers');
$states   = array_count_values(array_map('offer_state', $offers));
$tpls     = col_all('templates');
$active   = active_template();
$media    = col_all('media');
$pages    = col_all('pages');
$activity = array_slice(store_read('activity', []), 0, 12);
$upcoming = array_filter($offers, fn($o) => in_array(offer_state($o), ['active', 'scheduled'], true));
usort($upcoming, fn($a, $b) => strcmp($a['end_date'] ?? '9999', $b['end_date'] ?? '9999'));

admin_page_start('Dashboard', 'index', '<a class="btn btn-primary btn-sm" href="offers.php?new=1">' . admin_icon('plus') . ' New offer</a>');
?>
<div class="cards">
  <div class="stat"><div class="stat-ico"><?= admin_icon('offers') ?></div><div><b><?= (int)($states['active'] ?? 0) ?></b><span>Live offers · <?= count($offers) ?> total, <?= (int)($states['scheduled'] ?? 0) ?> scheduled, <?= (int)($states['expired'] ?? 0) ?> expired</span></div></div>
  <div class="stat"><div class="stat-ico"><?= admin_icon('templates') ?></div><div><b><?= count($tpls) ?></b><span>Templates · active: <?= e($active['name']) ?></span></div></div>
  <div class="stat"><div class="stat-ico"><?= admin_icon('media') ?></div><div><b><?= count($media) ?></b><span>Images in the media library</span></div></div>
  <div class="stat"><div class="stat-ico"><?= admin_icon('pages') ?></div><div><b><?= count(array_filter($pages, fn($p) => ($p['status'] ?? '') === 'published')) ?></b><span>Published pages · <?= count($pages) ?> total</span></div></div>
</div>
<div class="two-col">
  <div class="card">
    <div class="card-head"><h2>Recent activity</h2></div>
    <div class="table-wrap"><table>
      <thead><tr><th>When</th><th>Who</th><th>Action</th></tr></thead>
      <tbody>
      <?php if (!$activity): ?><tr><td colspan="3" class="empty">Nothing yet.</td></tr><?php endif; ?>
      <?php foreach ($activity as $a): ?>
        <tr><td class="muted small"><?= e($a['at'] ?? '') ?></td><td><?= e($a['user'] ?? '') ?></td><td><?= e($a['action'] ?? '') ?><?= !empty($a['label']) ? ' <b>' . e($a['label']) . '</b>' : '' ?></td></tr>
      <?php endforeach; ?>
      </tbody></table></div>
  </div>
  <div style="display:grid;gap:22px">
    <div class="card">
      <div class="card-head"><h2>Quick actions</h2></div>
      <div class="card-body quick">
        <a href="offers.php?new=1"><?= admin_icon('plus') ?> Create a new offer</a>
        <a href="media.php"><?= admin_icon('upload') ?> Upload images</a>
        <a href="content.php?s=hero"><?= admin_icon('edit') ?> Edit the homepage banner</a>
        <a href="pages.php?new=1"><?= admin_icon('pages') ?> Build a new page</a>
        <a href="templates.php"><?= admin_icon('templates') ?> Switch template</a>
        <a href="settings.php"><?= admin_icon('settings') ?> Publish static copy (GitHub Pages)</a>
      </div>
    </div>
    <div class="card">
      <div class="card-head"><h2>Offers running or scheduled</h2><a class="btn btn-ghost btn-sm" href="offers.php">All offers</a></div>
      <div class="table-wrap"><table>
        <tbody>
        <?php if (!$upcoming): ?><tr><td class="empty">No live or scheduled offers.</td></tr><?php endif; ?>
        <?php foreach (array_slice($upcoming, 0, 6) as $o): ?>
          <tr><td><a href="offers.php?edit=<?= e($o['id']) ?>"><?= e($o['title']) ?></a><br><span class="muted small"><?= !empty($o['end_date']) ? 'till ' . e(nice_date($o['end_date'])) : 'no end date' ?></span></td><td style="text-align:right"><?= state_badge(offer_state($o)) ?></td></tr>
        <?php endforeach; ?>
        </tbody></table></div>
    </div>
  </div>
</div>
<?php admin_page_end();
