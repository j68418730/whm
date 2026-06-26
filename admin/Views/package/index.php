<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<!-- Quick Stats -->
<div class="stats-grid" style="margin-bottom:16px;grid-template-columns:repeat(auto-fit,minmax(130px,1fr))">
<div class="stat-card"><h3>Total</h3><div class="value"><?php echo $totalPackages; ?></div></div>
<div class="stat-card"><h3>Active</h3><div class="value" style="color:#4ade80"><?php echo $activePackages; ?></div></div>
<div class="stat-card"><h3>Hosting</h3><div class="value"><?php echo $statsByType['hosting'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Streaming</h3><div class="value" style="color:#a855f7"><?php echo $statsByType['streaming'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Game</h3><div class="value" style="color:#4ade80"><?php echo $statsByType['game'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Addon</h3><div class="value" style="color:#fbbf24"><?php echo $statsByType['addon'] ?? 0; ?></div></div>
</div>

<!-- Toolbar -->
<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<a href="/admin/package/create" class="btn primary"><i class="bi bi-plus-circle"></i> Create Package</a>
<a href="/admin/packages/categories" class="btn secondary"><i class="bi bi-tags"></i> Categories</a>
<div style="flex:1;min-width:200px;position:relative">
<i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px"></i>
<input type="text" id="pkgSearch" placeholder="Search packages..." oninput="filterPackages(this.value)" style="width:100%;padding:8px 10px 8px 30px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none">
</div>
<select id="filterType" onchange="filterPackages()" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<option value="">All Types</option>
<?php foreach ($categories as $cat): ?>
<option value="<?php echo htmlspecialchars($cat->name); ?>"><?php echo htmlspecialchars($cat->name); ?></option>
<?php endforeach; ?>
</select>
<select id="filterStatus" onchange="filterPackages()" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<option value="">All Status</option>
<option value="active">Active</option>
<option value="inactive">Inactive</option>
<option value="hidden">Hidden</option>
</select>
<select id="filterCycle" onchange="filterPackages()" style="padding:8px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px">
<option value="">All Billing</option>
<option value="monthly">Monthly</option>
<option value="quarterly">Quarterly</option>
<option value="semi_annual">Semi-Annual</option>
<option value="annual">Annual</option>
<option value="one_time">One Time</option>
</select>
<div class="btn-group" style="display:flex;gap:4px">
<button class="btn btn-sm secondary" onclick="sortPackages('name')" style="font-size:11px">Name</button>
<button class="btn btn-sm secondary" onclick="sortPackages('price')" style="font-size:11px">Price</button>
<button class="btn btn-sm secondary" onclick="sortPackages('created_at')" style="font-size:11px">Newest</button>
</div>
</div>

<!-- Bulk Actions -->
<div id="bulkActions" style="display:none;margin-bottom:12px;padding:10px;background:rgba(0,140,255,.06);border:1px solid rgba(0,140,255,.15);border-radius:8px;align-items:center;gap:8px;flex-wrap:wrap">
<span style="font-size:12px;color:#94a3b8"><span id="bulkCount">0</span> selected</span>
<button class="btn btn-sm secondary" onclick="bulkAction('enable')">Enable</button>
<button class="btn btn-sm secondary" onclick="bulkAction('disable')">Disable</button>
<button class="btn btn-sm secondary" onclick="bulkAction('clone')">Clone</button>
<button class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;font-size:11px;padding:4px 10px" onclick="if(confirm(\'Delete selected?\'))bulkAction(\'delete\')">Delete</button>
<button class="btn btn-sm secondary" onclick="document.querySelectorAll(\".pkg-checkbox:checked\").forEach(function(c){c.checked=false});document.getElementById(\"bulkActions\").style.display=\"none\"">Cancel</button>
</div>

<!-- Packages Grid -->
<div id="packageGrid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px">
<?php foreach ($packages as $p): ?>
<div class="pkg-item" data-id="<?php echo $p->id; ?>" data-type="<?php echo htmlspecialchars($p->type); ?>" data-status="<?php echo $p->is_active ? 'active' : 'inactive'; ?>" data-cycle="<?php echo htmlspecialchars($p->billing_cycle ?? 'monthly'); ?>" data-price="<?php echo $p->monthly_price; ?>">
<div class="card" style="padding:16px;margin-bottom:0;position:relative;overflow:visible">
<div style="position:absolute;top:8px;left:8px;display:none"><input type="checkbox" class="pkg-checkbox" value="<?php echo $p->id; ?>" onchange="updateBulk()"></div>
<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px">
<div>
<div style="font-weight:700;font-size:15px"><?php echo htmlspecialchars($p->name); ?></div>
<div style="font-size:11px;color:#64748b"><?php echo htmlspecialchars($p->type); ?></div>
</div>
<div style="text-align:right">
<div style="font-size:20px;font-weight:800;color:<?php echo $p->monthly_price > 0 ? '#4ade80' : '#94a3b8'; ?>">$<?php echo number_format($p->monthly_price, 2); ?><small style="font-size:11px;color:#64748b;font-weight:400">/mo</small></div>
<?php if ($p->setup_fee > 0): ?><div style="font-size:10px;color:#64748b">+$<?php echo number_format($p->setup_fee, 2); ?> setup</div><?php endif; ?>
</div>
</div>
<?php
$colorMap = ['hosting'=>'#0A84FF','radio'=>'#a855f7','vps'=>'#ef4444','game'=>'#4ade80','addon'=>'#fbbf24','shoutcast'=>'#a855f7','icecast'=>'#a855f7','web_hosting'=>'#0A84FF','web_reseller'=>'#0A84FF','chat_room'=>'#fbbf24','dj_panel'=>'#fbbf24','game_server'=>'#4ade80'];
$badgeColor = $colorMap[$p->type] ?? '#64748b';
$badgeBg = substr($badgeColor, 0, 7) . '18';
$isActive = ($p->is_active ?? 1) && ($p->pkg_status ?? 'active') !== 'hidden';
?>
<div style="display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap">
<span style="padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600;background:<?php echo $badgeBg; ?>;color:<?php echo $badgeColor; ?>"><?php echo htmlspecialchars($p->type); ?></span>
<span style="padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600;background:<?php echo $isActive ? 'rgba(74,222,128,.12)' : 'rgba(248,113,113,.12)'; ?>;color:<?php echo $isActive ? '#4ade80' : '#f87171'; ?>"><?php echo $isActive ? 'Active' : ($p->pkg_status ?? 'Inactive'); ?></span>
<?php if ($p->featured): ?><span style="padding:2px 10px;border-radius:4px;font-size:10px;font-weight:600;background:rgba(250,204,21,.12);color:#fbbf24">Featured</span><?php endif; ?>
</div>
<div style="font-size:11px;color:#94a3b8;line-height:1.6;margin-bottom:8px">
<?php if ($p->disk_space): ?>📁 <?php echo $p->disk_space; ?> GB Disk<br><?php endif; ?>
<?php if ($p->bandwidth): ?>📶 <?php echo $p->bandwidth; ?> GB Bandwidth<br><?php endif; ?>
<?php if ($p->listener_limit): ?>🎧 <?php echo $p->listener_limit; ?> Listeners<br><?php endif; ?>
<?php if ($p->dj_accounts): ?>🎤 <?php echo $p->dj_accounts; ?> DJs<br><?php endif; ?>
<?php if ($p->chatroom_enabled): ?>💬 Chat<br><?php endif; ?>
<?php if ($p->game_enabled): ?>🎮 Games<br><?php endif; ?>
</div>

<!-- Used By -->
<div style="font-size:10px;color:#64748b;margin-bottom:8px;padding-top:6px;border-top:1px solid rgba(255,255,255,.04)">
<strong>Used By:</strong>
<?php
$usedBy = $usageCounts[$p->id] ?? [];
$usedParts = [];
if (!empty($usedBy['accounts'])) $usedParts[] = $usedBy['accounts'] . ' accounts';
if (!empty($usedBy['resellers'])) $usedParts[] = $usedBy['resellers'] . ' resellers';
if (!empty($usedBy['stations'])) $usedParts[] = $usedBy['stations'] . ' stations';
echo $usedParts ? implode(', ', $usedParts) : '<span style="color:#475569">None</span>';
?>
</div>

<!-- Actions -->
<div style="display:flex;gap:4px;flex-wrap:wrap;margin-top:8px;padding-top:8px;border-top:1px solid rgba(255,255,255,.04)">
<a href="/admin/package/edit/<?php echo $p->id; ?>" class="btn btn-sm" style="background:rgba(0,140,255,.1);color:#38bdf8;font-size:11px;padding:4px 10px;text-decoration:none;border-radius:4px">Edit</a>
<button class="btn btn-sm" style="background:rgba(74,222,128,.1);color:#4ade80;font-size:11px;padding:4px 10px;border:none;border-radius:4px;cursor:pointer" onclick="clonePkg(<?php echo $p->id; ?>)">Clone</button>
<button class="btn btn-sm" style="background:<?php echo $isActive ? 'rgba(248,113,113,.1)' : 'rgba(74,222,128,.1)'; ?>;color:<?php echo $isActive ? '#f87171' : '#4ade80'; ?>;font-size:11px;padding:4px 10px;border:none;border-radius:4px;cursor:pointer" onclick="togglePkg(<?php echo $p->id; ?>)"><?php echo $isActive ? 'Disable' : 'Enable'; ?></button>
<a href="/admin/package/delete/<?php echo $p->id; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.12);color:#f87171;font-size:11px;padding:4px 10px;text-decoration:none;border-radius:4px" onclick="return confirm('Delete <?php echo htmlspecialchars($p->name); ?>?')">Delete</a>
</div>
</div>
</div>
<?php endforeach; ?>
</div>

<?php if (empty($packages)): ?>
<div class="card" style="text-align:center;padding:40px">
<p style="color:#64748b">No packages yet. <a href="/admin/package/create">Create your first package</a></p>
</div>
<?php endif; ?>

<script>
var allPkgs = [];
document.querySelectorAll('.pkg-item').forEach(function(el) { allPkgs.push(el); });

function filterPackages(search) {
    var q = (search || document.getElementById('pkgSearch').value || '').toLowerCase();
    var type = document.getElementById('filterType').value;
    var status = document.getElementById('filterStatus').value;
    var cycle = document.getElementById('filterCycle').value;
    allPkgs.forEach(function(p) {
        var show = true;
        if (q && !p.querySelector('.card .p-name')?.textContent.toLowerCase().includes(q) && !p.dataset.type.includes(q)) show = false;
        if (type && p.dataset.type !== type) show = false;
        if (status && p.dataset.status !== status) show = false;
        if (cycle && p.dataset.cycle !== cycle) show = false;
        p.style.display = show ? '' : 'none';
    });
}

function sortPackages(by) {
    var grid = document.getElementById('packageGrid');
    var items = Array.from(grid.children);
    items.sort(function(a, b) {
        if (by === 'price') return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
        if (by === 'name') return a.querySelector('.card .p-name')?.textContent.localeCompare(b.querySelector('.card .p-name')?.textContent);
        return 0;
    });
    items.forEach(function(item) { grid.appendChild(item); });
}

function clonePkg(id) {
    if (!confirm('Clone this package?')) return;
    fetch('/admin/packages/clone/' + id, {method:'POST'}).then(function(r){return r.json()}).then(function(d){
        if (d.success) { location.reload(); }
        else { alert(d.error || 'Clone failed'); }
    }).catch(function(){alert('Request failed')});
}

function togglePkg(id) {
    fetch('/admin/packages/toggle/' + id, {method:'POST'}).then(function(r){return r.json()}).then(function(d){
        if (d.success) location.reload();
    }).catch(function(){});
}

function updateBulk() {
    var checked = document.querySelectorAll('.pkg-checkbox:checked').length;
    var el = document.getElementById('bulkActions');
    if (checked > 0) { el.style.display = 'flex'; document.getElementById('bulkCount').textContent = checked; }
    else { el.style.display = 'none'; }
}

function bulkAction(action) {
    var ids = [];
    document.querySelectorAll('.pkg-checkbox:checked').forEach(function(c) { ids.push(c.value); });
    if (!ids.length) return;
    fetch('/admin/packages/bulk', {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({action:action, ids:ids})})
    .then(function(r){return r.json()}).then(function(d){
        if (d.success) location.reload();
        else alert(d.error || 'Action failed');
    }).catch(function(){});
}
</script>
