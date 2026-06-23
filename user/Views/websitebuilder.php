<style>
.wb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
.wb-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:20px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.15s}
.wb-card:hover{transform:translateY(-2px);border-color:rgba(0,140,255,.3)}
.wb-card .icon{font-size:32px;margin-bottom:8px}
.wb-card .name{font-size:13px;font-weight:600}
</style>
<h2>🏗️ Website Builder</h2>
<p style="color:#64748b;margin-bottom:16px">Build and manage your websites with the Planet-Hosts website builder.</p>

<?php $wbSites = []; try { $wbSites = $this->db->table('website_builder_sites')->where('user_id', $hosting->id ?? 0)->get() ?: []; } catch(\Exception $e) {} ?>

<div class="wb-grid">
<a href="/user/websitebuilder" class="wb-card"><span class="icon">🌐</span><div class="name">My Websites</div></a>
<a href="/user/websitebuilder/themes" class="wb-card"><span class="icon">🎨</span><div class="name">Themes</div></a>
<a href="/user/websitebuilder" class="wb-card"><span class="icon">📄</span><div class="name">Pages</div></a>
<a href="/user/websitebuilder" class="wb-card"><span class="icon">🧩</span><div class="name">Widgets</div></a>
<a href="/user/websitebuilder" class="wb-card"><span class="icon">📋</span><div class="name">Menus</div></a>
<a href="/user/websitebuilder" class="wb-card"><span class="icon">📝</span><div class="name">Forms</div></a>
<a href="/user/websitebuilder" class="wb-card"><span class="icon">📈</span><div class="name">SEO Tools</div></a>
<a href="/user/files" class="wb-card"><span class="icon">🖼️</span><div class="name">Media Manager</div></a>
</div>

<div class="card" style="margin-top:16px">
<h3>My Websites <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo count($wbSites); ?>)</span></h3>
<?php if (empty($wbSites)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No websites created yet. Use the <strong>Website Builder Plugin</strong> to create one.</p>
<?php else: ?>
<table class="table"><thead><tr><th>Name</th><th>Status</th><th></th></tr></thead>
<tbody><?php foreach ($wbSites as $site): ?>
<tr><td><?php echo htmlspecialchars($site->name ?? 'Site'); ?></td><td><span style="color:#4ade80">● Active</span></td>
<td><a href="/user/websitebuilder" class="btn btn-sm btn-primary">Edit</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
</div>
