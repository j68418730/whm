<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/billing" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📊 Dashboard</a>
<a href="/admin/billing/products" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📦 Products</a>
<a href="/admin/billing/orders" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📋 Orders</a>
<a href="/admin/billing/services" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🖥 Services</a>
<a href="/admin/billing/invoices" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff">💰 Invoices</a>
<a href="/admin/billing/payments" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">💳 Payments</a>
<a href="/admin/billing/taxes" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏛️ Taxes</a>
<a href="/admin/billing/coupons" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🎟️ Coupons</a>
<a href="/admin/billing/credits" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🏦 Credits</a>
<a href="/admin/billing/refunds" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">↩️ Refunds</a>
<a href="/admin/billing/reports" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">📈 Reports</a>
</div>
<div style="display:flex;gap:8px;margin-bottom:12px">
<a class="btn primary" onclick="document.getElementById('invForm').classList.toggle('hidden')">+ Create Invoice</a>
</div>
<div id="invForm" class="card hidden" style="max-width:600px;margin-bottom:16px">
<form method="POST" action="/admin/billing/invoices/create">
<h3 style="color:var(--accent);margin-bottom:8px">New Invoice</h3>
<div class="form-group"><label>User</label>
<select name="user_id" required onchange="updateInvoiceUser(this.value)">
<option value="">-- Select User --</option>
<?php foreach ($hostingUsers as $h): ?>
<option value="<?php echo $h->id; ?>" data-credit="<?php echo $creditsByUser[$h->id] ?? 0; ?>" data-pastdue="<?php echo $pastDueByUser[$h->id] ?? 0; ?>"><?php echo htmlspecialchars($h->username . ' (' . ($h->domain ?? '') . ')'); ?></option>
<?php endforeach; ?>
</select></div>
<div id="userInvoiceInfo" style="display:none;background:rgba(0,191,255,.04);border-radius:6px;padding:8px;margin:8px 0;font-size:12px">
<div style="display:flex;justify-content:space-between">
<span>Credits Available: <strong>$<span id="userCredit">0.00</span></strong></span>
<span style="color:#f87171">Past Due: <strong>$<span id="userPastDue">0.00</span></strong></span>
</div>
<div id="userPackages"></div>
<div id="userUnpaidOrders"></div>
<div style="margin-top:6px"><label><input type="checkbox" name="apply_credit" value="1" onchange="updateInvoiceTotal()"> Apply available credit to reduce invoice total</label></div>
</div>
<div style="display:flex;gap:8px">
<div style="flex:1"><label>Total</label><input name="total" id="inv_total" type="number" step="0.01" required></div>
<div style="flex:1"><label>Due Date</label><input name="due_date" type="date" value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"></div>
</div>
<div class="form-group"><label><input type="checkbox" name="combine_unpaid" value="1"> Combine unpaid orders into this invoice</label></div>
<button type="submit" class="btn primary">Create Invoice</button>
<div style="font-size:11px;color:#64748b;margin-top:6px" id="appliedCreditNote"></div>
</form></div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:10px">
<?php if (!empty($invoices)): foreach ($invoices as $inv): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><span style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($inv->invoice_number); ?></span>
<span class="status-badge status-<?php echo $inv->status === 'paid' ? 'active' : ($inv->status === 'overdue' ? 'terminated' : ''); ?>" style="margin-left:6px;font-size:10px"><?php echo $inv->status; ?></span></div>
<span style="font-size:11px;color:#64748b">#<?php echo $inv->id; ?></span>
</div>
<div style="font-size:12px;color:#94a3b8;margin-top:4px"><?php echo htmlspecialchars($inv->username ?? "User #{$inv->user_id}"); ?><?php echo $inv->domain ? ' · ' . htmlspecialchars($inv->domain) : ''; ?></div>
<div style="font-size:11px;color:#64748b">Date: <?php echo $inv->date; ?> · Due: <?php echo $inv->due_date; ?></div>
<div style="font-size:14px;font-weight:600;margin-top:6px">$<?php echo number_format($inv->total, 2); ?><?php if ($inv->credit_applied > 0): ?> <span style="font-size:11px;color:#4ade80">(credit: -$<?php echo number_format($inv->credit_applied, 2); ?>)</span><?php endif; ?></div>
<div style="margin-top:8px;display:flex;gap:4px">
<form method="POST" action="/admin/billing/invoices/status/<?php echo $inv->id; ?>" style="display:flex;gap:4px;flex:1">
<select name="status" style="flex:1"><option value="draft" <?php echo $inv->status==='draft'?'selected':''; ?>>Draft</option><option value="sent" <?php echo $inv->status==='sent'?'selected':''; ?>>Sent</option><option value="paid" <?php echo $inv->status==='paid'?'selected':''; ?>>Paid</option><option value="overdue" <?php echo $inv->status==='overdue'?'selected':''; ?>>Overdue</option><option value="cancelled" <?php echo $inv->status==='cancelled'?'selected':''; ?>>Cancelled</option></select>
<button type="submit" class="btn btn-sm primary">Update</button>
</form>
<a href="/admin/billing/invoices/delete/<?php echo $inv->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete invoice?')">✕</a>
</div>
</div>
<?php endforeach; else: ?>
<div class="card" style="text-align:center;padding:20px;grid-column:1/-1;color:#64748b">No invoices yet.</div>
<?php endif; ?>
</div>
<script>
var userCredits = {};
var userOrders = {};
var userPastDue = {};
var userPackages = {};
<?php foreach ($hostingUsers as $h): ?>
userCredits[<?php echo $h->id; ?>] = <?php echo $creditsByUser[$h->id] ?? 0; ?>;
userPastDue[<?php echo $h->id; ?>] = <?php echo $pastDueByUser[$h->id] ?? 0; ?>;
<?php endforeach; ?>
<?php foreach ($unpaidOrders as $o): if (!isset($userOrders[$o->user_id])) $userOrders[$o->user_id] = []; $userOrders[$o->user_id][] = ['id' => $o->id, 'total' => $o->total, 'desc' => $o->description ?? '']; ?>
<?php endforeach; ?>
<?php foreach ($userPackageList as $upl): if (!isset($userPackages[$upl->user_id])) $userPackages[$upl->user_id] = []; $userPackages[$upl->user_id][] = ['name' => $upl->product_name ?? 'N/A', 'cycle' => $upl->billing_cycle ?? '-', 'price' => $upl->price ?? 0]; ?>
<?php endforeach; ?>
function updateInvoiceUser(uid) {
    var info = document.getElementById('userInvoiceInfo');
    if (!uid) { info.style.display = 'none'; return; }
    info.style.display = 'block';
    document.getElementById('userCredit').textContent = (userCredits[uid] || 0).toFixed(2);
    document.getElementById('userPastDue').textContent = (userPastDue[uid] || 0).toFixed(2);
    var pkgHtml = '';
    if (userPackages[uid]) {
        pkgHtml += '<div style="margin-top:4px">Packages:</div>';
        userPackages[uid].forEach(function(p) {
            pkgHtml += '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:11px"><span>' + (p.name || 'N/A') + ' (' + p.cycle + ')</span><span>$' + parseFloat(p.price).toFixed(2) + '</span></div>';
        });
    }
    document.getElementById('userPackages').innerHTML = pkgHtml;
    var ordersHtml = '';
    var totalUnpaid = 0;
    if (userOrders[uid]) {
        ordersHtml += '<div style="margin-top:4px">Unpaid Orders:</div>';
        userOrders[uid].forEach(function(o) {
            totalUnpaid += parseFloat(o.total);
            ordersHtml += '<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:11px"><span>#' + o.id + ' ' + (o.desc || '') + '</span><span>$' + parseFloat(o.total).toFixed(2) + '</span></div>';
        });
        ordersHtml += '<div style="font-weight:600;margin-top:2px">Total Unpaid: $' + totalUnpaid.toFixed(2) + '</div>';
        document.getElementById('inv_total').value = totalUnpaid.toFixed(2);
    } else {
        ordersHtml += '<div style="color:#64748b;margin-top:4px">No unpaid orders.</div>';
    }
    document.getElementById('userUnpaidOrders').innerHTML = ordersHtml;
    updateInvoiceTotal();
}
function updateInvoiceTotal() {
    var uid = document.querySelector('[name=user_id]').value;
    var totalEl = document.getElementById('inv_total');
    var noteEl = document.getElementById('appliedCreditNote');
    var applyCredit = document.querySelector('[name=apply_credit]').checked;
    if (applyCredit && uid) {
        var credit = userCredits[uid] || 0;
        var current = parseFloat(totalEl.value) || 0;
        var after = Math.max(0, current - credit);
        noteEl.textContent = 'Credit applied: $' + credit.toFixed(2) + ' → New total: $' + after.toFixed(2);
    } else {
        noteEl.textContent = '';
    }
}
</script>