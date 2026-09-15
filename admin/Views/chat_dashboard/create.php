<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<h2 style="margin-bottom:16px">➕ Create New Chatbox</h2>

<form method="POST" style="max-width:500px;margin:0 auto;padding:20px;background:rgba(8,16,28,.9);border:1px solid rgba(0,191,255,.15);border-radius:12px">
  <div style="margin-bottom:12px">
    <label style="display:block;font-size:13px;color:#94a3b8;margin-bottom:4px">Chatbox Name</label>
    <input name="name" type="text" required style="width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;font-family:Inter">
  </div>
  <div style="margin-bottom:12px">
    <label style="display:block;font-size:13px;color:#94a3b8;margin-bottom:4px">Widget Title (optional)</label>
    <input name="widget_title" type="text" placeholder="e.g. Radio Chat" style="width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;font-family:Inter">
  </div>
  <button class="btn btn-primary" style="width:100%;padding:12px;background:linear-gradient(135deg,#008cff,#3bb8ff);border:none;border-radius:8px;color:#fff;font-weight:700;font-size:13px;margin-top:16px">Create Chatbox</button>
  <p style="margin-top:12px;color:#64748b;font-size:12px"> <a href="/admin/chat-dashboard" style="color:#38bdf8">← Back to Dashboard</a></p>
</form>