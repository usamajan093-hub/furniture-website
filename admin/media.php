<?php
declare(strict_types=1);
require __DIR__ . '/inc/guard.php';

$media = col_all('media');
$cats  = media_categories();
admin_page_start('Media Library', 'media', '<label class="btn btn-primary btn-sm upload-btn">' . admin_icon('upload') . ' Upload images<input type="file" id="top-upload" accept="image/*" multiple hidden></label>');
?>
<div class="dropzone" id="dropzone">
  <?= admin_icon('upload') ?>
  <div><b>Drop images here</b> or <label style="color:var(--gold);cursor:pointer;text-decoration:underline">browse<input type="file" id="dz-upload" accept="image/*" multiple hidden></label></div>
  <div class="small">JPG, PNG, WebP or SVG · up to <?= (int)cfg('max_upload_mb') ?> MB each · optional category: <input type="text" id="up-cat" placeholder="e.g. Offers" style="width:140px;display:inline-block;padding:4px 8px" list="cat-list"></div>
  <datalist id="cat-list"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist>
  <div class="progress" id="up-progress"><i></i></div>
</div>
<div class="toolbar">
  <label class="search"><?= admin_icon('search') ?><input type="search" id="q" placeholder="Search by title or file name…"></label>
  <select id="cat"><option value="">All categories</option><?php foreach ($cats as $c): ?><option><?= e($c) ?></option><?php endforeach; ?></select>
  <span class="spacer"></span>
  <span class="muted small" id="count"></span>
</div>
<div class="media-grid" id="grid"></div>

<div class="modal" id="detail"><div class="modal-box">
  <div class="modal-head"><h2>Image details</h2><button class="icon-btn" data-modal-close aria-label="Close"><?= admin_icon('x') ?></button></div>
  <div class="modal-body media-detail">
    <div><img id="d-img" src="" alt=""><p class="muted small" id="d-meta" style="margin-top:8px"></p></div>
    <div style="display:grid;gap:12px">
      <div class="f"><label>Title</label><input type="text" id="d-title"></div>
      <div class="f"><label>Category</label><input type="text" id="d-cat" list="cat-list" placeholder="e.g. Doors"></div>
      <div class="f"><label>Path (use this in any field)</label><div class="copy-row"><input type="text" id="d-url" readonly><button class="btn btn-light btn-sm" id="d-copy" type="button"><?= admin_icon('copy') ?></button></div></div>
      <div class="hint" id="d-hint"></div>
    </div>
  </div>
  <div class="modal-foot"><button class="btn btn-danger btn-sm" id="d-delete" type="button"><?= admin_icon('trash') ?> Delete</button><span style="flex:1"></span><button class="btn btn-light" data-modal-close type="button">Close</button><button class="btn btn-primary" id="d-save" type="button">Save</button></div>
</div></div>

<script>
(function(){
  var items = <?= json_encode($media, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
  var $=function(s){return document.querySelector(s)};
  var grid=$("#grid"), q=$("#q"), cat=$("#cat"), cur=null;
  function fmt(b){ return b? (b>1048576? (b/1048576).toFixed(1)+" MB" : Math.round(b/1024)+" KB") : ""; }
  function render(){
    var s=q.value.toLowerCase(), c=cat.value;
    var list=items.filter(function(m){ return (!s||(m.title+" "+m.url).toLowerCase().indexOf(s)>=0)&&(!c||m.category===c); });
    $("#count").textContent=list.length+" of "+items.length+" images";
    grid.innerHTML=list.length? list.map(function(m){
      return '<div class="media-item" data-id="'+m.id+'"><div class="mi"><img src="../'+m.url+'" alt="" loading="lazy"></div><div class="mt"><b title="'+esc(m.title)+'">'+esc(m.title)+'</b><span>'+esc(m.category||"—")+(m.width?' · '+m.width+'×'+m.height:'')+'</span></div></div>';
    }).join("") : '<div class="empty" style="grid-column:1/-1">No images yet. Drop some above.</div>';
  }
  function refreshCats(){
    var cats={}; items.forEach(function(m){ if(m.category) cats[m.category]=1; });
    var keys=Object.keys(cats).sort(), v=cat.value;
    cat.innerHTML='<option value="">All categories</option>'+keys.map(function(k){return '<option>'+esc(k)+'</option>'}).join(""); cat.value=v;
    $("#cat-list").innerHTML=keys.map(function(k){return '<option value="'+esc(k)+'">'}).join("");
  }
  q.addEventListener("input",render); cat.addEventListener("change",render);
  grid.addEventListener("click",function(e){
    var it=e.target.closest(".media-item"); if(!it) return;
    cur=items.filter(function(m){return m.id===it.dataset.id})[0]; if(!cur) return;
    $("#d-img").src="../"+cur.url; $("#d-title").value=cur.title||""; $("#d-cat").value=cur.category||""; $("#d-url").value=cur.url;
    $("#d-meta").textContent=[cur.mime, cur.width?cur.width+"×"+cur.height:"", fmt(cur.size), cur.created_at].filter(Boolean).join(" · ");
    $("#d-hint").textContent=cur.managed? "" : "This photo is part of the original site files (assets/images). Deleting removes it from the library only.";
    openModal("#detail");
  });
  $("#d-copy").addEventListener("click",function(){ var v=$("#d-url").value; (navigator.clipboard?navigator.clipboard.writeText(v):Promise.reject()).then(function(){toast("Copied "+v)},function(){prompt("Copy:",v)}); });
  $("#d-save").addEventListener("click",function(){
    api("media_update",{id:cur.id,title:$("#d-title").value,category:$("#d-cat").value}).then(function(r){
      if(r.item){ Object.assign(cur,r.item); refreshCats(); render(); toast("Saved"); closeModal("#detail"); } else toast(r.error||"Error");
    });
  });
  $("#d-delete").addEventListener("click",function(){
    if(!confirm("Delete \""+cur.title+"\"? Pages or offers still using it will show a blank image.")) return;
    api("media_delete",{id:cur.id}).then(function(r){ if(r.ok){ items=items.filter(function(m){return m.id!==cur.id}); refreshCats(); render(); closeModal("#detail"); toast("Deleted"); } else toast(r.error||"Error"); });
  });
  function doUpload(files){
    if(!files||!files.length) return;
    var fd=new FormData(); Array.prototype.forEach.call(files,function(f){fd.append("files[]",f)}); fd.append("category",$("#up-cat").value);
    var pr=$("#up-progress"); pr.style.display="block"; pr.firstElementChild.style.width="30%";
    api("media_upload",fd,true).then(function(r){
      pr.firstElementChild.style.width="100%"; setTimeout(function(){pr.style.display="none";pr.firstElementChild.style.width="0"},600);
      (r.items||[]).forEach(function(m){ items.unshift(m); });
      refreshCats(); render();
      if(r.errors&&r.errors.length) alert(r.errors.join("\n")); else toast((r.items||[]).length+" image(s) uploaded");
    }).catch(function(){ pr.style.display="none"; alert("Upload failed."); });
  }
  ["#dz-upload","#top-upload"].forEach(function(s){ var el=$(s); if(el) el.addEventListener("change",function(){ doUpload(this.files); this.value=""; }); });
  var dz=$("#dropzone");
  ["dragenter","dragover"].forEach(function(ev){ dz.addEventListener(ev,function(e){ e.preventDefault(); dz.classList.add("over"); }); });
  ["dragleave","drop"].forEach(function(ev){ dz.addEventListener(ev,function(e){ e.preventDefault(); dz.classList.remove("over"); }); });
  dz.addEventListener("drop",function(e){ doUpload(e.dataTransfer.files); });
  refreshCats(); render();
})();
</script>
<?php admin_page_end();
