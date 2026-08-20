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
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/games/nodes/agent-installer" class="btn primary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-download"></i> Windows Installer (.exe)</a>
<a href="/admin/games/nodes/agent-zip" class="btn secondary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-file-earmark-zip"></i> Windows Package (.zip)</a>
<a href="/admin/games/nodes/agent-linux" class="btn secondary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-download"></i> Linux Package (.zip)</a>
<a href="/admin/games/nodes/agent-macos" class="btn secondary" style="text-decoration:none;padding:9px 16px;border-radius:8px"><i class="bi bi-download"></i> macOS Package (.zip)</a>
</div>
</div>

<div class="alert alert-info" style="background:rgba(0,191,255,.08);border:1px solid rgba(0,191,255,.2);color:#7dd3fc;padding:14px 16px;border-radius:8px;font-size:13px;margin-bottom:24px">
<b>How it works:</b> download the installer for the node's OS, run it, and fill in the panel URL + this node's token (generate one below with <b>Gen</b>). The agent connects OUT to the panel over HTTPS (no inbound ports), polls for jobs, and runs the game commands locally. Steam login is <b>only</b> required in the installer if the game must be purchased on Steam — otherwise it uses anonymous. The panel's own settings are never sent to the agent.
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
<div class="card" style="overflow:auto">
<table class="table" style="margin-bottom:0;min-width:1100px">
<thead><tr>
<th>Name</th><th>Type</th><th>Status</th><th>Last Seen</th><th>Connected IP</th><th>Location</th><th>Token</th><th>Actions</th>
</tr></thead>
<tbody>
<?php foreach ($nodes as $n):
    $fresh = $n->last_seen && (time() - strtotime($n->last_seen)) < 120;
    $st = $n->status === 'online' && $fresh ? 'online' : ($n->status === 'disabled' ? 'disabled' : 'offline');
    $color = $st === 'online' ? '#4ade80' : ($st === 'disabled' ? '#f87171' : '#94a3b8');
    $flag = '';
    $locParts = [];
    if (!empty($n->geo_iso) && strlen($n->geo_iso) === 2) {
        $iso = strtoupper($n->geo_iso);
        if (function_exists('mb_chr')) {
            $flag = mb_chr(127397 + ord($iso[0])) . mb_chr(127397 + ord($iso[1]));
        } else {
            $flag = '&#127757;';
        }
        $locParts[] = $iso;
    } elseif (!empty($n->geo_country)) {
        $locParts[] = $n->geo_country;
    }
    if (!empty($n->geo_city)) { array_unshift($locParts, $n->geo_city); }
    $loc = implode(', ', $locParts);
?>
<tr>
<td><strong><?php echo htmlspecialchars($n->name); ?></strong>
<?php if ($n->address): ?><br><small style="color:#64748b"><?php echo htmlspecialchars($n->address); ?></small><?php endif; ?></td>
<td><?php echo $n->type === 'local' ? 'Local' : 'Remote'; ?></td>
<td><span style="color:<?php echo $color; ?>">● <?php echo $st; ?></span></td>
<td><?php echo $n->last_seen ? htmlspecialchars($n->last_seen) : '—'; ?></td>
<td><?php echo $n->last_ip ? htmlspecialchars($n->last_ip) : '—'; ?></td>
<td><?php echo $loc ? $flag . ' ' . htmlspecialchars($loc) : '—'; ?></td>
<td><code style="font-size:11px"><?php echo $n->token ? htmlspecialchars(substr($n->token, 0, 16)) . '…' : '<i style="color:#f87171">no token</i>'; ?></code></td>
<td style="white-space:nowrap">
<div style="display:flex;gap:4px;flex-wrap:wrap">
<form method="POST" action="/admin/games/nodes/test/<?php echo (int)$n->id; ?>" style="display:inline"><button class="btn btn-sm secondary" title="Test"><i class="bi bi-activity"></i></button></form>
<form method="POST" action="/admin/games/nodes/token-gen/<?php echo (int)$n->id; ?>" style="display:inline" onsubmit="return confirm('Generate a NEW token? The old one stops working — update the agent config.');"><button class="btn btn-sm secondary" title="Generate token"><i class="bi bi-key"></i> Gen</button></form>
<form method="POST" action="/admin/games/nodes/token-del/<?php echo (int)$n->id; ?>" style="display:inline" onsubmit="return confirm('Delete this node token? The agent can no longer connect until a new one is generated.');"><button class="btn btn-sm danger" title="Delete token"><i class="bi bi-x-circle"></i> Del</button></form>
<form method="POST" action="/admin/games/nodes/delete/<?php echo (int)$n->id; ?>" style="display:inline" onsubmit="return confirm('Remove this node? Servers on it are un-linked.');"><button class="btn btn-sm danger" title="Remove node"><i class="bi bi-trash"></i></button></form>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php endif; ?>
