<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<style>
.acc{--a:#1683ff;--a2:#0b65d8;--line:rgba(0,191,255,.10);--card:linear-gradient(145deg,#0b1f39,#08192d);--muted:#9ab0ca;--text:#e8f1ff}
.acc h1,.acc h2,.acc h3,.acc p{margin:0}
.acc .page-head{display:flex;align-items:center;justify-content:space-between;margin:0 0 22px;flex-wrap:wrap;gap:12px}
.acc .page-title{display:flex;gap:14px;align-items:center}
.acc .page-title .ti{font-size:34px;color:#a9c9ee}
.acc .page-title h1{font-size:25px;color:var(--text);font-weight:750}
.acc .page-title p{color:var(--muted);font-size:13px;margin-top:2px}
.acc .btn-create{background:var(--a);border:0;color:#fff;border-radius:7px;padding:11px 20px;font-weight:650;font-size:13px;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:8px}
.acc .btn-create:hover{background:var(--a2)}
.acc .stats{display:grid;grid-template-columns:repeat(6,1fr);gap:13px;margin:0 0 20px}
.acc .stat{background:var(--card);border:1px solid #193a5d;border-radius:9px;padding:16px;min-height:120px}
.acc .stat .st-top{display:flex;gap:12px;align-items:center}
.acc .stat .st-ic{width:42px;height:42px;border-radius:11px;display:grid;place-items:center;font-size:20px;color:#fff;flex-shrink:0}
.acc .stat strong{font-size:21px;color:var(--text)}
.acc .stat small{display:block;color:var(--muted);margin-top:3px;font-size:11px}
.acc .bar{height:7px;background:#233e5d;border-radius:8px;overflow:hidden;margin-top:10px}
.acc .bar span{display:block;height:100%;border-radius:8px}
.acc .ic-blue{background:#1267bb}.acc .ic-green{background:#16d17e}.acc .ic-yellow{background:#f4ac15}.acc .ic-purple{background:#6338bd}.acc .ic-cyan{background:#0a91ae}
.acc .panel{background:var(--card);border:1px solid #17385b;border-radius:9px;box-shadow:0 8px 25px rgba(0,0,0,.13)}
.acc .filters{padding:16px;display:grid;grid-template-columns:1fr 180px 150px 96px;gap:10px;align-items:center}
.acc .filter-input{position:relative}
.acc .filter-input i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#6885a5;font-size:14px}
.acc .filter-input input,.acc .filters select{width:100%;height:40px;background:#0b2543;border:1px solid #24496f;color:#b8cae0;border-radius:6px;padding:0 13px;font-size:13px;outline:none}
.acc .filter-input input{padding-left:34px}
.acc .filters select option{background:#0b1f39;color:#e8f1ff}
.acc .filter-btn{background:var(--a);border:0;border-radius:6px;color:#fff;font-weight:650;height:40px;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;font-size:13px}
.acc .filter-btn:hover{background:var(--a2)}
.acc .twrap{padding:6px 6px 0;overflow-x:auto}
.acc table{width:100%;border-collapse:collapse;min-width:1080px}
.acc thead th{background:#102847;color:#c4d5e9;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;padding:13px 11px;text-align:left;border-bottom:1px solid #1c3d60;white-space:nowrap}
.acc thead th:first-child{border-radius:7px 0 0 0}
.acc tbody td{padding:13px 11px;font-size:12px;border-bottom:1px solid #183655;color:#d4e1ef;vertical-align:middle}
.acc tbody tr{transition:background .1s}
.acc tbody tr:hover{background:#0c2340}
.acc tbody tr:last-child td{border-bottom:none}
.acc .u-name strong{display:block;font-size:13px;color:#fff}
.acc .u-name small{color:#819ab7;font-size:11px}
.acc .domain a{color:#d4e1ef;text-decoration:none}
.acc .domain a:hover{color:var(--a)}
.acc .domain .ext{color:var(--a);margin-left:5px;font-size:11px}
.acc .pkg-tag{display:inline-block;padding:5px 10px;border-radius:14px;background:#0874d9;color:#fff;font-size:11px;font-weight:700}
.acc .pkg-tag.radio{background:#6742cc}.acc .pkg-tag.game{background:#ee7c12}.acc .pkg-tag.vps{background:#0a91ae}.acc .pkg-tag.reseller{background:#6338bd}
.acc .pkg-sub{display:block;color:var(--muted);margin-top:5px;font-size:11px}
.acc .st-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#16d987;margin-right:6px;vertical-align:middle}
.acc .st-dot.susp{background:#ffb51b}.acc .st-dot.term{background:#ff4757}.acc .st-dot.pend{background:#18c7d9}
.acc .usage{color:#d1dfef;font-size:11px;white-space:nowrap}
.acc .usage .bar{margin-top:5px}
.acc .usage .bar span{background:#12cbb4}
.acc .usage .bar.bw span{background:var(--a)}
.acc .actions{display:flex;gap:5px;white-space:nowrap}
.acc .act{width:29px;height:29px;border:0;border-radius:6px;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:13px;text-decoration:none;cursor:pointer}
.acc .act.b{background:#137ce4}.acc .act.g{background:#11bda2}.acc .act.y{background:#e5a20d}.acc .act.r{background:#ed3d4e}.acc .act.d{background:#244361}
.acc .owner-badge{background:#102847;color:#38bdf8;border:1px solid #1c3d60;border-radius:5px;padding:4px 8px;font-size:10px;font-weight:700;white-space:nowrap}
.acc .acct-group{margin-bottom:16px;border:1px solid #17385b;border-radius:9px;overflow:hidden;background:var(--card)}
.acc .acct-group-head{display:flex;align-items:center;justify-content:center;gap:10px;padding:10px 16px;background:linear-gradient(135deg,rgba(0,191,255,.08),rgba(0,140,255,.02));border-bottom:1px solid #17385b;cursor:pointer;user-select:none}
.acc .acct-group-head .gt{font-weight:700;font-size:14px;color:var(--a)}
.acc .acct-group-head .gc{background:rgba(0,140,255,.15);color:#38bdf8;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700}
.acc .acct-group-head .gl{color:#64748b;font-size:11px;transition:transform .2s}
.acc .acct-group.collapsed .gl{transform:rotate(-90deg)}
.acc .acct-group.collapsed .twrap{display:none}
.acc .foot{padding:13px 17px;color:var(--muted);font-size:12px;display:flex;justify-content:space-between;align-items:center}
.acc .pages{display:flex;gap:5px}
.acc .page{width:32px;height:32px;border-radius:6px;border:1px solid #1c3d60;background:#102847;color:#bcd0e7;display:grid;place-items:center;font-size:12px}
.acc .page.active{background:var(--a);color:#fff;border-color:var(--a)}
.acc .bottom{display:grid;grid-template-columns:1fr 300px;gap:15px;margin-top:20px}
.acc .quick{padding:13px;display:grid;grid-template-columns:repeat(7,1fr);gap:12px}
.acc .quick-card{border:1px solid #193b60;background:#0c2340;border-radius:8px;text-align:center;padding:15px 7px;min-height:132px;text-decoration:none;color:inherit;display:block;transition:.15s}
.acc .quick-card:hover{border-color:#24496f;transform:translateY(-2px)}
.acc .quick-card .qi{width:32px;height:32px;border-radius:50%;display:grid;place-items:center;margin:0 auto 10px;color:#fff;font-size:15px}
.acc .quick-card strong{font-size:12px;color:#e8f1ff;display:block}
.acc .quick-card small{display:block;color:#8ea8c5;font-size:10px;line-height:1.5;margin-top:6px}
.acc .tips{padding:18px}
.acc .tips h3{font-size:14px;color:#e8f1ff;margin:0 0 12px}
.acc .tips p{font-size:11px;color:#a9bdd4;margin:10px 0}
.acc .tips i{color:#16d889;margin-right:7px}
@media(max-width:1250px){.acc .stats{grid-template-columns:repeat(3,1fr)}.acc .bottom{grid-template-columns:1fr}.acc .quick{grid-template-columns:repeat(4,1fr)}}
@media(max-width:700px){.acc .stats{grid-template-columns:repeat(2,1fr)}.acc .filters{grid-template-columns:1fr}.acc .quick{grid-template-columns:repeat(2,1fr)}}
</style>

<div class="acc">

<div class="page-head">
<div class="page-title">
<i class="bi bi-people title-icon ti"></i>
<div><h1>Account Functions</h1><p>Manage your hosting accounts, domains and services from one central location.</p></div>
</div>
<a href="/admin/account/create" class="btn-create"><i class="bi bi-plus-lg"></i> Create New Account</a>
</div>

<?php
$allAccounts = $accountGroups ?? [];
$accountsArr = $accounts ?? [];
$totalAccounts = count($accountsArr);
$activeCount = 0; $suspendedCount = 0; $terminatedCount = 0;
$totalDiskMB = 0; $totalBWMB = 0;
foreach ($accountsArr as $a) {
    $st = $a->status ?? 'active';
    if ($st === 'active') $activeCount++;
    elseif ($st === 'suspended') $suspendedCount++;
    elseif ($st === 'terminated') $terminatedCount++;
    $dmb = (float)($a->disk_used ?? 0) / 1024; if ($dmb > 0) $totalDiskMB += $dmb;
    $bmb = (float)($a->bandwidth_used ?? 0) / 1048576; if ($bmb > 0) $totalBWMB += $bmb;
}
$totalPackages = count($packages ?? []);
function accSize($mb){ if($mb > 1048576) return number_format($mb/1048576,2).' TB'; if($mb > 1024) return number_format($mb/1024,1).' GB'; return number_format($mb,0).' MB'; }
?>
<div class="stats">
<div class="stat"><div class="st-top"><div class="st-ic ic-blue"><i class="bi bi-person-fill"></i></div><div><strong><?php echo $totalAccounts; ?></strong><small>Total Accounts</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-green"><i class="bi bi-check-lg"></i></div><div><strong><?php echo $activeCount; ?></strong><small>Active Accounts</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-yellow"><i class="bi bi-pause-fill"></i></div><div><strong><?php echo $suspendedCount; ?></strong><small>Suspended Accounts</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-purple"><i class="bi bi-box-seam"></i></div><div><strong><?php echo $terminatedCount; ?></strong><small>Terminated</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-blue"><i class="bi bi-stack"></i></div><div><strong><?php echo $totalPackages; ?></strong><small>Total Packages</small></div></div></div>
<div class="stat"><div class="st-top"><div class="st-ic ic-cyan"><i class="bi bi-hdd"></i></div><div><strong><?php echo accSize($totalDiskMB); ?></strong><small>Disk Usage</small></div></div><div class="bar"><span style="width:<?php echo min(100, round($totalDiskMB/2048)); ?>%"></span></div></div>
</div>

<div class="panel" style="margin-bottom:18px">
<div class="filters">
<form method="GET" action="/admin/account" class="d-flex" style="gap:10px;flex-wrap:wrap;align-items:center;width:100%">
<div class="filter-input" style="flex:1;min-width:180px"><i class="bi bi-search"></i><input type="text" name="search" value="<?php echo htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search by username, domain or email..."></div>
<select name="package" style="width:180px"><option value="">All Packages</option>
<?php foreach ($packages as $pg): ?><option value="<?php echo $pg->id; ?>" <?php echo (int)($package_filter ?? 0) === (int)$pg->id ? 'selected' : ''; ?>><?php echo htmlspecialchars($pg->name); ?></option><?php endforeach; ?>
</select>
<select name="status" style="width:150px"><option value="">All Statuses</option>
<option value="active" <?php echo ($status_filter ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
<option value="suspended" <?php echo ($status_filter ?? '') === 'suspended' ? 'selected' : ''; ?>>Suspended</option>
<option value="terminated" <?php echo ($status_filter ?? '') === 'terminated' ? 'selected' : ''; ?>>Terminated</option>
<option value="pending" <?php echo ($status_filter ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
</select>
<button type="submit" class="filter-btn"><i class="bi bi-funnel"></i> Filter</button>
<?php if ($search || ($package_filter ?? 0) || ($status_filter ?? '')): ?>
<a href="/admin/account" class="filter-btn" style="background:#244361;text-decoration:none"><i class="bi bi-x-lg"></i></a>
<?php endif; ?>
</form>
</div>
</div>

<?php
$groupCount = count($allAccounts);
$gi = 0;
foreach ($allAccounts as $ownerName => $group):
    $gi++;
    $groupAccounts = count($group);
?>
<div class="acct-group" data-owner="<?php echo htmlspecialchars($ownerName, ENT_QUOTES, 'UTF-8'); ?>">
<div class="acct-group-head" onclick="toggleGroup(this)">
<span class="gt"><?php echo htmlspecialchars($ownerName); ?></span>
<span class="gc"><?php echo $groupAccounts; ?> account<?php echo $groupAccounts === 1 ? '' : 's'; ?></span>
<span class="gl"><i class="bi bi-chevron-down"></i></span>
</div>
<div class="twrap">
<table>
<thead><tr><th>Username</th><th>Domain</th><th>Package</th><th>IP</th><th>Status</th><th>Disk Usage</th><th>Bandwidth</th><th>Created</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($group as $a):
    $pkg = $packageMap[$a->package_id] ?? null;
    $svcs = $servicesByUser[(int)$a->id] ?? [];
    $svcCount = count($svcs);
    $lastOrder = $latestOrderByUser[(int)$a->id] ?? null;
    $dmb = (float)($a->disk_used ?? 0) / 1024; // MB
    $bmb = (float)($a->bandwidth_used ?? 0) / 1048576; // MB
    $pkgGB = (float)($pkg->disk_space ?? 10) * 1024; $bwGB = (float)($pkg->bandwidth ?? 10) * 1024;
    $dpct = $pkgGB > 0 ? min(100, round($dmb / $pkgGB * 100)) : 0;
    $bpct = $bwGB > 0 ? min(100, round($bmb / $bwGB * 100)) : 0;
    $ownerCell = $a->reseller_id ? ('Reseller #' . $a->reseller_id) : 'Root';
    $pkgType = $pkg->type ?? '';
    $tagCls = '';
    if (str_contains($pkgType,'radio')||str_contains($pkgType,'icecast')||str_contains($pkgType,'shoutcast')) $tagCls='radio';
    elseif (str_contains($pkgType,'game')) $tagCls='game';
    elseif (str_contains($pkgType,'vps')||str_contains($pkgType,'virtual')) $tagCls='vps';
    elseif (str_contains($pkgType,'reseller')) $tagCls='reseller';
    $stDot = $a->status === 'suspended' ? 'susp' : ($a->status === 'terminated' ? 'term' : ($a->status === 'pending' ? 'pend' : ''));
?>
<tr>
<td class="u-name"><strong><?php echo htmlspecialchars($a->username); ?></strong>
<small><span class="owner-badge"><?php echo htmlspecialchars($ownerCell); ?></span><?php if (isset($a->no_auto_suspend) && (int)$a->no_auto_suspend === 1): ?> · 🔒 No auto-suspend<?php endif; ?><?php if ($svcCount): ?> · <?php echo $svcCount; ?> svc<?php endif; ?></small>
</td>
<td class="domain"><a href="http://<?php echo htmlspecialchars($a->domain ?: '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank"><?php echo htmlspecialchars($a->domain ?: '—'); ?></a><?php if ($a->domain): ?><i class="bi bi-box-arrow-up-right ext"></i><?php endif; ?></td>
<td><span class="pkg-tag <?php echo $tagCls; ?>"><?php echo $pkg ? htmlspecialchars($pkg->name) : ($a->package_id ? 'Pkg #'.$a->package_id : 'Free'); ?></span><span class="pkg-sub"><?php echo $pkg ? htmlspecialchars(ucwords(str_replace('_',' ',$pkg->type))) : ''; ?></span></td>
<td><?php echo htmlspecialchars($a->ip ?: '—'); ?></td>
<td class="status"><span class="st-dot <?php echo $stDot; ?>"></span><?php echo ucfirst($a->status ?: 'Active'); ?></td>
<td class="usage"><?php echo accSize($dmb); ?> / <?php echo $pkgGB ? accSize($pkgGB) : '∞'; ?><div class="bar"><span style="width:<?php echo $dpct; ?>%"></span></div></td>
<td class="usage"><?php echo accSize($bmb); ?> / <?php echo $bwGB ? accSize($bwGB) : '∞'; ?><div class="bar bw"><span style="width:<?php echo $bpct; ?>%"></span></div></td>
<td style="white-space:nowrap;color:#819ab7"><?php echo htmlspecialchars(date('Y-m-d', strtotime($a->created_at ?? 'now'))); ?><br><small style="color:#5d7897"><?php echo htmlspecialchars(date('h:i A', strtotime($a->created_at ?? 'now'))); ?></small></td>
<td class="actions">
<a class="act b" href="/admin/account/show/<?php echo (int)$a->id; ?>" title="Manage"><i class="bi bi-gear"></i></a>
<a class="act b" href="/admin/account/login-as/<?php echo (int)$a->id; ?>" title="Login as user" onclick="return confirm('Login as <?php echo htmlspecialchars($a->username); ?>?')"><i class="bi bi-person"></i></a>
<a class="act g" href="/admin/account/unsuspend/<?php echo (int)$a->id; ?>" title="Unsuspend"><i class="bi bi-play-fill"></i></a>
<form method="POST" style="display:inline" action="/admin/account/suspend/<?php echo (int)$a->id; ?>" onsubmit="return confirm('Suspend this account?')"><button class="act y" title="Suspend"><i class="bi bi-pause"></i></button></form>
<a class="act r" href="/admin/account/delete/<?php echo (int)$a->id; ?>" title="Delete" onclick="return confirm('DELETE account <?php echo htmlspecialchars($a->username); ?> permanently? This cannot be undone.')"><i class="bi bi-trash"></i></a>
<a class="act d" href="/admin/account/edit/<?php echo (int)$a->id; ?>" title="Edit"><i class="bi bi-pencil"></i></a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>
<?php endforeach; ?>

<?php if (empty($allAccounts)): ?>
<div class="panel" style="text-align:center;padding:34px;color:var(--muted)">No accounts found.</div>
<?php endif; ?>

<div class="bottom">
<section class="panel quick">
<a class="quick-card" href="/admin/account/create"><div class="qi ic-blue"><i class="bi bi-plus-lg"></i></div><strong>Add Account</strong><small>Create a new hosting account</small></a>
<a class="quick-card" href="/admin/account"><div class="qi ic-blue"><i class="bi bi-gear"></i></div><strong>Manage</strong><small>View and edit account details</small></a>
<a class="quick-card" href="/admin/account"><div class="qi ic-yellow"><i class="bi bi-pause"></i></div><strong>Suspend</strong><small>Suspend account and services</small></a>
<a class="quick-card" href="/admin/packages"><div class="qi ic-purple"><i class="bi bi-stack"></i></div><strong>Packages</strong><small>Upgrade or change package</small></a>
<a class="quick-card" href="/admin/account"><div class="qi ic-cyan"><i class="bi bi-key"></i></div><strong>Password</strong><small>Reset user password</small></a>
<a class="quick-card" href="/admin/account"><div class="qi ic-cyan"><i class="bi bi-box-arrow-in-right"></i></div><strong>Login as</strong><small>Access account as the user</small></a>
<a class="quick-card" href="/admin/account"><div class="qi ic-red"><i class="bi bi-trash"></i></div><strong>Delete</strong><small>Permanently delete account</small></a>
</section>
<aside class="panel tips">
<h3><i class="bi bi-lightbulb"></i> Quick Tips</h3>
<p><i class="bi bi-check-circle-fill"></i>Use the search box to quickly find accounts.</p>
<p><i class="bi bi-check-circle-fill"></i>Filter by package or status to narrow results.</p>
<p><i class="bi bi-check-circle-fill"></i>Disk and bandwidth usage are shown visually.</p>
<p><i class="bi bi-check-circle-fill"></i>Click a group header to expand or collapse.</p>
<p><i class="bi bi-check-circle-fill"></i>Always backup before deleting an account.</p>
</aside>
</div>

</div>

<script>
function toggleGroup(head) { head.closest('.acct-group').classList.toggle('collapsed'); }
</script>