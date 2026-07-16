<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Site Builder</h2>
<p style="color:#64748b;margin-bottom:20px">Build and manage your websites with our site builder.</p>
<div class="section-grid">
<a href="/user/builder" class="section-card"><span class="icon">🏗️</span><div class="name">My Websites</div><div class="desc">Manage your sites</div></a>
<a href="/user/builder/templates" class="section-card"><span class="icon">📐</span><div class="name">Templates</div><div class="desc">Site templates</div></a>
<a href="/user/builder/themes" class="section-card"><span class="icon">🎨</span><div class="name">Themes</div><div class="desc">Customize appearance</div></a>
<a href="/user/builder/media" class="section-card"><span class="icon">🖼️</span><div class="name">Media Manager</div><div class="desc">Upload & manage media</div></a>
<a href="/user/websites/ai" class="section-card" style="border-color:rgba(168,85,247,.3)"><span class="icon">🤖</span><div class="name">AI Builder</div><div class="desc">AI site generator</div></a>
</div>
