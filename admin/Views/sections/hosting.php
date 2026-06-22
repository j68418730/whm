<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Hosting</h2>
<p style="color:#64748b;margin-bottom:20px">Manage hosting services, mail, databases, FTP, and server configuration.</p>

<div class="section-grid">
<a href="/admin/email" class="section-card"><div class="icon">✉️</div><div class="name">Email</div><div class="desc">Email accounts & routing</div></a>
<a href="/admin/mysql" class="section-card"><div class="icon">🗄️</div><div class="name">Databases</div><div class="desc">MySQL & database management</div></a>
<a href="/admin/ftp" class="section-card"><div class="icon">📁</div><div class="name">FTP</div><div class="desc">FTP accounts & access</div></a>
<a href="/admin/ssl" class="section-card"><div class="icon">🔒</div><div class="name">SSL</div><div class="desc">SSL certificates & HTTPS</div></a>
<a href="/admin/backup" class="section-card"><div class="icon">💾</div><div class="name">Backups</div><div class="desc">Backup & restore</div></a>
<a href="/admin/cron" class="section-card"><div class="icon">⏰</div><div class="name">Cron Jobs</div><div class="desc">Scheduled tasks</div></a>
<a href="/admin/server" class="section-card"><div class="icon">📊</div><div class="name">Resource Usage</div><div class="desc">Server resource statistics</div></a>
<a href="/admin/server/terminal" class="section-card"><div class="icon">💻</div><div class="name">Web Terminal</div><div class="desc">Browser-based terminal</div></a>
<a href="/admin/apache" class="section-card"><div class="icon">🌐</div><div class="name">Apache</div><div class="desc">Apache configuration</div></a>
<a href="/admin/php" class="section-card"><div class="icon">🐘</div><div class="name">PHP</div><div class="desc">PHP settings & extensions</div></a>
</div>
