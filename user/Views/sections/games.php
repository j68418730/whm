<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Games</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your game servers and configurations.</p>
<div class="section-grid">
<a href="/user/games" class="section-card"><span class="icon">🎮</span><div class="name">My Servers</div><div class="desc">Server overview</div></a>
<a href="/user/games/console" class="section-card"><span class="icon">🖥️</span><div class="name">Console</div><div class="desc">Server console access</div></a>
<a href="/user/games/files" class="section-card"><span class="icon">📁</span><div class="name">File Manager</div><div class="desc">Manage server files</div></a>
<a href="/user/games/backups" class="section-card"><span class="icon">💾</span><div class="name">Backups</div><div class="desc">Backup & restore</div></a>
<a href="/user/games/schedules" class="section-card"><span class="icon">⏰</span><div class="name">Schedules</div><div class="desc">Automated tasks</div></a>
</div>
