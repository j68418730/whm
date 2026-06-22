<h2 style="margin-bottom:16px">🖥 Process Manager</h2>
<?php
$search = $_GET['search'] ?? '';
$cmd = 'ps aux --sort=-%mem 2>/dev/null | head -60';
if ($search) $cmd = "ps aux | grep -i " . escapeshellarg($search) . " | head -60";
$output = shell_exec($cmd);
$lines = explode("\n", trim($output ?? ''));
$header = array_shift($lines);

if ($_GET['action'] === 'kill' && isset($_GET['pid'])) {
    $pid = (int)$_GET['pid'];
    exec("kill {$pid} 2>/dev/null");
    exec("kill -9 {$pid} 2>/dev/null");
    $_SESSION['success_message'] = "Process {$pid} killed.";
    header('Location: /admin/process-manager'); exit;
}
?>
<div class="card" style="margin-bottom:16px">
<form method="GET" action="/admin/process-manager" style="display:flex;gap:8px">
<input name="search" placeholder="Search processes..." value="<?php echo htmlspecialchars($search); ?>" style="flex:1;padding:8px 12px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.08);border-radius:6px;color:#fff;outline:none">
<button type="submit" class="btn btn-sm primary">🔍 Search</button>
<a href="/admin/process-manager" class="btn btn-sm secondary">Clear</a>
</form>
</div>

<div class="card" style="padding:0;overflow-x:auto">
<table style="font-size:11px">
<tr><th>PID</th><th>User</th><th>CPU%</th><th>MEM%</th><th>Command</th><th>Action</th></tr>
<?php foreach ($lines as $line): $parts = preg_split('/\s+/', trim($line), 11); if (count($parts) < 11) continue; ?>
<tr>
<td><?php echo $parts[1]; ?></td>
<td><?php echo htmlspecialchars($parts[0]); ?></td>
<td><?php echo $parts[2]; ?></td>
<td><?php echo $parts[3]; ?></td>
<td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($parts[10] ?? ''); ?></td>
<td><a href="/admin/process-manager?action=kill&pid=<?php echo $parts[1]; ?>" class="btn btn-sm" style="background:rgba(248,113,113,.15);color:#f87171;padding:2px 8px;font-size:10px" onclick="return confirm('Kill PID <?php echo $parts[1]; ?>?')">Kill</a></td>
</tr>
<?php endforeach; ?>
</table>
</div>
