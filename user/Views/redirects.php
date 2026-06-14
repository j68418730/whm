<div class="card"><h3 style="color:var(--accent);margin-bottom:12px">Redirects</h3>
<p style="color:var(--text-secondary);margin-bottom:12px">Manage URL redirects for your domains.</p>
<?php $redirects = $redirects ?? []; ?>
<table><tr><th>Source</th><th>Destination</th><th>Type</th><th></th></tr>
<?php if (!empty($redirects)): foreach ($redirects as $r): ?>
<tr><td><?php echo htmlspecialchars($r->source ?? ''); ?></td>
<td><?php echo htmlspecialchars($r->destination ?? ''); ?></td>
<td><?php echo htmlspecialchars($r->type ?? '301'); ?></td>
<td><a href="/user/redirects/delete/<?php echo $r->id; ?>" class="btn btn-sm danger">Delete</a></td></tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No redirects configured.</td></tr>
<?php endif; ?></table>
<div style="margin-top:12px">
<a class="btn primary" onclick="document.getElementById('redirectForm').classList.toggle('hidden')">Add Redirect</a>
</div>
<div id="redirectForm" class="card hidden" style="max-width:500px;margin-top:12px">
<form method="POST" action="/user/redirects/add">
<div class="form-group"><label>Source URL</label><input name="source" required placeholder="/old-page"></div>
<div class="form-group"><label>Destination URL</label><input name="destination" required placeholder="https://example.com/new-page"></div>
<div class="form-group"><label>Type</label><select name="type"><option value="301">301 Permanent</option><option value="302">302 Temporary</option></select></div>
<button type="submit" class="btn primary">Create Redirect</button>
</form></div>
</div>
