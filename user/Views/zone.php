<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></div>
<?php endif; ?>
<div style="margin-bottom:12px"><a href="/user/domains" class="btn btn-sm secondary">&larr; Back</a></div>
<?php if ($zone): ?>
<div class="card" style="margin-bottom:16px">
<h3 style="color:var(--accent)"><?php echo htmlspecialchars($zone->domain); ?></h3>
<p style="color:var(--text-muted);font-size:13px">Serial: <?php echo $zone->serial ?? '-'; ?> | TTL: <?php echo $zone->ttl ?? 300; ?>s</p>
</div>
<div class="card" style="margin-bottom:16px">
<h4 style="color:var(--accent);margin-bottom:8px">DNS Records</h4>
<table><tr><th>Name</th><th>Type</th><th>Value</th><th>TTL</th><th></th></tr>
<?php foreach ($records as $r): ?>
<tr><td><?php echo htmlspecialchars($r->name); ?></td><td><span style="color:var(--accent)"><?php echo $r->type; ?></span></td>
<td style="font-family:monospace;font-size:13px"><?php echo htmlspecialchars($r->value); ?></td><td><?php echo $r->ttl; ?></td>
<td><a href="/user/domains/record/delete/<?php echo $zone->id; ?>/<?php echo $r->id; ?>" class="btn btn-sm danger" style="padding:3px 8px;font-size:11px" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; ?></table>
</div>
<div class="card">
<h4 style="color:var(--accent);margin-bottom:8px">Add Record</h4>
<form method="POST" action="/user/domains/record/<?php echo $zone->id; ?>">
<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1;min-width:100px"><label>Name</label><input name="name" value="@" style="padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group" style="flex:0 0 80px"><label>Type</label><select name="type" style="padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"><option>A</option><option>AAAA</option><option>CNAME</option><option>MX</option><option>TXT</option><option>NS</option></select></div>
<div class="form-group" style="flex:2;min-width:150px"><label>Value</label><input name="value" required style="padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group" style="flex:0 0 60px"><label>TTL</label><input name="ttl" value="300" style="width:60px;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none"></div>
<div class="form-group"><button type="submit" class="btn primary btn-sm">Add</button></div>
</div></form>
</div>
<?php endif; ?>
