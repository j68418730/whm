<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;flex-wrap:wrap;gap:12px">
<div>
<h2 style="margin:0">🌐 Game Nodes</h2>
<p style="color:#64748b;margin:4px 0 0">Remote destinations where game servers run. Agents connect OUT to this panel — no inbound ports or ISP port-forwarding needed.</p>
</div>
<a href="/admin/games" class="btn secondary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-arrow-left"></i> Back to Game Servers</a>
<div style="display:flex;gap:8px">
<a href="/admin/games/nodes/agent-zip" class="btn primary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-download"></i> Download Windows Agent (.exe)</a>
</div>
</div>

<div class="alert alert-info" style="background:rgba(0,191,255,.08);border:1px solid rgba(0,191,255,.2);color:#7dd3fc;padding:14px 16px;border-radius:8px;font-size:13px;margin-bottom:24px">
<b>How it works:</b> install the agent on a remote server or local computer (Node.js), put this panel URL + the node token in its config, and start it. The agent polls the panel and runs the game commands locally. Create a server and choose this node — install/start/stop/restart/etc. are all controlled from here.
</div>

<!-- Add Node -->
<div class="card" style="max-width:720px;margin-bottom:24px;padding:20px">
<h4 style="margin:0 0 14px;color:var(--accent)"><i class="bi bi-plus-circle"></i> Add Remote Node</h4>
<form method="POST" action="/admin/games/nodes/store">
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px">
<div><label>Node Name</label><input type="text" name="name" placeholder="e.g. Home PC" required></div>
<div><label>Address (optional)</label><input type="text" name="address" placeholder="remote IP / LAN or Tailscale IP"></div>
<div><label>Type</label><select name="type"><option value="remote">Remote</option><option value="local">Local (this server)</option></select></div>
</div>
<button type="submit" class="btn primary" style="margin-top:14px"><i class="bi bi-plus"></i> Add Node</button>
</form>
</div>

<!-- Node List -->
<?php if (empty($nodes)): ?>
<div class="card" style="padding:30px;text-align:center;color:#64748b">No nodes yet — add one above, then install the agent on the machine.</div>
<?php else: ?>
<div class="card" style="overflow:hidden">
<table class="table" style="margin-bottom:0">
<thead><tr><th>Name</th><th>Address</th><th>Type</th><th>Status</th><th>Last Seen</th><th>Token</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($nodes as $n):
    $fresh = $n->last_seen && (time() - strtotime($n->last_seen)) < 120;
    $st = $n->status === 'online' && $fresh ? 'online' : ($n->status === 'disabled' ? 'disabled' : 'offline');
    $color = $st === 'online' ? '#4ade80' : ($st === 'disabled' ? '#f87171' : '#94a3b8');
?>
<tr>
<td><strong><?php echo htmlspecialchars($n->name); ?></strong></td>
<td><?php echo htmlspecialchars($n->address ?: '—'); ?></td>
<td><?php echo $n->type === 'local' ? 'Local' : 'Remote'; ?></td>
<td><span style="color:<?php echo $color; ?>">● <?php echo $st; ?></span></td>
<td><?php echo $n->last_seen ? htmlspecialchars($n->last_seen) : '—'; ?></td>
<td><code style="font-size:11px"><?php echo htmlspecialchars(substr($n->token, 0, 16)); ?>…</code>
<button class="btn btn-sm secondary" style="font-size:11px;margin-left:4px" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($n->token); ?>');this.textContent='Copied'">Copy</button></td>
<td>
<form method="POST" action="/admin/games/nodes/test/<?php echo (int)$n->id; ?>" style="display:inline"><button class="btn btn-sm secondary"><i class="bi bi-activity"></i> Test</button></form>
<form method="POST" action="/admin/games/nodes/delete/<?php echo (int)$n->id; ?>" style="display:inline" onsubmit="return confirm('Remove this node? Servers on it are un-linked.');"><button class="btn btn-sm danger"><i class="bi bi-trash"></i></button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
