<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.mb{--a:#1683ff;--a2:#0b65d8;--card:linear-gradient(145deg,#0b1f39,#08192d);--muted:#9ab0ca;--text:#e8f1ff}
.mb h1,.mb h2,.mb p{margin:0}
.mb .page-head{display:flex;align-items:center;justify-content:space-between;margin:0 0 20px;flex-wrap:wrap;gap:12px}
.mb .page-title{display:flex;gap:14px;align-items:center}
.mb .page-title .ti{font-size:34px;color:#a9c9ee}
.mb .page-title h1{font-size:24px;color:var(--text);font-weight:750}
.mb .page-title p{color:var(--muted);font-size:13px;margin-top:2px}
.mb .btn-back{background:#244361;border:0;color:#fff;border-radius:7px;padding:10px 18px;font-weight:650;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.mb .btn-back:hover{background:#2c5278}
.mb .stats{display:grid;grid-template-columns:repeat(2,1fr);gap:13px;margin-bottom:18px;max-width:520px}
.mb .stat{background:var(--card);border:1px solid #193a5d;border-radius:9px;padding:16px}
.mb .stat strong{font-size:26px;color:var(--text)}
.mb .stat small{display:block;color:var(--muted);margin-top:3px;font-size:11px}
.mb .stat .st-ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:20px;color:#fff;background:#1267bb;margin-bottom:10px}
.mb .panel{background:var(--card);border:1px solid #17385b;border-radius:9px;box-shadow:0 8px 25px rgba(0,0,0,.13)}
.mb .twrap{padding:6px 6px 0;overflow-x:auto}
.mb table{width:100%;border-collapse:collapse;min-width:760px}
.mb thead th{background:#102847;color:#c4d5e9;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:13px 11px;text-align:left;border-bottom:1px solid #1c3d60;white-space:nowrap}
.mb tbody td{padding:13px 11px;font-size:12px;border-bottom:1px solid #183655;color:#d4e1ef;vertical-align:middle}
.mb tbody tr:hover{background:#0c2340}
.mb tbody tr:last-child td{border-bottom:none}
.mb .subj{color:#e8f1ff;font-weight:600;font-size:13px}
.mb .unseen{color:#fff}
.mb .subj small{display:block;color:#819ab7;font-weight:400;font-size:11px;margin-top:2px}
.mb .folder{display:inline-block;padding:3px 9px;border-radius:12px;font-size:10px;font-weight:700;text-transform:uppercase}
.mb .folder.new{background:rgba(22,131,255,.15);color:#38bdf8}
.mb .folder.cur{background:rgba(148,163,184,.12);color:#94a3b8}
.mb .act{width:29px;height:29px;border:0;border-radius:6px;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;cursor:pointer}
.mb .act.b{background:#137ce4}.mb .act.r{background:#ed3d4e}
.mb .empty{padding:30px;text-align:center;color:var(--muted);font-size:13px}
.mb .badge{background:rgba(248,113,113,.14);color:#f87171;border:1px solid rgba(248,113,113,.25);border-radius:6px;padding:4px 10px;font-size:11px;font-weight:700}
</style>

<div class="mb">
<div class="page-head">
<div class="page-title">
<i class="bi bi-envelope-open title-icon ti"></i>
<div><h1><?php echo htmlspecialchars($email); ?></h1><p>Root read access — view messages for spam/abuse review.</p></div>
</div>
<a class="btn-back" href="/admin/email?tab=accounts"><i class="bi bi-arrow-left"></i> Back to Email</a>
</div>

<div class="stats">
<div class="stat"><div class="st-ic"><i class="bi bi-envelope-fill"></i></div><strong><?php echo (int)($count['total'] ?? 0); ?></strong><small>Total Messages</small></div>
<div class="stat"><div class="st-ic" style="background:#0a91ae"><i class="bi bi-envelope-dash"></i></div><strong><?php echo (int)($count['unseen'] ?? 0); ?></strong><small>Unread</small></div>
</div>

<div class="panel">
<div class="twrap">
<table>
<thead><tr><th>Folder</th><th>Subject</th><th>From</th><th>Date</th><th></th></tr></thead>
<tbody>
<?php if (!empty($messages)): foreach ($messages as $m): ?>
<tr>
<td><span class="folder <?php echo $m['folder'] === 'new' ? 'new' : 'cur'; ?>"><?php echo $m['folder'] === 'new' ? 'New' : 'Read'; ?></span></td>
<td class="subj <?php echo $m['folder'] === 'new' ? 'unseen' : ''; ?>"><?php echo htmlspecialchars($m['subject'] ?: '(no subject)'); ?><small><?php echo htmlspecialchars($m['to'] ?: ''); ?></small></td>
<td><?php echo htmlspecialchars($m['from'] ?: '—'); ?></td>
<td style="white-space:nowrap;color:#819ab7"><?php echo htmlspecialchars($m['date'] ?: '—'); ?></td>
<td class="actions" style="white-space:nowrap">
<a class="act b" href="/admin/email/mailbox/<?php echo (int)($mbId ?? 0); ?>/read/<?php echo urlencode($m['file']); ?>" title="Read"><i class="bi bi-eye"></i></a>
</td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="5" class="empty">No messages in this mailbox.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<div class="panel" style="margin-top:14px;padding:12px 16px;font-size:11px;color:var(--muted)">
<span class="badge">🔒 Root only</span> This is a password-override read view for administrators investigating spam reports. Mailboxes are read directly from the mail server (Postfix/Dovecot).
</div>
</div>