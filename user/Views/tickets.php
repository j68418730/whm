<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success" style="padding:10px 16px;background:rgba(74,222,128,.12);border:1px solid rgba(74,222,128,.3);border-radius:8px;color:#4ade80;font-size:13px;margin-bottom:16px"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
<h2 style="margin:0">Support Tickets</h2>
<button onclick="document.getElementById('newTicketForm').style.display=document.getElementById('newTicketForm').style.display==='none'?'block':'none'" style="padding:8px 16px;border-radius:8px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600">+ Open New Ticket</button>
</div>

<div id="newTicketForm" style="display:none;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.12);border-radius:12px;padding:20px;margin-bottom:20px">
<h3 style="color:var(--accent);font-size:14px;margin-bottom:14px">Open a New Ticket</h3>
<form method="POST" action="/user/tickets/create">
<div style="display:grid;gap:12px">
<div>
<label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px">Subject</label>
<input name="subject" required placeholder="Brief description of your issue" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;font-size:13px">
</div>
<div>
<label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px">Department</label>
<select name="department" style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;font-size:13px">
<option value="Technical">Technical Support</option>
<option value="Billing">Billing</option>
<option value="Sales">Sales</option>
<option value="General">General Inquiry</option>
</select>
</div>
<div>
<label style="display:block;font-size:12px;color:#64748b;margin-bottom:4px">Message</label>
<textarea name="message" required rows="5" placeholder="Describe your issue in detail..." style="width:100%;padding:10px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#fff;outline:none;font-size:13px;resize:vertical"></textarea>
</div>
<div style="display:flex;gap:8px;justify-content:flex-end">
<button type="button" onclick="document.getElementById('newTicketForm').style.display='none'" style="padding:8px 16px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:transparent;color:#64748b;cursor:pointer;font-size:13px">Cancel</button>
<button type="submit" style="padding:8px 16px;border-radius:8px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600">Submit Ticket</button>
</div>
</div>
</form>
</div>

<?php if (!empty($tickets)): ?>
<div style="background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;overflow:hidden">
<table style="width:100%;border-collapse:collapse">
<tr style="border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:12px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:500">ID</th>
<th style="padding:12px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:500">Subject</th>
<th style="padding:12px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:500">Department</th>
<th style="padding:12px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:500">Status</th>
<th style="padding:12px 16px;text-align:left;font-size:12px;color:#64748b;font-weight:500">Date</th>
<th style="padding:12px 16px;text-align:right;font-size:12px;color:#64748b;font-weight:500">Actions</th>
</tr>
<?php foreach ($tickets as $t): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:.15s" onmouseover="this.style.background='rgba(0,140,255,.03)'" onmouseout="this.style.background='transparent'">
<td style="padding:12px 16px;font-size:12px;color:#64748b">#<?php echo $t->id; ?></td>
<td style="padding:12px 16px;font-size:13px;color:#e0e0e0;font-weight:500"><?php echo htmlspecialchars($t->subject); ?></td>
<td style="padding:12px 16px;font-size:12px;color:#94a3b8"><?php echo htmlspecialchars($t->department); ?></td>
<td style="padding:12px 16px">
<?php
$st = $t->status;
$sc = $st === 'open' ? 'rgba(250,204,21,.15);color:#facc15' : ($st === 'answered' || $st === 'pending' ? 'rgba(0,140,255,.15);color:#0A84FF' : ($st === 'closed' || $st === 'resolved' ? 'rgba(74,222,128,.15);color:#4ade80' : 'rgba(255,255,255,.08);color:#94a3b8'));
?>
<span style="padding:3px 10px;border-radius:6px;font-size:11px;font-weight:600;background:<?php echo $sc; ?>"><?php echo ucfirst(htmlspecialchars($st)); ?></span>
</td>
<td style="padding:12px 16px;font-size:12px;color:#64748b"><?php echo $t->created_at; ?></td>
<td style="padding:12px 16px;text-align:right">
<a href="/user/tickets/<?php echo $t->id; ?>" style="padding:5px 12px;border-radius:6px;background:rgba(0,140,255,.12);color:#0A84FF;text-decoration:none;font-size:12px;font-weight:500">View</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<?php else: ?>
<div style="text-align:center;padding:40px;background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px">
<div style="font-size:32px;margin-bottom:10px">🎫</div>
<p style="color:#64748b;font-size:13px;margin-bottom:16px">No support tickets yet.</p>
<button onclick="document.getElementById('newTicketForm').style.display='block'" style="padding:8px 16px;border-radius:8px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border:none;cursor:pointer;font-size:13px;font-weight:600">Open Your First Ticket</button>
</div>
<?php endif; ?>
