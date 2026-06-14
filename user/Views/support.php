<div class="card" style="margin-bottom:16px"><h3 style="color:var(--accent)">Support Center</h3>
<p style="color:var(--text-secondary);margin-top:8px">Submit a ticket or browse the knowledge base.</p>
<div style="display:flex;gap:8px;margin-top:12px">
<a class="btn primary" onclick="document.getElementById('ticketForm').classList.toggle('hidden')">Open Ticket</a>
</div></div>
<div id="ticketForm" class="card hidden" style="max-width:500px;margin-bottom:20px">
<form method="POST" action="/user/tickets/create">
<h4 style="color:var(--accent);margin-bottom:8px">New Ticket</h4>
<div class="form-group"><label>Subject</label><input name="subject" required></div>
<div class="form-group"><label>Department</label><select name="department"><option>Technical</option><option>Billing</option><option>Sales</option></select></div>
<div class="form-group"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
<button type="submit" class="btn primary">Submit Ticket</button>
</form></div>
