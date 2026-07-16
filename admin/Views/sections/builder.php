<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:16px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:24px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3);box-shadow:0 8px 30px rgba(0,140,255,.08)}
.section-card .icon{font-size:36px;margin-bottom:8px}
.section-card .name{font-size:15px;font-weight:600;margin-bottom:4px}
.section-card .count{font-size:28px;font-weight:800;color:var(--accent);margin-bottom:2px}
.section-card .desc{font-size:11px;color:#64748b}
</style>

<h2>Website Builder</h2>
<p style="color:#64748b;margin-bottom:20px">Manage website builder dashboard, sites, templates, and themes.</p>

<div class="section-grid">
<a href="/admin/websitebuilder" class="section-card"><div class="icon">🏗️</div><div class="name">Dashboard</div><div class="desc">Builder overview</div></a>
<a href="/admin/websitebuilder/sites" class="section-card"><div class="icon">🌐</div><div class="name">Sites</div><div class="desc">Published websites</div></a>
<a href="/admin/websitebuilder/templates" class="section-card"><div class="icon">📐</div><div class="name">Templates</div><div class="desc">Site templates</div></a>
<a href="/admin/websitebuilder/themes" class="section-card"><div class="icon">🎨</div><div class="name">Themes</div><div class="desc">Visual themes</div></a>
<a href="/admin/websitebuilder/ai" class="section-card" style="border-color:rgba(168,85,247,.3)"><div class="icon">🤖</div><div class="name">AI Builder</div><div class="desc">AI site generator</div></a>
</div>
