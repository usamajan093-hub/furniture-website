<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$schema  = content_schema();
$section = (string)($_GET['s'] ?? 'hero');
if (!isset($schema[$section])) {
    $section = 'hero';
}
$def = $schema[$section];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $clean = schema_clean_section($def['fields'], $_POST['d'] ?? []);
    if ($section === 'portfolio') {
        foreach ($clean['filters'] as &$f) {
            $f['key'] = slugify($f['key'] ?: $f['label']);
        }
        unset($f);
    }
    store_update('content', function (array $all) use ($section, $clean) {
        $all[$section] = $clean;
        return $all;
    });
    log_activity('updated content', 'content', $def['label']);
    flash('success', $def['label'] . ' saved. The website shows the new content immediately.');
    redirect('content.php?s=' . $section);
}

$data = content_all()[$section] ?? [];

function field_html(string $name, array $f, $value, string $id): string
{
    $value = is_string($value) ? $value : '';
    switch ($f['type']) {
        case 'textarea':
            return '<div class="f full"><label for="' . e($id) . '">' . e($f['label']) . '</label><textarea id="' . e($id) . '" name="' . e($name) . '">' . e($value) . '</textarea></div>';
        case 'image':
            return '<div class="full">' . img_field($name, $value, $f['label'], $id) . '</div>';
        case 'url':
            return '<div class="f"><label for="' . e($id) . '">' . e($f['label']) . '</label><input type="text" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '" placeholder="#contact, #portfolio?cat=polish, tel:…, https://…"></div>';
        default:
            return '<div class="f"><label for="' . e($id) . '">' . e($f['label']) . '</label><input type="text" id="' . e($id) . '" name="' . e($name) . '" value="' . e($value) . '"></div>';
    }
}

function strings_row(string $name, string $value, string $i): string
{
    return '<div class="rep-row strings"><span class="grip">' . admin_icon('grip') . '</span><input type="text" name="' . e($name) . '[' . $i . ']" value="' . e($value) . '"><div class="rep-tools"><button type="button" class="icon-btn" data-rep-remove aria-label="Remove">' . admin_icon('x') . '</button></div></div>';
}

function list_row(string $name, array $fields, array $row, string $i): string
{
    $h = '<div class="rep-row"><span class="grip">' . admin_icon('grip') . '</span><div class="rep-fields">';
    foreach ($fields as $k => $f) {
        $h .= field_html($name . '[' . $i . '][' . $k . ']', $f, $row[$k] ?? '', 'r' . substr(md5($name . $k), 0, 6) . $i);
    }
    $h .= '</div><div class="rep-tools"><button type="button" class="icon-btn" data-rep-up aria-label="Move up">' . admin_icon('up') . '</button><button type="button" class="icon-btn" data-rep-down aria-label="Move down">' . admin_icon('down') . '</button><button type="button" class="icon-btn" data-rep-remove aria-label="Remove">' . admin_icon('x') . '</button></div></div>';
    return $h;
}

admin_page_start('Website Content', 'content');
?>
<div class="sections-nav">
<?php foreach ($schema as $key => $s): ?>
  <a href="content.php?s=<?= e($key) ?>" class="<?= $key === $section ? 'active' : '' ?>"><?= e($s['label']) ?></a>
<?php endforeach; ?>
</div>
<form method="post" class="card">
  <div class="card-head"><h2><?= e($def['label']) ?></h2><button class="btn btn-primary btn-sm" type="submit"><?= admin_icon('check') ?> Save changes</button></div>
  <div class="card-body"><?= csrf_field() ?>
  <div class="form-grid">
  <?php foreach ($def['fields'] as $key => $f):
      $name = 'd[' . $key . ']';
      $id = 'f_' . $key;
      if ($f['type'] === 'strings'): ?>
    <div class="full"><div class="f-label" style="margin-bottom:8px"><?= e($f['label']) ?></div>
      <div class="repeater">
        <div class="rep-list" style="display:grid;gap:8px">
        <?php foreach (as_list($data[$key] ?? []) as $i => $v): ?><?= strings_row($name, (string)$v, (string)$i) ?><?php endforeach; ?>
        </div>
        <template><?= strings_row($name, '', '__i__') ?></template>
        <button type="button" class="btn btn-light btn-sm" data-rep-add style="margin-top:8px"><?= admin_icon('plus') ?> Add</button>
      </div>
    </div>
  <?php elseif ($f['type'] === 'list'): ?>
    <div class="full"><div class="f-label" style="margin-bottom:8px"><?= e($f['label']) ?></div>
      <div class="repeater">
        <div class="rep-list" style="display:grid;gap:10px">
        <?php foreach (as_list($data[$key] ?? []) as $i => $row): ?><?= list_row($name, $f['fields'], is_array($row) ? $row : [], (string)$i) ?><?php endforeach; ?>
        </div>
        <template><?= list_row($name, $f['fields'], [], '__i__') ?></template>
        <button type="button" class="btn btn-light btn-sm" data-rep-add style="margin-top:8px"><?= admin_icon('plus') ?> Add row</button>
      </div>
    </div>
  <?php else: ?>
    <?= field_html($name, $f, $data[$key] ?? '', $id) ?>
  <?php endif; endforeach; ?>
  </div>
  <div class="form-foot"><span class="muted small">Changes go live on the website as soon as you save.</span><span class="spacer"></span><button class="btn btn-primary" type="submit"><?= admin_icon('check') ?> Save changes</button></div>
  </div>
</form>
<?php admin_page_end();
