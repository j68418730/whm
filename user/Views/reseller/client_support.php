<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px;margin-bottom:20px}
.section-card{background:var(--card_bg,rgba(8,16,28,.85));border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:18px;text-align:center;text-decoration:none;color:var(--text,#e0e0e0);transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:28px;margin-bottom:6px}
.section-card .name{font-size:13px;font-weight:600;margin-bottom:2px}
.section-card .count{font-size:26px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:10px;color:#64748b}
</style>

<div class="section-grid">
<a href="/reseller/support-system" class="section-card"><div class="icon">🎯</div><div class="name">Support Center</div><div class="count"><?php echo count($tickets ?? []); ?></div><div class="desc">Client tickets</div></a>
<a href="/reseller/support-chat" class="section-card"><div class="icon">💬</div><div class="name">Live Chat</div><div class="desc">Real-time visitor chat</div></a>
<a href="/reseller/clients" class="section-card"><div class="icon">👥</div><div class="name">Clients</div><div class="desc">Client accounts</div></a>
</div>

<h3 style="color:var(--accent);margin-bottom:12px">Support System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">Tickets from <b>your own clients</b>. Client tickets route to you first; anything you escalate goes up to Planet Hosts support (two-level support).</p>
<table class="table">
<tr><th>#</th><th>Client</th><th>Subject</th><th>Priority</th><th>Status</th><th>Date</th></tr>
<?php if (!empty($tickets)): foreach ($tickets as $t): ?>
<tr>
<td><?php echo (int)$t->id; ?></td>
<td><?php echo htmlspecialchars($t->customer); ?></td>
<td><?php echo htmlspecialchars($t->subject); ?></td>
<td><?php echo htmlspecialchars($t->priority ?: '-'); ?></td>
<td><span class="status-badge status-<?php echo $t->status === 'closed' ? 'terminated' : 'active'; ?>"><?php echo htmlspecialchars($t->status); ?></span></td>
<td><?php echo $t->created_at; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="6" style="text-align:center;padding:20px;color:#64748b">No client tickets found.</td></tr>
<?php endif; ?>
</table>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>