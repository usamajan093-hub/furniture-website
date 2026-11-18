<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$fontChoices = ['Playfair Display', 'Cormorant Garamond', 'Cinzel', 'Libre Baskerville', 'Lora', 'DM Serif Display', 'Marcellus', 'Inter', 'Poppins', 'Montserrat', 'Lato', 'Nunito Sans', 'Raleway', 'Work Sans'];
$colorLabels = [
    'bg' => 'Page background', 'surface' => 'Card / section surface', 'surface2' => 'Input background',
    'gold' => 'Accent (gold)', 'gold_light' => 'Accent light (links, chips)', 'gold_dark' => 'Accent dark', 'gold_pale' => 'Accent pale (gradient start)',
    'heading' => 'Headings', 'text' => 'Body text', 'muted' => 'Muted text', 'on_gold' => 'Text on gold buttons',
    'intro_bg' => 'Intro screen background', 'marquee_bg' => 'Marquee background', 'footer_bg' => 'Footer background', 'footer_text' => 'Footer text',
];
$toggles = ['show_marquee' => 'Marquee strip', 'show_services' => 'What We Do cards', 'show_offers' => 'Offers', 'show_before_after' => 'Before & After slider', 'show_products' => 'Products', 'show_process' => 'Process steps', 'show_why' => 'Why choose us', 'show_testimonials' => 'Testimonials', 'show_cta' => 'Call-to-action band'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post_str('action');
    $id  = post_str('id');
    if ($act === 'activate') {
        store_update('templates', function (array $rows) use ($id) {
            foreach ($rows as &$r) {
                $r['active'] = ($r['id'] ?? '') === $id;
            }
            return $rows;
        });
        log_activity('activated template', 'template', col_find('templates', $id)['name'] ?? '');
        flash('success', 'Template activated. The website now uses it.');
        redirect('templates.php');
    }
    if ($act === 'duplicate') {
        $t = col_find('templates', $id);
        if ($t) {
            unset($t['id'], $t['created_at'], $t['updated_at']);
            $t['name'] .= ' (copy)';
            $t['active'] = false;
            $t = col_save('templates', $t);
            log_activity('duplicated template', 'template', $t['name']);
            redirect('templates.php?edit=' . rawurlencode($t['id']));
        }
    }
    if ($act === 'delete') {
        $t = col_find('templates', $id);
        if ($t && empty($t['active']) && count(col_all('templates')) > 1) {
            col_delete('templates', $id);
            log_activity('deleted template', 'template', $t['name']);
            flash('success', 'Template deleted.');
        } else {
            flash('error', 'The active template (or the last remaining one) cannot be deleted.');
        }
        redirect('templates.php');
    }
    if ($act === 'save') {
        $t = $id !== '' ? (col_find('templates', $id) ?? template_defaults()) : template_defaults();
        $t['name'] = post_str('name') ?: 'Untitled template';
        $t['description'] = post_str('description');
        $colors = [];
        foreach (array_keys($colorLabels) as $k) {
            $v = post_str('c_' . $k);
            $colors[$k] = preg_match('/^#[0-9a-f]{6}$/i', $v) ? strtolower($v) : (template_defaults()['colors'][$k]);
        }
        $t['colors'] = $colors;
        $t['border_alpha'] = (string)max(0.05, min(1, (float)post_str('border_alpha', '0.22')));
        $t['font_heading'] = post_str('font_heading') ?: 'Playfair Display';
        $t['font_body'] = post_str('font_body') ?: 'Inter';
        $t['button_style'] = in_array(post_str('button_style'), ['square', 'rounded', 'pill'], true) ? post_str('button_style') : 'square';
        $t['intro_enabled'] = post_str('intro_enabled') === '1';
        $t['intro_word'] = mb_substr(post_str('intro_word'), 0, 14);
        $t['intro_sub'] = mb_substr(post_str('intro_sub'), 0, 30);
        $t['hero_layout'] = post_str('hero_layout') === 'center' ? 'center' : 'left';
        $t['hero_image'] = post_str('hero_image');
        $t['hero_motion'] = post_str('hero_motion') === '1';
        foreach (array_keys($toggles) as $k) {
            $t[$k] = post_str($k) === '1';
        }
        $t['section_padding'] = max(40, min(200, post_int('section_padding', 110)));
        if ($id === '') {
            $t['active'] = false;
        }
        $t = col_save('templates', $t);
        log_activity($id ? 'updated template' : 'created template', 'template', $t['name']);
        flash('success', 'Template saved.' . (!empty($t['active']) ? ' It is live.' : ' Preview it or set it active.'));
        redirect('templates.php');
    }
}

$editing = null;
if (isset($_GET['new'])) {
    $editing = template_defaults();
    $editing['name'] = 'New template';
} elseif (!empty($_GET['edit'])) {
    $editing = col_find('templates', (string)$_GET['edit']);
    if (!$editing) {
        flash('error', 'Template not found.');
        redirect('templates.php');
    }
    $editing = template_merge($editing);
}

if ($editing !== null):
    $t = $editing;
    admin_page_start(empty($t['id']) ? 'New template' : 'Edit template', 'templates', '<a class="btn btn-light btn-sm" href="templates.php">← All templates</a>');
    ?>
<datalist id="fonts"><?php foreach ($fontChoices as $f): ?><option value="<?= e($f) ?>"><?php endforeach; ?></datalist>
<form method="post" class="card"><div class="card-body">
  <?= csrf_field() ?><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e($t['id'] ?? '') ?>">
  <div class="form-grid">
    <div class="f"><label>Template name</label><input type="text" name="name" value="<?= e($t['name']) ?>" required></div>
    <div class="f"><label>Description</label><input type="text" name="description" value="<?= e($t['description']) ?>" placeholder="What this look is for"></div>
  </div>
  <h3 style="margin:26px 0 12px">Colours</h3>
  <div class="form-grid" style="grid-template-columns:repeat(auto-fill,minmax(240px,1fr))">
    <?php foreach ($colorLabels as $k => $label): $v = $t['colors'][$k]; ?>
    <div class="f"><label><?= e($label) ?></label><div class="color-row"><input type="color" value="<?= e($v) ?>"><input type="text" name="c_<?= $k ?>" value="<?= e($v) ?>" pattern="#[0-9a-fA-F]{6}"></div></div>
    <?php endforeach; ?>
    <div class="f"><label>Border strength (0.05 – 1)</label><input type="number" step="0.01" min="0.05" max="1" name="border_alpha" value="<?= e((string)$t['border_alpha']) ?>"></div>
  </div>
  <h3 style="margin:26px 0 12px">Typography &amp; shapes</h3>
  <div class="form-grid" style="grid-template-columns:repeat(3,1fr)">
    <div class="f"><label>Heading font (Google Fonts name)</label><input type="text" name="font_heading" list="fonts" value="<?= e($t['font_heading']) ?>"></div>
    <div class="f"><label>Body font</label><input type="text" name="font_body" list="fonts" value="<?= e($t['font_body']) ?>"></div>
    <div class="f"><label>Button &amp; card corners</label><select name="button_style"><?php foreach (['square' => 'Square (sharp)', 'rounded' => 'Rounded', 'pill' => 'Pill'] as $k => $l): ?><option value="<?= $k ?>" <?= $t['button_style'] === $k ? 'selected' : '' ?>><?= $l ?></option><?php endforeach; ?></select></div>
    <div class="f"><label>Section spacing (px)</label><input type="number" name="section_padding" min="40" max="200" value="<?= (int)$t['section_padding'] ?>"></div>
  </div>
  <h3 style="margin:26px 0 12px">Intro animation &amp; hero</h3>
  <div class="form-grid">
    <div class="f"><label class="check"><input type="checkbox" name="intro_enabled" value="1" <?= $t['intro_enabled'] ? 'checked' : '' ?>> Show the full-screen intro when the site opens</label></div>
    <div></div>
    <div class="f"><label>Intro word (big letters)</label><input type="text" name="intro_word" value="<?= e($t['intro_word']) ?>" maxlength="14"></div>
    <div class="f"><label>Intro sub-line</label><input type="text" name="intro_sub" value="<?= e($t['intro_sub']) ?>" maxlength="30"></div>
    <div class="f"><label>Hero layout</label><select name="hero_layout"><option value="left" <?= $t['hero_layout'] === 'left' ? 'selected' : '' ?>>Text on the left</option><option value="center" <?= $t['hero_layout'] === 'center' ? 'selected' : '' ?>>Centred text</option></select></div>
    <div class="f"><label class="check" style="margin-top:26px"><input type="checkbox" name="hero_motion" value="1" <?= $t['hero_motion'] ? 'checked' : '' ?>> Slow zoom on the hero photo</label></div>
    <div class="full"><?= img_field('hero_image', (string)$t['hero_image'], 'Hero background photo') ?></div>
  </div>
  <h3 style="margin:26px 0 12px">Homepage sections in this template</h3>
  <div class="form-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr))">
    <?php foreach ($toggles as $k => $label): ?><label class="check"><input type="checkbox" name="<?= $k ?>" value="1" <?= !empty($t[$k]) ? 'checked' : '' ?>> <?= e($label) ?></label><?php endforeach; ?>
  </div>
  <div class="form-foot">
    <?php if (!empty($t['id'])): ?><a class="btn btn-light" href="../index.php?preview_template=<?= e($t['id']) ?>" target="_blank" rel="noopener"><?= admin_icon('eye') ?> Preview</a><?php endif; ?>
    <span class="spacer"></span><a class="btn btn-light" href="templates.php">Cancel</a>
    <button class="btn btn-primary" type="submit"><?= admin_icon('check') ?> Save template</button>
  </div>
</div></form>
<?php admin_page_end(); exit; endif;

$tpls = col_all('templates');
admin_page_start('Templates', 'templates', '<a class="btn btn-primary btn-sm" href="templates.php?new=1">' . admin_icon('plus') . ' New template</a>');
?>
<p class="muted" style="margin-bottom:18px">A template controls the whole look of the website: colours, fonts, intro animation, hero layout and which homepage sections appear. One template is active at a time. Preview any template before you set it live.</p>
<div class="tpl-grid">
<?php foreach ($tpls as $raw): $t = template_merge($raw); $c = $t['colors']; ?>
  <div class="tpl <?= !empty($t['active']) ? 'active' : '' ?>">
    <div class="tpl-swatch" style="background:<?= e($c['bg']) ?>">
      <i style="background:<?= e($c['surface']) ?>;border-right:1px solid <?= e($c['gold']) ?>"></i><i style="background:linear-gradient(135deg,<?= e($c['gold_pale']) ?>,<?= e($c['gold']) ?>,<?= e($c['gold_dark']) ?>)"></i>
      <b style="color:<?= e($c['gold_light']) ?>;font-family:'<?= e($t['font_heading']) ?>',Georgia,serif"><?= e($t['intro_word'] ?: $t['name']) ?></b>
      <?php if (!empty($t['active'])): ?><span class="badge badge-gold" style="position:absolute;top:10px;right:10px">Active</span><?php endif; ?>
    </div>
    <div class="tpl-body"><h3><?= e($t['name']) ?></h3><p class="muted small"><?= e($t['description']) ?></p>
      <p class="small" style="margin-top:8px"><?= e($t['font_heading']) ?> / <?= e($t['font_body']) ?> · <?= e(ucfirst($t['button_style'])) ?> · hero <?= e($t['hero_layout']) ?> · intro <?= $t['intro_enabled'] ? 'on' : 'off' ?></p></div>
    <div class="tpl-foot">
      <a class="btn btn-light btn-sm" href="../index.php?preview_template=<?= e($t['id']) ?>" target="_blank" rel="noopener"><?= admin_icon('eye') ?> Preview</a>
      <a class="btn btn-light btn-sm" href="templates.php?edit=<?= e($t['id']) ?>"><?= admin_icon('edit') ?> Edit</a>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="duplicate"><input type="hidden" name="id" value="<?= e($t['id']) ?>"><button class="btn btn-light btn-sm" type="submit"><?= admin_icon('copy') ?> Duplicate</button></form>
      <?php if (empty($t['active'])): ?>
      <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="activate"><input type="hidden" name="id" value="<?= e($t['id']) ?>"><button class="btn btn-primary btn-sm" type="submit"><?= admin_icon('star') ?> Set active</button></form>
      <form method="post" data-confirm="Delete this template?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($t['id']) ?>"><button class="btn btn-danger btn-sm" type="submit" aria-label="Delete"><?= admin_icon('trash') ?></button></form>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php admin_page_end();
