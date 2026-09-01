<style>
.bp-wrap{max-width:1400px;margin:0 auto}
.bp-toolbar{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;align-items:center}
.bp-toolbar .bp-search{flex:1;min-width:180px}
.bp-cat-filters{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:14px}
.bp-cat-filter{padding:6px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;background:rgba(255,255,255,.04);color:#94a3b8;border:1px solid transparent}
.bp-cat-filter:hover{background:rgba(0,140,255,.08);color:#0A84FF}
.bp-cat-filter.act{background:rgba(0,140,255,.15);border-color:rgba(0,140,255,.3);color:#0A84FF}
.bp-group{margin-bottom:22px}
.bp-group-title{font-size:14px;font-weight:700;color:#e0e0e0;margin:0 0 10px;padding-bottom:6px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:8px}
.bp-group-title .cnt{font-size:11px;color:#64748b;font-weight:400}
.bp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px}
.bp-card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:14px;transition:.15s;position:relative}
.bp-card:hover{border-color:rgba(0,140,255,.18)}
.bp-card img.bp-img{width:100%;height:90px;object-fit:cover;border-radius:8px;margin-bottom:8px;background:rgba(0,0,0,.3)}
.bp-card .bp-name{font-weight:600;font-size:14px;color:#e0e0e0}
.bp-card .bp-desc{font-size:11px;color:#64748b;margin-top:4px;line-height:1.5}
.bp-card .bp-meta{display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;align-items:center}
.bp-card .bp-price{font-size:13px;font-weight:700;color:#4ade80;margin-top:8px}
.bp-card .bp-price small{color:#64748b;font-weight:400;font-size:10px}
.bp-card .bp-actions{margin-top:10px;display:flex;gap:4px;flex-wrap:wrap}
.bp-card .bp-stats{display:flex;gap:10px;margin-top:8px;font-size:10px;color:#64748b}
.bp-toggle{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:6px;font-size:10px;font-weight:600;cursor:pointer;border:none;text-decoration:none}
.bp-toggle.on{background:rgba(74,222,128,.15);color:#4ade80}
.bp-toggle.off{background:rgba(248,113,113,.15);color:#f87171}
.bp-vis{display:inline-flex;align-items:center;gap:4px;padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;text-decoration:none}
.bp-vis.on{background:rgba(56,189,248,.12);color:#38bdf8}
.bp-vis.off{background:rgba(148,163,184,.12);color:#64748b}
.bp-card .bp-actions a,.bp-card .bp-actions button{padding:4px 10px;border-radius:5px;font-size:10px;text-decoration:none;font-weight:600;border:none;cursor:pointer}
.bp-modal{display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:flex-start;justify-content:center;overflow-y:auto;padding:40px 16px}
.bp-modal.open{display:flex}
.bp-modal .card{max-width:560px;width:100%;margin:0}
.bp-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.bp-form-grid .full{grid-column:1/-1}
.bp-modal label{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:#64748b;display:block;margin-bottom:4px;font-weight:600}
.bp-modal input,.bp-modal select,.bp-modal textarea{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;box-sizing:border-box}
.bp-modal input:focus,.bp-modal select:focus,.bp-modal textarea:focus{border-color:#0A84FF}
.bp-modal select option{background:#0a0f1a;color:#e0e0e0}
.bp-copy-modal textarea{width:100%;min-height:80px;background:rgba(0,0,0,.5);color:#4ade80;font-family:monospace;font-size:12px;padding:10px;border:1px solid rgba(255,255,255,.1);border-radius:6px;resize:vertical;box-sizing:border-box}
.bp-copy-modal .copy-row{margin-bottom:10px}
.bp-copy-modal .copy-row label{display:block;font-size:11px;color:#64748b;margin-bottom:4px;font-weight:600}
.bp-copy-modal .copy-btn{margin-top:6px}
</style>

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<?php foreach ($billingTabs as $tab): ?>
<a href="<?php echo $tab['url']; ?>" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo (str_contains($title ?? '', $tab['label']) || (basename(parse_url($tab['url'], PHP_URL_PATH)) === basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)))) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>"><?php echo $tab['label']; ?></a>
<?php endforeach; ?>
</div>

<div class="bp-wrap">
<div class="bp-toolbar">
<input type="text" class="bp-search" id="bpSearch" placeholder="🔍 Search products..." style="padding:8px 12px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;outline:none;font-size:13px">
<button class="btn primary" onclick="openAdd()">+ Add Product</button>
</div>

<div class="bp-cat-filters" id="bpCatFilters"></div>

<div id="bpGroups">
<?php
$products = $products ?? [];
$resellerPackages = $resellerPackages ?? [];
$billingCategories = $billingCategories ?? [];
$pkgMap = [];
foreach ($resellerPackages as $pg) $pkgMap[$pg->id] = $pg->name;

$catNames = [];
foreach ($products as $p) {
    $c = trim((string)($p->category ?? '')) ?: ($p->type ?? 'hosting');
    if (!in_array($c, $catNames)) $catNames[] = $c;
}
$catLabels = ['hosting'=>'Web Hosting','radio'=>'Radio','vps'=>'VPS','domain'=>'Domain','addon'=>'Addon','game'=>'Game Server','server'=>'Server','ssl'=>'SSL','other'=>'Other'];
function bp_label($c, $labels) { return $labels[$c] ?? ucwords(str_replace('_',' ',$c)); }
foreach ($catNames as $cat):
    $items = array_filter($products, function($p) use ($cat) { return (trim((string)($p->category ?? '')) ?: ($p->type ?? 'hosting')) === $cat; });
    if (empty($items)) continue;
?>
<div class="bp-group" data-cat="<?php echo htmlspecialchars($cat); ?>">
<div class="bp-group-title"><?php echo htmlspecialchars(bp_label($cat, $catLabels)); ?> <span class="cnt"><?php echo count($items); ?></span></div>
<div class="bp-grid">
<?php foreach ($items as $p):
    $catVal = trim((string)($p->category ?? '')) ?: ($p->type ?? 'hosting');
    $vis = $p->is_visible ?? 1;
?>
<div class="bp-card" data-id="<?php echo $p->id; ?>">
<?php if (!empty($p->image)): ?><img class="bp-img" src="<?php echo htmlspecialchars($p->image); ?>" alt=""><?php endif; ?>
<div class="bp-name"><?php echo htmlspecialchars($p->name); ?></div>
<?php if (!empty($p->description)): ?><div class="bp-desc"><?php echo htmlspecialchars($p->description); ?></div><?php endif; ?>
<div class="bp-meta">
<a class="bp-toggle <?php echo $p->is_active ? 'on' : 'off'; ?>" href="/reseller/billing-system/products/toggle/<?php echo $p->id; ?>"><?php echo $p->is_active ? '✓ Active' : '✕ Inactive'; ?></a>
<a class="bp-vis <?php echo $vis ? 'on' : 'off'; ?>" href="/reseller/billing-system/products/toggle-visible/<?php echo $p->id; ?>"><?php echo $vis ? '👁 Visible' : '🚫 Hidden'; ?></a>
<span style="font-size:10px;color:#94a3b8"><?php echo htmlspecialchars($p->type); ?> · <?php echo htmlspecialchars($p->billing_cycle); ?></span>
</div>
<?php if (isset($pkgMap[$p->reseller_package_id])): ?><div style="font-size:10px;color:#38bdf8;margin-top:4px">📦 <?php echo htmlspecialchars($pkgMap[$p->reseller_package_id]); ?></div><?php endif; ?>
<div class="bp-price">$<?php echo number_format((float)$p->price, 2); ?><small><?php echo (float)$p->setup_fee > 0 ? ' + $'.number_format((float)$p->setup_fee,2).' setup' : ' / '.$p->billing_cycle; ?></small></div>
<div class="bp-actions">
<a class="btn btn-sm secondary" style="background:rgba(0,140,255,.1);color:#38bdf8" onclick="openEdit(<?php echo $p->id; ?>,'<?php echo htmlspecialchars(addslashes($p->name)); ?>','<?php echo htmlspecialchars(addslashes($p->description ?? '')); ?>','<?php echo $p->type; ?>','<?php echo htmlspecialchars(addslashes($catVal)); ?>',<?php echo (float)$p->price; ?>,<?php echo (float)$p->setup_fee ?? 0; ?>,'<?php echo $p->billing_cycle; ?>',<?php echo (int)$p->is_active; ?>,<?php echo (int)$p->reseller_package_id ?? 0; ?>,'<?php echo htmlspecialchars(addslashes($p->license_key ?? '')); ?>','<?php echo htmlspecialchars(addslashes($p->image ?? '')); ?>',<?php echo (int)$vis; ?>)">✏ Edit</a>
<a href="/reseller/billing-system/products/clone/<?php echo $p->id; ?>" style="background:rgba(74,222,128,.1);color:#4ade80" onclick="return confirm('Clone this product?')">⧉ Clone</a>
<button type="button" style="background:rgba(168,85,247,.1);color:#c084fc" onclick="openCopy(<?php echo $p->id; ?>,'<?php echo htmlspecialchars(addslashes($p->name)); ?>')">📋 Copy</button>
<a href="/reseller/billing-system/products/toggle/<?php echo $p->id; ?>" style="background:rgba(250,204,21,.1);color:#facc15"><?php echo $p->is_active ? '⏻ Off' : '⏻ On'; ?></a>
<a href="/reseller/billing-system/products/delete/<?php echo $p->id; ?>" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete product <?php echo htmlspecialchars($p->name); ?>?')">🗑</a>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endforeach; ?>
<?php if (empty($products)): ?><div class="card" style="text-align:center;padding:24px;color:#64748b">No products yet. Click "+ Add Product".</div><?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="bp-modal" id="bpModal">
<div class="card">
<h3 style="color:var(--accent);margin:0 0 14px" id="bpModalTitle">Add Product</h3>
<form method="POST" action="/reseller/billing-system/products/store" id="bpForm">
<input type="hidden" name="is_active" id="f_active" value="1">
<div class="bp-form-grid">
<div class="full"><label>Name *</label><input name="name" id="f_name" required></div>
<div class="full"><label>Description</label><textarea name="description" id="f_desc" rows="2"></textarea></div>
<div class="full"><label>Image URL (optional)</label><input name="image" id="f_image" placeholder="https://.../logo.png"></div>
<div><label>Type</label><select name="type" id="f_type" onchange="syncCat()">
<option value="hosting">Hosting</option><option value="radio">Radio</option><option value="vps">VPS</option><option value="domain">Domain</option><option value="addon">Addon</option><option value="game">Game Server</option><option value="server">Server</option><option value="ssl">SSL</option><option value="other">Other</option>
</select></div>
<div><label>Category <a href="/reseller/billing-system/categories" target="_blank" style="color:#0A84FF;font-size:10px;text-decoration:none">(Manage)</a></label>
<select name="category" id="f_category"><option value="">— Select —</option>
<?php foreach ($billingCategories as $bc): ?><option value="<?php echo htmlspecialchars($bc->name); ?>"><?php echo htmlspecialchars(($bc->icon ?? '📦') . ' ' . $bc->name); ?></option><?php endforeach; ?>
</select></div>
<div><label>Reseller Package (optional)</label><select name="reseller_package_id" id="f_package"><option value="">— None —</option>
<?php foreach ($resellerPackages as $pg): ?><option value="<?php echo $pg->id; ?>"><?php echo htmlspecialchars($pg->name); ?> (<?php echo htmlspecialchars($pg->type); ?>)</option><?php endforeach; ?>
</select></div>
<div><label>License Key (optional)</label><input name="license_key" id="f_license"></div>
<div><label>Price ($)</label><input name="price" id="f_price" type="number" step="0.01" value="0.00"></div>
<div><label>Setup Fee ($)</label><input name="setup_fee" id="f_setup" type="number" step="0.01" value="0.00"></div>
<div><label>Billing Cycle</label><select name="billing_cycle" id="f_cycle"><option value="monthly">Monthly</option><option value="quarterly">Quarterly</option><option value="semiannual">Semi-Annual</option><option value="annual">Annual</option><option value="biennial">Biennial</option></select></div>
<div class="full" style="display:flex;gap:16px;align-items:center;padding-top:4px">
<label style="display:flex;align-items:center;gap:6px;font-size:12px;text-transform:none;letter-spacing:0;color:#e0e0e0;cursor:pointer;margin:0">
<input type="checkbox" name="is_visible" id="f_visible" value="1" checked style="width:auto"> Visible on Cart
</label>
</div>
</div>
<div style="display:flex;gap:8px;margin-top:16px">
<button type="submit" class="btn primary">Save Product</button>
<button type="button" class="btn secondary" onclick="closeModal()">Cancel</button>
</div>
</form>
</div>
</div>

<!-- Copy/Embed Modal -->
<div class="bp-modal bp-copy-modal" id="copyModal">
<div class="card">
<h3 style="color:var(--accent);margin:0 0 14px">📋 Embed Code</h3>
<p style="font-size:12px;color:#64748b;margin:0 0 14px">Share this product with a direct link or embed it on any website.</p>
<div class="copy-row">
<label>Direct Link</label>
<input type="text" id="copyLink" readonly style="width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;font-family:monospace;box-sizing:border-box">
</div>
<div class="copy-row">
<label>Embed (iframe)</label>
<textarea id="copyIframe" readonly rows="3"></textarea>
</div>
<div class="copy-row">
<label>Embed (script)</label>
<textarea id="copyScript" readonly rows="3"></textarea>
</div>
<div style="display:flex;gap:8px;margin-top:14px">
<button class="btn primary copy-btn" onclick="copyText('copyLink')">Copy Link</button>
<button class="btn secondary copy-btn" onclick="copyText('copyIframe')">Copy Iframe</button>
<button class="btn secondary copy-btn" onclick="copyText('copyScript')">Copy Script</button>
<button type="button" class="btn secondary" onclick="closeCopyModal()">Close</button>
</div>
</div>
</div>

<script>
var editingId = 0;
function openAdd() {
    editingId = 0;
    document.getElementById('bpModalTitle').textContent = 'Add Product';
    document.getElementById('bpForm').action = '/reseller/billing-system/products/store';
    ['f_name','f_desc','f_image','f_category','f_license'].forEach(function(id){document.getElementById(id).value='';});
    document.getElementById('f_type').value = 'hosting';
    document.getElementById('f_package').value = '';
    document.getElementById('f_price').value = '0.00';
    document.getElementById('f_setup').value = '0.00';
    document.getElementById('f_cycle').value = 'monthly';
    document.getElementById('f_active').value = '1';
    document.getElementById('f_visible').checked = true;
    openModal();
}
function openEdit(id,name,desc,type,cat,price,setup,cycle,active,pkg,license,image,visible) {
    editingId = id;
    document.getElementById('bpModalTitle').textContent = 'Edit Product';
    document.getElementById('bpForm').action = '/reseller/billing-system/products/update/' + id;
    document.getElementById('f_name').value = name;
    document.getElementById('f_desc').value = desc;
    document.getElementById('f_image').value = image;
    document.getElementById('f_type').value = type;
    var catSel = document.getElementById('f_category');
    for (var i = 0; i < catSel.options.length; i++) {
        if (catSel.options[i].value === cat) { catSel.selectedIndex = i; break; }
    }
    document.getElementById('f_package').value = pkg || '';
    document.getElementById('f_license').value = license;
    document.getElementById('f_price').value = price;
    document.getElementById('f_setup').value = setup;
    document.getElementById('f_cycle').value = cycle;
    document.getElementById('f_active').value = active;
    document.getElementById('f_visible').checked = !!visible;
    openModal();
}
function syncCat() {
    var t = document.getElementById('f_type');
    var c = document.getElementById('f_category');
    var map = {hosting:'Web Hosting',radio:'Radio',vps:'VPS',domain:'Domain',addon:'Addon',game:'Game Server',server:'Server',ssl:'SSL',other:'Other'};
    var target = map[t.value] || t.value;
    for (var i = 0; i < c.options.length; i++) {
        if (c.options[i].value === target) { c.selectedIndex = i; break; }
    }
}
function openModal(){ document.getElementById('bpModal').classList.add('open'); }
function closeModal(){ document.getElementById('bpModal').classList.remove('open'); }

function openCopy(id, name) {
    var base = window.location.origin;
    var link = base + '/reseller/billing/product?id=' + id;
    document.getElementById('copyLink').value = link;
    document.getElementById('copyIframe').value = '<iframe src="' + link + '" width="100%" height="600" frameborder="0" style="border:none;border-radius:8px"></iframe>';
    document.getElementById('copyScript').value = '<div id="ph-product-' + id + '"></div>\n<script src="' + base + '/reseller/billing/embed.js" data-product="' + id + '"><\/script>';
    document.getElementById('copyModal').classList.add('open');
}
function closeCopyModal(){ document.getElementById('copyModal').classList.remove('open'); }
function copyText(fieldId) {
    var el = document.getElementById(fieldId);
    el.select();
    el.setSelectionRange(0, 9999);
    document.execCommand('copy');
    if (typeof showToast === 'function') showToast('Copied!');
}

// Category filters
(function(){
    var cats = [];
    document.querySelectorAll('.bp-group').forEach(function(g){ var c=g.getAttribute('data-cat'); if(cats.indexOf(c)<0)cats.push(c); });
    var box = document.getElementById('bpCatFilters');
    if (cats.length > 1) {
        var all = document.createElement('div'); all.className='bp-cat-filter act'; all.setAttribute('data-cat',''); all.textContent='📂 All';
        all.onclick=function(){applyCat('');};
        box.appendChild(all);
        cats.forEach(function(c){
            var d=document.createElement('div'); d.className='bp-cat-filter'; d.setAttribute('data-cat',c); d.textContent=c;
            d.onclick=function(){applyCat(c);};
            box.appendChild(d);
        });
    }
    window.applyCat = function(cat){
        document.querySelectorAll('.bp-cat-filter').forEach(function(f){ f.classList.toggle('act', f.getAttribute('data-cat')===cat); });
        document.querySelectorAll('.bp-group').forEach(function(g){
            g.style.display = (!cat || g.getAttribute('data-cat')===cat) ? '' : 'none';
        });
        applySearch();
    };
})();

// Search filter
function applySearch() {
    var q = (document.getElementById('bpSearch').value||'').toLowerCase();
    document.querySelectorAll('.bp-card').forEach(function(card){
        var visible = card.textContent.toLowerCase().indexOf(q) >= 0;
        var group = card.closest('.bp-group');
        var gShown = group && group.style.display !== 'none';
        card.style.display = (visible && gShown) ? '' : 'none';
    });
}
document.getElementById('bpSearch').addEventListener('input', applySearch);
</script>