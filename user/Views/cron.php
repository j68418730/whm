<style>
.cron-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:12px;margin-bottom:16px}
.cron-stat{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:14px;text-align:center}
.cron-stat .num{font-size:22px;font-weight:800}
.cron-stat .lbl{font-size:11px;color:#64748b}
input,select{width:100%;padding:7px 10px;border-radius:6px;border:1px solid rgba(255,255,255,.1);background:rgba(0,0,0,.3);color:#e0e0e0;font-size:12px;outline:none}
</style>
<h2>⏰ Cron Jobs</h2>
<p style="color:#64748b;margin-bottom:16px">Schedule automated tasks for your hosting account.</p>
<?php
$cronFile = '/home/' . ($hosting->username ?? '') . '/crontab.txt';
$existingJobs = [];
if (is_file($cronFile)) {
    $lines = file($cronFile, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line && !str_starts_with($line, '#')) $existingJobs[] = $line;
    }
}
$cronCount = count($existingJobs);
?>
<div class="cron-grid">
<div class="cron-stat"><div class="num" style="color:#0A84FF"><?php echo $cronCount; ?></div><div class="lbl">Active Jobs</div></div>
<div class="cron-stat"><div class="num" style="color:#4ade80">0</div><div class="lbl">Running</div></div>
<div class="cron-stat"><div class="num" style="color:#facc15">--</div><div class="lbl">Last Run</div></div>
</div>
<div class="card">
<h3>➕ New Cron Job</h3>
<form method="POST" action="/user/cron/save" style="margin-top:10px">
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Minute</label>
<input name="minute" value="*/5" placeholder="*/5"></div>
<div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr;gap:8px;margin-bottom:8px">
<div><label style="font-size:11px;color:#64748b">Hour</label><input name="hour" value="*"></div>
<div><label style="font-size:11px;color:#64748b">Day</label><input name="day" value="*"></div>
<div><label style="font-size:11px;color:#64748b">Month</label><input name="month" value="*"></div>
<div><label style="font-size:11px;color:#64748b">Weekday</label><input name="weekday" value="*"></div>
<div><label style="font-size:11px;color:#64748b">Common</label>
<select onchange="if(this.value){var p=this.value.split(' ');this.form.minute.value=p[0];this.form.hour.value=p[1];this.form.day.value=p[2];this.form.month.value=p[3];this.form.weekday.value=p[4]}">
<option value="">Select...</option><option value="*/5 * * * *">Every 5 min</option><option value="*/10 * * * *">Every 10 min</option><option value="0 * * * *">Every hour</option><option value="0 0 * * *">Daily</option><option value="0 0 * * 0">Weekly</option><option value="0 0 1 * *">Monthly</option>
</select></div>
</div>
<div style="margin-bottom:8px"><label style="font-size:11px;color:#64748b">Command</label>
<input name="command" placeholder="e.g. /usr/bin/php /home/<?php echo htmlspecialchars($hosting->username ?? ''); ?>/public_html/cron.php"></div>
<button type="submit" class="btn btn-sm btn-primary">➕ Add Cron Job</button>
</form>
</div>
<div class="card">
<h3>Cron Jobs (<?php echo $cronCount; ?>)</h3>
<?php if ($cronCount === 0): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No cron jobs configured.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Schedule</th><th>Command</th><th></th></tr></thead>
<tbody><?php foreach ($existingJobs as $job): $parts = explode(' ', $job); $schedule = implode(' ', array_slice($parts, 0, 5)); $cmd = implode(' ', array_slice($parts, 5)); ?>
<tr><td><code><?php echo htmlspecialchars($schedule); ?></code></td><td style="font-size:12px"><?php echo htmlspecialchars($cmd); ?></td><td><a href="/user/cron/delete?job=<?php echo urlencode($job); ?>" class="btn btn-sm btn-danger">✕</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
</div>
