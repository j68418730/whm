<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;margin-bottom:16px">
<h3 style="color:var(--accent)">Credits</h3>
<a class="btn primary" onclick="document.getElementById('crdForm').classList.toggle('hidden')">Add Credit</a>
</div>
<div id="crdForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/admin/billing/credits/store">
<div class="form-group"><label>User ID</label><input name="user_id" type="number" required></div>
<div class="form-group"><label>Amount</label><input name="amount" type="number" step="0.01" required></div>
<div class="form-group"><label>Description</label><input name="description" placeholder="Credit reason"></div>
<button type="submit" class="btn primary">Add Credit</button>
</form></div>
<table><tr><th>#</th><th>User ID</th><th>Amount</th><th>Description</th><th>Date</th></tr>
<?php if (!empty($credits)): foreach ($credits as $c): ?>
<tr><td><?php echo $c->id; ?></td><td><?php echo $c->user_id; ?></td>
<td><?php echo $c->amount > 0 ? '+' : ''; ?>$<?php echo number_format($c->amount, 2); ?></td>
<td><?php echo htmlspecialchars($c->description ?: '-'); ?></td><td><?php echo $c->created_at; ?></td></tr>
<?php endforeach; else: ?><tr><td colspan="5" style="text-align:center;padding:20px;color:#64748b">No credits recorded.</td></tr>
<?php endif; ?></table>
