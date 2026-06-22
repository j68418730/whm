<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>System</h2>
<p style="color:#64748b;margin-bottom:20px">Manage system settings, server configuration, plugins, and maintenance tools.</p>

<div class="section-grid">
<a href="/admin/settings" class="section-card"><div class="icon">⚙️</div><div class="name">Settings</div><div class="desc">System configuration</div></a>
<a href="/admin/settings/general" class="section-card"><div class="icon">🏠</div><div class="name">General</div><div class="desc">General settings</div></a>
<a href="/admin/settings/company" class="section-card"><div class="icon">🏢</div><div class="name">Company</div><div class="desc">Company information</div></a>
<a href="/admin/settings/smtp" class="section-card"><div class="icon">📧</div><div class="name">SMTP</div><div class="desc">Mail server settings</div></a>
<a href="/admin/settings/security" class="section-card"><div class="icon">🔒</div><div class="name">Security</div><div class="desc">Security settings</div></a>
<a href="/admin/settings/api" class="section-card"><div class="icon">🔌</div><div class="name">API</div><div class="desc">API configuration</div></a>
<a href="/admin/settings/localization" class="section-card"><div class="icon">🌍</div><div class="name">Localization</div><div class="desc">Language & region</div></a>
<a href="/admin/serverconfig" class="section-card"><div class="icon">🖥️</div><div class="name">Server Config</div><div class="desc">Server configuration</div></a>
<a href="/admin/ip" class="section-card"><div class="icon">📶</div><div class="name">IP Management</div><div class="desc">IP address pools</div></a>
<a href="/admin/licensing" class="section-card"><div class="icon">📜</div><div class="name">Licensing</div><div class="desc">License management</div></a>
<a href="/admin/plugins" class="section-card"><div class="icon">🧩</div><div class="name">Plugins</div><div class="desc">Plugin management</div></a>
<a href="/admin/installers" class="section-card"><div class="icon">📥</div><div class="name">One-Click Installer</div><div class="desc">Auto-install applications</div></a>
<a href="/admin/todo" class="section-card"><div class="icon">✅</div><div class="name">Todo Board</div><div class="desc">Task management</div></a>
<a href="/admin/process-manager" class="section-card"><div class="icon">⚡</div><div class="name">Process Manager</div><div class="desc">Background processes</div></a>
<a href="/admin/cron" class="section-card"><div class="icon">⏰</div><div class="name">Cron Jobs</div><div class="desc">Scheduled tasks</div></a>
<a href="/admin/automation" class="section-card"><div class="icon">🤖</div><div class="name">Queue Manager</div><div class="desc">Automation queue</div></a>
<a href="/admin/filesystem" class="section-card"><div class="icon">📁</div><div class="name">Filesystem</div><div class="desc">File system manager</div></a>
<a href="/admin/backup" class="section-card"><div class="icon">💾</div><div class="name">Backup Manager</div><div class="desc">System backups</div></a>
<a href="/admin/themes" class="section-card"><div class="icon">🎨</div><div class="name">Theme Manager</div><div class="desc">Admin theme management</div></a>
</div>
