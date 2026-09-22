<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.ms{--a:#1683ff;--card:linear-gradient(145deg,#0b1f39,#08192d);--muted:#9ab0ca;--text:#e8f1ff}
.ms h1,.ms p{margin:0}
.ms .top{display:flex;justify-content:space-between;align-items:center;margin:0 0 16px;gap:12px;flex-wrap:wrap}
.ms .top h1{font-size:20px;color:var(--text)}
.ms .top small{color:var(--muted);font-size:12px;display:block;margin-top:3px}
.ms .btn-back{background:#244361;border:0;color:#fff;border-radius:7px;padding:10px 18px;font-weight:650;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.ms .btn-back:hover{background:#2c5278}
.ms .card{background:var(--card);border:1px solid #17385b;border-radius:9px;box-shadow:0 8px 25px rgba(0,0,0,.13);margin-bottom:14px;overflow:hidden}
.ms .hdr{padding:16px 20px;border-bottom:1px solid #17385b}
.ms .hdr .row{display:flex;gap:10px;padding:4px 0;font-size:13px}
.ms .hdr .lbl{color:var(--muted);width:70px;flex-shrink:0;text-transform:uppercase;font-size:11px;letter-spacing:.4px;padding-top:1px}
.ms .hdr .val{color:#e8f1ff;word-break:break-word}
.ms .body{padding:20px;font-size:13px;color:#d4e1ef;line-height:1.55;white-space:pre-wrap;word-break:break-word;font-family:monospace;max-height:600px;overflow-y:auto}
.ms .raw{padding:16px;background:rgba(0,0,0,.3);border-top:1px solid #17385b}
.ms .raw summary{color:var(--muted);font-size:12px;cursor:pointer;font-weight:600}
.ms .raw pre{white-space:pre-wrap;word-break:break-all;font-size:11px;color:#8b949e;margin:10px 0 0;max-height:300px;overflow-y:auto}
</style>

<div class="ms">
<div class="top">
<div>
<h1><?php echo htmlspecialchars($msg['subject'] ?? '(no subject)'); ?></h1>
<small><?php echo htmlspecialchars($email); ?></small>
</div>
<a class="btn-back" href="/admin/email/mailbox/<?php echo (int)($mbId ?? 0); ?>"><i class="bi bi-arrow-left"></i> Back to Mailbox</a>
</div>

<div class="card">
<div class="hdr">
<div class="row"><div class="lbl">From</div><div class="val"><?php echo htmlspecialchars($msg['from'] ?? '—'); ?></div></div>
<div class="row"><div class="lbl">To</div><div class="val"><?php echo htmlspecialchars($msg['to'] ?? '—'); ?></div></div>
<?php if (!empty($msg['cc'])): ?><div class="row"><div class="lbl">Cc</div><div class="val"><?php echo htmlspecialchars($msg['cc']); ?></div></div><?php endif; ?>
<div class="row"><div class="lbl">Date</div><div class="val"><?php echo htmlspecialchars($msg['date'] ?? '—'); ?></div></div>
</div>
<div class="body"><?php echo htmlspecialchars($msg['body'] ?? ''); ?></div>
<details class="raw"><summary>Raw message headers</summary><pre><?php echo htmlspecialchars($msg['headers'] ?? ''); ?></pre></details>
</div>
</div>