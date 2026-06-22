<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Login Attempts</h3><div class="value"><?php echo count($loginAttempts ?? []); ?></div></div>
<div class="stat-card"><h3>Successful</h3><div class="value"><?php echo (int)($successfulLogins ?? 0); ?></div></div>
<div class="stat-card"><h3>Failed</h3><div class="value"><?php echo (int)($failedLogins ?? 0); ?></div></div>
<div class="stat-card"><h3>Cron Jobs</h3><div class="value"><?php echo count($cronJobs ?? []); ?></div></div>
</div>

<div class="card" style="margin-bottom:20px">
<h3 style="margin-bottom:12px;color:var(--accent)">Recent Login Activity</h3>
<table>
<tr><th>User</th><th>IP</th><th>Status</th><th>Time</th></tr>
<?php if (!empty($loginAttempts)): foreach ($loginAttempts as $row): ?>
<tr>
<td><?php echo htmlspecialchars($row->username ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td style="font-family:monospace"><?php echo htmlspecialchars($row->ip_address ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo !empty($row->success) ? 'Success' : 'Failed'; ?></td>
<td><?php echo htmlspecialchars($row->created_at ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="4" style="text-align:center;color:var(--text-secondary);padding:20px">No activity captured yet.</td></tr>
<?php endif; ?>
</table>
</div>

<div class="card">
<h3 style="margin-bottom:12px;color:var(--accent)">Scheduled Jobs</h3>
<table>
<tr><th>Command</th><th>Schedule</th><th>Notes</th></tr>
<?php if (!empty($cronJobs)): foreach ($cronJobs as $job): ?>
<tr>
<td style="font-family:monospace"><?php echo htmlspecialchars($job->command ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars(trim(($job->minute ?? '*') . ' ' . ($job->hour ?? '*') . ' ' . ($job->day ?? '*') . ' ' . ($job->month ?? '*') . ' ' . ($job->weekday ?? '*')), ENT_QUOTES, 'UTF-8'); ?></td>
<td><?php echo htmlspecialchars($job->description ?? 'Scheduled task', ENT_QUOTES, 'UTF-8'); ?></td>
</tr>
<?php endforeach; else: ?>
<tr><td colspan="3" style="text-align:center;color:var(--text-secondary);padding:20px">No scheduled jobs found.</td></tr>
<?php endif; ?>
</table>
</div>
