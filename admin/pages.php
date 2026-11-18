<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$reserved = ['home', 'about', 'portfolio', 'contact', 'offers'];
$types = page_section_types();

function clean_sections($raw): array
{
    global $types;
    $out = [];
    if (!is_array($raw)) {
        return $out;
    }
    foreach (array_slice($raw, 0, 40) as $s) {
        if (!is_array($s) || !isset($types[$s['type'] ?? ''])) {
            continue;
        }
        $str = fn($k, $max = 2000) => mb_substr(trim((string)($s[$k] ?? '')), 0, $max);
        $sec = [
            'id' => preg_match('/^[a-z0-9]{6,16}$/', (string)($s['id'] ?? '')) ? $s['id'] : uid(),
            'type' => $s['type'], 'bg' => in_array($s['bg'] ?? '', ['base', 'surface', 'gold', 'custom'], true) ? $s['bg'] : 'base',
            'bg_color' => preg_match('/^#[0-9a-f]{6}$/i', (string)($s['bg_color'] ?? '')) ? strtolower($s['bg_color']) : '',
            'eyebrow' => $str('eyebrow', 120), 'heading' => $str('heading', 200), 'heading_em' => $str('heading_em', 200), 'text' => $str('text'),
            'image' => $str('image', 300), 'align' => ($s['align'] ?? 'left') === 'right' ? 'right' : 'left',
            'buttons' => [], 'images' => [], 'cards' => [],
        ];
        foreach (array_slice(is_array($s['buttons'] ?? null) ? $s['buttons'] : [], 0, 3) as $b) {
            if (is_array($b) && trim((string)($b['label'] ?? '')) !== '') {
                $sec['buttons'][] = ['label' => mb_substr(trim($b['label']), 0, 60), 'url' => mb_substr(trim((string)($b['url'] ?? '')), 0, 300), 'style' => ($b['style'] ?? '') === 'ghost' ? 'ghost' : 'primary'];
            }
        }
        foreach (array_slice(is_array($s['images'] ?? null) ? $s['images'] : [], 0, 12) as $u) {
            if (is_string($u) && trim($u) !== '') {
                $sec['images'][] = mb_substr(trim($u), 0, 300);
            }
        }
        foreach (array_slice(is_array($s['cards'] ?? null) ? $s['cards'] : [], 0, 6) as $c) {
            if (is_array($c) && trim((string)($c['title'] ?? '')) !== '') {
                $sec['cards'][] = ['title' => mb_substr(trim($c['title']), 0, 120), 'text' => mb_substr(trim((string)($c['text'] ?? '')), 0, 600)];
            }
        }
        $out[] = $sec;
    }
    return $out;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $act = post_str('action');
    $id  = post_str('id');
    if ($act === 'delete') {
        $p = col_find('pages', $id);
        if ($p) {
            col_delete('pages', $id);
            log_activity('deleted page', 'page', $p['title']);
            flash('success', 'Page deleted.');
        }
        redirect('pages.php');
    }
    if ($act === 'status') {
        $p = col_find('pages', $id);
        if ($p) {
            $p['status'] = post_str('status') === 'published' ? 'published' : 'draft';
            col_save('pages', $p);
            log_activity($p['status'] === 'published' ? 'published page' : 'unpublished page', 'page', $p['title']);
        }
        redirect('pages.php');
    }
    if ($act === 'save' || $act === 'preview') {
        $p = $id !== '' ? (col_find('pages', $id) ?? []) : ['sort' => count(col_all('pages')) + 1];
        $title = post_str('title') ?: 'Untitled page';
        $slug  = slugify(post_str('slug') ?: $title);
        $dupe = array_filter(col_all('pages'), fn($x) => $x['slug'] === $slug && ($x['id'] ?? '') !== $id);
        if (in_array($slug, $reserved, true) || $dupe) {
            $slug .= '-' . substr(uid(), 0, 4);
            flash('info', 'That address was already in use, so the page address became "' . $slug . '".');
        }
        $p = array_merge($p, [
            'title' => $title, 'slug' => $slug,
            'eyebrow' => post_str('eyebrow'), 'title_em' => post_str('title_em'),
            'status' => post_str('status') === 'published' ? 'published' : 'draft',
            'placement' => post_str('placement') === 'home' ? 'home' : 'standalone',
            'show_in_nav' => post_str('show_in_nav') === '1', 'nav_label' => post_str('nav_label'),
            'sort' => post_int('sort', (int)($p['sort'] ?? 0)),
            'sections' => clean_sections(json_decode((string)post('sections', '[]'), true)),
        ]);
        $p = col_save('pages', $p);
        log_activity($id ? 'updated page' : 'created page', 'page', $p['title']);
        if ($act === 'preview') {
            redirect('../index.php?preview_page=' . rawurlencode($p['id']) . '#' . ($p['placement'] === 'home' ? 'home' : $p['slug']));
        }
        flash('success', 'Page saved' . ($p['status'] === 'published' ? ' and published.' : ' as a draft.'));
        redirect('pages.php?edit=' . rawurlencode($p['id']));
    }
}

$editing = null;
if (isset($_GET['new'])) {
    $editing = ['title' => '', 'slug' => '', 'status' => 'draft', 'placement' => 'standalone', 'show_in_nav' => true, 'sections' => [], 'sort' => count(col_all('pages')) + 1];
} elseif (!empty($_GET['edit'])) {
    $editing = col_find('pages', (string)$_GET['edit']);
    if (!$editing) {
        flash('error', 'Page not found.');
        redirect('pages.php');
    }
}

if ($editing !== null):
    $p = $editing;
    admin_page_start(empty($p['id']) ? 'New page' : 'Edit page: ' . $p['title'], 'pages', '<a class="btn btn-light btn-sm" href="pages.php">← All pages</a>');
    ?>
<form method="post" id="pb-form">
  <?= csrf_field() ?><input type="hidden" name="action" value="save" id="pb-action"><input type="hidden" name="id" value="<?= e($p['id'] ?? '') ?>"><input type="hidden" name="sections" id="pb-sections">
  <div class="card" style="margin-bottom:22px"><div class="card-body form-grid" style="grid-template-columns:repeat(4,1fr)">
    <div class="f"><label>Page title</label><input type="text" name="title" id="p-title" value="<?= e($p['title']) ?>" required></div>
    <div class="f"><label>Address (after #)</label><input type="text" name="slug" value="<?= e($p['slug']) ?>" data-slug-from="#p-title" placeholder="auto"></div>
    <div class="f"><label>Title — gold italic part</label><input type="text" name="title_em" value="<?= e($p['title_em'] ?? '') ?>"></div>
    <div class="f"><label>Eyebrow above the title</label><input type="text" name="eyebrow" value="<?= e($p['eyebrow'] ?? '') ?>" placeholder="e.g. Services"></div>
    <div class="f"><label>Status</label><select name="status"><option value="draft" <?= $p['status'] !== 'published' ? 'selected' : '' ?>>Draft (hidden)</option><option value="published" <?= $p['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div>
    <div class="f"><label>Where it shows</label><select name="placement"><option value="standalone" <?= ($p['placement'] ?? '') !== 'home' ? 'selected' : '' ?>>Its own page</option><option value="home" <?= ($p['placement'] ?? '') === 'home' ? 'selected' : '' ?>>Sections on the home page</option></select></div>
    <div class="f"><label class="check" style="margin-top:28px"><input type="checkbox" name="show_in_nav" value="1" <?= !empty($p['show_in_nav']) ? 'checked' : '' ?>> Show in the menu</label></div>
    <div class="f"><label>Menu label (optional)</label><input type="text" name="nav_label" value="<?= e($p['nav_label'] ?? '') ?>"></div>
  </div></div>
  <div class="pb">
    <div>
      <div class="pb-list" id="pb-list"></div>
      <div class="empty" id="pb-empty" style="display:none">No sections yet. Add one from the panel on the right.</div>
    </div>
    <div class="sticky">
      <div class="card" style="margin-bottom:16px"><div class="card-head"><h2>Add a section</h2></div><div class="card-body pb-add">
        <?php foreach ($types as $k => $t): ?><button type="button" data-add="<?= e($k) ?>"><b><?= e($t['label']) ?></b><span><?= e($t['hint']) ?></span></button><?php endforeach; ?>
      </div></div>
      <div class="card"><div class="card-body" style="display:grid;gap:10px">
        <button class="btn btn-primary btn-block" type="submit"><?= admin_icon('check') ?> Save page</button>
        <button class="btn btn-light btn-block" type="button" id="pb-preview"><?= admin_icon('eye') ?> Save &amp; preview</button>
        <p class="muted small">Drag sections by the handle to rearrange. Previews show drafts too; visitors see published pages only.</p>
      </div></div>
    </div>
  </div>
</form>
<script>
(function(){
  var sections = <?= json_encode(array_values($p['sections'] ?? []), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var TYPES = <?= json_encode($types, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var $=function(s,r){return (r||document).querySelector(s)}, $$=function(s,r){return Array.prototype.slice.call((r||document).querySelectorAll(s))};
  var list=$("#pb-list");
  function uid(){ return Math.random().toString(36).slice(2,10); }
  function blank(type){ return {id:uid(),type:type,bg:"base",bg_color:"#14100d",eyebrow:"",heading:"",heading_em:"",text:"",image:"",align:"left",buttons:[],images:[],cards:[]}; }
  function inp(si,k,label,val,ph){ return '<div class="f"><label>'+label+'</label><input type="text" data-si="'+si+'" data-k="'+k+'" value="'+esc(val||"")+'" placeholder="'+esc(ph||"")+'"></div>'; }
  function ta(si,k,label,val){ return '<div class="f full"><label>'+label+'</label><textarea data-si="'+si+'" data-k="'+k+'">'+esc(val||"")+'</textarea></div>'; }
  function img(si,k,val,label,arr,idx){
    var d='data-si="'+si+'" data-k="'+k+'"'+(arr?' data-arr="'+arr+'" data-idx="'+idx+'"':'');
    return '<div class="img-field">'+(label?'<label>'+label+'</label>':'')+'<div class="img-field-row"><div class="img-prev">'+(val?'<img src="../'+esc(val)+'" alt="">':'')+'</div><input type="text" '+d+' value="'+esc(val||"")+'" placeholder="Choose an image"><button type="button" class="btn btn-sm btn-light" data-pick>Choose</button><button type="button" class="btn btn-sm btn-ghost" data-clear>Clear</button></div></div>';
  }
  function buttons(si,s){
    var h='<div class="full"><div class="f-label" style="margin-bottom:6px">Buttons (up to 2)</div>';
    for(var i=0;i<2;i++){ var b=s.buttons[i]||{label:"",url:"",style:i?"ghost":"primary"};
      h+='<div class="form-grid" style="grid-template-columns:1fr 1fr auto;margin-bottom:8px"><input type="text" placeholder="Label" data-si="'+si+'" data-arr="buttons" data-idx="'+i+'" data-k="label" value="'+esc(b.label)+'"><input type="text" placeholder="Link (#contact, https://…)" data-si="'+si+'" data-arr="buttons" data-idx="'+i+'" data-k="url" value="'+esc(b.url)+'"><select data-si="'+si+'" data-arr="buttons" data-idx="'+i+'" data-k="style"><option value="primary"'+(b.style!=="ghost"?" selected":"")+'>Gold</option><option value="ghost"'+(b.style==="ghost"?" selected":"")+'>Outline</option></select></div>'; }
    return h+'</div>';
  }
  function render(){
    $("#pb-empty").style.display=sections.length?"none":"block";
    list.innerHTML=sections.map(function(s,si){
      var body='<div class="form-grid">';
      body+='<div class="f"><label>Background</label><select data-si="'+si+'" data-k="bg"><option value="base"'+(s.bg==="base"?" selected":"")+'>Page background</option><option value="surface"'+(s.bg==="surface"?" selected":"")+'>Surface (card colour)</option><option value="gold"'+(s.bg==="gold"?" selected":"")+'>Gold</option><option value="custom"'+(s.bg==="custom"?" selected":"")+'>Custom colour…</option></select></div>';
      body+='<div class="f"><label>Custom colour</label><div class="color-row"><input type="color" value="'+esc(s.bg_color||"#14100d")+'" data-si="'+si+'" data-k="bg_color"><input type="text" data-si="'+si+'" data-k="bg_color" value="'+esc(s.bg_color||"")+'" placeholder="#14100d"></div></div>';
      body+=inp(si,"eyebrow","Eyebrow",s.eyebrow,"Small gold line above the heading")+inp(si,"heading","Heading",s.heading,"")+inp(si,"heading_em","Heading — gold italic part",s.heading_em,"");
      if(s.type!=="gallery"||true) body+=ta(si,"text","Text",s.text);
      if(s.type==="banner"||s.type==="split") body+='<div class="full">'+img(si,"image",s.image,s.type==="banner"?"Background photo":"Photo")+'</div>';
      if(s.type==="split") body+='<div class="f"><label>Photo position</label><select data-si="'+si+'" data-k="align"><option value="left"'+(s.align!=="right"?" selected":"")+'>Left</option><option value="right"'+(s.align==="right"?" selected":"")+'>Right</option></select></div>';
      if(s.type==="gallery"){ body+='<div class="full"><div class="f-label" style="margin-bottom:6px">Photos</div><div class="imgs-list">'+s.images.map(function(u,i){return '<div style="display:flex;gap:6px;align-items:center">'+img(si,"images",u,"","images",i)+'<button type="button" class="icon-btn" data-rm-img="'+i+'" data-si="'+si+'" aria-label="Remove">✕</button></div>'}).join("")+'</div><button type="button" class="btn btn-light btn-sm" data-add-img="'+si+'" style="margin-top:8px">+ Add photo</button></div>'; }
      if(s.type==="cards"){ body+='<div class="full"><div class="f-label" style="margin-bottom:6px">Cards</div>'+s.cards.map(function(c,i){return '<div class="form-grid" style="grid-template-columns:1fr 2fr auto;margin-bottom:8px"><input type="text" placeholder="Title" data-si="'+si+'" data-arr="cards" data-idx="'+i+'" data-k="title" value="'+esc(c.title)+'"><input type="text" placeholder="Text" data-si="'+si+'" data-arr="cards" data-idx="'+i+'" data-k="text" value="'+esc(c.text)+'"><button type="button" class="icon-btn" data-rm-card="'+i+'" data-si="'+si+'" aria-label="Remove">✕</button></div>'}).join("")+'<button type="button" class="btn btn-light btn-sm" data-add-card="'+si+'">+ Add card</button></div>'; }
      if(s.type!=="gallery"&&s.type!=="cards") body+=buttons(si,s);
      body+='</div>';
      return '<div class="pb-sec" data-si="'+si+'"><div class="pb-sec-head"><span class="grip" draggable="true">⋮⋮</span><b>'+esc(s.heading||TYPES[s.type].label)+'</b><span class="badge badge-muted">'+esc(TYPES[s.type].label)+'</span><button type="button" class="icon-btn" data-toggle aria-label="Collapse">▾</button><button type="button" class="icon-btn" data-remove aria-label="Remove">✕</button></div><div class="pb-sec-body">'+body+'</div></div>';
    }).join("");
    bindImgFields(list);
  }
  list.addEventListener("input",update); list.addEventListener("change",update);
  function update(e){
    var el=e.target; if(!el.dataset||el.dataset.si===undefined) return;
    var s=sections[+el.dataset.si]; if(!s) return;
    if(el.dataset.arr){ var arr=s[el.dataset.arr], i=+el.dataset.idx; if(el.dataset.arr==="images"){ arr[i]=el.value; } else { arr[i]=arr[i]||{}; arr[i][el.dataset.k]=el.value; } }
    else { s[el.dataset.k]=el.value; if(el.type==="color"){ var t=el.parentNode.querySelector("input[type=text]"); if(t) t.value=el.value; } }
    if(el.dataset.k==="heading"){ var b=el.closest(".pb-sec").querySelector(".pb-sec-head b"); b.textContent=el.value||TYPES[s.type].label; }
  }
  list.addEventListener("click",function(e){
    var sec=e.target.closest(".pb-sec"); if(!sec) return; var si=+sec.dataset.si, s=sections[si];
    if(e.target.closest("[data-remove]")){ if(confirm("Remove this section?")){ sections.splice(si,1); render(); } return; }
    if(e.target.closest("[data-toggle]")){ var b=sec.querySelector(".pb-sec-body"); b.style.display=b.style.display==="none"?"":"none"; return; }
    var ai=e.target.closest("[data-add-img]"); if(ai){ s.images.push(""); render(); return; }
    var ri=e.target.closest("[data-rm-img]"); if(ri){ s.images.splice(+ri.dataset.rmImg,1); render(); return; }
    var ac=e.target.closest("[data-add-card]"); if(ac){ s.cards.push({title:"",text:""}); render(); return; }
    var rc=e.target.closest("[data-rm-card]"); if(rc){ s.cards.splice(+rc.dataset.rmCard,1); render(); return; }
  });
  $$("[data-add]").forEach(function(b){ b.addEventListener("click",function(){ var s=blank(b.dataset.add); if(s.type==="cards") s.cards=[{title:"",text:""},{title:"",text:""},{title:"",text:""}]; if(s.type==="gallery") s.images=["",""]; sections.push(s); render(); list.lastElementChild.scrollIntoView({behavior:"smooth",block:"start"}); }); });
  document.addEventListener("reordered",function(e){ if(!list.contains(e.detail)) return; var order=$$(".pb-sec",list).map(function(el){return sections[+el.dataset.si]}); sections=order; render(); });
  function serialize(){ sections.forEach(function(s){ s.buttons=(s.buttons||[]).filter(function(b){return b&&b.label}); s.images=(s.images||[]).filter(Boolean); }); $("#pb-sections").value=JSON.stringify(sections); }
  $("#pb-form").addEventListener("submit",serialize);
  $("#pb-preview").addEventListener("click",function(){ $("#pb-action").value="preview"; $("#pb-form").requestSubmit(); });
  render();
})();
</script>
<?php admin_page_end(); exit; endif;

$pages = col_all('pages');
usort($pages, fn($a, $b) => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
admin_page_start('Page Builder', 'pages', '<a class="btn btn-primary btn-sm" href="pages.php?new=1">' . admin_icon('plus') . ' New page</a>');
?>
<div class="card"><div class="table-wrap"><table>
  <thead><tr><th>Page</th><th>Address</th><th>Shows</th><th>Sections</th><th>Status</th><th>Updated</th><th></th></tr></thead>
  <tbody>
  <?php if (!$pages): ?><tr><td colspan="7" class="empty">No pages yet. Build your first page — for example "Deco & Panels" or "Factory Contracts".</td></tr><?php endif; ?>
  <?php foreach ($pages as $p): ?>
    <tr>
      <td><a href="pages.php?edit=<?= e($p['id']) ?>"><b><?= e($p['title']) ?></b></a></td>
      <td class="small">#<?= e($p['slug']) ?></td>
      <td class="small"><?= ($p['placement'] ?? '') === 'home' ? 'On the home page' : 'Own page' . (!empty($p['show_in_nav']) ? ' · in menu' : '') ?></td>
      <td><?= count($p['sections'] ?? []) ?></td>
      <td><?= state_badge($p['status'] ?? 'draft') ?></td>
      <td class="muted small"><?= e($p['updated_at'] ?? '') ?></td>
      <td><div class="row-actions">
        <a class="btn btn-light btn-sm" href="../index.php?preview_page=<?= e($p['id']) ?>#<?= ($p['placement'] ?? '') === 'home' ? 'home' : e($p['slug']) ?>" target="_blank" rel="noopener"><?= admin_icon('eye') ?></a>
        <form method="post"><?= csrf_field() ?><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><input type="hidden" name="status" value="<?= ($p['status'] ?? '') === 'published' ? 'draft' : 'published' ?>"><button class="btn btn-light btn-sm" type="submit"><?= ($p['status'] ?? '') === 'published' ? 'Unpublish' : 'Publish' ?></button></form>
        <a class="btn btn-light btn-sm" href="pages.php?edit=<?= e($p['id']) ?>"><?= admin_icon('edit') ?></a>
        <form method="post" data-confirm="Delete this page?"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="btn btn-danger btn-sm" type="submit" aria-label="Delete"><?= admin_icon('trash') ?></button></form>
      </div></td>
    </tr>
  <?php endforeach; ?>
  </tbody></table></div></div>
<?php admin_page_end();
