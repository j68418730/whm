<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<div class="card" style="background:rgba(8,16,28,.95);border:1px solid rgba(0,191,255,.12);border-radius:12px;padding-30px;max-width:500px;margin:0 auto">
<h2 style="margin-bottom:16px; color:var(--accent, #008cff);">➕ Create New Chatbox</h2>

<form method="POST" style="max-width:100%">
  <div class="mb-3">
    <label class="form-label small" style="color:var(--secondary, #64748b);margin-bottom:4px">Chatbox Name</label>
    <input name="name" type="text" required style="width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;font-family:Inter">
  </div>
  <div class="mb-3">
    <label class="form-label small" style="color:var(--secondary, #64748b);margin-bottom:4px">Widget Title (optional)</label>
    <input name="widget_title" type="text" placeholder="e.g. Radio Chat" style="width:100%;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;font-family:Inter">
  </div>
  <button type="submit" class="btn btn-primary w-100" style="background:linear-gradient(135deg,var(--accent, #008cff),var(--accent-hover, #3bb8ff));border:none;border-radius:6px;padding:12px;font-weight:600">
    Create Chatbox
  </button>
</form>
<p class="text-center text-muted small mt-2"><a href="/admin/chat-dashboard" style="color:var(--secondary, #64748b);">← Back to Dashboard</a></p>
</div>