<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit DNS Zone - Planet Hosts</title>
<link rel="stylesheet" href="/theme/assets/css/style.css">
<style>
body{font-family:Inter,sans-serif;background:#000;color:#fff;margin:0;padding:40px}
.bg-overlay{position:fixed;inset:0;background:linear-gradient(rgba(2,8,23,.88),rgba(2,8,23,.96)),url(/theme/assets/img/background.png);background-size:cover;z-index:-2}
.card{background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.12);border-radius:16px;padding:32px 40px;max-width:1000px;margin:auto;position:relative;z-index:1}
h1{color:#0A84FF;margin-bottom:8px}
.zone-info{color:#94a3b8;font-size:14px;margin-bottom:24px}
table{width:100%;border-collapse:collapse;margin:16px 0}
th,td{padding:10px 12px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);font-size:14px}
th{color:#0A84FF;font-weight:600}
td{color:#cbd5e1}
tr:hover{background:rgba(255,255,255,.02)}
.badge{padding:2px 8px;border-radius:4px;font-size:12px;font-weight:600}
.badge.A{background:#1a3a2a;color:#4ade80}
.badge.AAAA{background:#1a2a3a;color:#60a5fa}
.badge.CNAME{background:#3a2a1a;color:#facc15}
.badge.MX{background:#2a1a3a;color:#c084fc}
.badge.TXT{background:#1a3a3a;color:#2dd4bf}
.badge.NS{background:#3a1a1a;color:#f87171}
.badge.SOA{background:#2a2a2a;color:#94a3b8}
.btn{padding:10px 20px;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;transition:.3s;text-decoration:none;display:inline-block}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px)}
.btn.secondary{background:rgba(255,255,255,.06);color:#ccc;border:1px solid rgba(255,255,255,.1)}
.btn.small{padding:6px 12px;font-size:12px}
.btn.danger{background:rgba(255,50,50,.15);color:#ff6b6b;border:1px solid rgba(255,50,50,.2)}
.alert-success{background:rgba(50,255,50,.08);border:1px solid rgba(50,255,50,.2);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#4ade80;font-size:14px}
.row{display:flex;gap:10px;flex-wrap:wrap;align-items:end}
.form-group{margin-bottom:12px}
.form-group label{display:block;color:#94a3b8;font-size:12px;font-weight:600;margin-bottom:4px}
.form-group input,.form-group select{width:100%;padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;font-size:13px;outline:none;box-sizing:border-box}
</style>
</head>
<body>
<div class="bg-overlay"></div>
<div class="card">
<h1>DNS Zone Editor</h1>
<div class="zone-info"><?php echo htmlspecialchars($zone->domain, ENT_QUOTES, 'UTF-8'); ?> &middot; Serial: <?php echo $zone->serial ?? 'N/A'; ?></div>

<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<h2 style="font-size:18px;margin-bottom:12px;">Records</h2>
<table>
<tr><th>Name</th><th>Type</th><th>Value</th><th>TTL</th><th>Priority</th><th></th></tr>
<?php if (!empty($records)): foreach ($records as $r): ?>
<tr>
<td><?php echo htmlspecialchars($r->name, ENT_QUOTES, 'UTF-8'); ?></td>
<td><span class="badge <?php echo $r->type; ?>"><?php echo $r->type; ?></span></td>
<td style="font-family:monospace;font-size:13px"><?php echo htmlspecialchars($r->value, ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo $r->ttl; ?></td>
<td><?php echo $r->priority ?? '-'; ?></td>
<td><a href="/admin/dns/delete-record/<?php echo $zone->id; ?>/<?php echo $r->id; ?>" class="btn small danger" onclick="return confirm('Delete record?')">Delete</a></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;color:#64748b;padding:20px">No records yet.</td></tr>
<?php endif; ?>
</table>

<h2 style="font-size:18px;margin:24px 0 12px">Add Record</h2>
<form method="POST" action="/admin/dns/add-record/<?php echo $zone->id; ?>">
<div style="display:grid;grid-template-columns:1fr 100px 2fr 80px 80px;gap:10px;align-items:end;flex-wrap:wrap;margin-bottom:12px">
<div class="form-group"><label>Name</label><input name="name" placeholder="@ or subdomain"></div>
<div class="form-group"><label>Type</label><select name="type"><option>A</option><option>AAAA</option><option>CNAME</option><option>MX</option><option>TXT</option><option>NS</option></select></div>
<div class="form-group"><label>Value</label><input name="value" placeholder="IP or target"></div>
<div class="form-group"><label>TTL</label><input name="ttl" value="300"></div>
<div class="form-group"><label>Priority</label><input name="priority" placeholder="-"></div>
</div>
<button type="submit" class="btn primary">Add Record</button>
</form>

<div style="margin-top:24px">
<a href="/admin/dns" class="btn secondary">&larr; Back to Zones</a>
</div>
</div>
</body>
</html>
