<div class="stats-grid" style="margin-bottom:20px">
<div class="stat-card"><h3>Version</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($mysqlStats['mysql_version'] ?? ''); ?></div></div>
<div class="stat-card"><h3>Databases</h3><div class="value"><?php echo $mysqlStats['total_databases'] ?? 0; ?></div></div>
<div class="stat-card"><h3>DB Users</h3><div class="value"><?php echo $mysqlStats['total_db_users'] ?? 0; ?></div></div>
<div class="stat-card"><h3>Size</h3><div class="value"><?php echo $mysqlStats['database_size'] ?? 0; ?> MB</div></div>
<div class="stat-card"><h3>Queries/s</h3><div class="value" style="font-size:20px"><?php echo number_format($mysqlStats['queries_per_second'] ?? 0); ?></div></div>
<div class="stat-card"><h3>Slow</h3><div class="value"><?php echo $mysqlStats['slow_queries'] ?? 0; ?></div></div>
</div>

<div class="card" style="text-align:center;padding:30px;max-width:500px;margin:0 auto 20px">
<div style="font-size:48px;margin-bottom:12px">🐬</div>
<h3 style="color:var(--accent);margin-bottom:8px">phpMyAdmin</h3>
<p style="color:var(--text-secondary);font-size:13px;margin-bottom:16px">Auto-login with root credentials to manage databases, users, and permissions</p>
<a href="/pma_signon.php" target="_blank" style="display:inline-block;padding:14px 40px;background:linear-gradient(135deg,#008cff,#3bb8ff);color:#fff;border-radius:10px;text-decoration:none;font-weight:700;font-size:16px">Open phpMyAdmin →</a>
</div>

<div class="card" style="margin-top:16px">
<h3 style="color:var(--accent);margin-bottom:8px">Database Access</h3>
<p style="color:var(--text-secondary);margin-top:8px;font-size:13px">Each hosting account has its own database credentials. Use phpMyAdmin above to manage databases directly with root access.</p>
</div>