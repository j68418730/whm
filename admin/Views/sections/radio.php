<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Radio</h2>
<p style="color:#64748b;margin-bottom:20px">Manage radio streams, DJ accounts, AutoDJ, and radio settings.</p>

<div class="section-grid">
<a href="/admin/radio_dashboard" class="section-card"><div class="icon">📻</div><div class="name">Radio Dashboard</div><div class="desc">Radio station overview</div></a>
<a href="/admin/streams" class="section-card"><div class="icon">🔊</div><div class="name">Streams</div><div class="desc">Audio stream management</div></a>
<a href="/admin/djs" class="section-card"><div class="icon">🎧</div><div class="name">DJ Accounts</div><div class="desc">DJ user management</div></a>
<a href="/admin/autodj" class="section-card"><div class="icon">🤖</div><div class="name">AutoDJ</div><div class="desc">Automated playlist DJ</div></a>
<a href="/admin/radio_dashboard" class="section-card"><div class="icon">🪟</div><div class="name">Radio Widgets</div><div class="desc">Embeddable radio widgets</div></a>
<a href="/admin/streaming" class="section-card"><div class="icon">🔀</div><div class="name">Streaming Engine</div><div class="desc">SHOUTcast/Icecast multi-engine</div></a>
<a href="/admin/radiosettings" class="section-card"><div class="icon">⚙️</div><div class="name">Radio Settings</div><div class="desc">Radio configuration</div></a>
</div>
