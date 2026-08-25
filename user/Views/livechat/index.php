<?php
$waiting = array_filter($sessions, fn($s) => $s->status === 'waiting');
$active  = array_filter($sessions, fn($s) => $s->status === 'active');
$closed  = array_filter($sessions, fn($s) => $s->status === 'closed');
$tenantId = $chatboxTenantId ?? 0;
?>
<div style="max-width:900px;margin:0 auto">
<h1 style="margin-bottom:8px">💬 Live Chat Support</h1>
<p style="color:#64748b;margin-bottom:24px">Start a new chat with support, or view your past and current conversations.</p>

<div style="display:grid;grid-template-columns:1fr 300px;gap:24px">
<div>
<?php if (!empty($active)): ?>
<h3 style="color:#4ade80;margin:0 0 12px">Active Chats</h3>
<?php foreach ($active as $s): ?>
<div style="background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);border-radius:10px;padding:16px;margin-bottom:12px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<strong>#<?php echo $s->id; ?></strong> <?php echo htmlspecialchars($s->subject ?: 'Support'); ?>
<?php if ($s->operator_name): ?><span style="color:#64748b;font-size:12px"> with <?php echo htmlspecialchars($s->operator_name); ?></span><?php endif; ?>
<br><span style="font-size:12px;color:#94a3b8">Created <?php echo date('M j, Y g:i A', strtotime($s->created_at)); ?></span>
</div>
<a href="/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>&session_id=<?php echo $s->id; ?>" target="_blank" class="btn btn-sm primary" style="padding:6px 14px;font-size:12px">Open Chat</a>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($waiting)): ?>
<h3 style="color:#fbbf24;margin:24px 0 12px">Waiting for Operator</h3>
<?php foreach ($waiting as $s): ?>
<div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.2);border-radius:10px;padding:16px;margin-bottom:12px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<strong>#<?php echo $s->id; ?></strong> <?php echo htmlspecialchars($s->subject ?: 'Support'); ?>
<br><span style="font-size:12px;color:#94a3b8">Requested <?php echo date('M j, Y g:i A', strtotime($s->created_at)); ?></span>
</div>
<a href="/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>&session_id=<?php echo $s->id; ?>" target="_blank" class="btn btn-sm secondary" style="padding:6px 14px;font-size:12px">View Chat</a>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($closed)): ?>
<h3 style="color:#94a3b8;margin:24px 0 12px">Closed Chats</h3>
<?php foreach ($closed as $s): ?>
<div style="background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.2);border-radius:10px;padding:16px;margin-bottom:12px">
<div style="display:flex;justify-content:space-between;align-items:center">
<div>
<strong>#<?php echo $s->id; ?></strong> <?php echo htmlspecialchars($s->subject ?: 'Support'); ?>
<br><span style="font-size:12px;color:#94a3b8">Closed <?php echo $s->closed_at ? date('M j, Y g:i A', strtotime($s->closed_at)) : date('M j, Y g:i A', strtotime($s->created_at)); ?></span>
</div>
<a href="/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>&session_id=<?php echo $s->id; ?>" target="_blank" class="btn btn-sm" style="background:#333;color:#ccc;padding:6px 14px;font-size:12px">View Transcript</a>
</div>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if (empty($sessions)): ?>
<div style="background:rgba(8,16,28,.6);border:1px solid rgba(0,191,255,.1);border-radius:12px;padding:40px;text-align:center;color:#64748b">
<div style="font-size:48px;margin-bottom:12px">💬</div>
<p style="font-size:16px;margin-bottom:8px">No chat history yet</p>
<p style="font-size:13px">Start a new chat below to get help from our support team.</p>
</div>
<?php endif; ?>
</div>

<aside>
<div class="card" style="padding:20px">
<h3 style="margin:0 0 16px;color:var(--accent)">Start New Chat</h3>
<form id="newChatForm" style="display:flex;flex-direction:column;gap:12px">
<input type="hidden" name="action" value="start">
<div class="form-group">
<label style="font-size:11px;color:#94a3b8">Name</label>
<input name="name" value="<?php echo htmlspecialchars($hosting->username ?? ''); ?>" required style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px">
</div>
<div class="form-group">
<label style="font-size:11px;color:#94a3b8">Email</label>
<input name="email" type="email" value="<?php echo htmlspecialchars($hosting->email ?? ''); ?>" required style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px">
</div>
<div class="form-group">
<label style="font-size:11px;color:#94a3b8">Subject</label>
<input name="subject" placeholder="e.g. Billing question, Technical issue..." required style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px">
</div>
<div class="form-group">
<label style="font-size:11px;color:#94a3b8">Initial Message</label>
<textarea name="message" rows="4" placeholder="Describe your issue..." required style="width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;resize:vertical"></textarea>
</div>
<button type="submit" class="btn primary" style="width:100%;padding:12px;font-weight:600"><i class="bi bi-chat-dots"></i> Start Chat</button>
</form>
</div>
</aside>
</div>

<script>
document.getElementById('newChatForm').addEventListener('submit', async function(e) {
e.preventDefault();
const btn = this.querySelector('button');
btn.disabled = true;
btn.textContent = 'Starting...';
const fd = new FormData(this);
try {
const res = await fetch('/chat/start', {method:'POST', body:fd});
const data = await res.json();
if (data.id) {
window.open('/chatbox/widget.js.php?tenant_id=<?php echo $tenantId; ?>&session_id=' + data.id, '_blank', 'width=400,height=600');
setTimeout(() => location.reload(), 1000);
} else {
alert('Failed to start chat: ' + (data.error || 'Unknown error'));
}
} catch (err) {
alert('Error: ' + err.message);
} finally {
btn.disabled = false;
btn.textContent = 'Start Chat';
}
});
</script>

<style>
.form-group{margin-bottom:12px}
.form-group label{display:block;font-size:11px;color:#94a3b8;margin-bottom:4px}
.form-group input,.form-group textarea{width:100%;padding:10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;box-sizing:border-box}
.form-group textarea{resize:vertical}
.btn{padding:10px 16px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;transition:.2s}
.btn.primary{background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff}
.btn.primary:hover{transform:translateY(-2px);box-shadow:0 0 25px rgba(0,140,255,.3)}
</style>