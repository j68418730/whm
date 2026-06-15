<div class="stats-grid">
<div class="stat-card"><h3>Version</h3><div class="value" style="font-size:18px"><?php echo htmlspecialchars($mysqlStats['mysql_version']); ?></div></div>
<div class="stat-card"><h3>Databases</h3><div class="value"><?php echo $mysqlStats['total_databases']; ?></div></div>
<div class="stat-card"><h3>DB Users</h3><div class="value"><?php echo $mysqlStats['total_db_users']; ?></div></div>
<div class="stat-card"><h3>Size</h3><div class="value"><?php echo $mysqlStats['database_size']; ?> MB</div></div>
<div class="stat-card"><h3>Queries</h3><div class="value" style="font-size:20px"><?php echo number_format($mysqlStats['queries_per_second']); ?></div></div>
<div class="stat-card"><h3>Slow</h3><div class="value"><?php echo $mysqlStats['slow_queries']; ?></div></div>
</div>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
<a href="/admin/mysql/restart" class="btn secondary">🔄 Restart MariaDB</a>
<a href="/pma_signon.php" target="_blank" class="btn primary">🔑 phpMyAdmin Auto-Login</a>
</div>
<div class="card"><h3 style="color:var(--accent)">Database Access</h3>
<p style="color:var(--text-secondary);margin-top:8px">Each hosting account has its own database credentials. Click phpMyAdmin above to manage databases directly with root access.</p>
</div>
