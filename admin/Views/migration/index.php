<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<?php if (isset($_SESSION['error_message'])): ?>
<div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['error_message']); ?></div>
<?php endif; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:16px;border-bottom:1px solid rgba(255,255,255,.06);padding-bottom:8px">
<a href="/admin/migration" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;<?php echo empty($adapterView) ? 'background:rgba(0,191,255,.1);color:#00bfff;border-bottom:2px solid #008cff' : 'color:var(--text-secondary)'; ?>">🔄 Migration Center</a>
<a href="/admin/restore-center" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🔄 Restore Center</a>
<a href="/admin/migration/adapters" style="padding:8px 14px;border-radius:6px 6px 0 0;text-decoration:none;font-size:13px;color:var(--text-secondary)">🔌 Adapters</a>
</div>

<?php if (!empty($adapterView)): ?>
<h3 style="color:var(--accent);margin-bottom:12px">Migration Adapters</h3>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:10px">
<?php foreach ($adapters as $key => $a): ?>
<div class="card" style="margin-bottom:0;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:start">
<div><span style="font-size:22px"><?php echo $a['icon'] ?? '🔌'; ?></span>
<span style="font-weight:600;font-size:14px;margin-left:6px"><?php echo $a['name']; ?></span></div>
<span style="font-size:10px;color:#64748b">v<?php echo $a['version'] ?? '1.0'; ?></span>
</div>
<div style="font-size:11px;color:#64748b;margin-top:4px">Port: <?php echo $a['port']; ?> · Class: <code style="font-size:9px"><?php echo $a['adapter_class']; ?></code></div>
<div style="font-size:10px;color:#64748b;margin-top:2px">Sources: <?php echo implode(', ', $a['source_types']); ?> · Types: <?php echo implode(', ', $a['migration_types']); ?></div>
</div>
<?php endforeach; ?>
</div>

<?php else: ?>

<?php
$wizardSteps = ['Select Panel', 'Source Server', 'Select Accounts', 'Analyze', 'Compatibility', 'Package Mapping', 'Options', 'Migrate', 'Verify', 'Report'];
$stepLabels = ['Choose panel to migrate from', 'Enter source server credentials', 'Choose accounts to migrate', 'Scan source server data', 'Check compatibility', 'Map packages', 'Configure migration', 'Migration in progress', 'Verification', 'Migration complete'];
?>

<div style="display:flex;gap:4px;margin-bottom:12px;overflow-x:auto">
<?php foreach ($wizardSteps as $i => $s): $num = $i + 1; ?>
<div style="flex:1;min-width:55px;text-align:center;padding:6px 3px;border-radius:6px;font-size:9px;font-weight:600;
background:<?php echo $step > $num ? '#1a3a2a' : ($step === $num ? 'var(--accent)' : 'var(--bg-card)'); ?>;
color:<?php echo $step >= $num ? '#fff' : 'var(--text-secondary)'; ?>">
<?php echo $num; ?></div>
<?php endforeach; ?>
</div>
<div style="font-size:10px;color:#64748b;margin-bottom:16px;text-align:center"><?php echo $stepLabels[$step-1] ?? ''; ?></div>

<?php if ($step === 1): ?>
<h3 style="color:var(--accent);margin-bottom:12px">1. Select Source Panel</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:14px">Choose the control panel or platform to migrate services from into Planet Hosts:</p>

<div style="margin-bottom:16px">
<div style="font-size:11px;color:var(--text-secondary);margin-bottom:6px;font-weight:600">🌐 Web Hosting Panels</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:8px">
<?php $webPanels = ['cpanel','plesk','directadmin','cyberpanel','aapanel','hestiacp','ispconfig','virtualmin','webmin','cwp','froxlor','vestacp','cloudpanel','enhance']; ?>
<?php foreach ($adapters as $key => $a): if (!in_array($key, $webPanels)) continue; ?>
<div class="card" style="margin-bottom:0;padding:12px;cursor:pointer;border:2px solid transparent;transition:.15s"
     onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='transparent'"
     onclick="selectPanel('<?php echo $key; ?>', '<?php echo $a['name']; ?>', '<?php echo $a['icon']; ?>', <?php echo $a['port']; ?>)">
<div style="font-size:22px;margin-bottom:2px"><?php echo $a['icon'] ?? '🔌'; ?></div>
<div style="font-weight:600;font-size:13px"><?php echo $a['name']; ?></div>
<div style="font-size:9px;color:#64748b;margin-top:2px"><?php echo implode(', ', $a['source_types']); ?></div>
</div>
<?php endforeach; ?>
</div></div>

<div style="margin-bottom:16px">
<div style="font-size:11px;color:var(--text-secondary);margin-bottom:6px;font-weight:600">📻 Streaming Panels</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:8px">
<?php foreach ($adapters as $key => $a): if (in_array($key, $webPanels) || $key === 'custom') continue; ?>
<div class="card" style="margin-bottom:0;padding:12px;cursor:pointer;border:2px solid transparent;transition:.15s"
     onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='transparent'"
     onclick="selectPanel('<?php echo $key; ?>', '<?php echo $a['name']; ?>', '<?php echo $a['icon']; ?>', <?php echo $a['port']; ?>)">
<div style="font-size:22px;margin-bottom:2px"><?php echo $a['icon'] ?? '🔌'; ?></div>
<div style="font-weight:600;font-size:13px"><?php echo $a['name']; ?></div>
<div style="font-size:9px;color:#64748b;margin-top:2px"><?php echo implode(', ', $a['source_types']); ?></div>
</div>
<?php endforeach; ?>
</div></div>

<div style="font-size:11px;color:var(--text-secondary);margin-bottom:6px;font-weight:600">📦 Custom Import</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(190px,1fr));gap:8px;margin-bottom:16px">
<?php if (isset($adapters['custom'])): $c = $adapters['custom']; ?>
<div class="card" style="margin-bottom:0;padding:12px;cursor:pointer;border:2px solid transparent;transition:.15s"
     onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='transparent'"
     onclick="selectPanel('custom', '<?php echo $c['name']; ?>', '<?php echo $c['icon']; ?>', 0)">
<div style="font-size:22px;margin-bottom:2px"><?php echo $c['icon']; ?></div>
<div style="font-weight:600;font-size:13px"><?php echo $c['name']; ?></div>
<div style="font-size:9px;color:#64748b;margin-top:2px">ZIP, TAR, CSV, JSON, SQL, FTP, SFTP, SCP, RSYNC</div>
</div>
<?php endif; ?>
</div>

<div id="connectForm" class="card hidden" style="max-width:620px;margin:0 auto 16px">
<form method="POST" action="/admin/migration/start">
<h4 style="color:var(--accent);margin-bottom:10px"><span id="connectIcon">🔌</span> Connect to <span id="connectPanel">Panel</span></h4>
<input type="hidden" name="source_type" id="sourceType">
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
<div class="form-group"><label>Hostname / IP Address</label><input name="source_host" id="sourceHost" required placeholder="192.168.1.100"></div>
<div class="form-group"><label>SSH Port</label><input name="source_port" id="sourcePort" placeholder="22"></div>
<div class="form-group"><label>SSH Username</label><input name="source_username" placeholder="root"></div>
<div class="form-group"><label>SSH Password</label><input name="source_password" type="password" placeholder="SSH password"></div>
<div class="form-group"><label>SSH Key (optional)</label><textarea name="ssh_key" rows="2" placeholder="-----BEGIN RSA PRIVATE KEY-----" style="width:100%;padding:6px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:11px;outline:none;font-family:monospace"></textarea></div>
<div class="form-group"><label>Control Panel URL</label><input name="panel_url" placeholder="https://server:2083"></div>
<div class="form-group"><label>API Key (if available)</label><input name="api_key" placeholder="API token or key"></div>
</div>
<div class="form-group" style="margin-top:6px"><label>Migration Type</label>
<select name="migration_type" class="form-control"><option value="single_customer">Single Customer</option><option value="multi_customer">Multiple Customers</option><option value="entire_server">Entire Server</option><option value="reseller">Reseller</option><option value="email_only">Email Only</option><option value="database_only">Database Only</option><option value="dns_only">DNS Only</option><option value="streaming">Streaming Account</option></select></div>
<div class="form-group"><label>Transport Method</label>
<select name="source_transport" class="form-control"><option value="live_ssh">Live Server (SSH)</option><option value="remote_api">Remote API</option><option value="local_backup">Local Backup</option><option value="remote_backup">Remote Backup</option><option value="ftp">FTP</option><option value="sftp">SFTP</option><option value="scp">SCP</option><option value="rsync">Rsync</option></select></div>
<div style="display:flex;gap:8px;margin-top:8px">
<button type="button" class="btn secondary" onclick="testConnection()" style="flex:1">🔍 Test Connection</button>
<button type="submit" class="btn primary" style="flex:2">▶ Begin Analysis</button>
</div>
<div id="connectionResult" style="margin-top:8px;display:none"></div>
</form></div>

<script>
let selectedPanel = '';
function selectPanel(key, name, icon, port) {
    selectedPanel = key;
    document.getElementById('sourceType').value = key;
    document.getElementById('sourceHost').placeholder = name + ' hostname or IP';
    document.getElementById('sourcePort').value = port || 22;
    document.getElementById('connectPanel').textContent = name;
    document.getElementById('connectIcon').textContent = icon;
    document.getElementById('connectForm').classList.remove('hidden');
    document.getElementById('connectForm').scrollIntoView({behavior:'smooth'});
}
function testConnection() {
    const btn = document.querySelector('button[onclick="testConnection()"]');
    btn.disabled = true; btn.textContent = '⏳ Testing...';
    const result = document.getElementById('connectionResult');
    result.style.display = 'block';
    result.innerHTML = '<div style="padding:8px;border-radius:6px;background:rgba(0,191,255,.08);color:#38bdf8">Testing connection...</div>';
    const form = document.querySelector('#connectForm form');
    const fd = new FormData(form);
    fetch('/admin/migration/test-connection', { method: 'POST', body: fd })
    .then(r => r.json()).then(d => {
        if (d.connected) {
            result.innerHTML = '<div style="padding:8px;border-radius:6px;background:rgba(74,222,128,.1);color:#4ade80">✓ Connection successful! ' + (d.server_info || '') + '</div>';
        } else {
            result.innerHTML = '<div style="padding:8px;border-radius:6px;background:rgba(248,113,113,.1);color:#f87171">✗ Connection failed: ' + (d.error || 'Unknown') + '</div>';
        }
    }).catch(e => {
        result.innerHTML = '<div style="padding:8px;border-radius:6px;background:rgba(248,113,113,.1);color:#f87171">✗ Error: ' + e.message + '</div>';
    }).finally(() => { btn.disabled = false; btn.textContent = '🔍 Test Connection'; });
}
</script>

<?php elseif ($step === 2 && $job): ?>
<h3 style="color:var(--accent);margin-bottom:12px">2. Source Server</h3>
<div class="card" style="margin-bottom:14px;padding:14px">
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px">
<div><strong style="color:var(--accent)">Job #<?php echo $job->id; ?></strong>
<span style="font-size:12px;color:#64748b;margin-left:8px"><?php echo $job->source_type; ?> → <?php echo $job->migration_type; ?> (<?php echo $job->source_transport; ?>)</span></div>
<span class="status-badge status-<?php echo $job->status === 'completed' ? 'active' : ($job->status === 'failed' ? 'terminated' : ''); ?>"><?php echo $job->status; ?></span>
</div>
<div style="font-size:11px;color:#64748b;margin-top:6px"><?php echo $job->source_host; ?>:<?php echo $job->source_port; ?> as <?php echo $job->source_username; ?></div>
</div>

<a href="/admin/migration/run-preflight/<?php echo $job->id; ?>" class="btn primary">▶ Run Pre-flight Analysis</a>
<a href="/admin/migration" class="btn secondary">← Cancel</a>

<?php elseif ($step === 3 && $job): $preflight = json_decode($job->preflight_data ?? '{}', true); $analysis = $job->analysis_data ? json_decode($job->analysis_data, true) : []; ?>
<h3 style="color:var(--accent);margin-bottom:12px">3. Select Customer Accounts</h3>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px">
<span class="btn btn-sm secondary" onclick="document.getElementById('accountSearch').focus()">🔍 Search</span>
<span class="btn btn-sm secondary" onclick="document.querySelectorAll('.account-checkbox').forEach(c=>c.checked=true)">Select All</span>
<span class="btn btn-sm secondary" onclick="document.querySelectorAll('.account-checkbox').forEach(c=>c.checked=false)">Deselect All</span>
</div>
<input id="accountSearch" placeholder="Search accounts..." style="width:100%;padding:8px 12px;border-radius:8px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#fff;font-size:13px;outline:none;margin-bottom:12px;box-sizing:border-box"
oninput="filterAccounts(this.value)">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:8px;margin-bottom:14px" id="accountList">
<?php foreach ($preflight['accounts'] ?? [] as $a): ?>
<label class="card" style="margin-bottom:0;padding:10px;cursor:pointer;display:flex;gap:8px;align-items:start;border:2px solid transparent" data-search="<?php echo strtolower(($a['username']??'').' '.($a['domain']??'').' '.($a['plan']??'')); ?>">
<input type="checkbox" class="account-checkbox" form="migrateForm" name="selected_accounts[]" value="<?php echo htmlspecialchars($a['username']); ?>" checked style="margin-top:3px">
<div style="flex:1">
<div style="font-weight:600;font-size:13px"><?php echo htmlspecialchars($a['username'] ?: 'N/A'); ?></div>
<div style="font-size:10px;color:#64748b"><?php echo htmlspecialchars($a['domain'] ?: 'No domain'); ?> · <?php echo htmlspecialchars($a['plan'] ?: 'N/A'); ?></div>
<div style="font-size:10px;color:#64748b;margin-top:2px">Disk: <?php echo round((float)($a['disk_used']??0), 1); ?>MB · PHP: <?php echo $a['php_version'] ?? 'N/A'; ?></div>
</div>
</label>
<?php endforeach; ?>
</div>
<form id="migrateForm" method="POST" action="/admin/migration/select-accounts?job=<?php echo $job->id; ?>">
<button type="submit" class="btn primary">Continue with Selected (<?php echo count($preflight['accounts'] ?? []); ?>)</button>
</form>
<script>
function filterAccounts(q) {
    q = q.toLowerCase();
    document.querySelectorAll('#accountList .card').forEach(c => {
        c.style.display = c.dataset.search.includes(q) ? 'flex' : 'none';
    });
}
</script>

<?php elseif ($step === 4 && $job): $preflight = json_decode($job->preflight_data ?? '{}', true); $analysis = $job->analysis_data ? json_decode($job->analysis_data, true) : []; $s = $analysis['summary'] ?? $preflight; ?>
<h3 style="color:var(--accent);margin-bottom:12px">4. Analyze Server</h3>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(130px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Accounts</h3><div class="value"><?php echo $s['total_accounts'] ?? count($preflight['accounts'] ?? []); ?></div></div>
<div class="stat-card"><h3>Domains</h3><div class="value"><?php echo $s['total_domains'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>Databases</h3><div class="value"><?php echo $s['total_databases'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>Email</h3><div class="value"><?php echo $s['total_email_accounts'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>FTP</h3><div class="value"><?php echo $s['total_ftp_accounts'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>SSL</h3><div class="value"><?php echo $s['total_ssl_certificates'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>Cron</h3><div class="value"><?php echo $s['total_cron_jobs'] ?? '?'; ?></div></div>
<div class="stat-card"><h3>DNS Zones</h3><div class="value"><?php echo $s['total_dns_zones'] ?? '?'; ?></div></div>
</div>

<?php if ($analysis): ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
<div class="card" style="margin-bottom:0;padding:14px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:13px">💾 Disk & Bandwidth</h4>
<div style="font-size:12px;color:#64748b">Disk: <?php echo $s['total_disk_used_gb'] ?? '0'; ?> GB used</div>
<div style="font-size:12px;color:#64748b">Bandwidth: <?php echo round(($s['total_bandwidth_used_mb'] ?? 0)/1024, 2); ?> GB used</div>
</div>
<div class="card" style="margin-bottom:0;padding:14px">
<h4 style="color:var(--accent);margin-bottom:8px;font-size:13px">⏱ Estimated Time</h4>
<div style="font-size:16px;font-weight:600;color:var(--text-primary)"><?php echo $analysis['estimated_time_human'] ?? 'Calculating...'; ?></div>
<div style="font-size:10px;color:#64748b">Based on accounts, databases, email, and disk usage</div>
</div>
</div>
<?php endif; ?>

<?php if (!empty($analysis['potential_issues'])): ?>
<h4 style="color:var(--accent);margin-bottom:8px">⚠ Potential Issues</h4>
<div style="display:grid;gap:6px;margin-bottom:14px">
<?php foreach ($analysis['potential_issues'] as $issue): $c = $issue['severity'] === 'warning' ? '#f59e0b' : '#3b82f6'; ?>
<div style="padding:8px 12px;border-left:3px solid <?php echo $c; ?>;background:rgba(255,255,255,.02);border-radius:6px">
<strong style="font-size:12px"><?php echo $issue['icon']; ?> <?php echo $issue['title']; ?></strong>
<div style="font-size:10px;color:#64748b;margin-top:2px"><?php echo $issue['detail']; ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($analysis['php_version_distribution'])): ?>
<h4 style="color:var(--accent);margin-bottom:6px">🐘 PHP Version Distribution</h4>
<div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px">
<?php foreach ($analysis['php_version_distribution'] as $v => $c): ?>
<div style="padding:4px 10px;background:rgba(0,191,255,.06);border-radius:6px;font-size:11px">PHP <?php echo $v; ?>: <strong><?php echo $c; ?></strong></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<a href="/admin/migration/step/5?job=<?php echo $job->id; ?>" class="btn primary">Continue to Compatibility Check →</a>

<?php elseif ($step === 5 && $job): $compat = json_decode($job->compat_data ?? '{}', true); ?>
<h3 style="color:var(--accent);margin-bottom:12px">5. Compatibility Check</h3>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:16px">
<?php
$checks = [
    'php' => ['PHP Version', $compat['php_compatible'] ?? true, $compat['php_warning'] ?? ''],
    'mariadb' => ['MariaDB', $compat['mariadb_compatible'] ?? true, $compat['mariadb_warning'] ?? ''],
    'mysql' => ['MySQL', $compat['mysql_compatible'] ?? true, $compat['mysql_warning'] ?? ''],
    'apache' => ['Apache', $compat['apache_compatible'] ?? true, $compat['apache_warning'] ?? ''],
    'nginx' => ['Nginx', $compat['nginx_compatible'] ?? true, $compat['nginx_warning'] ?? ''],
    'ssl' => ['SSL', $compat['ssl_compatible'] ?? true, $compat['ssl_warning'] ?? ''],
    'email' => ['Email', $compat['email_compatible'] ?? true, $compat['email_warning'] ?? ''],
    'dns' => ['DNS', $compat['dns_compatible'] ?? true, $compat['dns_warning'] ?? ''],
    'streaming' => ['Streaming', $compat['streaming_compatible'] ?? true, $compat['streaming_warning'] ?? ''],
];
foreach ($checks as $ck => $cv):
    $ok = $cv[1];
    $warn = $cv[2];
?>
<div class="card" style="margin-bottom:0;padding:12px;border-left:3px solid <?php echo $ok ? '#4ade80' : '#f59e0b'; ?>">
<div style="display:flex;justify-content:space-between;align-items:center">
<span style="font-weight:600;font-size:12px"><?php echo $cv[0]; ?></span>
<span style="font-size:16px"><?php echo $ok ? '✓' : '⚠'; ?></span>
</div>
<?php if ($warn): ?><div style="font-size:9px;color:#f59e0b;margin-top:4px"><?php echo $warn; ?></div><?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php if (!empty($compat['warnings'])): ?>
<div style="padding:8px 12px;background:rgba(245,158,11,.08);border-radius:6px;margin-bottom:14px">
<strong style="font-size:12px;color:#f59e0b">⚠ Warnings</strong>
<ul style="margin:4px 0 0;padding-left:16px;font-size:11px;color:#64748b">
<?php foreach ($compat['warnings'] as $w): ?><li><?php echo htmlspecialchars($w); ?></li><?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<a href="/admin/migration/step/6?job=<?php echo $job->id; ?>" class="btn primary">Continue to Package Mapping →</a>

<?php elseif ($step === 6 && $job): $preflight = json_decode($job->preflight_data ?? '{}', true); ?>
<h3 style="color:var(--accent);margin-bottom:12px">6. Package Mapping</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:12px">Map source control panel packages to Planet Hosts packages. Unmapped packages will be auto-created.</p>
<form method="POST" action="/admin/migration/save-package-map?job=<?php echo $job->id; ?>">
<div style="overflow-x:auto;margin-bottom:12px">
<table style="width:100%;border-collapse:collapse;font-size:12px">
<thead><tr style="background:var(--bg-card);border-bottom:1px solid rgba(255,255,255,.06)">
<th style="padding:8px;text-align:left">User</th><th style="padding:8px;text-align:left">Domain</th><th style="padding:8px;text-align:left">Source Package</th><th style="padding:8px;text-align:left">Planet Hosts Package</th><th style="padding:8px;text-align:center">Include</th></tr></thead>
<tbody>
<?php foreach ($preflight['accounts'] ?? [] as $i => $a): ?>
<tr style="border-bottom:1px solid rgba(255,255,255,.04)">
<td style="padding:8px"><?php echo htmlspecialchars($a['username']); ?></td>
<td style="padding:8px;font-size:10px;color:#64748b"><?php echo htmlspecialchars($a['domain'] ?: '-'); ?></td>
<td style="padding:8px"><code style="font-size:10px"><?php echo htmlspecialchars($a['plan'] ?: 'N/A'); ?></code></td>
<td style="padding:8px"><select name="package_map[<?php echo $a['plan'] ?: 'default'; ?>]" class="form-control" style="font-size:11px">
<option value="create_new">✨ Auto-create matching package</option>
<option value="">— Skip (no package)</option>
<?php foreach ($packages as $p): ?>
<option value="<?php echo $p->id; ?>"><?php echo htmlspecialchars($p->name); ?></option>
<?php endforeach; ?>
</select></td>
<td style="padding:8px;text-align:center"><input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($a['username']); ?>" checked></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<div style="display:flex;gap:8px">
<button type="submit" class="btn primary">Apply Mapping & Continue</button>
</div>
</form>

<?php elseif ($step === 7 && $job): ?>
<h3 style="color:var(--accent);margin-bottom:12px">7. Migration Options</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:12px">Select what data to migrate to Planet Hosts:</p>
<form method="POST" action="/admin/migration/start-migration?job=<?php echo $job->id; ?>">
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:16px">
<?php
$options = [
    'home_directory' => ['Home Directory', '📁', true],
    'website_files' => ['Website Files', '🌐', true],
    'databases' => ['Databases', '🗄️', true],
    'email' => ['Email Accounts', '📧', true],
    'ftp' => ['FTP Accounts', '📂', true],
    'ssl' => ['SSL Certificates', '🔒', true],
    'cron' => ['Cron Jobs', '⏰', true],
    'dns' => ['DNS Zones', '🌍', true],
    'packages' => ['Packages', '📦', true],
    'users' => ['Users', '👤', true],
    'streaming' => ['Streaming', '📻', true],
    'game_servers' => ['Game Servers', '🎮', true],
    'backups' => ['Backups', '💾', true],
    'settings' => ['Settings', '⚙️', true],
];
foreach ($options as $ok => $ov):
?>
<label class="card" style="margin-bottom:0;padding:10px;cursor:pointer;display:flex;align-items:center;gap:8px;border:2px solid transparent"
       onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='transparent'">
<input type="checkbox" name="migration_options[]" value="<?php echo $ok; ?>" <?php echo $ov[2] ? 'checked' : ''; ?> style="width:16px;height:16px;accent-color:var(--accent)">
<div><div style="font-weight:600;font-size:13px"><?php echo $ov[1]; ?> <?php echo $ov[0]; ?></div></div>
</label>
<?php endforeach; ?>
</div>
<div style="display:flex;gap:8px">
<button type="submit" class="btn primary">▶ Start Migration</button>
<a href="/admin/migration?step=6&job=<?php echo $job->id; ?>" class="btn secondary">← Back</a>
</div>
</form>

<?php elseif ($step === 8 && $job): ?>
<h3 style="color:var(--accent);margin-bottom:12px">8. Migration in Progress</h3>
<div class="card" style="margin-bottom:14px;padding:14px">
<div style="display:flex;justify-content:space-between;margin-bottom:8px">
<span style="font-size:12px;color:#64748b">Progress</span>
<span style="font-size:12px;font-weight:600" id="progressPct">0%</span>
</div>
<div style="height:8px;background:rgba(255,255,255,.06);border-radius:4px;overflow:hidden;margin-bottom:12px">
<div id="progressBar" style="height:100%;width:0%;background:linear-gradient(90deg,#008cff,#3bb8ff);border-radius:4px;transition:width .5s"></div>
</div>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px">
<div><span style="font-size:10px;color:#64748b">Current File</span><br><span id="currentFile" style="font-size:11px;font-family:monospace">Waiting...</span></div>
<div><span style="font-size:10px;color:#64748b">Transfer Speed</span><br><span id="transferSpeed" style="font-size:11px">-- MB/s</span></div>
<div><span style="font-size:10px;color:#64748b">Estimated Time</span><br><span id="etaTime" style="font-size:11px">--</span></div>
<div><span style="font-size:10px;color:#64748b">Current Account</span><br><span id="currentAccount" style="font-size:11px">--</span></div>
</div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
<div class="card" style="margin-bottom:0;padding:12px">
<h4 style="color:var(--accent);margin-bottom:6px;font-size:12px">📋 Live Log</h4>
<div id="liveLog" style="font-size:10px;font-family:monospace;max-height:300px;overflow-y:auto;background:rgba(0,0,0,.3);padding:8px;border-radius:4px;color:#8b949e;line-height:1.6"></div>
</div>
<div class="card" style="margin-bottom:0;padding:12px">
<h4 style="color:var(--accent);margin-bottom:6px;font-size:12px">⚠ Errors & Warnings</h4>
<div id="errorLog" style="font-size:10px;font-family:monospace;max-height:300px;overflow-y:auto;background:rgba(0,0,0,.3);padding:8px;border-radius:4px;color:#f87171;line-height:1.6"></div>
</div>
</div>

<script>
let pollInterval;
(function startMigrate() {
    fetch('/admin/migration/execute?job=<?php echo $job->id; ?>', { method: 'POST' }).then(r => r.json()).then(d => {
        pollInterval = setInterval(() => pollProgress(<?php echo $job->id; ?>), 1000);
    });
})();
function pollProgress(jobId) {
    fetch('/admin/migration/progress/' + jobId).then(r => r.json()).then(d => {
        const pct = d.progress || 0;
        document.getElementById('progressBar').style.width = pct + '%';
        document.getElementById('progressPct').textContent = pct + '%';
        if (d.current_file) document.getElementById('currentFile').textContent = d.current_file;
        if (d.speed) document.getElementById('transferSpeed').textContent = d.speed;
        if (d.eta) document.getElementById('etaTime').textContent = d.eta;
        if (d.current_account) document.getElementById('currentAccount').textContent = d.current_account;
        if (d.log) document.getElementById('liveLog').innerHTML = d.log.split('\n').map(l => '<div>' + l + '</div>').join('');
        if (d.errors) document.getElementById('errorLog').innerHTML = d.errors.split('\n').map(l => '<div>' + l + '</div>').join('');
        if (d.completed || pct >= 100) {
            clearInterval(pollInterval);
            setTimeout(() => window.location.href = '/admin/migration?step=9&job=' + jobId, 1500);
        }
    }).catch(() => {});
}
</script>

<?php elseif ($step === 9 && $job): $validation = json_decode($job->validation_data ?? '{}', true); ?>
<h3 style="color:var(--accent);margin-bottom:12px">9. Verification</h3>
<p style="font-size:12px;color:#64748b;margin-bottom:12px">Verifying migrated data integrity and services:</p>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-bottom:16px">
<?php
$verifyTypes = ['files','databases','email','dns','ssl','streaming','permissions','services','checksums'];
$verifyLabels = ['Files','Databases','Email','DNS','SSL','Streaming','Permissions','Services','Checksums'];
$verifyIcons = ['📁','🗄️','📧','🌍','🔒','📻','🔐','⚙️','✅'];
foreach ($verifyTypes as $vi => $vt):
    $vr = $validation['results'][$vt] ?? null;
    $ok = $vr ? $vr['passed'] : null;
?>
<div class="card" style="margin-bottom:0;padding:12px;border-left:3px solid <?php echo $ok === null ? '#64748b' : ($ok ? '#4ade80' : '#f87171'); ?>">
<div style="display:flex;justify-content:space-between;align-items:center">
<span style="font-weight:600;font-size:12px"><?php echo $verifyIcons[$vi]; ?> <?php echo $verifyLabels[$vi]; ?></span>
<span style="font-size:14px"><?php echo $ok === null ? '⏳' : ($ok ? '✓' : '✗'); ?></span>
</div>
<?php if ($vr): ?>
<div style="font-size:10px;color:#64748b;margin-top:4px"><?php echo $vr['passed_count'] ?? 0; ?>/<?php echo $vr['total'] ?? 0; ?> passed</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>

<?php if (!empty($validation['errors'])): ?>
<div style="padding:8px 12px;background:rgba(248,113,113,.08);border-radius:6px;margin-bottom:14px">
<strong style="font-size:12px;color:#f87171">Verification Errors</strong>
<ul style="margin:4px 0 0;padding-left:16px;font-size:11px;color:#64748b">
<?php foreach ((array)$validation['errors'] as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<a href="/admin/migration/complete/<?php echo $job->id; ?>" class="btn primary">Continue to Report →</a>

<?php elseif ($step === 10 && $job): $validation = json_decode($job->validation_data ?? '{}', true); $analysis = $job->analysis_data ? json_decode($job->analysis_data, true) : []; ?>
<h3 style="color:var(--accent);margin-bottom:12px">10. Migration Report</h3>

<div class="stats-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));margin-bottom:16px">
<div class="stat-card"><h3>Status</h3><div class="value" style="font-size:16px;color:<?php echo $job->status === 'completed' ? '#4ade80' : '#f87171'; ?>"><?php echo strtoupper($job->status); ?></div></div>
<div class="stat-card"><h3>Migrated</h3><div class="value"><?php echo $job->items_migrated; ?>/<?php echo $job->total_items; ?></div></div>
<div class="stat-card"><h3>Source</h3><div class="value" style="font-size:12px"><?php echo $job->source_type; ?></div></div>
<div class="stat-card"><h3>Duration</h3><div class="value" style="font-size:14px"><?php echo $job->completed_at ? (strtotime($job->completed_at) - strtotime($job->created_at)) . 's' : '-'; ?></div></div>
</div>

<?php if (!empty($analysis['potential_issues'])): ?>
<div class="card" style="margin-bottom:14px;padding:14px">
<h4 style="color:var(--accent);margin-bottom:8px">⚠ Warnings</h4>
<?php foreach ($analysis['potential_issues'] as $issue): ?>
<div style="padding:4px 0;font-size:11px;color:#64748b">• <?php echo $issue['title']; ?>: <?php echo $issue['detail']; ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($analysis['recommendations'])): ?>
<div class="card" style="margin-bottom:14px;padding:14px">
<h4 style="color:var(--accent);margin-bottom:8px">📋 Recommendations</h4>
<?php foreach ($analysis['recommendations'] as $r): ?>
<div style="padding:4px 0;font-size:11px;color:#64748b">• <?php echo $r['text']; ?></div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($validation['results'])): ?>
<div class="card" style="margin-bottom:14px;padding:14px">
<h4 style="color:var(--accent);margin-bottom:8px">✅ Verification Summary</h4>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;font-size:11px">
<?php foreach ($validation['results'] as $type => $vr): ?>
<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.04)">
<span style="text-transform:capitalize"><?php echo $type; ?></span>
<span style="color:<?php echo $vr['passed'] ? '#4ade80' : '#f87171'; ?>"><?php echo $vr['passed'] ? '✓' : '✗'; ?> <?php echo $vr['passed_count'] ?? 0; ?>/<?php echo $vr['total'] ?? 0; ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
<?php endif; ?>

<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="/admin/migration/report/<?php echo $job->id; ?>" class="btn primary" target="_blank">📄 View Full Report</a>
<a href="/admin/migration/report-pdf/<?php echo $job->id; ?>" class="btn secondary">📥 Download PDF Report</a>
<a href="/admin/migration/rollback?job=<?php echo $job->id; ?>" class="btn btn-sm danger" onclick="return confirm('Rollback this entire migration?')">↩ Rollback Migration</a>
<a href="/admin/migration" class="btn secondary">🔄 New Migration</a>
</div>

<?php endif; ?>

<?php if (!empty($jobs) && $step < 2): ?>
<h4 style="color:var(--accent);margin:20px 0 8px">Recent Migration Jobs</h4>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:8px">
<?php foreach ($jobs as $j): ?>
<div class="card" style="margin-bottom:0;padding:10px;cursor:pointer;transition:.15s"
     onmouseover="this.style.borderColor='var(--accent)'" onmouseout="this.style.borderColor='var(--bg-card)'"
     onclick="window.location.href='/admin/migration?job=<?php echo $j->id; ?>&step=<?php echo $j->step > 1 ? min($j->step, 10) : 2; ?>'">
<div style="display:flex;justify-content:space-between;align-items:center">
<span style="font-weight:600;font-size:12px">#<?php echo $j->id; ?> <?php echo $j->source_type; ?></span>
<span class="status-badge status-<?php echo $j->status === 'completed' ? 'active' : ($j->status === 'failed' ? 'terminated' : ''); ?>" style="font-size:9px"><?php echo $j->status; ?></span>
</div>
<div style="font-size:10px;color:#64748b;margin-top:2px"><?php echo $j->migration_type; ?> · <?php echo $j->source_host; ?></div>
<div style="font-size:10px;color:#64748b"><?php echo $j->items_migrated; ?>/<?php echo $j->total_items; ?> items · <?php echo $j->created_at; ?></div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if (!empty($job->error_message) && $step >= 2): ?>
<div style="font-size:11px;color:#f87171;margin-top:8px;padding:8px;background:rgba(248,113,113,.1);border-radius:6px"><?php echo htmlspecialchars($job->error_message); ?></div>
<?php endif; ?>
<?php if (!empty($job->log) && $step >= 8): ?>
<details style="margin-top:8px"><summary style="font-size:11px;color:var(--accent);cursor:pointer">📋 View Full Log</summary>
<pre style="font-size:9px;background:rgba(0,0,0,.3);padding:8px;border-radius:4px;max-height:200px;overflow-y:auto;margin-top:4px;color:#8b949e"><?php echo htmlspecialchars($job->log); ?></pre>
</details>
<?php endif; ?>

<?php endif; ?>
