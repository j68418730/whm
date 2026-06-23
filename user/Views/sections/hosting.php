<style>
.section-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:14px}
.section-card{background:rgba(8,16,28,.85);border:1px solid rgba(0,191,255,.08);border-radius:12px;padding:22px;text-align:center;text-decoration:none;color:#e0e0e0;transition:.2s}
.section-card:hover{transform:translateY(-3px);border-color:rgba(0,140,255,.3)}
.section-card .icon{font-size:32px;margin-bottom:8px}
.section-card .name{font-size:14px;font-weight:600}
.section-card .desc{font-size:11px;color:#64748b;margin-top:2px}
</style>
<h2>Hosting</h2>
<p style="color:#64748b;margin-bottom:20px">Manage your hosting services, files, databases, and more.</p>
<div class="section-grid">
<a href="/user/services" class="section-card"><span class="icon">🖥</span><div class="name">My Services</div><div class="desc">Active services overview</div></a>
<a href="/user/files" class="section-card"><span class="icon">📁</span><div class="name">File Manager</div><div class="desc">Manage website files</div></a>
<a href="/user/ftp" class="section-card"><span class="icon">📤</span><div class="name">FTP Accounts</div><div class="desc">FTP access</div></a>
<a href="/user/databases" class="section-card"><span class="icon">🗄️</span><div class="name">Databases</div><div class="desc">MySQL databases</div></a>
<a href="/pma_autologin.php" target="_blank" class="section-card"><span class="icon">🐘</span><div class="name">phpMyAdmin</div><div class="desc">Database manager</div></a>
<a href="/user/ssl" class="section-card"><span class="icon">🔒</span><div class="name">SSL Certificates</div><div class="desc">SSL management</div></a>
<a href="/user/usage" class="section-card"><span class="icon">📊</span><div class="name">Resource Usage</div><div class="desc">Disk & bandwidth</div></a>
<a href="/user/apps/node" class="section-card"><span class="icon">🟢</span><div class="name">Node.js Apps</div><div class="desc">Node.js applications</div></a>
<a href="/user/apps/python" class="section-card"><span class="icon">🐍</span><div class="name">Python Apps</div><div class="desc">Python applications</div></a>
</div>
