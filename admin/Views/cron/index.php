<?php if (isset($_SESSION['success_message'])): ?>
<div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success_message'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success_message']); ?></div>
<?php endif; ?>
<div class="card" style="max-width:600px;margin-bottom:20px">
<form method="POST" action="/admin/cron"><div style="display:flex;gap:6px;flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:0 0 50px"><label>Min</label><input name="minute" value="*" style="width:50px"></div>
<div class="form-group" style="flex:0 0 50px"><label>Hour</label><input name="hour" value="*" style="width:50px"></div>
<div class="form-group" style="flex:0 0 50px"><label>Day</label><input name="day" value="*" style="width:50px"></div>
<div class="form-group" style="flex:0 0 50px"><label>Mon</label><input name="month" value="*" style="width:50px"></div>
<div class="form-group" style="flex:0 0 50px"><label>WDay</label><input name="weekday" value="*" style="width:50px"></div>
<div class="form-group" style="flex:2"><label>Command</label><input name="command" required placeholder="php /path/to/script.php"></div>
<div class="form-group"><button type="submit" class="btn primary">Add Cron</button></div>
</div></form>
</div>
<table><tr><th>Schedule</th><th>Command</th><th>Created</th><th></th></tr>
<?php if (!empty($crons)): foreach ($crons as $c): ?>
<tr><td style="font-family:monospace"><?php echo "{$c->minute} {$c->hour} {$c->day} {$c->month} {$c->weekday}"; ?></td>
<td style="font-family:monospace;font-size:13px"><?php echo htmlspecialchars($c->command); ?></td><td><?php echo $c->created_at ?? '-'; ?></td>
<td><a href="/admin/cron/delete/<?php echo $c->id; ?>" class="btn btn-sm danger" onclick="return confirm('Delete?')">✕</a></td></tr>
<?php endforeach; else: ?><tr><td colspan="4" style="text-align:center;padding:20px;color:#64748b">No cron jobs yet.</td></tr>
<?php endif; ?></table>
