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
<a href="/reseller/chat-system" class="section-card"><div class="icon">💬</div><div class="name">Chat Dashboard</div><div class="desc">Clients &amp; chat boxes</div></a>
<a href="/reseller/clients" class="section-card"><div class="icon">👥</div><div class="name">Clients</div><div class="desc">Client accounts</div></a>
</div>

<h3 style="color:var(--accent);margin-bottom:12px">Chat System</h3>
<p style="color:var(--text_muted,#94a3b8);font-size:13px;margin-bottom:16px">Manage live chat boxes for <b>your own clients</b>. Copy the embed script into their website — visitors chat through their tenant, you moderate through the chat admin panel, and messages never leak across resellers.</p>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">➕ Create Chat Box</h4>
<form method="POST" action="/reseller/chat-system/create">
<div class="row g-3">
<div class="col-md-6">
<label class="form-label">Client *</label>
<select name="client_id" required>
<?php foreach ($clients as $c): ?>
<option value="<?php echo (int)$c->user_id; ?>"><?php echo htmlspecialchars($c->username); ?></option>
<?php endforeach; ?>
</select>
</div>
<div style="padding-left:14px"><button type="submit" class="btn btn-primary">Create Chat Box</button></div>
</div>
</form>
<p style="color:#64748b;font-size:12px;margin-top:8px">Multiple chat boxes per client allowed. Voice/Video enabled if client's package includes it.</p>
</div>

<div class="card">
<h4 style="color:var(--accent);margin-bottom:12px">Client Chat Boxes</h4>
<table class="table">
<tr><th>Client</th><th>Chat</th><th>Color</th><th>Voice</th><th>Video</th><th>Status</th><th>Embed Script</th><th></th></tr>
<?php if (!empty($clients)): foreach ($clients as $c): ?>
<?php if ($c->tenant_id): ?>
<tr>
<td><?php echo htmlspecialchars($c->username); ?></td>
<td><?php echo htmlspecialchars($c->widget_title ?: 'Live Chat'); ?></td>
<td><span style="display:inline-block;width:16px;height:16px;border-radius:4px;background:<?php echo htmlspecialchars($c->widget_color ?: '#008cff'); ?>"></span></td>
<td><?php echo !empty($c->voice_enabled) ? '✅' : '❌'; ?></td>
<td><?php echo !empty($c->video_enabled) ? '✅' : '❌'; ?></td>
<td><span class="status-badge status-<?php echo $c->is_active ? 'active' : 'terminated'; ?>"><?php echo $c->is_active ? 'Active' : 'Disabled'; ?></span></td>
<td><code style="font-size:11px"><script src="/chatbox/widget.js.php?tenant_id=<?php echo (int)$c->tenant_id; ?>"></script></code>
<button class="btn btn-sm secondary" style="padding:2px 8px;font-size:11px" onclick="navigator.clipboard.writeText('<script src=\"/chatbox/widget.js.php?tenant_id=<?php echo (int)$c->tenant_id; ?>\"></script>')">Copy</button></td>
<td><a href="/reseller/chat-system/toggle/<?php echo (int)$c->tenant_id; ?>" class="btn btn-sm <?php echo $c->is_active ? 'danger' : 'primary'; ?>"><?php echo $c->is_active ? 'Disable' : 'Enable'; ?></a></td>
</tr>
<?php else: ?>
<tr>
<td><?php echo htmlspecialchars($c->username); ?></td>
<td colspan="7" style="color:#64748b">No chat box yet — <a href="/reseller/chat-system/create?client_id=<?php echo (int)$c->user_id; ?>" style="color:#0A84FF">Create one</a></td>
</tr>
<?php endif; ?>
<?php endforeach; else: ?>
<tr><td colspan="8" style="text-align:center;padding:20px;color:#64748b">No clients.</td></tr>
<?php endif; ?>
</table>
</div>
<a href="/reseller" class="btn secondary" style="margin-top:12px">&larr; Back</a>