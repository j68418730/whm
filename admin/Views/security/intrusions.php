<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.id-sum{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;margin-bottom:18px}
.id-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:16px;text-align:center}
.id-stat .v{font-size:26px;font-weight:800}.id-stat .l{font-size:11px;color:#64748b;margin-top:2px}
.id-stat .v.red{color:#f87171}.id-stat .v.yellow{color:#facc15}.id-stat .v.blue{color:#38bdf8}.id-stat .v.green{color:#4ade80}
.id-table{width:100%;border-collapse:collapse;font-size:13px}
.id-table th{text-align:left;padding:8px;color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid rgba(255,255,255,.08)}
.id-table td{padding:8px;border-bottom:1px solid rgba(255,255,255,.04);vertical-align:top}
.sev{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
.sev.critical{background:rgba(248,113,113,.15);color:#f87171}
.sev.high{background:rgba(251,146,60,.15);color:#fb923c}
.sev.medium{background:rgba(250,204,21,.12);color:#facc15}
.type-tag{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;background:rgba(0,140,255,.12);color:#38bdf8;font-weight:600}
.detail{color:#94a3b8;font-size:12px;word-break:break-all;max-width:420px}
.chips{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.chip{padding:4px 12px;border-radius:999px;font-size:12px;text-decoration:none;background:rgba(255,255,255,.05);color:#94a3b8;border:1px solid rgba(255,255,255,.06)}
.chip.active,.chip:hover{background:rgba(0,140,255,.15);color:#0A84FF;border-color:rgba(0,140,255,.3)}
.empty{padding:30px;text-align:center;color:#64748b}
.mono{font-family:monospace;font-size:12px}
</style>

<h2>🛡️ Intrusion Detection</h2>
<p style="color:#64748b;margin-bottom:14px">Real-time attack monitor — brute-force, SQL injection, file inclusion, XSS, scanners, and auth abuse detected from Apache logs. <a href="/admin/security" style="color:#0A84FF">← Security Center</a></p>

<div class="id-sum">
  <div class="id-stat"><div class="v red"><?php echo (int)$summary->total; ?></div><div class="l">Total Events</div></div>
  <div class="id-stat"><div class="v red"><?php echo (int)$summary->crit; ?></div><div class="l">Critical</div></div>
  <div class="id-stat"><div class="v yellow"><?php echo (int)$summary->open; ?></div><div class="l">Unresolved</div></div>
  <div class="id-stat"><div class="v blue"><?php echo (int)$summary->ips; ?></div><div class="l">Attackers (IPs)</div></div>
</div>

<div class="chips">
  <a class="chip <?php echo $typeFilter ? '' : 'active'; ?>" href="/admin/security/intrusions">All</a>
  <?php foreach ($types as $t): ?>
  <a class="chip <?php echo $typeFilter === $t->type ? 'active' : ''; ?>" href="/admin/security/intrusions?type=<?php echo urlencode($t->type); ?>"><?php echo htmlspecialchars($t->type); ?> (<?php echo (int)$t->c; ?>)</a>
  <?php endforeach; ?>
  <?php if ($typeFilter): ?><a class="chip" href="/admin/security/intrusions?limit=200">Reset</a><?php endif; ?>
  <span style="flex:1"></span>
  <form method="POST" action="/admin/security/intrusions/resolve" style="display:inline" onsubmit="return confirm('Mark ALL unresolved events as resolved?')">
    <input type="hidden" name="all" value="1">
    <button class="chip" style="cursor:pointer">✓ Mark All Resolved</button>
  </form>
</div>

<?php if (empty($events)): ?>
<div class="empty">No intrusion events recorded. The monitor runs every minute and alerts by email on critical attacks.</div>
<?php else: ?>
<table class="id-table">
<tr><th>Severity</th><th>Type</th><th>IP</th><th>Details</th><th>Count</th><th>First / Last</th><th>Status</th><th></th></tr>
<?php foreach ($events as $e): ?>
<tr>
  <td><span class="sev <?php echo $e->severity; ?>"><?php echo $e->severity; ?></span></td>
  <td><span class="type-tag"><?php echo htmlspecialchars($e->type); ?></span></td>
  <td class="mono"><?php echo htmlspecialchars($e->ip); ?></td>
  <td class="detail"><?php echo htmlspecialchars($e->details ?? ''); ?></td>
  <td><?php echo (int)$e->count; ?></td>
  <td class="mono" style="font-size:11px;color:#64748b"><?php echo $e->first_seen; ?><br><?php echo $e->last_seen; ?></td>
  <td><?php if ($e->resolved): ?><span style="color:#4ade80">Resolved</span><?php else: ?><span style="color:#f87171">Open</span><?php endif; ?>
      <?php if ($e->emailed): ?><br><span style="color:#64748b;font-size:10px">✉ alerted</span><?php endif; ?></td>
  <td><?php if (!$e->resolved): ?>
      <form method="POST" action="/admin/security/intrusions/resolve" style="display:inline"><input type="hidden" name="id" value="<?php echo (int)$e->id; ?>"><button class="chip" style="cursor:pointer">✓</button></form>
      <?php endif; ?></td>
</tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<p style="color:#64748b;font-size:12px;margin-top:14px">The monitor only detects and alerts — it never blocks traffic. Blocking is handled by <a href="/admin/firewall" style="color:#0A84FF">fail2ban</a>. Critical events also email <code>root@planet-hosts.com</code> (max 1 per 15 minutes).</p>
