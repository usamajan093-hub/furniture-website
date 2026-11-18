<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$categories = ['Polish Work', 'Furniture', 'Bedroom Sets', 'Doors & Windows', 'Deco & Interiors', 'Building Projects', 'Factory Contracts', 'Seasonal'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post_str('action');
    if ($act === 'delete') {
        $o = col_find('offers', post_str('id'));
        if ($o) {
            col_delete('offers', $o['id']);
            log_activity('deleted offer', 'offer', $o['title']);
            flash('success', 'Offer deleted.');
        }
        redirect('offers.php');
    }
    if ($act === 'save') {
        $id = post_str('id');
        $o  = $id !== '' ? (col_find('offers', $id) ?? []) : [];
        $title = post_str('title');
        if ($title === '') {
            flash('error', 'The offer needs a title.');
            redirect('offers.php?' . ($id ? 'edit=' . rawurlencode($id) : 'new=1'));
        }
        $o = array_merge($o, [
            'title'            => $title,
            'slug'             => slugify($title),
            'description'      => post_str('description'),
            'image'            => post_str('image'),
            'category'         => post_str('category'),
            'discount_type'    => in_array(post_str('discount_type'), ['percent', 'fixed', 'none'], true) ? post_str('discount_type') : 'none',
            'discount_value'   => post_float('discount_value'),
            'price_original'   => post_float('price_original'),
            'price_discounted' => post_float('price_discounted'),
            'start_date'       => post_str('start_date'),
            'end_date'         => post_str('end_date'),
            'status'           => post_str('status') === 'published' ? 'published' : 'draft',
            'cta_type'         => in_array(post_str('cta_type'), ['shop', 'contact', 'details', 'whatsapp'], true) ? post_str('cta_type') : 'contact',
            'cta_label'        => post_str('cta_label'),
            'cta_url'          => post_str('cta_url'),
        ]);
        // Work out the discounted price when only a discount is given.
        if ($o['price_discounted'] === null && $o['price_original'] !== null && $o['discount_value']) {
            $o['price_discounted'] = $o['discount_type'] === 'percent'
                ? round($o['price_original'] * (1 - $o['discount_value'] / 100), 2)
                : ($o['discount_type'] === 'fixed' ? max(0, $o['price_original'] - $o['discount_value']) : null);
        }
        if ($o['end_date'] !== '' && $o['start_date'] !== '' && $o['end_date'] < $o['start_date']) {
            flash('error', 'The expiry date is before the start date — check the dates.');
        }
        $o = col_save('offers', $o);
        log_activity($id ? 'updated offer' : 'created offer', 'offer', $o['title']);
        flash('success', 'Offer saved. It is ' . offer_state($o) . '.');
        redirect('offers.php');
    }
}

$editing = null;
if (isset($_GET['new'])) {
    $editing = ['status' => 'draft', 'discount_type' => 'percent', 'cta_type' => 'contact', 'start_date' => date('Y-m-d')];
} elseif (!empty($_GET['edit'])) {
    $editing = col_find('offers', (string)$_GET['edit']);
    if (!$editing) {
        flash('error', 'Offer not found.');
        redirect('offers.php');
    }
}

if ($editing !== null):
    $o = $editing;
    admin_page_start(empty($o['id']) ? 'New offer' : 'Edit offer', 'offers', '<a class="btn btn-light btn-sm" href="offers.php">← All offers</a>');
    ?>
<form method="post" class="card"><div class="card-body">
  <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($o['id'] ?? '') ?>">
  <div class="form-grid">
    <div class="f full"><label>Offer title</label><input type="text" name="title" value="<?= e($o['title'] ?? '') ?>" required placeholder="e.g. 20% off PU polish for 6+ doors"></div>
    <div class="f full"><label>Description</label><textarea name="description" placeholder="What is included, conditions, who it is for…"><?= e($o['description'] ?? '') ?></textarea></div>
    <div class="full"><?= img_field('image', $o['image'] ?? '', 'Offer image') ?></div>
    <div class="f"><label>Category</label><input type="text" name="category" value="<?= e($o['category'] ?? '') ?>" list="offer-cats" placeholder="Choose or type"><datalist id="offer-cats"><?php foreach ($categories as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
    <div class="f"><label>Status</label><select name="status"><option value="draft" <?= ($o['status'] ?? '') !== 'published' ? 'selected' : '' ?>>Draft (hidden)</option><option value="published" <?= ($o['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published (shown within the dates)</option></select></div>
    <div class="f"><label>Discount type</label><select name="discount_type"><option value="percent" <?= ($o['discount_type'] ?? '') === 'percent' ? 'selected' : '' ?>>Percentage</option><option value="fixed" <?= ($o['discount_type'] ?? '') === 'fixed' ? 'selected' : '' ?>>Fixed amount (Rs)</option><option value="none" <?= ($o['discount_type'] ?? '') === 'none' ? 'selected' : '' ?>>No discount badge</option></select></div>
    <div class="f"><label>Discount value</label><input type="number" step="0.01" min="0" name="discount_value" value="<?= e((string)($o['discount_value'] ?? '')) ?>" placeholder="20"></div>
    <div class="f"><label>Original price (Rs)</label><input type="number" step="0.01" min="0" name="price_original" value="<?= e((string)($o['price_original'] ?? '')) ?>" placeholder="optional"></div>
    <div class="f"><label>Discounted price (Rs)</label><input type="number" step="0.01" min="0" name="price_discounted" value="<?= e((string)($o['price_discounted'] ?? '')) ?>" placeholder="auto-calculated if left empty"></div>
    <div class="f"><label>Start date</label><input type="date" name="start_date" value="<?= e($o['start_date'] ?? '') ?>"><span class="hint">Leave empty to start immediately when published.</span></div>
    <div class="f"><label>Expiry date</label><input type="date" name="end_date" value="<?= e($o['end_date'] ?? '') ?>"><span class="hint">The offer disappears from the site after this day.</span></div>
    <div class="f"><label>Button type</label><select name="cta_type">
      <?php foreach (['contact' => 'Contact Us (goes to contact page)', 'whatsapp' => 'WhatsApp Us (opens chat about this offer)', 'shop' => 'Shop Now (custom link)', 'details' => 'View Details (custom link)'] as $k => $l): ?>
      <option value="<?= $k ?>" <?= ($o['cta_type'] ?? '') === $k ? 'selected' : '' ?>><?= e($l) ?></option>
      <?php endforeach; ?></select></div>
    <div class="f"><label>Button label (optional)</label><input type="text" name="cta_label" value="<?= e($o['cta_label'] ?? '') ?>" placeholder="Shop Now"></div>
    <div class="f full"><label>Button link (for Shop Now / View Details)</label><input type="text" name="cta_url" value="<?= e($o['cta_url'] ?? '') ?>" placeholder="https://… or #contact"></div>
  </div>
  <div class="form-foot">
    <?php if (!empty($o['id'])): ?><span class="muted small">Current state: <?= state_badge(offer_state($o)) ?></span><?php endif; ?>
    <span class="spacer"></span>
    <a class="btn btn-light" href="offers.php">Cancel</a>
    <button class="btn btn-primary" type="submit"><?= admin_icon('check') ?> Save offer</button>
  </div>
</div></form>
<?php admin_page_end(); exit; endif;

/* ---------- list ---------- */
$offers = col_all('offers');
$filter = (string)($_GET['state'] ?? '');
$q = strtolower((string)($_GET['q'] ?? ''));
$rows = array_filter($offers, function ($o) use ($filter, $q) {
    if ($filter !== '' && offer_state($o) !== $filter) {
        return false;
    }
    return $q === '' || str_contains(strtolower($o['title'] . ' ' . ($o['category'] ?? '')), $q);
});
admin_page_start('Offers', 'offers', '<a class="btn btn-primary btn-sm" href="offers.php?new=1">' . admin_icon('plus') . ' New offer</a>');
?>
<form class="toolbar" method="get">
  <label class="search"><?= admin_icon('search') ?><input type="search" name="q" value="<?= e((string)($_GET['q'] ?? '')) ?>" placeholder="Search offers…"></label>
  <select name="state" onchange="this.form.submit()">
    <option value="">All states</option>
    <?php foreach (['active' => 'Live', 'scheduled' => 'Scheduled', 'expired' => 'Expired', 'draft' => 'Draft'] as $k => $l): ?><option value="<?= $k ?>" <?= $filter === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?>
  </select>
  <button class="btn btn-light btn-sm" type="submit">Filter</button>
</form>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th></th><th>Offer</th><th>Discount</th><th>Prices</th><th>Dates</th><th>State</th><th></th></tr></thead>
  <tbody>
  <?php if (!$rows): ?><tr><td colspan="7" class="empty">No offers<?= $filter || $q ? ' match' : ' yet. Create the first one.' ?></td></tr><?php endif; ?>
  <?php foreach ($rows as $o): $st = offer_state($o); ?>
    <tr>
      <td><?php if (!empty($o['image'])): ?><img class="thumb" src="../<?= e($o['image']) ?>" alt=""><?php else: ?><div class="thumb"></div><?php endif; ?></td>
      <td><a href="offers.php?edit=<?= e($o['id']) ?>"><b><?= e($o['title']) ?></b></a><br><span class="muted small"><?= e($o['category'] ?? '') ?></span></td>
      <td><?= e(offer_badge($o)) ?></td>
      <td class="small"><?php if (($o['price_discounted'] ?? null) !== null): ?><b><?= e(money((float)$o['price_discounted'])) ?></b> <?php endif; ?><?php if (($o['price_original'] ?? null) !== null): ?><s class="muted"><?= e(money((float)$o['price_original'])) ?></s><?php endif; ?></td>
      <td class="small"><?= e(nice_date($o['start_date'] ?? '') ?: '—') ?> → <?= e(nice_date($o['end_date'] ?? '') ?: 'no expiry') ?></td>
      <td><?= state_badge($st) ?></td>
      <td><div class="row-actions">
        <button class="btn btn-light btn-sm" type="button" data-toggle="<?= e($o['id']) ?>" data-next="<?= $o['status'] === 'published' ? 'draft' : 'published' ?>"><?= $o['status'] === 'published' ? 'Unpublish' : 'Publish' ?></button>
        <a class="btn btn-light btn-sm" href="offers.php?edit=<?= e($o['id']) ?>"><?= admin_icon('edit') ?></a>
        <form method="post" data-confirm="Delete this offer?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($o['id']) ?>"><button class="btn btn-danger btn-sm" type="submit" aria-label="Delete"><?= admin_icon('trash') ?></button></form>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<script>
document.addEventListener("click",function(e){
  var b=e.target.closest("[data-toggle]"); if(!b) return;
  api("offer_status",{id:b.dataset.toggle,status:b.dataset.next}).then(function(r){ if(r.ok){ location.reload(); } else toast(r.error||"Error"); });
});
</script>
<?php admin_page_end();
