<style>
.dns-zones{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:12px}
.dns-zone{background:var(--card_bg,rgba(8,16,28,.6));border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:16px;transition:.15s}
.dns-zone:hover{border-color:rgba(0,191,255,.25)}
.dns-zone .dz-top{display:flex;justify-content:space-between;align-items:flex-start;gap:8px}
.dns-zone .dz-name{font-size:14px;font-weight:600;color:#fff;word-break:break-all}
.dns-zone .dz-sub{font-size:11px;color:var(--text_muted,#64748b);margin-top:3px}
.dns-zone .dz-acts{display:flex;gap:6px;margin-top:12px}
.dns-zone .dz-acts a{padding:5px 10px;border-radius:6px;font-size:11px;font-weight:600;text-decoration:none}
.dns-stat{font-size:12px;display:inline-flex;align-items:center;gap:5px;padding:3px 8px;border-radius:20px}
.dns-stat.ok{background:rgba(74,222,128,.12);color:#4ade80}
.dns-stat.bad{background:rgba(248,113,113,.12);color:#f87171}
</style>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>DNS Zones</h3><div class="value"><?php echo $dnsStats['total_zones'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Records</h3><div class="value"><?php echo $dnsStats['total_records'] ?? 0; ?></div></div>
<div class="stat-card"><h3>BIND9</h3><div class="value" style="font-size:16px;color:<?php echo ($bindStatus ?? '') === 'active' ? '#4ade80' : '#f87171'; ?>"><?php echo ($bindStatus ?? 'unknown') === 'active' ? '● Running' : '○ Stopped'; ?></div></div>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px">
<a class="btn primary" onclick="document.getElementById('zoneForm').classList.toggle('hidden')"><i class="bi bi-plus-circle"></i> Create Zone</a>
<a href="/admin/dns/nameservers" class="btn secondary" style="text-decoration:none"><i class="bi bi-diagram-2"></i> Nameservers</a>
</div>

<div id="zoneForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/dns/create-zone">
<div class="form-group"><label>Domain</label><input name="domain" required placeholder="example.com"></div>
<div class="form-group"><label>Admin Email</label><input name="admin_email" value="admin@planet-hosts.com"></div>
<button type="submit" class="btn primary">Create</button>
</form></div>

<?php if (!empty($zones)): ?>
<div class="dns-zones">
<?php foreach ($zones as $z): ?>
<div class="dns-zone">
<div class="dz-top">
<div>
<div class="dz-name">🌐 <?php echo htmlspecialchars($z->domain); ?></div>
<div class="dz-sub"><?php echo (int)($z->record_count ?? 0); ?> records · serial <?php echo htmlspecialchars((string)($z->serial ?? '—')); ?></div>
</div>
<span class="dns-stat ok">Active</span>
</div>
<div class="dz-sub" style="margin-top:6px">Created <?php echo htmlspecialchars($z->created_at ?? '—'); ?></div>
<div class="dz-acts">
<a href="/admin/dns/edit/<?php echo (int)$z->id; ?>" style="background:rgba(0,140,255,.14);color:#0A84FF">✏️ Edit</a>
<a href="/admin/dns/delete/<?php echo (int)$z->id; ?>" style="background:rgba(248,113,113,.12);color:#f87171" onclick="return confirm('Delete zone <?php echo htmlspecialchars($z->domain); ?>?')">🗑 Delete</a>
</div>
</div>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="card"><div style="text-align:center;padding:30px;color:var(--text_muted,#64748b)">No DNS zones yet. Create one above.</div></div>
<?php endif; ?>