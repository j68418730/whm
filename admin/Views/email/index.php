<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.em{--a:#1683ff;--a2:#0b65d8;--line:rgba(0,191,255,.10);--card:linear-gradient(145deg,#0b1f39,#08192d);--muted:#9ab0ca;--text:#e8f1ff}
.em h1,.em h2,.em h3,.em h4,.em p{margin:0}
.em .page-head{display:flex;align-items:center;justify-content:space-between;margin:0 0 22px;flex-wrap:wrap;gap:12px}
.em .page-title{display:flex;gap:14px;align-items:center}
.em .page-title .ti{font-size:34px;color:#a9c9ee}
.em .page-title h1{font-size:25px;color:var(--text);font-weight:750}
.em .page-title p{color:var(--muted);font-size:13px;margin-top:2px}
.em .btn-create{background:var(--a);border:0;color:#fff;border-radius:7px;padding:11px 20px;font-weight:650;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.em .btn-create:hover{background:var(--a2)}
.em .btn-mail{background:linear-gradient(135deg,#147ff0,#0c65c8);border:0;color:#fff;border-radius:7px;padding:11px 20px;font-weight:650;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.em .stats{display:grid;grid-template-columns:repeat(6,1fr);gap:13px;margin:0 0 20px}
.em .stat{background:var(--card);border:1px solid #193a5d;border-radius:9px;padding:16px;min-height:120px}
.em .stat .st-top{display:flex;gap:12px;align-items:center}
.em .stat .st-ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:20px;color:#fff;flex-shrink:0}
.em .stat strong{font-size:21px;color:var(--text)}
.em .stat small{display:block;color:var(--muted);margin-top:3px;font-size:11px}
.em .stat .svc{display:inline-block;padding:4px 10px;border-radius:14px;font-size:10px;font-weight:700;margin-top:8px}
.em .svc.on{background:rgba(74,222,128,.14);color:#4ade80}
.em .svc.off{background:rgba(248,113,113,.14);color:#f87171}
.em .ic-blue{background:#1267bb}.em .ic-green{background:#16d17e}.em .ic-yellow{background:#f4ac15}.em .ic-purple{background:#6338bd}.em .ic-cyan{background:#0a91ae}.em .ic-red{background:#ed3d4e}
.em .tabs{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:18px;border-bottom:1px solid #17385b;padding-bottom:8px}
.em .tab{display:inline-flex;align-items:center;gap:7px;padding:9px 16px;border-radius:8px 8px 0 0;text-decoration:none;font-size:13px;font-weight:600;color:#9ab0ca;transition:.12s}
.em .tab:hover{color:#e8f1ff;background:rgba(0,191,255,.05)}
.em .tab.active{background:rgba(0,140,255,.13);color:#38bdf8;border-bottom:2px solid var(--a)}
.em .panel{background:var(--card);border:1px solid #17385b;border-radius:9px;box-shadow:0 8px 25px rgba(0,0,0,.13)}
.em .filters{padding:16px;display:grid;grid-template-columns:1fr 180px 96px;gap:10px;align-items:center}
.em .filter-input{position:relative}
.em .filter-input i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6885a5;font-size:14px}
.em .filter-input input,.em .filters select{width:100%;height:40px;background:#0b2543;border:1px solid #24496f;color:#b8cae0;border-radius:6px;padding:0 13px;font-size:13px;outline:none}
.em .filter-input input{padding-left:34px}
.em .filters select option{background:#0b1f39;color:#e8f1ff}
.em .filter-btn{background:var(--a);border:0;border-radius:6px;color:#fff;font-weight:650;height:40px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;font-size:13px}
.em .filter-btn:hover{background:var(--a2)}
.em .form-card{max-width:560px;margin:0 0 18px;padding:18px}
.em .form-card h4{font-size:14px;color:#e8f1ff;margin-bottom:14px}
.em .form-card label{display:block;font-size:11px;color:var(--muted);font-weight:600;margin:0 0 5px;text-transform:uppercase;letter-spacing:.4px}
.em .form-card input,.em .form-card select,.em .form-card textarea{width:100%;padding:9px 12px;border-radius:7px;border:1px solid #24496f;background:#0b2543;color:#e8f1ff;font-size:13px;outline:none;box-sizing:border-box}
.em .form-card input:focus,.em .form-card select:focus{border-color:var(--a)}
.em .form-card select option{background:#0b1f39}
.em .fg-row{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.em .twrap{padding:6px 6px 0;overflow-x:auto}
.em table{width:100%;border-collapse:collapse;min-width:860px}
.em thead th{background:#102847;color:#c4d5e9;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:13px 11px;text-align:left;border-bottom:1px solid #1c3d60;white-space:nowrap}
.em tbody td{padding:13px 11px;font-size:12px;border-bottom:1px solid #183655;color:#d4e1ef;vertical-align:middle}
.em tbody tr{transition:background .1s}
.em tbody tr:hover{background:#0c2340}
.em tbody tr:last-child td{border-bottom:none}
.em .u-name strong{display:block;font-size:13px;color:#fff}
.em .u-name small{color:#819ab7;font-size:11px}
.em .actions{display:flex;gap:5px;white-space:nowrap}
.em .act{width:29px;height:29px;border:0;border-radius:6px;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;cursor:pointer}
.em .act.b{background:#137ce4}.em .act.g{background:#11bda2}.em .act.y{background:#e5a20d}.em .act.r{background:#ed3d4e}.em .act.d{background:#244361}
.em .st-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#16d987;margin-right:6px;vertical-align:middle}
.em .st-dot.off{background:#f87171}
.em .status-badge{display:inline-block;padding:4px 10px;border-radius:14px;font-size:11px;font-weight:700}
.em .status-badge.on{background:rgba(74,222,128,.14);color:#4ade80}
.em .status-badge.off{background:rgba(248,113,113,.14);color:#f87171}
.em .pw-inline{display:inline-flex;gap:4px;margin-left:6px;align-items:center}
.em .pw-inline input{width:130px;padding:5px 8px;border-radius:6px;border:1px solid #24496f;background:#0b2543;color:#e8f1ff;font-size:12px}
.em .foot{padding:13px 17px;color:var(--muted);font-size:12px}
.em .empty{padding:30px;text-align:center;color:var(--muted);font-size:13px}
.em .em-group{margin-bottom:16px;border:1px solid #17385b;border-radius:9px;overflow:hidden;background:var(--card)}
.em .em-group-head{display:flex;align-items:center;justify-content:center;gap:10px;padding:10px 16px;background:linear-gradient(135deg,rgba(0,191,255,.08),rgba(0,140,255,.02));border-bottom:1px solid #17385b;cursor:pointer;user-select:none}
.em .em-group-head .gt{font-weight:700;font-size:14px;color:var(--a)}
.em .em-group-head .gc{background:rgba(0,140,255,.15);color:#38bdf8;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700}
.em .em-group-head .gl{color:#64748b;font-size:11px;transition:transform .2s}
.em .em-group.collapsed .gl{transform:rotate(-90deg)}
.em .em-group.collapsed .twrap{display:none}
@media(max-width:1250px){.em .stats{grid-template-columns:repeat(3,1fr)}.em .filters{grid-template-columns:1fr}}
@media(max-width:700px){.em .stats{grid-template-columns:repeat(2,1fr)}.em .fg-row{grid-template-columns:1fr}}
</style>

<div class="em">

<div class="page-head">
<div class="page-title">
<i class="bi bi-envelope-paper title-icon ti"></i>
<div><h1>Email Administration</h1><p>Manage mailboxes, forwarders, autoresponders and the mail queue.</p></div>
</div>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<?php if ($tab === 'accounts'): ?><a class="btn-create" onclick="document.getElementById('acctForm').classList.toggle('hidden')"><i class="bi bi-plus-lg"></i> Add Mailbox</a><?php endif; ?>
<?php if ($tab === 'forwarders'): ?><a class="btn-create" onclick="document.getElementById('fwdForm').classList.toggle('hidden')"><i class="bi bi-plus-lg"></i> Add Forwarder</a><?php endif; ?>
<?php if ($tab === 'autoresponders'): ?><a class="btn-create" onclick="document.getElementById('autoForm').classList.toggle('hidden')"><i class="bi bi-plus-lg"></i> Add Autoresponder</a><?php endif; ?>
<a class="btn-mail" href="https://<?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'localhost'); ?>:2096/" target="_blank"><i class="bi bi-envelope-open"></i> Planet Hosts Mail</a>
</div>
</div>

<div class="stats">
<div class="stat"><div class="st-top"><div class="st-ic ic-blue"><i class="bi bi-envelope-fill"></i></div><div><strong><?php echo count($accounts); ?></strong><small>Mailboxes</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-green"><i class="bi bi-check-lg"></i></div><div><strong><?php echo count($forwarders); ?></strong><small>Forwarders</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-purple"><i class="bi bi-robot"></i></div><div><strong><?php echo count($autoresponders); ?></strong><small>Autoresponders</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-yellow"><i class="bi bi-inbox"></i></div><div><strong><?php echo $queueSize; ?></strong><small>Queue Messages</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-cyan"><i class="bi bi-send"></i></div><div><strong><?php echo ucfirst($postfix); ?></strong><small>Postfix</small></div><span class="svc <?php echo $postfix === 'active' ? 'on' : 'off'; ?>"><?php echo $postfix === 'active' ? '● Running' : '○ Stopped'; ?></span></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-red"><i class="bi bi-database"></i></div><div><strong><?php echo ucfirst($dovecot); ?></strong><small>Dovecot</small></div><span class="svc <?php echo $dovecot === 'active' ? 'on' : 'off'; ?>"><?php echo $dovecot === 'active' ? '● Running' : '○ Stopped'; ?></span></div></div>
</div>

<div class="tabs">
<a class="tab <?php echo $tab==='accounts'?'active':''; ?>" href="/admin/email?tab=accounts"><i class="bi bi-envelope-fill"></i> Accounts</a>
<a class="tab <?php echo $tab==='forwarders'?'active':''; ?>" href="/admin/email?tab=forwarders"><i class="bi bi-forward"></i> Forwarders</a>
<a class="tab <?php echo $tab==='autoresponders'?'active':''; ?>" href="/admin/email?tab=autoresponders"><i class="bi bi-robot"></i> Autoresponders</a>
<a class="tab <?php echo $tab==='spam'?'active':''; ?>" href="/admin/email?tab=spam"><i class="bi bi-shield-check"></i> Spam</a>
<a class="tab <?php echo $tab==='queue'?'active':''; ?>" href="/admin/email?tab=queue"><i class="bi bi-inbox"></i> Mail Queue</a>
</div>

<?php if ($tab === 'accounts'): ?>
<div id="acctForm" class="panel form-card hidden">
<form method="POST" action="/admin/email/account/create">
<h4>Create Mailbox</h4>
<div class="fg-row">
<div><label>Username</label><input name="email" required placeholder="user"></div>
<div><label>@ Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
</div>
<div class="fg-row" style="margin-top:10px">
<div><label>Password</label><input name="password" type="password" required minlength="6"></div>
<div><label>Quota (MB)</label><input name="quota" type="number" value="1000"></div>
</div>
<button type="submit" class="btn-create" style="margin-top:14px;border:0"><i class="bi bi-check-lg"></i> Create</button>
</form>
</div>

<div class="panel" style="margin-bottom:18px">
<div class="filters">
<div class="filter-input" style="flex:1"><i class="bi bi-search"></i><input type="text" id="emSearch" placeholder="Search mailboxes..."></div>
<select id="emDomain"><option value="">All Domains</option><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select>
<button class="filter-btn" type="button" onclick="emClearFilters()"><i class="bi bi-x-lg"></i> Clear</button>
</div>
</div>

<div class="panel form-card" style="max-width:720px">
<h4><i class="bi bi-bell"></i> Server Email &amp; Alerts</h4>
<p style="color:var(--muted);font-size:12px;margin:0 0 12px">Pick a mailbox to watch as the <strong style="color:#e8f1ff">Server Email</strong>. Whenever a new email lands in it, an alert is created: <em>"New email to admin@planet-hosts.com — Subject: Domain not working"</em>.</p>
<form method="POST" action="/admin/email/server-email/set" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap">
<div style="flex:1;min-width:220px"><label>Server Email</label>
<select name="email">
<option value="">— None —</option>
<?php foreach ($accounts as $a): ?><option value="<?php echo htmlspecialchars($a->email); ?>" <?php echo ($serverEmail ?? '') === $a->email ? 'selected' : ''; ?>><?php echo htmlspecialchars($a->email); ?></option><?php endforeach; ?>
</select></div>
<div><label>Alert To (optional email)</label><input name="alert_to" value="" placeholder="Forward alert email to..."></div>
<button type="submit" class="btn-create" style="border:0"><i class="bi bi-check-lg"></i> Save</button>
</form>
<form method="POST" action="/admin/email/server-email/alert-to" style="display:flex;gap:10px;align-items:end;flex-wrap:wrap;margin-top:10px">
<div style="flex:1;min-width:220px"><label>Forward Alerts To (SMTP email)</label><input name="email" value="<?php echo htmlspecialchars($serverAlertEmail ?? ''); ?>" placeholder="admin@planet-hosts.com"></div>
<button type="submit" class="btn-create" style="background:#244361;border:0"><i class="bi bi-send"></i> Set Alert Email</button>
</form>
<?php if (!empty($serverEmail)): ?>
<div style="margin-top:12px;padding:10px 14px;border-radius:8px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);font-size:12px;color:#4ade80"><i class="bi bi-bell-fill"></i> Watching <strong><?php echo htmlspecialchars($serverEmail); ?></strong> — new mail triggers alerts.</div>
<?php endif; ?>
</div>

<?php
$groups = [];
foreach ($accounts as $a) { $groups[$a->domain ?: 'Other'][] = $a; }
?>
<?php foreach ($groups as $domain => $group): ?>
<div class="em-group">
<div class="em-group-head" onclick="this.closest('.em-group').classList.toggle('collapsed')">
<span class="gt"><i class="bi bi-globe2"></i> <?php echo htmlspecialchars($domain); ?></span>
<span class="gc"><?php echo count($group); ?> mailbox<?php echo count($group) === 1 ? '' : 'es'; ?></span>
<span class="gl"><i class="bi bi-chevron-down"></i></span>
</div>
<div class="twrap">
<table>
<thead><tr><th>Email</th><th>Quota</th><th>Created</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($group as $a): ?>
<tr data-search="<?php echo strtolower(htmlspecialchars($a->email)); ?>" data-domain="<?php echo strtolower(htmlspecialchars($a->domain)); ?>">
<td class="u-name"><strong><?php echo htmlspecialchars($a->email); ?></strong><small>Local part: <?php echo htmlspecialchars(explode('@', $a->email)[0]); ?></small></td>
<td><?php echo (int)$a->quota_mb; ?> MB</td>
<td style="white-space:nowrap;color:#819ab7"><?php echo htmlspecialchars(date('Y-m-d', strtotime($a->created_at ?? 'now'))); ?><br><small style="color:#5d7897"><?php echo htmlspecialchars(date('h:i A', strtotime($a->created_at ?? 'now'))); ?></small></td>
<td><span class="st-dot"></span>Active</td>
<td class="actions">
<button class="act b" title="Change Password" onclick="var f=document.getElementById('pw<?php echo (int)$a->id; ?>');f.classList.toggle('hidden');f.querySelector('input').focus();"><i class="bi bi-key"></i></button>
<form id="pw<?php echo (int)$a->id; ?>" class="pw-inline hidden" method="POST" action="/admin/email/account/password/<?php echo (int)$a->id; ?>">
<input name="password" type="password" placeholder="New password" required minlength="6">
<button class="act g" title="Save Password" type="submit" onclick="return confirm('Change password for <?php echo htmlspecialchars($a->email); ?>?')"><i class="bi bi-check-lg"></i></button>
</form>
<a class="act b" href="/admin/email/mailbox/<?php echo (int)$a->id; ?>" title="Read Mailbox (root override)"><i class="bi bi-envelope-open"></i></a>
<?php if (($serverEmail ?? '') === $a->email): ?>
<span class="st-dot" style="background:#16d987"></span>
<?php endif; ?>
<a class="act r" href="/admin/email/account/delete/<?php echo (int)$a->id; ?>" title="Delete" onclick="return confirm('Delete mailbox <?php echo htmlspecialchars($a->email); ?> permanently?')"><i class="bi bi-trash"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endforeach; ?>
<?php if (empty($groups)): ?>
<div class="panel"><div class="empty">No mailboxes yet. Click <strong>Add Mailbox</strong> to create one.</div></div>
<?php endif; ?>

<?php elseif ($tab === 'forwarders'): ?>
<div id="fwdForm" class="panel form-card hidden">
<form method="POST" action="/admin/email/forwarder/create">
<h4>Add Forwarder</h4>
<div class="fg-row">
<div><label>From (local part)</label><input name="from" required placeholder="info"></div>
<div><label>@ Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-top:10px"><label>Forward To</label><input name="to" type="email" required placeholder="user@example.com"></div>
<button type="submit" class="btn-create" style="margin-top:14px;border:0"><i class="bi bi-check-lg"></i> Add</button>
</form>
</div>
<div class="panel">
<div class="twrap">
<table>
<thead><tr><th>From</th><th>Domain</th><th>Forward To</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($forwarders)): foreach ($forwarders as $f): ?>
<tr>
<td class="u-name"><strong><?php echo htmlspecialchars($f->from_email); ?></strong></td>
<td><?php echo htmlspecialchars($f->domain); ?></td>
<td><?php echo htmlspecialchars($f->to_email); ?></td>
<td class="actions"><a class="act r" href="/admin/email/forwarder/delete/<?php echo (int)$f->id; ?>" title="Delete" onclick="return confirm('Delete this forwarder?')"><i class="bi bi-trash"></i></a></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" class="empty">No forwarders configured.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<?php elseif ($tab === 'autoresponders'): ?>
<div id="autoForm" class="panel form-card hidden">
<form method="POST" action="/admin/email/autoresponder/set">
<h4>Add Autoresponder</h4>
<div class="fg-row">
<div><label>Email</label><input name="email" required placeholder="info@domain.com"></div>
<div><label>@ Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
</div>
<div class="form-group" style="margin-top:10px"><label>Subject</label><input name="subject" value="Out of Office"></div>
<div class="form-group" style="margin-top:10px"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
<button type="submit" class="btn-create" style="margin-top:14px;border:0"><i class="bi bi-check-lg"></i> Save</button>
</form>
</div>
<div class="panel">
<div class="twrap">
<table>
<thead><tr><th>Email</th><th>Subject</th><th>Status</th><th>Actions</th></tr></thead>
<tbody>
<?php if (!empty($autoresponders)): foreach ($autoresponders as $a): ?>
<tr>
<td class="u-name"><strong><?php echo htmlspecialchars($a->email); ?></strong></td>
<td><?php echo htmlspecialchars($a->subject); ?></td>
<td><span class="status-badge <?php echo $a->enabled ? 'on' : 'off'; ?>"><?php echo $a->enabled ? '● On' : '○ Off'; ?></span></td>
<td class="actions"><?php if ($a->enabled): ?><a class="act y" href="/admin/email/autoresponder/disable/<?php echo (int)$a->id; ?>" title="Disable"><i class="bi bi-pause"></i></a><?php endif; ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" class="empty">No autoresponders configured.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<?php elseif ($tab === 'spam'): ?>
<div class="panel form-card">
<form method="POST" action="/admin/email/spam/set">
<h4>Spam Filter Settings</h4>
<div class="form-group"><label>Domain</label><select name="domain"><?php foreach ($domains as $d): if ($d->domain): ?><option value="<?php echo htmlspecialchars($d->domain); ?>"><?php echo htmlspecialchars($d->domain); ?></option><?php endif; endforeach; ?></select></div>
<div class="fg-row" style="margin-top:10px">
<div><label>Action</label><select name="action"><option value="move_junk">Move to Junk</option><option value="delete">Delete</option><option value="tag">Tag Subject</option></select></div>
<div><label>Threshold</label><input name="threshold" value="5.0" placeholder="1.0 - 10.0"></div>
</div>
<button type="submit" class="btn-create" style="margin-top:14px;border:0"><i class="bi bi-check-lg"></i> Save</button>
</form>
</div>
<div class="panel">
<div class="twrap">
<table>
<thead><tr><th>Domain</th><th>Action</th><th>Threshold</th><th>Date</th></tr></thead>
<tbody>
<?php if (!empty($spam)): foreach ($spam as $s): ?>
<tr>
<td class="u-name"><strong><?php echo htmlspecialchars($s->domain); ?></strong></td>
<td><?php echo htmlspecialchars($s->action); ?></td>
<td><?php echo htmlspecialchars($s->threshold); ?></td>
<td style="color:#819ab7"><?php echo htmlspecialchars($s->created_at); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" class="empty">No spam settings configured.</td></tr>
<?php endif; ?>
</tbody>
</table>
</div>
</div>

<?php elseif ($tab === 'queue'): ?>
<div class="panel form-card">
<h4>Mail Queue</h4>
<p style="color:var(--muted);font-size:13px;margin-top:8px"><?php echo (int)$queueSize; ?> message<?php echo (int)$queueSize === 1 ? '' : 's'; ?> currently in the Postfix queue.</p>
<a class="btn-create" style="background:#ed3d4e;border:0;margin-top:14px;display:inline-flex" href="/admin/email/queue/clear" onclick="return confirm('Delete ALL queued messages? This cannot be undone.')"><i class="bi bi-trash"></i> Clear Queue</a>
</div>
<div class="panel" style="margin-top:14px">
<pre style="padding:16px;font-size:12px;color:#8b949e;max-height:400px;overflow-y:auto;font-family:monospace;margin:0"><?php echo htmlspecialchars(shell_exec('mailq 2>/dev/null') ?: 'No mail in queue.'); ?></pre>
</div>
<?php endif; ?>

</div>

<script>
function emClearFilters(){var s=document.getElementById('emSearch'),d=document.getElementById('emDomain');if(s)s.value='';if(d)d.value='';emApply();}
function emApply(){
var q=((document.getElementById('emSearch')||{}).value||'').toLowerCase();
var d=((document.getElementById('emDomain')||{}).value||'').toLowerCase();
document.querySelectorAll('.em .em-group').forEach(function(g){
var vis=false;
g.querySelectorAll('tr[data-search]').forEach(function(r){
var ok=(!q||(r.getAttribute('data-search')||'').indexOf(q)>-1)&&(!d||(r.getAttribute('data-domain')||'')===d);
r.style.display=ok?'':'none'; if(ok)vis=true;
});
g.style.display=vis?'':'none';
});
}
(function(){var s=document.getElementById('emSearch');if(s)s.addEventListener('input',emApply);var d=document.getElementById('emDomain');if(d)d.addEventListener('change',emApply);})();
function toggleEmGroup(el){el.closest('.em-group').classList.toggle('collapsed');}
</script>