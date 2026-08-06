<?php
$tab = $activeTab ?? 'dashboard';
$data = $data ?? [];
$services = $services ?? [];
$actions = $actions ?? [];
$ruleTypes = $ruleTypes ?? [];
$hosting = $hosting ?? null;
$activeService = $activeService ?? 'all';
$serviceData = $serviceData ?? [];
$svcLabels = array_merge(['all' => 'All Services'], $services);
$actLabels = array_merge(['block' => 'Block'], $actions);
$typeLabels = array_merge(['user' => 'Planet Hosts User'], $ruleTypes);
$alerts = $data['alerts'] ?? [];
$rules = $data['rules'] ?? [];
$logs = $data['logs'] ?? [];
$attempts = $data['attempts'] ?? [];
$settings = $data['settings'] ?? [];
$trusted = $data['trusted'] ?? [];
$sessions = $sessions ?? ($data['sessions'] ?? []);
?>
<style>
.sc-wrap{max-width:1200px;margin:0 auto}
.sc-tabs{display:flex;gap:4px;flex-wrap:wrap;margin-bottom:18px;background:rgba(8,16,28,.6);border-radius:8px;padding:4px}
.sc-tabs a{padding:8px 14px;border-radius:6px;font-size:12px;text-decoration:none;color:#94a3b8;transition:.1s;font-weight:500}
.sc-tabs a:hover{color:#e0e0e0;background:rgba(255,255,255,.04)}
.sc-tabs a.active{color:#fff;background:rgba(0,140,255,.2)}
.sc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:16px}
.sc-stat{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:14px;text-align:center}
.sc-stat .num{font-size:22px;font-weight:700;color:#e0e0e0}
.sc-stat .lbl{font-size:10px;color:#64748b;margin-top:2px;text-transform:uppercase;letter-spacing:.5px}
.sc-card{background:rgba(8,16,28,.6);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;margin-bottom:12px}
.sc-card h3{font-size:14px;font-weight:600;color:#e0e0e0;margin:0 0 12px}
.sc-card h3 .hint{font-weight:400;font-size:11px;color:#64748b}
.sc-inp{padding:8px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.08);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:13px;outline:none;width:100%;box-sizing:border-box}
.sc-inp:focus{border-color:rgba(0,140,255,.4)}
.sc-btn{padding:8px 16px;border-radius:6px;font-size:12px;font-weight:600;border:none;cursor:pointer;transition:.1s;text-decoration:none;display:inline-block}
.sc-btn-p{background:rgba(0,140,255,.2);color:#0A84FF}
.sc-btn-p:hover{background:rgba(0,140,255,.3)}
.sc-btn-d{background:rgba(255,68,68,.15);color:#ff4444}
.sc-btn-d:hover{background:rgba(255,68,68,.25)}
.sc-btn-s{background:rgba(255,255,255,.06);color:#94a3b8}
.sc-btn-s:hover{background:rgba(255,255,255,.1)}
.sc-badge{display:inline-block;padding:2px 10px;border-radius:12px;font-size:10px;font-weight:600}
.sc-b-g{background:rgba(0,200,83,.12);color:#00C853}
.sc-b-r{background:rgba(255,68,68,.12);color:#ff4444}
.sc-b-y{background:rgba(255,193,7,.15);color:#ffc107}
.sc-b-b{background:rgba(96,165,250,.12);color:#60a5fa}
.sc-b-p{background:rgba(168,85,247,.15);color:#a855f7}
table.sc-t{width:100%;border-collapse:collapse;font-size:12px}
.sc-t th{padding:8px 6px;text-align:left;font-weight:600;color:#64748b;border-bottom:1px solid rgba(255,255,255,.06)}
.sc-t td{padding:8px 6px;border-bottom:1px solid rgba(255,255,255,.04);color:#c0c0c0}
.sc-t tr:hover td{background:rgba(255,255,255,.02)}
.sc-empty{padding:24px;text-align:center;color:#64748b;font-size:12px}
.sc-note{font-size:11px;color:#64748b;background:rgba(56,189,248,.05);border:1px solid rgba(56,189,248,.1);border-radius:8px;padding:10px 14px;margin-bottom:14px;line-height:1.6}
</style>
<div class="sc-wrap">
<div class="sc-tabs">
  <a href="/user/security" class="<?=$tab==='dashboard'?'active':''?>">Dashboard</a>
  <a href="/user/security?tab=rules" class="<?=$tab==='rules'?'active':''?>">Access Rules</a>
  <a href="/user/security?tab=services" class="<?=$tab==='services'?'active':''?>">Service Controls</a>
  <a href="/user/security?tab=login" class="<?=$tab==='login'?'active':''?>">Login Security</a>
  <a href="/user/security?tab=audit" class="<?=$tab==='audit'?'active':''?>">Audit Log</a>
  <a href="/user/security?tab=alerts" class="<?=$tab==='alerts'?'active':''?>">Notifications</a>
</div>

<?php if (isset($_SESSION['success'])): ?><div class="sc-card" style="background:rgba(0,200,83,.08);border-color:rgba(0,200,83,.2);color:#00C853;font-size:13px"><?=htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div><?php endif; ?>

<?php if ($tab === 'dashboard'): ?>
<div class="sc-note">🔒 <strong>Security Center</strong> controls access to your own services only. This is an application-level system — the server firewall (firewalld, fail2ban, ModSecurity, CSF) is managed by Planet Hosts administrators and is not affected here.</div>
<div class="sc-grid">
  <div class="sc-stat"><div class="num"><?=(int)($data['blocked_users'] ?? 0)?></div><div class="lbl">Blocked Users</div></div>
  <div class="sc-stat"><div class="num"><?=(int)($data['blocked_ips'] ?? 0)?></div><div class="lbl">Blocked IPs</div></div>
  <div class="sc-stat"><div class="num"><?=(int)($data['active_sessions'] ?? 0)?></div><div class="lbl">Active Sessions</div></div>
  <div class="sc-stat"><div class="num"><?=(int)($data['failed_logins'] ?? 0)?></div><div class="lbl">Failed Logins (24h)</div></div>
  <div class="sc-stat"><div class="num"><?=(int)($data['alerts'] ?? 0)?></div><div class="lbl">Alerts</div></div>
  <div class="sc-stat"><div class="num"><?=(int)($data['total_rules'] ?? 0)?></div><div class="lbl">Active Rules</div></div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="sc-card"><h3>Recent Activity</h3>
<?php $activity = $data['recent_activity'] ?? []; if (empty($activity)): ?>
<div class="sc-empty">No activity yet.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Action</th><th>Target</th><th>Time</th></tr>
<?php foreach ($activity as $l): ?>
<tr><td><?=htmlspecialchars($l->action ?? '')?></td><td><?=htmlspecialchars($l->target ?? '-')?></td><td style="font-size:11px;color:#64748b"><?=htmlspecialchars($l->created_at ?? '')?></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>
<div class="sc-card"><h3>Service Coverage</h3>
<?php $cov = $data['blocked_services'] ?? []; if (empty($cov)): ?>
<div class="sc-empty">No services restricted yet.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Service</th><th>Rules</th></tr>
<?php foreach ($cov as $c): ?>
<tr><td><?=htmlspecialchars($svcLabels[$c->service] ?? $c->service)?></td><td><span class="sc-badge sc-b-r"><?=(int)$c->c?></span></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>
</div>

<?php elseif ($tab === 'rules'): ?>
<div class="sc-note">Block or allow access to your services by user, username, email, IP, CIDR range, country, ASN, VPN/proxy/Tor, or device. Rules apply to <strong>your</strong> services only.</div>
<div class="sc-card"><h3>Add Access Rule</h3>
<form method="POST" action="/user/security/rules/store">
<input type="hidden" name="_csrf_token" value="<?=htmlspecialchars($csrfField ?? '')?>">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px">
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Type</label>
<select name="rule_type" class="sc-inp"><?php foreach ($typeLabels as $k=>$v): ?><option value="<?=$k?>"><?=htmlspecialchars($v)?></option><?php endforeach; ?></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Target</label>
<input name="target" class="sc-inp" required placeholder="username / email / IP / 1.2.3.0/24 / US"></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Service</label>
<select name="service" class="sc-inp"><?php foreach ($svcLabels as $k=>$v): ?><option value="<?=$k?>"><?=htmlspecialchars($v)?></option><?php endforeach; ?></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Action</label>
<select name="action" class="sc-inp"><?php foreach ($actLabels as $k=>$v): ?><option value="<?=$k?>"><?=htmlspecialchars($v)?></option><?php endforeach; ?></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Expires</label>
<select name="days" class="sc-inp"><option value="7">7 days</option><option value="30" selected>30 days</option><option value="90">90 days</option><option value="365">1 year</option><option value="3650">Never</option></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Reason</label>
<input name="reason" class="sc-inp" placeholder="Spam, abuse, etc."></div>
</div>
<button class="sc-btn sc-btn-p" style="margin-top:12px">+ Add Rule</button>
</form>
</div>
<div class="sc-card"><h3>Active Rules <span class="hint">(<?=count($rules)?>)</span></h3>
<?php if (empty($rules)): ?><div class="sc-empty">No rules yet.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Type</th><th>Target</th><th>Service</th><th>Action</th><th>Expires</th><th>Reason</th><th></th></tr>
<?php foreach ($rules as $r): $exp = $r->expires_at ?? null; ?>
<tr>
<td><?=htmlspecialchars($typeLabels[$r->rule_type] ?? $r->rule_type)?></td>
<td><code style="color:#38bdf8"><?=htmlspecialchars($r->target)?></code></td>
<td><?=htmlspecialchars($svcLabels[$r->service] ?? $r->service)?></td>
<td><span class="sc-badge <?=$r->action==='block'?'sc-b-r':($r->action==='allow'?'sc-b-g':'sc-b-y')?>"><?=htmlspecialchars($r->action)?></span></td>
<td style="font-size:11px;color:#64748b"><?=$exp?htmlspecialchars(substr($exp,0,10)):'Never'?></td>
<td style="font-size:11px"><?=htmlspecialchars($r->reason ?? '-')?></td>
<td><a href="/user/security/rules/delete/<?=$r->id?>" class="sc-btn sc-btn-d" onclick="return confirm('Remove rule?')">✕</a></td>
</tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>

<?php elseif ($tab === 'services'): ?>
<div class="sc-note">Pick a service to manage. Rules created here apply only to that service on your account.</div>
<div class="sc-card">
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
<?php foreach ($svcLabels as $k=>$v): ?>
<a href="/user/security?tab=services&service=<?=$k?>" class="sc-btn <?=$activeService===$k?'sc-btn-p':'sc-btn-s'?>"><?=htmlspecialchars($v)?></a>
<?php endforeach; ?>
</div>
<h3>Service: <?=htmlspecialchars($svcLabels[$activeService] ?? $activeService)?></h3>
<?php if ($activeService === 'radio' && !empty($serviceData['stations'])): ?>
<p style="font-size:11px;color:#64748b;margin-bottom:8px">Your radio stations:</p>
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
<?php foreach ($serviceData['stations'] as $st): ?><span class="sc-badge sc-b-b"><?=htmlspecialchars($st->name ?? 'Station #'.$st->id)?></span><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($activeService === 'chat' && !empty($serviceData['rooms'])): ?>
<p style="font-size:11px;color:#64748b;margin-bottom:8px">Your chat rooms:</p>
<div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
<?php foreach ($serviceData['rooms'] as $rm): ?><span class="sc-badge sc-b-p"><?=htmlspecialchars($rm->name ?? 'Room')?></span><?php endforeach; ?>
</div>
<?php endif; ?>
<form method="POST" action="/user/security/rules/store">
<input type="hidden" name="_csrf_token" value="<?=htmlspecialchars($csrfField ?? '')?>">
<input type="hidden" name="service" value="<?=htmlspecialchars($activeService)?>">
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px">
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Type</label>
<select name="rule_type" class="sc-inp"><?php foreach ($typeLabels as $k=>$v): ?><option value="<?=$k?>"><?=htmlspecialchars($v)?></option><?php endforeach; ?></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Target</label>
<input name="target" class="sc-inp" required placeholder="user / IP / CIDR / country"></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Action</label>
<select name="action" class="sc-inp"><?php foreach ($actLabels as $k=>$v): ?><option value="<?=$k?>"><?=htmlspecialchars($v)?></option><?php endforeach; ?></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Expires</label>
<select name="days" class="sc-inp"><option value="7">7 days</option><option value="30" selected>30 days</option><option value="90">90 days</option><option value="365">1 year</option><option value="3650">Never</option></select></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Reason</label>
<input name="reason" class="sc-inp" placeholder="Reason"></div>
</div>
<button class="sc-btn sc-btn-p" style="margin-top:12px">+ Add Rule for <?=htmlspecialchars($svcLabels[$activeService] ?? $activeService)?></button>
</form>
<h3 style="margin-top:16px">Rules for this service</h3>
<?php $svcRules = array_filter($rules, fn($r) => ($r->service ?? 'all') === $activeService || ($r->service ?? 'all') === 'all'); ?>
<?php if (empty($svcRules)): ?><div class="sc-empty">No rules for this service yet.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Type</th><th>Target</th><th>Action</th><th>Expires</th><th></th></tr>
<?php foreach ($svcRules as $r): ?>
<tr><td><?=htmlspecialchars($typeLabels[$r->rule_type] ?? $r->rule_type)?></td><td><code style="color:#38bdf8"><?=htmlspecialchars($r->target)?></code></td>
<td><span class="sc-badge <?=$r->action==='block'?'sc-b-r':'sc-b-y'?>"><?=htmlspecialchars($r->action)?></span></td>
<td style="font-size:11px;color:#64748b"><?=($r->expires_at)?htmlspecialchars(substr($r->expires_at,0,10)):'Never'?></td>
<td><a href="/user/security/rules/delete/<?=$r->id?>" class="sc-btn sc-btn-d" onclick="return confirm('Remove?')">✕</a></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>

<?php elseif ($tab === 'login'): ?>
<div class="sc-note">Configure login protections for your customer account. Failed-attempt thresholds and lockouts are tracked per account.</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
<div class="sc-card"><h3>Login Protection</h3>
<form method="POST" action="/user/security/login/save">
<input type="hidden" name="_csrf_token" value="<?=htmlspecialchars($csrfField ?? '')?>">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Max Login Attempts</label>
<input name="max_login_attempts" type="number" min="1" max="20" class="sc-inp" value="<?=htmlspecialchars($settings['max_login_attempts'] ?? '5')?>"></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Lockout (minutes)</label>
<input name="lockout_minutes" type="number" min="1" max="1440" class="sc-inp" value="<?=htmlspecialchars($settings['lockout_minutes'] ?? '15')?>"></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Session Timeout (minutes)</label>
<input name="session_timeout_minutes" type="number" min="5" max="4320" class="sc-inp" value="<?=htmlspecialchars($settings['session_timeout_minutes'] ?? '60')?>"></div>
<div><label style="font-size:10px;color:#64748b;display:block;margin-bottom:4px">Country Restriction (ISO, comma separated)</label>
<input name="country_restriction" class="sc-inp" value="<?=htmlspecialchars($settings['country_restriction'] ?? '')?>" placeholder="US,GB,CA"></div>
</div>
<button class="sc-btn sc-btn-p" style="margin-top:12px">Save Login Security</button>
</form>
</div>
<div class="sc-card"><h3>Trusted Devices &amp; IPs</h3>
<form method="POST" action="/user/security/login/save">
<input type="hidden" name="_csrf_token" value="<?=htmlspecialchars($csrfField ?? '')?>">
<div style="display:flex;gap:8px;flex-wrap:wrap">
<input name="trust_ip" class="sc-inp" style="flex:1;min-width:140px" placeholder="IP to trust">
<input name="trust_label" class="sc-inp" style="flex:1;min-width:120px" placeholder="Label (e.g. Office)">
<button class="sc-btn sc-btn-p">Trust IP</button>
</div>
</form>
<?php if (empty($trusted)): ?><div class="sc-empty">No trusted entries yet.</div>
<?php else: ?>
<table class="sc-t" style="margin-top:10px"><tr><th>Type</th><th>Value</th><th>Label</th><th></th></tr>
<?php foreach ($trusted as $t): ?>
<tr><td><?=htmlspecialchars($t->kind)?></td><td><code style="color:#4ade80"><?=htmlspecialchars($t->value)?></code></td><td><?=htmlspecialchars($t->label ?? '-')?></td>
<td><a href="/user/security/trusted/delete/<?=$t->id?>" class="sc-btn sc-btn-d">✕</a></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>
</div>
<div class="sc-card"><h3>Active Sessions <span class="hint">(<?=count($sessions)?>)</span></h3>
<?php if (empty($sessions)): ?><div class="sc-empty">No active sessions tracked.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Browser</th><th>Device</th><th>IP</th><th>Trusted</th><th>Last Active</th><th></th></tr>
<?php foreach ($sessions as $s): ?>
<tr><td><?=htmlspecialchars($s->browser ?? '-')?></td><td><?=htmlspecialchars($s->device ?? '-')?></td><td><?=htmlspecialchars($s->ip_address ?? '-')?></td>
<td><?=$s->trusted?'<span class="sc-badge sc-b-g">Trusted</span>':'<span class="sc-badge sc-b-y">Untrusted</span>'?></td>
<td style="font-size:11px;color:#64748b"><?=htmlspecialchars($s->last_active ?? '')?></td>
<td><a href="/user/security/sessions/terminate/<?=$s->id?>" class="sc-btn sc-btn-d" onclick="return confirm('Terminate this session?')">Terminate</a></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>
<div class="sc-card"><h3>Recent Login Attempts</h3>
<?php if (empty($attempts)): ?><div class="sc-empty">No login attempts recorded.</div>
<?php else: ?>
<table class="sc-t"><tr><th>User</th><th>IP</th><th>Result</th><th>Time</th></tr>
<?php foreach ($attempts as $a): ?>
<tr><td><?=htmlspecialchars($a->username ?? '-')?></td><td><?=htmlspecialchars($a->ip_address ?? '-')?></td>
<td><?=$a->success?'<span class="sc-badge sc-b-g">Success</span>':'<span class="sc-badge sc-b-r">Failed</span>'?></td>
<td style="font-size:11px;color:#64748b"><?=htmlspecialchars($a->created_at ?? '')?></td></tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>

<?php elseif ($tab === 'audit'): ?>
<div class="sc-card"><h3>Audit Log <span class="hint">(<?=count($logs)?> entries)</span></h3>
<?php if (empty($logs)): ?><div class="sc-empty">No audit entries yet.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Time</th><th>Action</th><th>Target</th><th>Service</th><th>Result</th><th>Performed By</th></tr>
<?php foreach ($logs as $l): ?>
<tr>
<td style="font-size:11px;color:#64748b;white-space:nowrap"><?=htmlspecialchars($l->created_at ?? '')?></td>
<td><?=htmlspecialchars($l->action ?? '')?></td>
<td><?=htmlspecialchars($l->target ?? '-')?></td>
<td><?=htmlspecialchars($svcLabels[$l->service] ?? ($l->service ?? '-'))?></td>
<td><span class="sc-badge <?=($l->result??'')==='blocked'?'sc-b-r':'sc-b-b'?>"><?=htmlspecialchars($l->result ?? '-')?></span></td>
<td><?=htmlspecialchars($l->performed_by ?? '-')?></td>
</tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>

<?php elseif ($tab === 'alerts'): ?>
<div class="sc-card"><h3>Notifications <span class="hint">(<?=count($alerts)?>)</span>
<a href="/user/security/alerts/clear" class="sc-btn sc-btn-s" style="float:right" onclick="return confirm('Clear all?')">Clear all</a></h3>
<?php if (empty($alerts)): ?><div class="sc-empty">No notifications.</div>
<?php else: ?>
<table class="sc-t"><tr><th>Type</th><th>Message</th><th>Time</th></tr>
<?php foreach ($alerts as $a): ?>
<tr>
<td><span class="sc-badge <?=$a->type==='block'?'sc-b-r':($a->type==='failed_login'?'sc-b-y':'sc-b-b')?>"><?=htmlspecialchars($a->type)?></span></td>
<td><?=htmlspecialchars($a->message)?></td>
<td style="font-size:11px;color:#64748b"><?=htmlspecialchars($a->created_at ?? '')?></td>
</tr>
<?php endforeach; ?>
</table><?php endif; ?>
</div>

<?php endif; ?>
</div>
