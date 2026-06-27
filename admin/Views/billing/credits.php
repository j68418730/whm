<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('crdForm').classList.toggle('hidden')">+ Add Credit</a>
</div>
<div id="crdForm" class="card hidden" style="max-width:500px;margin-bottom:16px">
<form method="POST" action="/admin/billing/credits/store">
<h3 style="color:var(--accent);margin-bottom:8px">Add Credit</h3>
<div class="form-group"><label>User</label>
<select name="user_id" required>
<option value="">-- Select User --</option>
<?php foreach ($hostingUsers as $h): ?>
<option value="<?php echo $h->id; ?>"><?php echo htmlspecialchars($h->username . ' (' . ($h->domain ?? '') . ')'); ?></option>
<?php endforeach; ?>
</select></div>
<div class="form-group" style="display:flex;gap:8px"><div style="flex:1"><label>Amount</label><input name="amount" type="number" step="0.01" required></div></div>
<div class="form-group"><label>Description</label><input name="description" placeholder="Credit reason"></div>
<button type="submit" class="btn primary">Add Credit</button>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:10px">
<?php if (!empty($credits)): foreach ($credits as $c): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between">
<div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($c->username ?? "User #{$c->user_id}"); ?></div>
<div style="font-size:16px;font-weight:700;color:<?php echo $c->amount > 0 ? '#4ade80' : '#f87171'; ?>"><?php echo $c->amount > 0 ? '+' : ''; ?>$<?php echo number_format($c->amount, 2); ?></div>
</div>
<div style="font-size:11px;color:#64748b;margin-top:4px"><?php echo htmlspecialchars($c->description ?: '-'); ?></div>
<div style="font-size:10px;color:#64748b;margin-top:2px"><?php echo $c->created_at; ?></div>
<div style="margin-top:6px;display:flex;gap:4px">
<a class="btn btn-sm secondary" onclick="editCredit(<?php echo $c->id; ?>, <?php echo $c->amount; ?>, '<?php echo htmlspecialchars(addslashes($c->description ?? '')); ?>')">✏ Edit</a>
<a href="/admin/billing/credits/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete credit?')">Delete</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No credits recorded.</div>
<?php endif; ?>
</div>

<div id="editCreditModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.7);align-items:center;justify-content:center" onclick="if(event.target===this)this.style.display='none'">
<div class="card" style="max-width:400px;margin:auto;position:relative;top:10%">
<h3 style="color:var(--accent);margin-bottom:8px">Edit Credit</h3>
<form method="POST" action="" id="editCreditForm">
<div class="form-group"><label>Amount</label><input name="amount" id="ecr_amount" type="number" step="0.01" required></div>
<div class="form-group"><label>Description</label><input name="description" id="ecr_desc"></div>
<button type="submit" class="btn primary">Save</button>
<button type="button" class="btn secondary" onclick="document.getElementById('editCreditModal').style.display='none'">Cancel</button>
</form></div></div>
<script>
function editCredit(id, amount, desc) {
    document.getElementById('editCreditForm').action = '/admin/billing/credits/update/' + id;
    document.getElementById('ecr_amount').value = amount;
    document.getElementById('ecr_desc').value = desc;
    document.getElementById('editCreditModal').style.display = 'flex';
}
</script>