<div class="stats-grid" style="margin-bottom:24px">
<div class="stat-card"><h3>Total Tickets</h3><div class="value"><?php echo $ticketCount; ?></div></div>
<div class="stat-card"><h3>Open</h3><div class="value" style="color:#facc15"><?php echo $openCount; ?></div></div>
<div class="stat-card"><h3>KB Articles</h3><div class="value"><?php echo $articleCount; ?></div></div>
<div class="stat-card"><h3>Announcements</h3><div class="value"><?php echo $announceCount; ?></div></div>
</div>

<div class="card" style="padding:0;overflow:hidden">
<table style="margin:0">
<tr><th style="padding:14px 20px;font-size:13px;background:rgba(0,191,255,.04);border-bottom:1px solid rgba(255,255,255,.06)" colspan="2">Support Tools</th></tr>
<tr><td style="padding:0" colspan="2">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr))">
<a href="/admin/support/tickets" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">🎫</span><div><strong style="color:#fff">Tickets</strong><br><span style="font-size:12px;color:var(--text-secondary)">Customer support tickets</span></div></a>
<a href="/admin/support/kb" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">📚</span><div><strong style="color:#fff">Knowledgebase</strong><br><span style="font-size:12px;color:var(--text-secondary)">Articles and categories</span></div></a>
<a href="/admin/support/announcements" style="display:flex;align-items:center;gap:12px;padding:14px 20px;color:var(--text-table);text-decoration:none;border-right:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);transition:.15s">
<span style="font-size:20px">📢</span><div><strong style="color:#fff">Announcements</strong><br><span style="font-size:12px;color:var(--text-secondary)">System announcements</span></div></a>
</div>
</td></tr></table>
</div>
