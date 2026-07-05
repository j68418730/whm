<style>
.wb-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px}
.wb-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:10px;padding:20px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.15s}
.wb-card:hover{transform:translateY(-2px);border-color:rgba(0,140,255,.3)}
.wb-card .icon{font-size:32px;margin-bottom:8px}
.wb-card .name{font-size:13px;font-weight:600}
</style>
<h2><i class="bi bi-bricks"></i> Website Builder</h2>
<p style="color:#64748b;margin-bottom:16px">Build and manage your websites with the Planet-Hosts website builder.</p>

<?php $wbSites = []; try { $wbSites = $this->db->table('wb_sites')->where('user_id', $hosting->id ?? 0)->get() ?: []; } catch(\Exception $e) {} ?>

<div class="wb-grid">
<a href="/user/websites" class="wb-card"><span class="icon">🌐</span><div class="name">My Websites</div></a>
<a href="/user/websites/create" class="wb-card"><span class="icon">➕</span><div class="name">New Site</div></a>
<a href="/user/websites/ai" class="wb-card" style="border-color:rgba(139,92,246,.3);background:rgba(139,92,246,.06)"><span class="icon">🤖</span><div class="name">AI Builder</div></a>
<a href="/user/websites?tab=templates" class="wb-card"><span class="icon">📐</span><div class="name">Templates</div></a>
<a href="/user/websites?tab=themes" class="wb-card"><span class="icon">🎨</span><div class="name">Themes</div></a>
<a href="/user/websites?tab=media" class="wb-card"><span class="icon">🖼️</span><div class="name">Media</div></a>
<a href="/user/files" class="wb-card"><span class="icon">📁</span><div class="name">File Manager</div></a>
<a href="/user/websites/ai/memory" class="wb-card"><span class="icon">🧠</span><div class="name">AI Memory</div></a>
</div>

<div class="card" style="margin-top:16px">
<h3>My Websites <span style="font-size:12px;color:#64748b;font-weight:400">(<?php echo count($wbSites); ?>)</span></h3>
<?php if (empty($wbSites)): ?>
<p style="color:#64748b;font-size:13px;text-align:center;padding:20px">No websites yet. <a href="/user/websites/create" style="color:#0A84FF">Create one now</a> or use the <a href="/user/websites/ai" style="color:#8b5cf6">AI Builder</a>.</p>
<?php else: ?>
<table><thead><tr><th>Name</th><th>Domain</th><th>Status</th><th>Theme</th><th>Actions</th></tr></thead>
<tbody><?php foreach ($wbSites as $site): ?>
<tr><td><strong><?php echo htmlspecialchars($site->name ?? 'Site'); ?></strong></td>
<td><code style="font-size:10px"><?php echo htmlspecialchars($site->domain ?? '—'); ?></code></td>
<td><span style="color:<?php echo ($site->status??'draft')==='published'?'#4ade80':'#facc15'; ?>">● <?php echo ucfirst($site->status ?? 'draft'); ?></span></td>
<td><?php echo htmlspecialchars($site->theme_id ? 'Theme #'.$site->theme_id : 'Default'); ?></td>
<td><a href="/user/websites/<?php echo $site->id; ?>" class="btn btn-sm primary" style="font-size:10px;padding:4px 10px">Manage</a>
<a href="/user/websites/<?php echo $site->id; ?>/editor/<?php echo $site->id; ?>" class="btn btn-sm secondary" style="font-size:10px;padding:4px 10px">Edit</a></td></tr>
<?php endforeach; ?></tbody></table>
<?php endif; ?>
</div>
